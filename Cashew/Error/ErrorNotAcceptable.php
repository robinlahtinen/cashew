<?php


namespace Cashew\Error;

use Cashew\Kernel\Error\Error;

class ErrorNotAcceptable extends Error {

    protected int $status = 406;

    protected string $title = "Not Acceptable";

    protected string $detail = "The server cannot produce a response matching the list of acceptable values defined in the request's proactive content negotiation headers.";

}
