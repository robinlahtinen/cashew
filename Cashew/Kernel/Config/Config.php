<?php


namespace Cashew\Kernel\Config;

/**
 * Class Config
 * @package Cashew\Kernel\Config
 */
class Config {

    protected string $domain = "";

    protected string $domainScheme = "";

    protected string $origin = "";

    protected string $failsafeUri = "";

    /**
     * @var bool Production status.
     */
    protected bool $production = true;

    protected bool $logging = true;

    protected bool $debug = false;

    protected string $secret = "";

    /**
     * @var string
     */
    protected string $pdoDriver = "";

    /**
     * @var string
     */
    protected string $pdoHost = "";

    /**
     * @var string
     */
    protected string $pdoDatabase = "";
    /**
     * @var string
     */
    protected string $pdoUsername = "";
    /**
     * @var string
     */
    protected string $pdoPassword = "";
    /**
     * @var bool
     */
    protected bool $mailEnabled = false;
    /**
     * @var string
     */
    protected string $mailDriver = "";
    /**
     * @var string
     */
    protected string $mailHost = "";
    /**
     * @var string
     */
    protected string $mailPort = "";
    /**
     * @var string
     */
    protected string $mailUsername = "";
    /**
     * @var string
     */
    protected string $mailPassword = "";
    protected string $mailSystemEmail = "";
    /**
     * @var array
     */
    protected array $logLevels = [];

    /**
     * @return string
     */
    public function getPdoDriver(): string {
        return $this->pdoDriver;
    }

    /**
     * @param string $pdoDriver
     */
    public function setPdoDriver(string $pdoDriver): void {
        $this->pdoDriver = $pdoDriver;
    }

    /**
     * @return string
     */
    public function getPdoHost(): string {
        return $this->pdoHost;
    }

    /**
     * @param string $pdoHost
     */
    public function setPdoHost(string $pdoHost): void {
        $this->pdoHost = $pdoHost;
    }

    /**
     * @return string
     */
    public function getPdoDatabase(): string {
        return $this->pdoDatabase;
    }

    /**
     * @param string $pdoDatabase
     */
    public function setPdoDatabase(string $pdoDatabase): void {
        $this->pdoDatabase = $pdoDatabase;
    }

    /**
     * @return string
     */
    public function getPdoUsername(): string {
        return $this->pdoUsername;
    }

    /**
     * @param string $pdoUsername
     */
    public function setPdoUsername(string $pdoUsername): void {
        $this->pdoUsername = $pdoUsername;
    }

    /**
     * @return string
     */
    public function getPdoPassword(): string {
        return $this->pdoPassword;
    }

    /**
     * @param string $pdoPassword
     */
    public function setPdoPassword(string $pdoPassword): void {
        $this->pdoPassword = $pdoPassword;
    }

    /**
     * @return bool
     */
    public function isProduction(): bool {
        return $this->production;
    }

    /**
     * @param bool $production
     */
    public function setProduction(bool $production): void {
        $this->production = $production;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void {
        $this->debug = $debug;
    }

    /**
     * @return bool
     */
    public function isLogging(): bool {
        return $this->logging;
    }

    /**
     * @param bool $logging
     */
    public function setLogging(bool $logging): void {
        $this->logging = $logging;
    }

    /**
     * @return array
     */
    public function getLogLevels(): array {
        return $this->logLevels;
    }

    /**
     * @param array $logLevels
     */
    public function setLogLevels(array $logLevels): void {
        $this->logLevels = $logLevels;
    }

    /**
     * @return string
     */
    public function getDomain(): string {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getSecret(): string {
        return $this->secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void {
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getOrigin(): string {
        return $this->origin;
    }

    /**
     * @param string $origin
     */
    public function setOrigin(string $origin): void {
        $this->origin = $origin;
    }

    /**
     * @return string
     */
    public function getDomainScheme(): string {
        return $this->domainScheme;
    }

    /**
     * @param string $domainScheme
     */
    public function setDomainScheme(string $domainScheme): void {
        $this->domainScheme = $domainScheme;
    }

    /**
     * @return string
     */
    public function getFailsafeUri(): string {
        return $this->failsafeUri;
    }

    /**
     * @param string $failsafeUri
     */
    public function setFailsafeUri(string $failsafeUri): void {
        $this->failsafeUri = $failsafeUri;
    }

    /**
     * @return string
     */
    public function getMailDriver(): string {
        return $this->mailDriver;
    }

    /**
     * @param string $mailDriver
     */
    public function setMailDriver(string $mailDriver): void {
        $this->mailDriver = $mailDriver;
    }

    /**
     * @return string
     */
    public function getMailHost(): string {
        return $this->mailHost;
    }

    /**
     * @param string $mailHost
     */
    public function setMailHost(string $mailHost): void {
        $this->mailHost = $mailHost;
    }

    /**
     * @return string
     */
    public function getMailPort(): string {
        return $this->mailPort;
    }

    /**
     * @param string $mailPort
     */
    public function setMailPort(string $mailPort): void {
        $this->mailPort = $mailPort;
    }

    /**
     * @return string
     */
    public function getMailUsername(): string {
        return $this->mailUsername;
    }

    /**
     * @param string $mailUsername
     */
    public function setMailUsername(string $mailUsername): void {
        $this->mailUsername = $mailUsername;
    }

    /**
     * @return string
     */
    public function getMailPassword(): string {
        return $this->mailPassword;
    }

    /**
     * @param string $mailPassword
     */
    public function setMailPassword(string $mailPassword): void {
        $this->mailPassword = $mailPassword;
    }

    /**
     * @return string
     */
    public function getMailSystemEmail(): string {
        return $this->mailSystemEmail;
    }

    /**
     * @param string $mailSystemEmail
     */
    public function setMailSystemEmail(string $mailSystemEmail): void {
        $this->mailSystemEmail = $mailSystemEmail;
    }

    /**
     * @return bool
     */
    public function isMailEnabled(): bool {
        return $this->mailEnabled;
    }

    /**
     * @param bool $mailEnabled
     */
    public function setMailEnabled(bool $mailEnabled): void {
        $this->mailEnabled = $mailEnabled;
    }

}
