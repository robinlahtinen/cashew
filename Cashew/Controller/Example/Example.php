<?php

namespace Cashew\Controller\Example;

use Cashew\Component\ExampleComponent;
use Cashew\ComponentPool\Root;
use Cashew\Error\ErrorInvalidMethod;
use Cashew\Kernel\Controller\AbstractController;
use Cashew\Kernel\Kernel;
use Cashew\Kernel\Request\Request;
use Cashew\Kernel\Route\Route;

/**
 * Class ExampleComponent
 * @package Cashew\Controller\ExampleComponent
 */
class Example extends AbstractController {

    protected array $paths = [
        "index" => "getIndexPage"
    ];

    public function getIndexPage(): void {
        $kernel = Kernel::getInstance();
        $request = $kernel->getRequest();

        if ($request->isOptions()) {
            $kernel->getRoute()->setAllowedMethods([
                Request::METHOD_GET
            ]);

            return;
        }

        if (!$request->isGet()) {
            ErrorInvalidMethod::new();

            return;
        }

        $kernel->getRoute()->setMethod(Route::METHOD_VIEW);

        $this->setComponentPool(new Root([
            new ExampleComponent()
        ]));
    }

}
