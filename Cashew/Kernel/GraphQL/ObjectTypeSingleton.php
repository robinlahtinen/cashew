<?php


namespace Cashew\Kernel\GraphQL;

use GraphQL\Type\Definition\ObjectType;

abstract class ObjectTypeSingleton extends ObjectType {

    /**
     * @var self[]
     */
    protected static array $instances = [];

    /**
     * @return $this
     */
    public static function get(array $config = []): self {
        $class = static::class;

        if (empty(self::$instances[$class])) {
            $newClass = new $class($config);

            self::$instances[$class] = $newClass;

            return $newClass;
        }

        return self::$instances[$class];
    }

}
