<?php


namespace Cashew\Kernel\Log;

use Cashew\Kernel\Helper\Helper;
use Cashew\Kernel\Kernel;
use DateTime;
use Throwable;

class Log {

    protected static string $name = "Main";

    protected static string $traceId = "";

    /**
     * Log a debug message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function debug(string $message, array $context = []): string {
        if (Kernel::getInstance()->getConfig()->isDebug()) {
            return self::write("debug", $message, $context);
        }

        return "";
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return string
     */
    protected static function write(string $level, string $message, array $context): string {
        if (empty(self::getTraceId())) {
            self::setTraceId(Helper::getRandomToken(8));
        }

        try {
            $kernel = Kernel::getInstance();
        } catch (Throwable $throwable) {
            if ($level != "emergency") {
                self::emergency("Cannot fetch kernel instance. " . $throwable->getMessage(), $throwable->getTrace());
            }
        }

        $config = $kernel->getConfig();
        $logLevels = $config->getLogLevels();

        if ($config->isLogging() === true && in_array($level, $logLevels) && $logLevels[$level] === true) {
            $debugTrace = debug_backtrace(0, 3);
            $lastTrace = end($debugTrace);

            $callingClass = "";

            if (!empty($lastTrace) && count($debugTrace) > 2) {
                if (!empty($lastTrace["class"])) {
                    $callingClass .= $lastTrace["class"];
                } elseif (!empty($lastTrace["file"])) {
                    $callingClass .= $lastTrace["file"];
                }

                if (!empty($lastTrace["type"])) {
                    $callingClass .= $lastTrace["type"];
                } elseif (!empty($lastTrace["class"]) || !empty($lastTrace["file"])) {
                    $callingClass .= "::";
                }

                if (!empty($lastTrace["function"])) {
                    $callingClass .= $lastTrace["function"];
                }
            } else {
                $callingClass = "Index";
            }

            $name = self::getName();
            $path = $kernel->getRootDirectory() . "/Storage/Logs/Cashew/" . $name;

            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            try {
                $time = new DateTime("now");

                $tags[] = "[" . $time->format("Y-m-d\TH:i:s.uO") . "]";
            } catch (Throwable $throwable) {
                if (!in_array($level, ["emergency", "alert", "critical"])) {
                    self::critical("DateTime initialization failed. " . $throwable->getMessage(), $throwable->getTrace());
                }
            }

            if (!empty($level)) {
                $tags[] = "[" . strtoupper($level) . "]";
            }

            if ($kernel->isInitialized()) {
                try {
                    $request = $kernel->getRequest();
                    try {
                        $tags[] = "[" . $request->getClientIp() . "]";
                    } catch (Throwable $throwable) {
                        if (!in_array($level, ["emergency", "alert"])) {
                            self::alert("Failing to fetch client IP-address. " . $throwable->getMessage(), $throwable->getTrace());
                        }
                    }
                } catch (Throwable $throwable) {
                    if (!in_array($level, ["emergency", "alert"])) {
                        self::alert("Cannot fetch request instance. " . $throwable->getMessage(), $throwable->getTrace());
                    }
                }
            }

            $tags[] = "[" . self::getTraceId() . "]";

            if (!empty($_SERVER["REQUEST_METHOD"]) && isset($_SERVER["REQUEST_URI"])) {
                $tags[] = "[" . $_SERVER["REQUEST_METHOD"] . " " . $_SERVER["REQUEST_URI"] . "]";
            }

            if (!empty($callingClass)) {
                $tags[] = "[" . $callingClass . "]";
            }

            $debug = "";

            if (!empty($context)) {
                $debug = " " . json_encode($context);
            }

            $message = implode("", $tags) . " " . $message . $debug . PHP_EOL;

            $format = ".log";

            if ($level === "debug") {
                $format = "-debug" . $format;
            }

            file_put_contents($path . "/" . date("Y-m-d") . $format, $message, FILE_APPEND | LOCK_EX);
        }

        return self::getTraceId();
    }

    /**
     * @return string
     */
    public static function getTraceId(): string {
        return self::$traceId;
    }

    /**
     * @param string $traceId
     */
    public static function setTraceId(string $traceId): void {
        $oldTraceId = "";

        if (!empty(self::getTraceId())) {
            $oldTraceId = self::getTraceId();
        }

        self::$traceId = $traceId;

        if (!empty($oldTraceId)) {
            Log::info("Previous Trace ID: " . $oldTraceId);
        }
    }

    /**
     * Log an emergency message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function emergency(string $message, array $context = []): string {
        return self::write("emergency", $message, $context);
    }

    /**
     * @return string
     */
    protected static function getName(): string {
        return self::$name;
    }

    /**
     * Log a critical message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function critical(string $message, array $context = []): string {
        return self::write("critical", $message, $context);
    }

    /**
     * Log an alert message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function alert(string $message, array $context = []): string {
        return self::write("alert", $message, $context);
    }

    /**
     * Log a info message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function info(string $message, array $context = []): string {
        return self::write("info", $message, $context);
    }

    /**
     * Log a notice message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function notice(string $message, array $context = []): string {
        return self::write("notice", $message, $context);
    }

    /**
     * Log a warning message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function warning(string $message, array $context = []): string {
        return self::write("warning", $message, $context);
    }

    /**
     * Log an error message.
     *
     * @param string $message Log message.
     * @param array $context Event context array.
     */
    public static function error(string $message, array $context = []): string {
        return self::write("error", $message, $context);
    }

}
