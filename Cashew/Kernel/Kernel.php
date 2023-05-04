<?php

namespace Cashew\Kernel;

use Cashew\Kernel\Config\Config;
use Cashew\Kernel\Config\ConfigFactory;
use Cashew\Kernel\Error\ErrorPool;
use Cashew\Kernel\GraphQL\GraphQL;
use Cashew\Kernel\GraphQL\GraphQLFactory;
use Cashew\Kernel\Helper\Helper;
use Cashew\Kernel\JsonPool\JsonPool;
use Cashew\Kernel\Localization\AbstractLocalization;
use Cashew\Kernel\Log\Log;
use Cashew\Kernel\Mail\MailInterface;
use Cashew\Kernel\Mail\MailSymfonyMailer;
use Cashew\Kernel\Request\Request;
use Cashew\Kernel\Route\Route;
use Cashew\Kernel\View\ViewInterface;
use Cashew\Kernel\View\ViewTwig;
use Cashew\Localization\en_US;
use Exception;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use PDO;

/**
 * Class Kernel
 * @package Cashew\Kernel
 * @psalm-suppress MissingConstructor
 * @author Robin Lahtinen
 */
final class Kernel {

    /**
     * @var Kernel Static kernel instance.
     */
    private static Kernel $instance;

    /**
     * @var Route Route instance.
     */
    private Route $route;

    /**
     * @var Request Request instance.
     */
    private Request $request;

    /**
     * @var ViewInterface View instance.
     */
    private ViewInterface $view;

    /**
     * @var MailInterface Mail instance.
     */
    private MailInterface $mail;

    /**
     * @var string Root directory.
     */
    private string $rootDirectory = "";

    /**
     * @var Config Config instance.
     */
    private Config $config;

    /**
     * @var PDO Database connection.
     */
    private PDO $pdo;

    /**
     * @var AbstractLocalization Localization instance.
     */
    private AbstractLocalization $localization;

    /**
     * @var string Cashew version.
     */
    private string $version = "0.1.0";

    /**
     * @var string Logging trace id.
     */
    private string $traceId = "";

    /**
     * @var bool True if initialized, false if otherwise.
     */
    private bool $initialized = false;

    private JsonPool $jsonPool;

    private Configuration $jwtConfiguration;

    private GraphQL $graphQL;

    private ErrorPool $errorPool;

    /**
     * Get static kernel instance.
     *
     * @return Kernel Kernel instance.
     */
    public static function getInstance(): Kernel {
        return self::$instance;
    }

    /**
     * Set static kernel instance.
     *
     * @param Kernel $instance Kernel instance.
     */
    public static function setInstance(Kernel $instance): void {
        self::$instance = $instance;
    }

    /**
     * Initialize kernel.
     *
     * @throws Exception
     */
    final public function init(): void {
        $configFactory = new ConfigFactory();
        $this->setConfig($configFactory->getConfigFromIni($this->getRootDirectory() . DIRECTORY_SEPARATOR . "config.ini"));

        $this->setTraceId(Helper::getRandomToken(8));
        Log::setTraceId($this->getTraceId());

        $this->setMail(new MailSymfonyMailer());

        $this->initJwtConfiguration();
        $this->setErrorPool(new ErrorPool());

        $this->setRequest(new Request());
        $this->setPdo($this->getPdoByConfig());

        $graphQLFactory = new GraphQLFactory();
        $this->setGraphQL($graphQLFactory->getKernelGraphQL());

        $this->setJsonPool(new JsonPool());
        $this->setRoute(new Route());

        $this->setLocalization(new en_US());
        $this->getLocalization()->init();

        $this->setView(new ViewTwig());

        $this->setInitialized(true);

        Log::debug("Kernel initialization completed.");

        $config = $this->getConfig();

        if ($config->isProduction() && $config->isDebug()) {
            Log::warning("Debug is used on production.");
        }
    }

    /**
     * @return string
     */
    public function getRootDirectory(): string {
        return $this->rootDirectory;
    }

    /**
     * @param string $rootDirectory
     */
    public function setRootDirectory(string $rootDirectory): void {
        $this->rootDirectory = $rootDirectory;
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

    protected function initJwtConfiguration(): void {
        $config = Configuration::forSymmetricSigner(new Sha512(), InMemory::base64Encoded($this->getConfig()->getSecret()));

        $issuedBy = new IssuedBy($this->getConfig()->getDomain());

        $config->setValidationConstraints($issuedBy);

        $this->setJwtConfiguration($config);
    }

    /**
     * @return Config
     */
    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void {
        $this->config = $config;
    }

    /**
     * @return PDO PDO instance.
     */
    private function getPdoByConfig(): PDO {
        $config = $this->getConfig();

        $dsn = $config->getPdoDriver() . ":dbname=" . $config->getPdoDatabase() . ";host=" . $config->getPdoHost();

        return new PDO($dsn, $config->getPdoUsername(), $config->getPdoPassword());
    }

    /**
     * @return AbstractLocalization
     */
    public function getLocalization(): AbstractLocalization {
        return $this->localization;
    }

    /**
     * @param AbstractLocalization $localization
     */
    public function setLocalization(AbstractLocalization $localization): void {
        $this->localization = $localization;
    }

    /**
     * @return Route
     */
    public function getRoute(): Route {
        return $this->route;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route): void {
        $this->route = $route;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void {
        $this->request = $request;
    }

    /**
     * @return ViewInterface
     */
    public function getView(): ViewInterface {
        return $this->view;
    }

    /**
     * @param ViewInterface $view
     */
    public function setView(ViewInterface $view): void {
        $this->view = $view;
    }

    /**
     * @return PDO
     */
    public function getPdo(): PDO {
        return $this->pdo;
    }

    /**
     * @param PDO $pdo
     */
    public function setPdo(PDO $pdo): void {
        $this->pdo = $pdo;
    }

    /**
     * @return string
     */
    public function getVersion(): string {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion(string $version): void {
        $this->version = $version;
    }

    /**
     * @return bool
     */
    public function isInitialized(): bool {
        return $this->initialized;
    }

    /**
     * @param bool $initialized
     */
    public function setInitialized(bool $initialized): void {
        $this->initialized = $initialized;
    }

    /**
     * @return JsonPool
     */
    public function getJsonPool(): JsonPool {
        return $this->jsonPool;
    }

    /**
     * @param JsonPool $jsonPool
     */
    public function setJsonPool(JsonPool $jsonPool): void {
        $this->jsonPool = $jsonPool;
    }

    /**
     * @return Configuration
     */
    public function getJwtConfiguration(): Configuration {
        return $this->jwtConfiguration;
    }

    /**
     * @param Configuration $jwtConfiguration
     */
    public function setJwtConfiguration(Configuration $jwtConfiguration): void {
        $this->jwtConfiguration = $jwtConfiguration;
    }

    /**
     * @return GraphQL
     */
    public function getGraphQL(): GraphQL {
        return $this->graphQL;
    }

    /**
     * @param GraphQL $graphQL
     */
    public function setGraphQL(GraphQL $graphQL): void {
        $this->graphQL = $graphQL;
    }

    /**
     * @return ErrorPool
     */
    public function getErrorPool(): ErrorPool {
        return $this->errorPool;
    }

    /**
     * @param ErrorPool $errorPool
     */
    public function setErrorPool(ErrorPool $errorPool): void {
        $this->errorPool = $errorPool;
    }

    /**
     * @return MailInterface
     */
    public function getMail(): MailInterface {
        return $this->mail;
    }

    /**
     * @param MailInterface $mail
     */
    public function setMail(MailInterface $mail): void {
        $this->mail = $mail;
    }

}
