<?php
/**
 * Cashew - Modern and simple framework.
 *
 * @package Cashew
 * @copyright Robin Lahtinen 2023. All rights reserved.
 * @license MIT
 * @author Robin Lahtinen
 * @version 0.1.0
 */

/**
 * Front to the Cashew framework.
 */

declare(strict_types=1);

use Cashew\Controller\Index\Index;
use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;
use Cashew\Kernel\Route\Route;
use JetBrains\PhpStorm\NoReturn;

function doCleanExit(): void {
    header("HTTP/1.1 500 Internal Server Error");

    try {
        $kernel = Kernel::getInstance();

        if ($kernel->isInitialized()) {
            $route = $kernel->getRoute();

            if ($route->getMethod() === Route::METHOD_JSON) {
                $instance = $kernel->getRequest()->getPathAsString();
                $traceId = $kernel->getTraceId();

                header("Content-Type: application/problem+json; charset=UTF-8");

                echo '{"type":"about:blank","status":500,"title":"Internal Server Error","detail":"The server encountered an unexpected condition that prevented it from fulfilling the request.","instance":"\\' . $instance . '","traceId":"' . $traceId . '"}';
            }
        } else {
            // If kernel is not working, then default to a generic API error message.
            header("Content-Type: application/problem+json; charset=UTF-8");

            echo '{"type":"about:blank","status":500,"title":"Internal Server Error","detail":"The server encountered an unexpected condition that prevented it from fulfilling the request."}';
        }
    } catch (Exception $exception) {
    };
}

#[NoReturn] function handleException(Throwable $exception): void {
    doCleanExit();

    Log::critical($exception->getMessage(), $exception->getTrace());

    exit();
}

#[NoReturn] function handleError(int $errno, string $errstr, string $errfile = "", int $errline = 0, array $errcontext = []): void {
    doCleanExit();

    Log::error("{$errstr} (File: {$errfile}) (Line: $errline) (Code: $errno)", $errcontext);

    exit();
}

function handleShutdown(): void {
    $executionTime = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3);
    $memoryUsage = round(memory_get_usage(false) / 1048576, 2);
    $memoryPeakUsage = round(memory_get_peak_usage(false) / 1048576, 2);

    Log::debug("Execution took {$executionTime}s. Allocated {$memoryUsage} MB of memory with peak usage of {$memoryPeakUsage} MB.");

    if ($executionTime >= 2) {
        Log::warning("Execution took over two seconds. ({$executionTime} seconds)");
    }

    Log::debug("Execution ended.");
}

set_exception_handler("handleException");
set_error_handler("handleError");
register_shutdown_function("handleShutdown");

$rootDirectory = realpath(__DIR__ . DIRECTORY_SEPARATOR . "..");

/**
 * Register the autoloader.
 */
require($rootDirectory . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php");

$kernel = new Kernel();

Kernel::setInstance($kernel);

$kernel->setRootDirectory($rootDirectory);

// Initialize the kernel.
$kernel->init();

// Fetch and render a page. In this instance get the page by user URI path.
echo $kernel->getRoute()->getByPath(new Index());
