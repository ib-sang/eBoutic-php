<?php

namespace Controllers\Database;

use ArrayAccess;
use Iterator;

class QueryResult implements ArrayAccess, Iterator
{

    /**
     * records
     *
     * @var mixed
     */
    private $records;

    private $index;
    
    /**
     * hydrateRecords
     *
     * @var array
     */
    private $hydratedRecords = [];
    
    /**
     * entity
     *
     * @var string
     */
    private $entity;

    
    /**
     * __construct
     *
     * @param  array $records
     * @param  string/null $entity
     * @return void
     */
    public function __construct(array $records, ?string $entity = null)
    {
        $this->records = $records;
        $this->entity = $entity;
    }
    
    /**
     * get
     *
     * @param  int $index
     * @return void
     */
    public function get(int $index)
    {
        if ($this->entity) {
            if (!isset($this->hydratedRecords[$this->index])) {
                $this->hydratedRecords[$this->index]= Hydrator::hydrate($this->records[$index], $this->entity);
            }
            return $this->hydratedRecords[$this->index];
        }
        return $this->entity;
    }

    public function getRecords()
    {
        return $this->records;
    }

    public function current():mixed
    {
        return $this->get($this->index);
    }

    public function next():void
    {
        $this->index++;
    }

    public function key():mixed
    {
        return $this->index;
    }

    public function valid():bool
    {
        return isset($this->records[$this->index]);
    }

    public function rewind():void
    {
        $this->index=0;
    }

    public function offsetExists($offset):bool
    {
        return isset($this->records[$offset]);
    }

    public function offsetGet($offset):mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception("can't alter records");
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception("can't alter records");
    }
}
