<?php


namespace Cashew\Kernel\GraphQL\Resolver;

class Buffer {

    /**
     * @var BufferItem[]
     */
    protected array $items = [];

    /**
     * @var BufferItem[][]
     */
    protected array $meta = [];

    public function add($item, array $meta): void {
        $bufferItem = new BufferItem();
        $bufferItem->set($item);

        $items = $this->getItems();
        $bufferMeta = $this->getMeta();

        $items[] = $bufferItem;

        foreach ($meta as $field => $value) {
            $bufferMeta[$field][$value] = $bufferItem;
        }

        $this->setItems($items);
        $this->setMeta($bufferMeta);
    }

    /**
     * @return BufferItem[]
     */
    protected function getItems(): array {
        return $this->items;
    }

    /**
     * @param BufferItem[] $items
     */
    protected function setItems(array $items): void {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getMeta(): array {
        return $this->meta;
    }

    /**
     * @param array $meta
     */
    protected function setMeta(array $meta): void {
        $this->meta = $meta;
    }

    /**
     * @return array
     */
    public function getAll(): array {
        $items = [];

        foreach ($this->getItems() as $item) {
            $items[] = $item->get();
        }

        return $items;
    }

    /**
     * @param string $field
     * @param $value
     * @return mixed|null
     */
    public function get(string $field, $value) {
        $item = null;
        $meta = $this->getMeta();

        if (!empty($meta[$field][$value])) {
            $item = $meta[$field][$value]->get();
        }

        return $item;
    }

    public function getAllFrom(string $field): array {
        $items = [];
        $meta = $this->getMeta();

        if (!empty($meta[$field])) {
            foreach ($meta[$field] as $item) {
                $items[] = $item->get();
            }
        }

        return $items;
    }

}
