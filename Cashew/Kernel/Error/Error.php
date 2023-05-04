<?php


namespace Cashew\Kernel\Error;

use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;
use Exception;

class Error {

    protected string $type = "about:blank";

    protected int $status = 400;

    protected string $title = "";

    protected string $detail = "";

    protected string $instance = "";

    protected string $traceId = "";

    /**
     * @param string $type
     * @param int|null $status
     * @param string $title
     * @param string $detail
     * @param string $instance
     * @param string $traceId
     */
    public function __construct(string $type = "", ?int $status = null, string $title = "", string $detail = "", string $instance = "", string $traceId = "") {
        if (!empty($type)) {
            $this->setType($type);
        }

        if (!empty($status)) {
            $this->setStatus($status);
        }

        if (!empty($title)) {
            $this->setTitle($title);
        }

        if (!empty($detail)) {
            $this->setDetail($detail);
        }

        if (!empty($instance)) {
            $this->setInstance($instance);
        }

        if (!empty($traceId)) {
            $this->setTraceId($traceId);
        }

        if (empty($instance)) {
            $this->setInstance(Kernel::getInstance()->getRequest()->getPathAsString());
        }

    }

    /**
     * @return string
     */
    public function getInstance(): string {
        return $this->instance;
    }

    /**
     * @param string $instance
     */
    public function setInstance(string $instance): void {
        $this->instance = $instance;
    }

    /**
     * @param string $type
     * @param int|null $status
     * @param string $title
     * @param string $detail
     * @param string $instance
     * @param string $traceId
     * @return static
     */
    public static function new(string $type = "", ?int $status = null, string $title = "", string $detail = "", string $instance = "", string $traceId = ""): self {
        $class = static::class;
        $error = new $class($type, $status, $title, $detail, $instance);

        $error->add();

        return $error;
    }

    public function add(): void {
        Kernel::getInstance()->getErrorPool()->addError($this);

        $this->process();
    }

    protected function process(): void {
        $kernel = Kernel::getInstance();

        try {
            $kernel->getRoute()->setResponseStatus($kernel->getRequest()->getHttpStatusCode($this->getStatus()));
        } catch (Exception $exception) {
            Log::error("Invalid HTTP Status code given", $exception->getTrace());
        }

        $traceId = Log::notice("Client error", $this->getTemplateData());
        $this->setTraceId($traceId);
    }

    /**
     * @return int
     */
    public function getStatus(): int {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void {
        $this->status = $status;
    }

    /**
     * @return array
     */
    protected function getTemplateData(): array {
        return [
            "type" => $this->getType(),
            "status" => $this->getStatus(),
            "title" => $this->getTitle(),
            "detail" => $this->getDetail(),
            "instance" => $this->getInstance(),
            "traceId" => $this->getTraceId()
        ];
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getDetail(): string {
        return $this->detail;
    }

    /**
     * @param string $detail
     */
    public function setDetail(string $detail): void {
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getTraceId(): string {
        return $this->traceId;
    }

    /**
     * @param string $traceId
     */
    public function setTraceId(string $traceId): void {
        $this->traceId = $traceId;
    }

    public function addToJsonPool(): void {
        $jsonPool = Kernel::getInstance()->getJsonPool();

        $jsonPool->addJson(json_encode($this->getTemplateData()));
    }

}
