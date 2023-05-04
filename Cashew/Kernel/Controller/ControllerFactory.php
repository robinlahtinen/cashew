<?php


namespace Cashew\Kernel\Controller;

use Cashew\Controller\Home\Home;

/**
 * Class ControllerFactory
 * @package Cashew\Kernel\Controller
 * @author Robin Lahtinen
 */
class ControllerFactory {

    /**
     * @param string $name Controller name.
     * @return AbstractController Controller instance.
     * @psalm-suppress MixedMethodCall False positive.
     */
    public function getControllerByName(string $name): AbstractController {
        $path = "Cashew\\Controller\\" . $name . "\\" . $name;

        return new $path();
    }

    /**
     * @param string $name Controller name.
     * @return bool True if controller exists, false if otherwise.
     */
    public function doesControllerExistByName(string $name): bool {
        $exists = false;

        if (!empty($name)) {
            $path = "Cashew\\Controller\\" . $name . "\\" . $name;

            if (class_exists($path)) {
                $exists = true;
            }
        }

        return $exists;
    }

}
