<?php

namespace Controllers\Session;

use ArrayAccess;

class PHPSession implements SessionInterface, ArrayAccess
{
    
    /**
     * ensureStarted
     *
     * @return void
     */
    private function ensureStarted()
    {
        if (session_status()===PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * get
     *
     * @param  string $key
     * @param  mixed $default
     * @return void
     */
    public function get(string $key, $default = null)
    {
        $this->ensureStarted();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
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
        $this->ensureStarted();
        $_SESSION[$key]=$value;
    }
    
    /**
     * delete
     *
     * @param  string $key
     * @return void
     */
    public function delete(string $key):void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    public function offsetExists($offset):bool
    {
        $this->ensureStarted();
        return array_key_exists($offset, $_SESSION);
    }

    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }
}
