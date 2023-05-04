<?php


namespace Cashew\Error;

use Cashew\Kernel\Error\Error;

class ErrorUnsupportedMediaType extends Error {

    protected int $status = 415;

    protected string $title = "Unsupported Media Type";

    protected string $detail = "The server refuses to accept the request because the payload format is in an unsupported format.";

}
