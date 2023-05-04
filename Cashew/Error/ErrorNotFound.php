<?php


namespace Cashew\Error;

use Cashew\Kernel\Error\Error;

class ErrorNotFound extends Error {

    protected int $status = 404;

    protected string $title = "Not Found";

    protected string $detail = "The requested resource is not available.";

}
