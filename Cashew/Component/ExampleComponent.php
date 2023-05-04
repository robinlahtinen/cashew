<?php

namespace Cashew\Component;


use Cashew\Kernel\Component\AbstractComponent;

class ExampleComponent extends AbstractComponent {

    protected string $templateFolder = "example";

    protected string $template = "test1";

    public function renderCallback(): void {
        parent::renderCallback();

        $this->assign("example", "Hello world!");
    }

}
