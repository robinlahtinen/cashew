<?php


namespace Cashew\Error;

use Cashew\Kernel\Error\Error;

class ErrorBadRequest extends Error {

    protected int $status = 400;

    protected string $title = "Bad Request";

    protected string $detail = "The server refuses to process the request due to a client error.";

}
