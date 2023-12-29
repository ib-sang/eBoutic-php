<?php

namespace Controllers\Database;

use PDO;

class Table
{

    /**
     * table
     *
     * @var string
     */
    protected $table;

    /**
     * entity
     *
     * @var string
     */
    protected $entity = \stdClass::class;

    /**
     * pdo
     *
     * @var PDO
     */
    private $pdo;

    /**
     * pouces
     *
     * @var int
     */
    protected $pouces = 0;

    /**
     * connection
     *
     * @var mixed
     */
    protected $connection;

    /**
     * __construct
     *
     * @param  PDO $pdo
     * @return void
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * findLatest
     * return last element by field, value
     *
     * @param string $field
     * @param string $value
     * @return stdClass
     */
    public function findLatestBy(string $field, string $value)
    {
        return $this->makeQuery()
            ->select("*, CURTIME() as time")
            ->where("$field = $value")
            ->order("created_at DESC")
            ->fetchOrFail()
            ;
    }

    /**
     * findLatest
     * return last element
     *
     * @return stdClass
     */
    public function findLatest()
    {
        return $this->makeQuery()->order("created_at DESC")->fetchOrFail();
    }

    /**
     * findLatest
     * return last element
     *
     * @return stdClass
     */
    public function findLatestDay()
    {
        return $this->makeQuery()
            ->where("created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY_HOUR )")
            ->order("created_at DESC")
            ->fetchOrFail()
            ;
    }

    /**
     * findLatest
     * return last element
     *
     * @return stdClass
     */
    public function findLatestDayId(int $id, int $users_id)
    {
        return $this->makeQuery()
                ->where("created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY_HOUR )")
                ->where("users_id = $users_id")
                ->where("enterprises_id = $id")
                ->order("created_at DESC")->fetchOrFail();
    }

    /**
     * findList
     *
     * @return array
     */
    public function findList(): array
    {
        $queryString = "SELECT id, name FROM $this->table";
        
        $results = $this->pdo->query("$queryString")->fetchAll(PDO::FETCH_NUM);
        $list = [];
        foreach ($results as $result) {
            $list[$result[0]] = $result[1];
        }
        return $list;
    }

    /**
     * makeQuery
     *
     * @return Query
     */
    protected function makeQuery(): Query
    {
        return (new Query($this->pdo))->from($this->table, $this->table[0])->into($this->entity);
    }

    /**
     * findAll
     *
     * @return void
     */
    public function findAll()
    {
        return $this->makeQuery();
    }

    /**
     * fetch
     *
     * @return void
     */
    public function fetchAllList()
    {
        return $this->pdo->query("SELECT * FROM $this->table")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * findByAll select all post where field equals value
     *
     * @param  string $field
     * @param  string $value
     * @return void
     */
    public function findByAll(string $field, string $value)
    {
        return $this->makeQuery()
                ->where("$field=:field")
                ->params(['field' => $value])
                ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * findBy select one post
     *
     * @param  string $field
     * @param  string $value
     * @return mixed
     */
    public function findBy(string $field, string $value):mixed
    {
        return $this->makeQuery()
                ->where("$field=:field")
                ->order("created_at DESC")
                ->params(['field' => $value])
                ->fetchOrFail();
    }

    public function findUser(?int $id)
    {
        return $this->makeQuery()
                ->where("id=$id")
                ->select("*, DATE_FORMAT(CURRENT_TIMESTAMP, '%Y-%m-%d %h:%i:%s') as day")
                ->fetchOrFail();
    }

    /**
     * find
     *
     * @param  int/null $id
     * @return mixed
     */
    public function find(?int $id):mixed
    {
        return $this->makeQuery()->where("id=$id")->fetchOrFail();
    }

    /**
     * count
     *
     * @return int
     */
    public function count(): int
    {
        return $this->makeQuery()->count();
    }

    public function countByColum(string $field): int
    {
        return $this->makeQuery()->countByColum($field);
    }

    /**
     * update
     *
     * @param  int $id
     * @param  array $params
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldQuery = $this->buildFieldQuery($params);
        $params['id'] = $id;
        
        $statement = $this->pdo->prepare("UPDATE $this->table SET $fieldQuery WHERE id=:id");
        $fieldQuery=$this->buildFieldQuery($params);
        $params['id']=$id;
        $statement=$this->pdo->prepare("UPDATE $this->table SET $fieldQuery WHERE id=:id");
        if (array_key_exists('created_up', $params)) {
            $params['created_up'] = $params['created_up']->format('Y-m-d H:i:s');
        }
        if (array_key_exists('created_at', $params)) {
            $params['created_at'] = $params['created_at']->format('Y-m-d H:i:s');
        }
        //var_dump($params);
        //die();
        return $statement->execute($params);
    }

    /**
     * insert
     *
     * @param  array $params
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = array_keys($params);
        $value = array_map(function ($field) {
            return ':' . $field;
        }, $fields);

        $fieldQuery = $this->buildFieldQuery($params);
        $statement = $this->pdo
                ->prepare("INSERT INTO $this->table (" . join(',', $fields) . ") VALUES (" . join(',', $value) . ")");
        
        $fieldQuery=$this->buildFieldQuery($params);
        // if (!array_key_exists('published', $params)) {
        //     $params['published']=true;
        //     $fieldQuery.=', published=:published';
        // }
     
        $statement = $this->pdo
                ->prepare("INSERT INTO $this->table (".join(',', $fields).") VALUES (".join(',', $value).")");
        
        if (array_key_exists('created_up', $params)) {
            $params['created_up'] = $params['created_up']->format('Y-m-d H:i:s');
        }
        if (array_key_exists('created_at', $params)) {
            $params['created_at'] = $params['created_at']->format('Y-m-d H:i:s');
        }
        // var_dump($statement,$params); die();
        return $statement->execute($params);
    }

    /**
     * delete
     *
     * @param  int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare("DELETE FROM $this->table WHERE id=?");
        return $statement->execute([$id]);
    }

    private function buildFieldQuery(array $params)
    {
        return join(', ', array_map(function ($field) {
            return "$field=:$field";
        }, array_keys($params)));
    }

    /**
     * Get pdo
     *
     * @return  PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Get table
     *
     * @return  string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get entity
     *
     * @return  string
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
