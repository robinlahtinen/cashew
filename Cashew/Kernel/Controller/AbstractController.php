<?php


namespace Cashew\Kernel\Controller;

use Cashew\Error\ErrorNotAcceptable;
use Cashew\Error\ErrorNotFound;
use Cashew\Kernel\AcceptHeader\AcceptHeader;
use Cashew\Kernel\Component\ComponentPool;
use Cashew\Kernel\Helper\Helper;
use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;
use Negotiation\Negotiator;
use ReflectionClass;

/**
 * Class AbstractController
 * @package Cashew\Kernel\Controller
 * @author Robin Lahtinen
 */
abstract class AbstractController {

    /**
     * @var AbstractController Parent controller instance.
     */
    protected AbstractController $parentController;

    /**
     * @var ControllerPool Sub-controller instances.
     */
    protected ControllerPool $subControllerPool;

    /**
     * @var array Current path.
     */
    protected array $currentPath = [];

    /**
     * @var string[] Paths.
     */
    protected array $paths = [];

    /**
     * @var array Sub-controller paths.
     */
    protected array $subControllerPaths = [];

    protected string $requestedMethod = "";

    /**
     * @var string[] Acceptable media types.
     */
    protected array $clientContentNegotiation = [
        "application/json"
    ];

    /**
     * AbstractController constructor.
     * @param AbstractController[] $subControllers Sub-controller instances.
     */
    public function __construct(array $subControllers = []) {
        $this->setParentController($this);
        $this->setSubControllerPool(new ControllerPool($subControllers));
    }

    /**
     * Primary callback.
     * @psalm-suppress all False positives.
     * @return bool True if useful, false if otherwise.
     */
    public function primaryCallback(): bool {
        $kernel = Kernel::getInstance();
        $config = $kernel->getConfig();
        $request = $kernel->getRequest();

        if (!empty($this->getClientContentNegotiation()) && !$config->isDebug() && !$request->isOptions()) {
            $clientContentNegotiationSuccess = false;
            $jsonType = false;

            $accept = $request->getAcceptAsArray();

            foreach ($this->getClientContentNegotiation() as $mimeType) {
                if ($accept["content"] == "application/json") {
                    $jsonType = true;
                }

                if ($accept["content"] == $mimeType) {
                    $clientContentNegotiationSuccess = true;

                    break;
                }
            }

            if (!$clientContentNegotiationSuccess) {
                Log::notice("Tried to access the controller with a non-whitelisted content type. (Accept: \"{$request->getAccept()}\")");

                if ($jsonType === true) {
                    ErrorNotAcceptable::new();
                } else {
                    // Redirect end-user to a proper website.
                    $kernel->getRoute()->redirect($config->getFailsafeUri());
                }

                return true;
            }
        }

        $currentPath = $this->getCurrentPath();
        $paths = $this->getPaths();

        $method = "";

        if (empty($currentPath) && !empty($paths["index"])) {
            $currentPath = [
                "index"
            ];
        }

        if (!empty($currentPath)) {
            if (!empty($paths["index"])) {
                $method = $paths["index"];
            }

            if (array_key_exists($currentPath[0], $paths)) {
                $method = $paths[$currentPath[0]];

                $currentPath = Helper::removeFirstElementOfArray($currentPath);
            }

            if (is_array($method)) {
                $preParameters = [];

                $index = 0;

                while (is_array($method)) {
                    $subPaths = $method;

                    if (count($currentPath) > 1) {
                        $preParameters[$index] = $currentPath[0];

                        $currentPath = Helper::removeFirstElementOfArray($currentPath);
                    }

                    if (!empty($currentPath) && array_key_exists($currentPath[0], $subPaths)) {
                        $method = $subPaths[$currentPath[0]];

                        $currentPath = Helper::removeFirstElementOfArray($currentPath);
                    } elseif (!empty($preParameters) && !empty($subPaths[$preParameters[$index]]["index"])) {
                        $method = $subPaths[$preParameters[$index]]["index"];

                        array_splice($preParameters, $index, 1);
                    } elseif (!empty($subPaths["index"])) {
                        $method = $subPaths["index"];
                    } else {
                        // Break method loop.
                        $method = "";
                    }

                    $index++;
                }

                $currentPath = array_merge($preParameters, $currentPath);
            }

            $this->setCurrentPath($currentPath);

            if (!empty($method) && method_exists($this, $method)) {
                $reflectionClass = new ReflectionClass($this);
                $reflectionMethod = $reflectionClass->getMethod($method);

                $methodParameters = $reflectionMethod->getNumberOfParameters();

                if (count($currentPath) <= $methodParameters && ((!empty($currentPath[0]) && empty($this->getSubControllerPaths()[$currentPath[0]])) || empty($currentPath))) {
                    Log::debug("Calling requested controller method. (Requested method: {$method})");

                    $this->setRequestedMethod($method);

                    if (call_user_func_array([$this, $method], $currentPath) === false) {
                        $this->callNotFound();

                        Log::warning("Calling function failed.");
                    }

                    return true;
                } else {
                    Log::debug("Trying to use a controller method with less parameters than requested. (Requested method: {$method})");
                }
            }
        }

        if (!empty($this->getSubControllerPaths()) || $this->callPrimarySubCallbacks() === false) {
            $this->callNotFound();
        }

        return false;
    }

    /**
     * @return array
     */
    public function getClientContentNegotiation(): array {
        return $this->clientContentNegotiation;
    }

    /**
     * @param array $clientContentNegotiation
     */
    public function setClientContentNegotiation(array $clientContentNegotiation): void {
        $this->clientContentNegotiation = $clientContentNegotiation;
    }

    /**
     * @return array
     */
    public function getCurrentPath(): array {
        return $this->currentPath;
    }

    /**
     * @param array $currentPath
     */
    public function setCurrentPath(array $currentPath): void {
        $this->currentPath = $currentPath;
    }

    /**
     * @return array|string[]
     */
    public function getPaths(): array {
        return $this->paths;
    }

    /**
     * @param string[] $paths
     */
    public function setPaths(array $paths): void {
        $this->paths = $paths;
    }

    /**
     * @return array
     */
    public function getSubControllerPaths(): array {
        if (empty($this->subControllerPaths)) {
            $paths = [];

            foreach ($this->getSubControllerPool()->getControllers() as $subController) {
                $paths = array_merge($paths, $subController->getPaths());
            }

            $this->setSubControllerPaths($paths);
        }

        return $this->subControllerPaths;
    }

    /**
     * @param array $subControllerPaths
     */
    public function setSubControllerPaths(array $subControllerPaths): void {
        $this->subControllerPaths = $subControllerPaths;
    }

    /**
     * @return ControllerPool
     */
    public function getSubControllerPool(): ControllerPool {
        return $this->subControllerPool;
    }

    /**
     * @param ControllerPool $subControllerPool
     */
    public function setSubControllerPool(ControllerPool $subControllerPool): void {
        $this->subControllerPool = $subControllerPool;
    }

    public function callNotFound(): void {
        ErrorNotFound::new();
    }

    /**
     * Call primary sub callbacks.
     *
     * @return bool
     */
    protected function callPrimarySubCallbacks(): bool {
        $currentPath = $this->getCurrentPath();

        foreach ($this->getSubControllerPool()->getControllers() as $subController) {
            $subController->setParentController($this);
            $subController->setCurrentPath($currentPath);

            if ($subController->primaryCallback() === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ComponentPool $componentPool
     */
    public function setComponentPool(ComponentPool $componentPool): void {
        Kernel::getInstance()->getView()->setComponentPool($componentPool);
    }

    /**
     * @return AbstractController
     */
    public function getParentController(): AbstractController {
        return $this->parentController;
    }

    /**
     * @param AbstractController $parentController
     */
    public function setParentController(AbstractController $parentController): void {
        $this->parentController = $parentController;
    }

    /**
     * @return string
     */
    public function getRequestedMethod(): string {
        return $this->requestedMethod;
    }

    /**
     * @param string $requestedMethod
     */
    public function setRequestedMethod(string $requestedMethod): void {
        $this->requestedMethod = $requestedMethod;
    }

}
