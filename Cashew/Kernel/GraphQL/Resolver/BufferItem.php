<?php


namespace Cashew\Kernel\GraphQL\Resolver;

class BufferItem {

    /**
     * @var mixed
     */
    protected $item = null;

    /**
     * @return mixed
     */
    public function get() {
        return $this->item;
    }

    /**
     * @param mixed $item
     */
    public function set($item): void {
        $this->item = $item;
    }

}
