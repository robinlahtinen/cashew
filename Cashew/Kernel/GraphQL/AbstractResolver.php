<?php


namespace Cashew\Kernel\GraphQL;

use Cashew\Kernel\GraphQL\Resolver\Buffer;

abstract class AbstractResolver {

    /**
     * @var self[]
     */
    protected static array $instances = [];

    protected array $queue = [];

    protected Buffer $buffer;

    public function __construct() {
        $this->setBuffer(new Buffer());
    }

    public static function add(string $field, $value): void {
        $resolver = self::getInstance();

        $queue = $resolver->getQueue();
        $queue[$field][] = $value;

        $resolver->setQueue($queue);
    }

    /**
     * @return $this
     */
    public static function getInstance(): self {
        $instances = self::getInstances();
        $class = static::class;

        if (empty($instances[$class])) {
            $instances[$class] = new $class();
        }

        self::setInstances($instances);

        return $instances[$class];
    }

    /**
     * @return $this[]
     */
    protected static function getInstances(): array {
        return self::$instances;
    }

    /**
     * @param self[] $instances
     */
    protected static function setInstances(array $instances): void {
        self::$instances = $instances;
    }

    /**
     * @return array
     */
    protected function getQueue(): array {
        return $this->queue;
    }

    /**
     * @param array $queue
     */
    protected function setQueue(array $queue): void {
        $this->queue = $queue;
    }

    public static function reset(): void {
        $resolver = self::getInstance();
        $resolver->resetQueue();
    }

    public function resetQueue(): void {
        $this->setQueue([]);
    }

    public static function load(): void {
        $resolver = self::getInstance();
        $queue = $resolver->getQueue();
        $bufferMeta = $resolver->getBuffer()->getMeta();

        foreach ($queue as $field => $values) {
            foreach ($values as $value) {
                if (!empty($bufferMeta[$field][$value])) {
                    unset($queue[$field][array_search($value, $values)]);

                    if (empty($queue[$field])) {
                        unset($queue[$field]);
                    }
                }
            }
        }

        $resolver->setQueue($queue);
        $resolver->process();
    }

    /**
     * @return Buffer
     */
    protected function getBuffer(): Buffer {
        return $this->buffer;
    }

    /**
     * @param Buffer $buffer
     */
    protected function setBuffer(Buffer $buffer): void {
        $this->buffer = $buffer;
    }

    abstract public function process(): void;

    public static function get(string $field, $value) {
        $resolver = self::getInstance();

        return $resolver->getBuffer()->get($field, $value);
    }

    public static function getAll(): array {
        $resolver = self::getInstance();

        return $resolver->getBuffer()->getAll();
    }

    public static function getAllFrom(string $field): array {
        $resolver = self::getInstance();

        return $resolver->getBuffer()->getAllFrom($field);
    }

}
