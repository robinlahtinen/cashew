<?php


namespace Cashew\Controller\Index;

use Cashew\Error\ErrorInvalidMethod;
use Cashew\Kernel\Controller\AbstractController;
use Cashew\Kernel\Kernel;
use Cashew\Kernel\Request\Request;

/**
 * Class Index
 * @package Cashew\Controller\Index
 */
class Index extends AbstractController {

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

        Kernel::getInstance()->getJsonPool()->addJson("{\"api\":\"Welcome to the Cashew framework!\"}");
    }

}
