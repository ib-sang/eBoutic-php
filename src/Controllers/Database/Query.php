<?php

namespace Controllers\Database;

use PDO;
use Traversable;
use IteratorAggregate;
use Pagerfanta\Pagerfanta;
use Controllers\Database\PaginatedQuery;

class Query implements IteratorAggregate
{

    private $select;

    public $from;

    private $where=[];

    private $group;

    private $entity;

    private $order=[];

    private $limit;

    private $in = [];

    private $table = "";

    private $records;

    
    /**
     * pdo
     *
     * @var PDO
     */
    private $pdo;

    private $params=[];
    
    private $joins;
    
    /**
     * __construct
     *
     * @param  PDO $pdo
     * @return void
     */
    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo=$pdo;
    }
    
    /**
     * from
     *
     * @param  string $table
     * @param  sring/null $aliace
     * @return self
     */
    public function from(string $table, ?string $aliace = null):self
    {
        if ($aliace) {
            $this->from[$table]=$aliace;
        } else {
            $this->from[]=$table;
        }
        return $this;
    }

    public function in($tab, string $table):self
    {
        $this->in = $tab;
        $this->table = $table;
        return $this;
    }

    
    /**
     * select
     *
     * @param  string $fields
     * @return self
     */
    public function select(string ...$fields):self
    {
        $this->select=$fields;
        return $this;
    }

    
    /**
     * limit
     *
     * @param  int $length
     * @param  int $offset
     * @return self
     */
    public function limit(int $length, int $offset = 0):self
    {
        $this->limit="$offset, $length";
        return $this;
    }

        
    /**
     * order
     *
     * @param  string $orders
     * @return self
     */
    public function order(string $orders):self
    {
        $this->order[]=$orders;
        return $this;
    }

    /**
     * group
     *
     * @param  string $column
     * @return self
     */
    public function group(string $column):self
    {
        $this->group=$column;
        return $this;
    }
    
    /**
     * join
     *
     * @param  string $table
     * @param  string $condition
     * @param  string $type
     * @return self
     */
    public function join(string $table, string $condition, string $type = 'left'):self
    {
        $this->joins[$type][]=[$table,$condition];
        return $this;
    }
    
    /**
     * where
     *
     * @param  string $condition
     * @return self
     */
    public function where(string ...$condition):self
    {
        $this->where=array_merge($this->where, $condition);
        return $this;
    }
        
    /**
     * count
     *
     * @return int
     */
    public function countDif():int
    {
        $query = clone $this;
        $table = current($this->from);
        $result = $query->select("COUNT($table.id)")->group('disease')->execute()->fetchColumn();
        if (is_null($result)) {
            return 0;
        }
        return $result;
    }

    public function countByColum(string $field)
    {
        $query = clone $this;
        $table = current($this->from);
        $result = $query->select("SUM($table.$field)")->execute()->fetchColumn();
        if (is_null($result)) {
            return 0;
        }
        return $result;
    }

    /**
     * paginate
     *
     * @param  int $perPage
     * @param  int $currentPage
     * @return Pagerfanta
     */
    public function paginate(int $perPage, int $currentPage = 1):Pagerfanta
    {
        $paginator=new PaginatedQuery($this);
        return  (new Pagerfanta($paginator))->setMaxPerPage($perPage)->setCurrentPage($currentPage);
    }

    /**
     * count
     *
     * @return int
     */
    public function count():int
    {
        $query = clone $this;
        $table = current($this->from);
        $result = $query->select("COUNT($table.id)")->execute()->fetchColumn();
        if (is_null($result)) {
            return 0;
        }
        return $result;
    }
    
    /**
     * fetchAll
     *
     * @return QueryResult
     */
    public function fetchAll():QueryResult
    {
        return new QueryResult($this->records=$this->execute()->fetchAll(PDO::FETCH_ASSOC), $this->entity);
    }
    
    /**
     * params
     *
     * @param  array $params
     * @return self
     */
    public function params(array $params):self
    {
        $this->params=array_merge($this->params, $params);
        return $this;
    }
        
    /**
     * fetch
     *
     * @return void
     */
    public function fetch():mixed
    {
        
        $record = $this->execute()->fetch(PDO::FETCH_ASSOC);
        if ($record===false) {
            return false;
        }
        if ($this->entity) {
            return Hydrator::hydrate($record, $this->entity);
        }
        return $record;
    }

    /**
     * fetchOrFail
     *
     * @return void
     */
    public function fetchOrFail():mixed
    {
        $record = $this->fetch();
        if ($record==false) {
            // throw new NoRecordException();
            return false;
        }
        return $record;
    }
    
    /**
     * into
     *
     * @param  string $entity
     * @return self
     */
    public function into($entity):self
    {
        $this->entity=$entity;
        return $this;
    }
    
    public function __toString()
    {
        $parts=['SELECT'];
        if ($this->select) {
            $parts[]=join(', ', $this->select);
        } else {
            $parts[]='*';
        }
        $parts[]='FROM';
        $parts[]=$this->buildFrom();
        if (!empty($this->joins)) {
            foreach ($this->joins as $type => $join) {
                foreach ($join as [$table,$condition]) {
                    $parts[]=strtoupper($type)." JOIN $table ON $condition";
                }
            }
        }
        if (!empty($this->where)) {
            $parts[]='WHERE';
            $parts[]="(".join(') AND (', $this->where).")";
        }
        if (!empty($this->order)) {
            $parts[]='ORDER BY';
                $parts[]=join(',', $this->order);
        }
        if ($this->limit) {
            $parts[]='LIMIT '.$this->limit;
        }
        if (!empty($this->in)) {
            $table = $this->table;
            $parts[] = "WHERE $this->table.id IN (".implode(',', $this->in).')';
        }
        if ($this->group) {
            $parts[]='GROUP BY '.$this->group;
        }
        return join(' ', $parts);
    }

    public function getIterator(): Traversable
    {
        return $this->fetchAll();
    }

    private function buildFrom():string
    {
        $from=[];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[]="$key as $value";
            } else {
                $from[]=$value;
            }
        }

        return join(', ', $from);
    }

    private function execute()
    {
        $query = $this->__toString();
        if (!empty($this->params)) {
            $statement=$this->pdo->prepare($query);
            $statement->execute($this->params);
            return $statement;
        }
        // var_dump($query); die();
        return $this->pdo->query($query);
    }
}
