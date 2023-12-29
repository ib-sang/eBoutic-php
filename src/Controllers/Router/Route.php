<?php

namespace Controllers\Router;

class Route
{
    
    /**
     * name
     *
     * @var mixed
     */
    private $name;
        
    /**
     * callable
     *
     * @var mixed
     */
    private $callable;
        
    /**
     * params
     *
     * @var mixed
     */
    private $params;


    public function __construct($name, $callable, $params)
    {
        $this->name = $name;
        $this->callable = $callable;
        $this->params = $params;
    }

    /**
     * Get the value of name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of callable
     */
    public function getCallback()
    {
        return $this->callable;
    }

    /**
     * Set the value of callable
     *
     * @return  self
     */
    public function setCallback($callable)
    {
        $this->callable = $callable;

        return $this;
    }

    /**
     * Get the value of params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set the value of params
     *
     * @return  self
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }
}
