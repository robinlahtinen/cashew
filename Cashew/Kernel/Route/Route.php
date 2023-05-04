<?php


namespace Cashew\Kernel\Route;

use Cashew\Error\ErrorNotFound;
use Cashew\Kernel\Controller\AbstractController;
use Cashew\Kernel\Controller\ControllerFactory;
use Cashew\Kernel\Helper\Helper;
use Cashew\Kernel\Kernel;
use Cashew\Kernel\Log\Log;

/**
 * Class Route
 * @package Cashew\Kernel\Route
 */
class Route {

    public const METHOD_VIEW = 0;

    public const METHOD_JSON = 1;

    public const METHOD_REDIRECT = 2;

    /**
     * @var int Method type.
     */
    protected int $method = self::METHOD_JSON;

    /**
     * @var string Redirect URI.
     */
    protected string $redirectUri = "";

    /**
     * @var AbstractController Main controller instance.
     */
    protected AbstractController $mainController;

    protected string $responseStatus = "200 OK";

    protected array $allowedMethods = [];

    /**
     * Get by path.
     *
     * @param AbstractController $defaultController Controller to default to.
     * @return string HTML.
     * @throws \ReflectionException
     */
    public function getByPath(AbstractController $defaultController): string {
        $controllerFactory = new ControllerFactory();

        $kernel = Kernel::getInstance();
        $request = $kernel->getRequest();

        $domain = $request->getHttpHost();
        $config = $kernel->getConfig();
        $configDomain = $config->getDomain();

        if ($domain !== $configDomain) {
            Log::alert("Tried to access the system from a non-standard domain. (Domain: {$domain})");

            $this->redirectRefresh();

            return $this->finish();
        }

        if (str_starts_with($request->getRawUri(), "//")) {
            Log::notice("Given path starts with multiple \"/\" characters.");

            $this->redirectRefresh();

            return $this->finish();
        }

        $path = $request->getPath();

        $controllerName = "";
        $controller = null;

        if (!empty($path)) {
            $controllerName = ucfirst($path[0]);
        }

        if (!empty($controllerName)) {
            if ($controllerFactory->doesControllerExistByName($controllerName)) {
                $controller = $controllerFactory->getControllerByName($controllerName);

                $path = Helper::removeFirstElementOfArray($path);
            } else {
                Log::debug("Controller not found by name \"{$controllerName}\". (Requested URI: {$_SERVER['REQUEST_URI']})");
            }
        } else {
            Log::debug("Using default controller.");

            $controller = $defaultController;
        }

        if (!is_object($controller)) {
            ErrorNotFound::new();

            return $this->finish();
        }

        Log::debug("Controller is set as \"" . get_class($controller) . "\" for requested URI: " . $_SERVER['REQUEST_URI']);

        $this->setMainController($controller);

        $controller->setParentController($controller);
        $controller->setCurrentPath($path);

        $controller->primaryCallback();

        return $this->finish();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function redirectRefresh(): void {
        $kernel = Kernel::getInstance();
        $config = $kernel->getConfig();
        $request = $kernel->getRequest();

        $this->redirect($config->getDomainScheme() . $config->getDomain() . $request->getPathAsString());
    }

    /**
     * Redirect to given URI.
     *
     * @param string $redirectUri Redirect URI.
     */
    public function redirect(string $redirectUri = ""): void {
        $this->setMethod(self::METHOD_REDIRECT);

        if (!empty($redirectUri)) {
            $this->setRedirectUri($redirectUri);
        }

        $this->finish();
        exit();
    }

    /**
     * @return string HTML.
     * @throws \Exception
     */
    public function finish(): string {
        $return = "";

        $kernel = Kernel::getInstance();
        $method = $this->getMethod();

        $view = $kernel->getView();

        $request = $kernel->getRequest();

        if (!$request->isOptions()) {
            $kernel->getMail()->sendEmails();

            if ($method === self::METHOD_VIEW) {
                Log::debug("Rendering HTML.");

                $return = $view->renderComponents();
            } elseif (!empty($view->getComponentPool())) {
                Log::debug("Non-empty component pool skipped.");
            }

            if ($method === self::METHOD_JSON) {
                Log::debug("Outputting JSON.");

                $errorPool = $kernel->getErrorPool();

                if ($errorPool->hasErrors()) {
                    header("Content-Type: application/problem+json; charset=UTF-8");
                } else {
                    header("Content-Type: application/json; charset=UTF-8");
                }

                $kernel->getErrorPool()->addToJsonPool();

                $return = $kernel->getJsonPool()->getJsonAsString();
            }

            if ($method === self::METHOD_REDIRECT) {
                $redirectUri = $this->getRedirectUri();

                Log::debug("Redirecting to: " . $redirectUri);

                header("Location: " . $redirectUri);

                $this->setResponseStatus("302 Found");
            }
        } else {
            $this->doAllowCredentialsHeader();
            $this->doAllowedMethodsHeader();
            $this->doAllowedHeadersHeader();

            Log::debug("Outputting empty body for OPTIONS request.");
        }

        $this->doResponseHeader();
        $this->doAccessControlAllowOrigin();

        Log::debug("Routing finished.");

        return $return;
    }

    /**
     * @return int
     */
    public function getMethod(): int {
        return $this->method;
    }

    /**
     * @param int $method
     */
    public function setMethod(int $method): void {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri(string $redirectUri): void {
        $this->redirectUri = $redirectUri;
    }

    protected function doAllowCredentialsHeader(): void {
        header("Access-Control-Allow-Credentials: true");
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function doAllowedMethodsHeader(): void {
        $methodNames = [];

        foreach ($this->getAllowedMethods() as $method) {
            $methodNames[] = Kernel::getInstance()->getRequest()->getMethodName($method);
        }

        if (!empty($methodNames)) {
            header("Access-Control-Allow-Methods: " . implode(", ", $methodNames));
        }
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array {
        return $this->allowedMethods;
    }

    /**
     * @param array $allowedMethods
     */
    public function setAllowedMethods(array $allowedMethods): void {
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * @return void
     */
    protected function doAllowedHeadersHeader(): void {
        header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization");
    }

    public function doResponseHeader(): void {
        header("HTTP/1.1 " . $this->getResponseStatus());
    }

    /**
     * @return string
     */
    public function getResponseStatus(): string {
        return $this->responseStatus;
    }

    /**
     * @param string $responseStatus
     */
    public function setResponseStatus(string $responseStatus): void {
        $this->responseStatus = $responseStatus;
    }

    /**
     * CORS
     * @return void
     */
    protected function doAccessControlAllowOrigin(): void {
        // TODO: Move to ini
        $domains = [
            "http://127.0.0.1",
            "http://localhost",
        ];

        $origin = Kernel::getInstance()->getRequest()->getOrigin();

        if (in_array($origin, $domains)) {
            header("Access-Control-Allow-Origin: " . $origin);
        }
    }

    /**
     * @return AbstractController
     */
    public function getMainController(): AbstractController {
        return $this->mainController;
    }

    /**
     * @param AbstractController $mainController
     */
    public function setMainController(AbstractController $mainController): void {
        $this->mainController = $mainController;
    }

}
