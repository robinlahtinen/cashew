<?php


namespace Cashew\Component\Template;

use Cashew\Kernel\Component\AbstractComponent;
use Cashew\Kernel\Kernel;

/**
 * Class Html
 * @package Cashew\Component
 */
class Html extends AbstractComponent {

    protected string $templateFolder = "main";

    protected string $template = "html";

    public function constructCallback(): void {
        parent::constructCallback();

        $this->setId("html");
        $this->assign("lang", Kernel::getInstance()->getLocalization()->getLang());
    }

}
