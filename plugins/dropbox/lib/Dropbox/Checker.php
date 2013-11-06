<?php
namespace Dropbox;

/**
 * Helper functions to validate arguments.
 *
 * @internal
 */
class Checker
{
    static function throwError($argName, $argValue, $expectedTypeName)
    {
        if ($argValue === null) throw new \InvalidArgumentException("'$argName' must not be null");

        if (is_object($argValue)) {
            // Class type.
            $argTypeName = get_class($argValue);
        } else {
            // Built-in type.
            $argTypeName = gettype($argValue);
        }
        throw new \InvalidArgumentException("'$argName' has bad type; expecting $expectedTypeName, got $argTypeName");
    }

    static function argResource($argName, $argValue)
    {
        if (!is_resource($argValue)) self::throwError($argName, $argValue, "resource");
    }

    static function argCallable($argName, $argValue)
    {
        if (!is_callable($argValue)) self::throwError($argName, $argValue, "callable");
    }

    static function argBool($argName, $argValue)
    {
        if (!is_bool($argValue)) self::throwError($argName, $argValue, "boolean");
    }

    static function argArray($argName, $argValue)
    {
        if (!is_array($argValue)) self::throwError($argName, $argValue, "array");
    }

    static function argString($argName, $argValue)
    {
        if (!is_string($argValue)) self::throwError($argName, $argValue, "string");
    }

    static function argStringOrNull($argName, $argValue)
    {
        if ($argValue === null) return;
        if (!is_string($argValue)) self::throwError($argName, $argValue, "string");
    }

    static function argStringNonEmpty($argName, $argValue)
    {
        if (!is_string($argValue)) self::throwError($argName, $argValue, "string");
        if (strlen($argValue) === 0) throw new \InvalidArgumentException("'$argName' must be non-empty");
    }

    static function argStringNonEmptyOrNull($argName, $argValue)
    {
        if ($argValue === null) return;
        if (!is_string($argValue)) self::throwError($argName, $argValue, "string");
        if (strlen($argValue) === 0) throw new \InvalidArgumentException("'$argName' must be non-empty");
    }

    static function argNat($argName, $argValue)
    {
        if (!is_int($argValue)) self::throwError($argName, $argValue, "int");
        if ($argValue < 0) throw new \InvalidArgumentException("'$argName' must be non-negative (you passed in $argValue)");
    }

    static function argNatOrNull($argName, $argValue)
    {
        if ($argValue === null) return;
        if (!is_int($argValue)) self::throwError($argName, $argValue, "int");
        if ($argValue < 0) throw new \InvalidArgumentException("'$argName' must be non-negative (you passed in $argValue)");
    }

    static function argIntPositive($argName, $argValue)
    {
        if (!is_int($argValue)) self::throwError($argName, $argValue, "int");
        if ($argValue < 1) throw new \InvalidArgumentException("'$argName' must be positive (you passed in $argValue)");
    }

    static function argIntPositiveOrNull($argName, $argValue)
    {
        if ($argValue === null) return;
        if (!is_int($argValue)) self::throwError($argName, $argValue, "int");
        if ($argValue < 1) throw new \InvalidArgumentException("'$argName' must be positive (you passed in $argValue)");
    }
}
