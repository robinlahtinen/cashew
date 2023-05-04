<?php


namespace Cashew\Component\Debug;

use Cashew\Kernel\Component\AbstractComponent;
use Cashew\Kernel\Kernel;

class DebugBar extends AbstractComponent {

    protected string $templateFolder = "debug";

    protected string $template = "bar";

    public function constructCallback(): void {
        parent::constructCallback();

        $this->setId("debugbar");

        $kernel = Kernel::getInstance();
        $config = $kernel->getConfig();

        $version = $kernel->getVersion();

        if (!$config->isProduction()) {
            $version .= "-" . date("Ymd");
        }

        $this->assign("version", $version);
        $this->assign("phpVersion", phpversion());
        $this->assign("production", $config->isProduction());
    }

    public function renderCallback(): void {
        parent::renderCallback();

        $executionTime = round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 3);

        $this->assign("executionTime", $executionTime);
    }

}
