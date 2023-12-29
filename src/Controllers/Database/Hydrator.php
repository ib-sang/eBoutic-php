<?php

namespace Controllers\Database;

class Hydrator
{
    
    /**
     * hydrate
     *
     * @param  array $array
     * @param  mixed $object
     * @return void
     */
    public static function hydrate(array $array, $object)
    {
        if (is_string($object)) {
            $instance=new $object;
        } else {
            $instance=$object;
        }
        foreach ($array as $key => $value) {
            $method=self::getSetter($key);
            if (method_exists($instance, $method)) {
                $instance->$method($value);
            } else {
                $property=lcfirst(self::getProperty($key));
                $instance->$property=$value;
            }
        }
        return $instance;
    }
    
    /**
     * getSetter
     *
     * @param  string $fieldName
     * @return string
     */
    private static function getSetter(string $fieldName):string
    {
        return 'set'.self::getProperty($fieldName);
    }
    
    /**
     * getProperty
     *
     * @param  string $fieldName
     * @return string
     */
    private static function getProperty(string $fieldName):string
    {
        return join('', array_map('ucfirst', explode('_', $fieldName)));
    }
}
