<?php

namespace Goodby\CQRS\Assertion;

use InvalidArgumentException;

class Assert
{
    /**
     * @param mixed  $argument
     * @param string $message
     * @throws InvalidArgumentException
     */
    public static function argumentNotEmpty($argument, $message)
    {
        if (empty($argument)) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param string $className
     * @param string $message
     * @throws InvalidArgumentException
     */
    public static function argumentIsClass($className, $message)
    {
        if (class_exists($className) === false) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param string $className
     * @param string $message
     * @param string $superClassName
     * @throws InvalidArgumentException
     */
    public static function argumentSubclass($className, $message, $superClassName)
    {
        if (is_subclass_of($className, $superClassName) === false) {
            throw new InvalidArgumentException($message . ':' . $superClassName);
        }
    }

    /**
     * @param int $number
     * @param int $minimum
     * @param string $message
     * @throws InvalidArgumentException
     */
    public static function argumentAtLeast($number, $minimum, $message)
    {
        if ($number < $minimum) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param array $array
     * @param int $minimum
     * @param string $message
     * @throws InvalidArgumentException
     */
    public static function argumentAtLeastArray(array $array, $minimum, $message)
    {
        if (count($array) < $minimum) {
            throw new InvalidArgumentException($message);
        }
    }

    /**
     * @param $object
     * @param $message
     * @throws InvalidArgumentException
     */
    public static function argumentIsObject($object, $message)
    {
        if (is_object($object) === false) {
            throw new InvalidArgumentException($message);
        }
    }
}
