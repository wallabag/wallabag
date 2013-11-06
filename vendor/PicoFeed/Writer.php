<?php

namespace PicoFeed;

abstract class Writer
{
    public $items = array();


    abstract public function execute($filename = '');


    public function checkRequiredProperties($properties, $container)
    {
        foreach ($properties as $property) {

            if ((is_object($container) && ! isset($container->$property)) || (is_array($container) && ! isset($container[$property]))) {

                throw new \RuntimeException('Required property missing: '.$property);
            }
        }
    }
}