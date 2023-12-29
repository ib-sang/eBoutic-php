<?php

namespace Controllers\Session;

class ArraySession implements SessionInterface
{

    
    /**
     * session
     *
     * @var array
     */
    private $session=[];
    
    /**
     * get
     *
     * @param  string $key
     * @param  mixed $default
     * @return void
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->session)) {
            return $this->session[$key];
        }
        return $default;
    }
    
    /**
     * set
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function set(string $key, $value):void
    {
        $this->session[$key]=$value;
    }
    
    /**
     * delete
     *
     * @param  string $key
     * @return void
     */
    public function delete(string $key):void
    {
        unset($this->session[$key]);
    }
}
