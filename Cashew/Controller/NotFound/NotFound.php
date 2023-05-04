<?php

namespace Cashew\Controller\NotFound;

use Cashew\Kernel\Controller\AbstractController;
use Cashew\Kernel\Kernel;

/**
 * Class NotFound
 * @package Cashew\Controller\NotFound
 */
class NotFound extends AbstractController {

    protected array $paths = [
        "index" => "getIndexPage"
    ];

    public function getIndexPage(): void {
        Kernel::getInstance()->getRoute()->setResponseStatus("404 Not Found");
    }

}
