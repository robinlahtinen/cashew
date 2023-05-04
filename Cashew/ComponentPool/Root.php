<?php


namespace Cashew\ComponentPool;

use Cashew\Component\Debug\DebugBar;
use Cashew\Component\Template\Body;
use Cashew\Component\Template\Head;
use Cashew\Component\Template\Html;
use Cashew\Kernel\Component\AbstractComponent;
use Cashew\Kernel\Component\ComponentPool;
use Cashew\Kernel\Kernel;

class Root extends ComponentPool {

    /**
     * AbstractComponent constructor.
     * @param AbstractComponent[]|ComponentPool[] $components Component instances.
     * @psalm-suppress InvalidArgument
     */
    public function __construct(array $components = []) {
        $config = Kernel::getInstance()->getConfig();

        if ($config->isDebug()) {
            $components[] = new DebugBar();
        }

        $preset = [
            new Html([
                new Head(),
                new Body($components)
            ])
        ];

        parent::__construct($preset);
    }

}
