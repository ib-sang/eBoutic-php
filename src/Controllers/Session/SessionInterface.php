<?php

namespace Controllers\Session;

interface SessionInterface
{
    
    /**
     * get
     *
     * @param  string $key
     * @param  mixed $default
     * @return void
     */
    public function get(string $key, $default = null);
    
    /**
     * set
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function set(string $key, $value):void;
    
    /**
     * delete
     *
     * @param  string $key
     * @return void
     */
    public function delete(string $key):void;
}
