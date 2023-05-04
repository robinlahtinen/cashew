<?php


namespace Cashew\Kernel\Request;

use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;
use Exception;

/**
 * Class Request
 * @package Cashew\Kernel\Request
 */
class Request {

    public const METHOD_NONE = 0;

    public const METHOD_GET = 1;

    public const METHOD_POST = 2;

    public const METHOD_PUT = 3;

    public const METHOD_DELETE = 4;

    public const METHOD_PATCH = 5;

    public const METHOD_OPTIONS = 6;

    public const METHOD_HEAD = 7;

    protected array $methods = [
        1 => "GET",
        2 => "POST",
        3 => "PUT",
        4 => "DELETE",
        5 => "PATCH",
        6 => "OPTIONS",
        7 => "HEAD"
    ];

    /**
     * @var string[] Requested page path.
     */
    protected array $path = [];

    protected array $httpStatusCodes = [
        100 => "Continue",
        101 => "Switching Protocols",
        102 => "Processing", // WebDAV; RFC 2518
        103 => "Early Hints", // RFC 8297
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        203 => "Non-Authoritative Information", // since HTTP/1.1
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content", // RFC 7233
        207 => "Multi-Status", // WebDAV; RFC 4918
        208 => "Already Reported", // WebDAV; RFC 5842
        226 => "IM Used", // RFC 3229
        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found", // Previously "Moved temporarily"
        303 => "See Other", // since HTTP/1.1
        304 => "Not Modified", // RFC 7232
        305 => "Use Proxy", // since HTTP/1.1
        306 => "Switch Proxy",
        307 => "Temporary Redirect", // since HTTP/1.1
        308 => "Permanent Redirect", // RFC 7538
        400 => "Bad Request",
        401 => "Unauthorized", // RFC 7235
        402 => "Payment Required",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        407 => "Proxy Authentication Required", // RFC 7235
        408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        412 => "Precondition Failed", // RFC 7232
        413 => "Payload Too Large", // RFC 7231
        414 => "URI Too Long", // RFC 7231
        415 => "Unsupported Media Type", // RFC 7231
        416 => "Range Not Satisfiable", // RFC 7233
        417 => "Expectation Failed",
        418 => "I'm a teapot", // RFC 2324, RFC 7168
        421 => "Misdirected Request", // RFC 7540
        422 => "Unprocessable Entity", // WebDAV; RFC 4918
        423 => "Locked", // WebDAV; RFC 4918
        424 => "Failed Dependency", // WebDAV; RFC 4918
        425 => "Too Early", // RFC 8470
        426 => "Upgrade Required",
        428 => "Precondition Required", // RFC 6585
        429 => "Too Many Requests", // RFC 6585
        431 => "Request Header Fields Too Large", // RFC 6585
        451 => "Unavailable For Legal Reasons", // RFC 7725
        500 => "Internal Server Error",
        501 => "Not Implemented",
        502 => "Bad Gateway",
        503 => "Service Unavailable",
        504 => "Gateway Timeout",
        505 => "HTTP Version Not Supported",
        506 => "Variant Also Negotiates", // RFC 2295
        507 => "Insufficient Storage", // WebDAV; RFC 4918
        508 => "Loop Detected", // WebDAV; RFC 5842
        510 => "Not Extended", // RFC 2774
        511 => "Network Authentication Required", // RFC 6585
    ];

    /**
     * Get post request string value.
     *
     * @param string $key String to get.
     * @return string Post request string.
     */
    public function getPostString(string $key): string {
        $value = "";

        if (!empty($_POST[$key])) {
            $value = (string)$_POST[$key];
        }

        return $value;
    }

    public function getRequestJson(): array {
        $json = json_decode($this->getRequestBody(), true);

        if (empty($json) || !is_array($json)) {
            Log::warning("JSON cannot be decoded. (JSON: " . print_r($json, true) . ")");

            $json = [];
        }

        return $json;
    }

    /**
     * @return string
     */
    public function getRequestBody(): string {
        return file_get_contents("php://input");
    }

    /**
     * @return string IP-address.
     */
    public function getClientIp(): string {
        $ip = $_SERVER["REMOTE_ADDR"]; // TODO: Support multiple IP sources (ex. Cloudflare)

        $filteredIp = filter_var($ip, FILTER_VALIDATE_IP);

        if (empty($filteredIp)) {
            $filteredIp = "0.0.0.0";

            Log::notice("Client IP address is invalid. (IP: {$ip})");
        }

        return $filteredIp;
    }

    /**
     * @return string
     */
    public function getPathAsString(): string {
        return "/" . implode("/", $this->getPath());
    }

    /**
     * @return string[]
     */
    public function getPath(): array {
        if (empty($this->path)) {
            $this->setPath($this->getPathFromUri());
        }

        return $this->path;
    }

    /**
     * @param string[] $path
     */
    protected function setPath(array $path): void {
        $this->path = $path;
    }

    /**
     * Get request URI array.
     *
     * @return string[] Request URI array.
     */
    protected function getPathFromUri(): array {
        $rawUrl = $this->getRawUri();

        while (str_starts_with($rawUrl, "//")) {
            $rawUrl = substr($rawUrl, 1);
        }

        $parsedUrl = parse_url($rawUrl, PHP_URL_PATH);

        if (empty($parsedUrl)) {
            Log::error("Parsed url is malformed.");

            $parsedUrl = "/";
        }

        return array_values(array_filter(explode("/", strip_tags(trim(htmlentities($parsedUrl))))));
    }

    /**
     * @return string
     */
    public function getRawUri(): string {
        return (string)$_SERVER["REQUEST_URI"];
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @return bool
     */
    public function setCookie(string $name, $value, array $options = []): bool {
        if (empty($options)) {
            $config = Kernel::getInstance()->getConfig();
            $origin = $config->getOrigin();

            $expires = time() + 31536000;

            $options = [
                "expires" => $expires,
                "path" => "/",
                "domain" => $origin,
                "secure" => true,
                "httponly" => true,
                "samesite" => "Strict"
            ];

            if ($config->isDebug()) {
                $options = [
                    "expires" => $expires,
                    "path" => "/",
                    "domain" => $origin,
                    "secure" => false,
                    "httponly" => true,
                    "samesite" => "Strict"
                ];
            }
        }

        return setcookie($name, $value, $options);
    }

    public function getOrigin(): string {
        $origin = "";

        if (!empty($_SERVER["HTTP_ORIGIN"])) {
            $origin = trim($_SERVER["HTTP_ORIGIN"]);
        }

        return $origin;
    }

    /**
     * @param string $cookie
     * @return string
     */
    public function getCookie(string $cookie): string {
        $value = "";

        if (!empty($_COOKIE[$cookie])) {
            $value = (string)strip_tags(htmlentities($_COOKIE[$cookie]));
        }

        return $value;
    }

    /**
     * @return bool
     */
    public function isGet(): bool {
        if ($this->getMethod() === self::METHOD_GET) {
            return true;
        }
        return false;
    }

    /**
     * @return int
     */
    public function getMethod(): int {
        $method = self::METHOD_NONE;
        $rawMethod = trim($_SERVER["REQUEST_METHOD"]);

        if (!empty($rawMethod) && is_string($rawMethod)) {
            if (strcasecmp($rawMethod, "GET") === 0) {
                $method = self::METHOD_GET;
            } elseif (strcasecmp($rawMethod, "POST") === 0) {
                $method = self::METHOD_POST;
            } elseif (strcasecmp($rawMethod, "PUT") === 0) {
                $method = self::METHOD_PUT;
            } elseif (strcasecmp($rawMethod, "DELETE") === 0) {
                $method = self::METHOD_DELETE;
            } elseif (strcasecmp($rawMethod, "PATCH") === 0) {
                $method = self::METHOD_PATCH;
            } elseif (strcasecmp($rawMethod, "OPTIONS") === 0) {
                $method = self::METHOD_OPTIONS;
            } elseif (strcasecmp($rawMethod, "HEAD") === 0) {
                $method = self::METHOD_HEAD;
            }
        }

        return $method;
    }

    public function isPost(): bool {
        if ($this->getMethod() === self::METHOD_POST) {
            return true;
        }
        return false;
    }

    public function isPut(): bool {
        if ($this->getMethod() === self::METHOD_PUT) {
            return true;
        }
        return false;
    }

    public function isDelete(): bool {
        if ($this->getMethod() === self::METHOD_DELETE) {
            return true;
        }
        return false;
    }

    public function isPatch(): bool {
        if ($this->getMethod() === self::METHOD_PATCH) {
            return true;
        }
        return false;
    }

    public function isOptions(): bool {
        if ($this->getMethod() === self::METHOD_OPTIONS) {
            return true;
        }
        return false;
    }

    public function isHead(): bool {
        if ($this->getMethod() === self::METHOD_HEAD) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getAuthToken(): string {
        $token = "";
        $auth = $this->getAuthorization();

        if (!empty($auth)) {
            $matches = [];

            if (preg_match('/Bearer\s(\S+)/', $auth, $matches) && !empty($matches[1])) {
                $token = $matches[1];
            }
        }

        return $token;
    }

    /**
     * @return string
     */
    protected function getAuthorization(): string {
        return trim($_SERVER["HTTP_AUTHORIZATION"]);
    }

    /**
     * @param int $status
     * @return string
     * @throws Exception
     */
    public function getHttpStatusCode(int $status): string {
        $code = "";
        $codes = $this->getHttpStatusCodes();

        if (!empty($codes[$status])) {
            $code = $status . " " . $codes[$status];
        } else {
            throw new Exception("HTTP Status code doesn't exist.");
        }

        return $code;
    }

    /**
     * @return array|string[]
     */
    protected function getHttpStatusCodes(): array {
        return $this->httpStatusCodes;
    }

    /**
     * @param int $method
     * @return string
     * @throws Exception
     */
    public function getMethodName(int $method): string {
        $name = "";
        $names = $this->getMethods();

        if (!empty($names[$method])) {
            $name = $names[$method];
        } else {
            throw new Exception("Method name doesn't exist.");
        }

        return $name;
    }

    /**
     * @return array|string[]
     */
    protected function getMethods(): array {
        return $this->methods;
    }

    /**
     * @return string
     */
    public function getHttpHost(): string {
        return trim($_SERVER["HTTP_HOST"]);
    }

    public function getAcceptAsArray(): array {
        $accept = [];

        preg_match("/(?<content>(?<type>.+?)\/(?<sub>.+?)(?:\+(?<suffix>.+?))?)(?:;.*?(?:q=(?<weight>[.\d]+))?.*?)?(?:,|$)/", $this->getAccept(), $accept);

        return $accept;
    }

    /**
     * @return string
     */
    public function getAccept(): string {
        return trim($_SERVER["HTTP_ACCEPT"]);
    }

}
