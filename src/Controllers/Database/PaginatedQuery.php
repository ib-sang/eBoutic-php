<?php

namespace Controllers\Database;

use Controllers\Database\Query;
use Pagerfanta\Adapter\AdapterInterface;

class PaginatedQuery implements AdapterInterface
{

    
    /**
     * query
     *
     * @var Query
     */
    private $query;
    
    /**
     * __construct
     *
     * @param  Query $query
     * @return void
     */
    public function __construct(Query $query)
    {
        $this->query=$query;
    }
    
    /**
     * getNbResults
     *
     * @return int
     */
    public function getNbResults():int
    {
        return $this->query->count();
    }
    
    /**
     * getSlice
     *
     * @param  mixed $offset
     * @param  mixed $length
     * @return QueryResult
     */
    public function getSlice($offset, $length):QueryResult
    {
        $query=clone $this->query;
        return $query->limit($length, $offset)->fetchAll();
    }
}
