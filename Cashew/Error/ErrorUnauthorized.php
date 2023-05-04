<?php


namespace Cashew\Error;

use Cashew\Kernel\Error\Error;

class ErrorUnauthorized extends Error {

    protected int $status = 401;

    protected string $title = "Unauthorized";

    protected string $detail = "Authentication is required and has failed or has not yet been provided.";

}
