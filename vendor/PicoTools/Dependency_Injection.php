<?php

/*
 * This file is part of PicoTools.
 *
 * (c) Frédéric Guillot http://fredericguillot.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PicoTools;


function singleton($name)
{
    static $instance = array();

    if (! isset($instance[$name])) {

        $callback = container($name);

        if (! is_callable($callback)) {

            return null;
        }

        $instance[$name] = $callback();
    }

    return $instance[$name];
}


function container($name, $value = null)
{
    static $container = array();

    if (null !== $value) {

        $container[$name] = $value;
    }
    else if (isset($container[$name])) {

        return $container[$name];
    }

    return null;
}