<?php


namespace Cashew\Kernel\Controller;

/**
 * Class ControllerPool
 * @package Cashew\Kernel\Controller
 */
class ControllerPool {

    /**
     * @var AbstractController[] Controller instances.
     */
    protected array $controllers = [];

    /**
     * ControllerPool constructor.
     * @param AbstractController[] $controllers Controller instances.
     */
    public function __construct(array $controllers = []) {
        $this->setControllers($controllers);
    }

    /**
     * @return AbstractController[]
     */
    public function getControllers(): array {
        return $this->controllers;
    }

    /**
     * @param AbstractController[] $controllers Controller instances.
     */
    public function setControllers(array $controllers): void {
        $this->controllers = $controllers;
    }

}
