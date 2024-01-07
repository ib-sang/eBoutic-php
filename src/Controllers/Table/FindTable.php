<?php

namespace Controllers\Table;

use DateTime;
use Controllers\Database\Query;
use Controllers\Database\Table;
use App\Services\Auth\Table\UserTable;
use App\Services\Circuits\Table\CircuitTable;
use App\Services\Deplacements\Table\DeplacementTable;
use App\Services\Product\Table\ProductTable;

class FindTable extends Table
{

    protected $entity=\stdClass::class;
    protected $table;

    public function findAll():Query
    {
        $alias='u';
        $aliasJoin = lcfirst($this->table)[0];
        $aliasTable = "$alias.lastname as lastname, $alias.firstname as firstname, 
            $alias.email as email, $alias.username as username , $alias.phone as phone";
        $user = new UserTable($this->getPdo());

        if (array_key_exists('users', $this->makeQuery()->from)) {
            return $this->makeQuery()
                ->select("$alias.*")
                ->order("$alias.id DESC")
            ;
        }

        if (array_key_exists('statuses', $this->makeQuery()->from)) {
            $alias = 's';
            return $this->makeQuery()
                ->select("$alias.*")
                ->order("$alias.id DESC")
            ;
        }

        if (array_key_exists('stocks', $this->makeQuery()->from)) {
            $alias = 's';
            $tab = new ProductTable($this->getPdo());
            $aliasTab = "p";
            $aliasUser = "u";
            return $this->makeQuery()
                ->join($tab->getTable()." as $aliasTab", "$aliasTab.id=$aliasJoin.products_id")
                ->join($user->getTable()." as $aliasUser", "$aliasUser.id=$aliasJoin.users_id")
                ->select("$aliasJoin.in_stock ,$aliasJoin.created_at, $aliasJoin.id as id, $aliasTab.name as name, $aliasTab.id as products_id, $aliasTab.categories_id as categories_id, $aliasTab.price_per_unit as price_per_unit, $aliasTab.basic_unit as basic_unit, $aliasTable")
                ->order("$aliasTab.id DESC")
            ;
        }
        return $this->makeQuery()
            ->join($user->getTable()." as $alias", "$alias.id=$aliasJoin.users_id")
            ->select("$aliasJoin.*, $aliasTable")
            ->order("$alias.id DESC")
            ;
    }

    public function findByUser(string $field, string $value, int $id)
    {
        $alias='u';
        $user = new UserTable($this->getPdo());
        $aliasJoin = lcfirst($this->table)[0];
        $query = $this->makeQuery()
                ->join($user->getTable()." as $alias", "$alias.id = $aliasJoin.$field")
                ->where("$aliasJoin.id = $id")
                ->select("$aliasJoin.*, u.username as username, u.firstname as firstname, u.lastname as lastname")
                ->fetch()
                ;
        return $query;
    }

    public function findByUserBusAll(string $field, string $value)
    {
        $alias='u';
        $aliasCircuit = 'c';
        $user = new UserTable($this->getPdo());
        // $circuit = new CircuitTable($this->getPdo());

        $aliasJoin = 'r';

        $query = $this->makeQuery()
            ->join($user->getTable()." as $alias", "$alias.id = $aliasJoin.customers_id")
            // ->join($circuit->getTable()." as $aliasCircuit", "$aliasCircuit.id = $aliasJoin.circuit_id")
            ->where("$alias.id = $aliasJoin.customers_id")
            ->where("$aliasCircuit.id = $aliasJoin.circuit_id")
            ->where("$aliasJoin.$field = $value")
            ->select("$aliasJoin.*, u.firstname as firstname, 
                u.lastname as lastname, u.phone as phone, c.circuit as circuit")
        ;
        return $query;
    }

    public function findByCollieBusAll(string $field, string $value)
    {
        $alias='u';
        $aliasCircuit = 'ci';
        $user = new UserTable($this->getPdo());
        // $circuit = new CircuitTable($this->getPdo());

        $aliasJoin = 'c';

        $query = $this->makeQuery()
            ->join($user->getTable()." as $alias", "$alias.id = $aliasJoin.recevery_id")
            // ->join($circuit->getTable()." as $aliasCircuit", "$aliasCircuit.id = $aliasJoin.circuit_id")
            ->where("$alias.id = $aliasJoin.recevery_id")
            ->where("$aliasCircuit.id = $aliasJoin.circuit_id")
            ->where("$aliasJoin.$field = $value")
            ->select("$aliasJoin.*, u.firstname as firstname, 
                u.lastname as lastname, u.phone as phone, ci.circuit as circuit")
        ;
        return $query;
    }

    public function countGroupByColumEnterprise($grp, $colum, $enterprise):Query
    {
        $alias=lcfirst($this->table)[0];
        $results = $this->makeQuery()
            ->select("SUM($alias.$colum) as $colum, $alias.name as name, $alias.enterprise_id")
            ->group($grp)
            ->where("enterprise_id=$enterprise")
            // ->countByColum($colum)
            ;
        return $results;
    }

    public function countGroupByJourney() : Query {
        
        $alias=lcfirst($this->table)[0];
        $results = $this->makeQuery()
            ->select("SUM($alias.total) as total, DATE($alias.created_at) as created_at")
            ->group("DATE($alias.created_at)")
            // ->countByColum($colum)
            ;
            
        return $results;
    }

    function findPostGroupBy($filed, $value) : Query {

        $aliasCircuit = 'd';
        $alias = lcfirst($this->table)[0];
        // $circuit = new DeplacementTable($this->getPdo());

        $results = $this->makeQuery()
            ->join($circuit->getTable()." as $aliasCircuit", "$aliasCircuit.id = $alias.voyage_id")
            ->select("$alias.name as name, SUM($alias.total) as total, DATE($alias.created_at) as created_at, $aliasCircuit.days_dep, $aliasCircuit.time_dep")
            ->where("$aliasCircuit.id = $alias.voyage_id")
            ->where("$alias.$filed=$value")
            ->group("$alias.name")
        ;
        return $results;
    }

    public function findPublicTimeUser($field, $v, $time)
    {
        $alias=lcfirst($this->table)[0];
        $results = $this->makeQuery()
            ->select("$alias.*")
            ->where("$alias.$field=$v")
            ->where("$alias.created_at BETWEEN '$time' AND NOW()")
            // ->fetchAll()
            // ->countByColum($colum)
            ;
        // var_dump($results); die();
        return $results;
    }

    public function countByColumEnterprise($colum, $enterprise)
    {
        $results = $this->makeQuery()->where("enterprise_id=$enterprise")->countByColum($colum);
        return $results;
    }

    /**
     * findPublic
     *
     * @return Query
     */
    public function findAllPublic():Query
    {
        $alias=lcfirst($this->table)[0];
        
        $query =   $this->findAll()
            ->where("$alias.published=1")
            ;
        return $query;
    }

    public function findAllPublicToDay($day):Query
    {
        $alias=lcfirst($this->table)[0];
        
        $query =   $this->findAll()
            ->where("$alias.published=1")
            ->where("DATE(`$day`) = CURRENT_DATE()")
            ;
        return $query;
    }

    /**
     * findPublic
     *
     * @return Query
     */
    public function findAllPublicBy(string $field, string $value):Query
    {
        $alias=lcfirst($this->table)[0];
        $query =   $this->findAll()
            ->where("$alias.published=1")
            ->where("$alias.$field='$value'")
            ->order("$alias.id DESC")
            ;
        return $query;
    }

     /**
     * findPublic
     *
     * @return Query
     */
    public function findAllByPublicParams(string $field, string $value, $target):Query
    {
        $alias=lcfirst($this->table)[0];
        $query =  $this->findAll()
            ->where("$alias.published=1")
            ->where("$alias.$field='$value'")
            ->where("$alias.$target > '0'")
            ;
        return $query;
    }

    
     /**
     * findPublic
     *
     * @return Query
     */
    public function findAllByPublicDay(string $field, string $value):Query
    {
        $alias=lcfirst($this->table)[0];
        $query =   $this->findAll()
            ->where("$alias.published=1")
            ->where("$alias.$field='$value'")
            ->where("$alias.created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY_HOUR )")
            ;
        return $query;
    }

    public function findAllByPublicStatic(string $field, string $value):Query
    {
        $alias=lcfirst($this->table)[0];
        $query =   $this->makeQuery()
            ->select("$alias.type, SUM($alias.money) as money")
            ->where("$alias.$field='$value'")
            ->where("created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY_HOUR )")
            ->group("$alias.type")
            
            ;
        return $query;
    }

    public function findAllByPublicNopay(string $field, string $value):Query
    {
        $alias=lcfirst($this->table)[0];
        $query =   $this->findAll()
            ->where("$alias.published=1")
            ->where("$alias.$field='$value'")
            ->where("$alias.rapport < 0")
            ->order("$alias.created_at DESC")
            ;
        return $query;
    }
}
