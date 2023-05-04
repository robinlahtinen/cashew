<?php


namespace Cashew\Kernel\Component;

/**
 * Class ComponentPool
 * @package Cashew\Kernel\Component
 * @author Robin Lahtinen
 */
class ComponentPool implements ComponentInterface {
    /**
     * @var ComponentInterface[] Component instances.
     */
    protected array $components = [];

    /**
     * ComponentPool constructor.
     * @param ComponentInterface[] $components Component instances.
     * @psalm-suppress InvalidArgument
     */
    public function __construct(array $components = []) {
        $this->setComponents($components);
    }

    /**
     * @return string Rendered components.
     */
    public function render(): string {
        $renderedComponents = "";

        foreach ($this->getComponents() as $component) {
            $renderedComponents .= $component->render();
        }

        return $renderedComponents;
    }

    /**
     * @return ComponentInterface[]
     */
    public function getComponents(): array {
        return $this->components;
    }

    /**
     * @param ComponentInterface[] $components
     */
    public function setComponents(array $components): void {
        $this->components = $components;
    }
}
