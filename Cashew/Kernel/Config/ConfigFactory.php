<?php


namespace Cashew\Kernel\Config;


use Cashew\Kernel\Helper\Helper;
use Exception;

class ConfigFactory {

    /**
     * @var bool[][][] Default ini fields.
     */
    protected array $defaultIniFields = [
        "main" => [
            "production" => [
                "required" => true
            ],
            "security" => [
                "required" => true
            ],
            "debug" => [
                "required" => true
            ]
        ]
    ];

    /**
     * Get config from ini.
     *
     * @param string $filename Config.ini file path.
     * @return Config Config instance.
     * @throws Exception Exception on missing required ini fields.
     */
    public function getConfigFromIni(string $filename): Config {
        $config = new Config();

        $defaultIniFields = $this->getDefaultIniFields();

        /**
         * @var bool[][] $iniFile
         */
        $iniFile = Helper::getArrayFromIni($filename);

        /**
         * @var bool[] $defaultIniField
         */
        foreach ($defaultIniFields as $defaultIniCategory => $defaultIniField) {
            /**
             * @var string $defaultIniCategory
             */
            if ($defaultIniFields[$defaultIniCategory][key($defaultIniField)]["required"] === true && !isset($iniFile[$defaultIniCategory][key($defaultIniField)])) {
                throw new Exception("Missing required config ini fields.");
            }
        }

        $config->setDomain((string)$iniFile["main"]["domain"]);
        $config->setDomainScheme((string)$iniFile["main"]["domain_scheme"]);
        $config->setOrigin((string)$iniFile["main"]["origin"]);
        $config->setFailsafeUri((string)$iniFile["main"]["failsafe_uri"]);
        $config->setProduction((bool)$iniFile["main"]["production"]);
        $config->setLogging((bool)$iniFile["main"]["logging"]);
        $config->setDebug((bool)$iniFile["main"]["debug"]);

        $config->setSecret((string)$iniFile["security"]["secret"]);

        $config->setPdoDriver((string)$iniFile["pdo"]["driver"]);
        $config->setPdoHost((string)$iniFile["pdo"]["host"]);
        $config->setPdoDatabase((string)$iniFile["pdo"]["database"]);
        $config->setPdoUsername((string)$iniFile["pdo"]["username"]);
        $config->setPdoPassword((string)$iniFile["pdo"]["password"]);

        $config->setMailEnabled((bool)$iniFile["mail"]["enabled"]);
        $config->setMailDriver((string)$iniFile["mail"]["driver"]);
        $config->setMailHost((string)$iniFile["mail"]["host"]);
        $config->setMailPort((string)$iniFile["mail"]["port"]);
        $config->setMailUsername((string)$iniFile["mail"]["username"]);
        $config->setMailPassword((string)$iniFile["mail"]["password"]);
        $config->setMailSystemEmail((string)$iniFile["mail"]["system_email"]);

        $logLevels = [];

        foreach ($iniFile["log_levels"] as $logLevel => $logLevelValue) {
            $logLevels[$logLevel] = (bool)$logLevelValue;
        }

        $config->setLogLevels($logLevels);

        return $config;
    }

    /**
     * @return bool[][][]
     */
    public function getDefaultIniFields(): array {
        return $this->defaultIniFields;
    }

    /**
     * @param bool[][][] $defaultIniFields
     */
    public function setDefaultIniFields(array $defaultIniFields): void {
        $this->defaultIniFields = $defaultIniFields;
    }

}
