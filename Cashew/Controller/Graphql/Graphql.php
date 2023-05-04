<?php


namespace Cashew\Controller\Graphql;

use Cashew\Error\ErrorInvalidMethod;
use Cashew\Kernel\Controller\AbstractController;
use Cashew\Kernel\Kernel;
use Cashew\Kernel\Request\Request;
use GraphQL\Error\DebugFlag;

class Graphql extends AbstractController {

    protected array $paths = [
        "index" => "getIndexPage"
    ];

    public function getIndexPage(): void {
        $kernel = Kernel::getInstance();
        $request = $kernel->getRequest();

        if ($request->isOptions()) {
            $kernel->getRoute()->setAllowedMethods([
                Request::METHOD_GET,
                Request::METHOD_POST
            ]);

            return;
        }

        if (!$request->isPost() && !$request->isGet()) {
            ErrorInvalidMethod::new();

            return;
        }

        $server = $kernel->getGraphQL()->getServer();
        $executionResult = $server->executeRequest();

        $debug = DebugFlag::NONE;

        if (Kernel::getInstance()->getConfig()->isDebug()) {
            $debug = DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE;
        }

        $kernel->getJsonPool()->addJson(json_encode($executionResult->toArray($debug)));
    }

}
