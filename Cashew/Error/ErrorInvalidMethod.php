<?php


namespace Cashew\Error;

use Cashew\Kernel\Error\Error;

class ErrorInvalidMethod extends Error {

    protected int $status = 405;

    protected string $title = "Invalid method";

    protected string $detail = "The request method is not supported for the requested resource.";

}
