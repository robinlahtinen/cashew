<?php


namespace Cashew\Kernel\View;

use Cashew\Kernel\Component\ComponentPool;
use Cashew\Kernel\Kernel;
use Cashew\Twig\Extension\CashewExtension;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use voku\helper\HtmlMin;

/**
 * Class ViewTwig
 * @package Cashew\Kernel\View
 */
class ViewTwig implements ViewInterface {

    /**
     * @var ComponentPool Component pool instance.
     */
    protected ComponentPool $componentPool;

    /**
     * @var Environment Twig instance.
     */
    protected Environment $twig;

    /**
     * @var HtmlMin HtmlMin instance.
     */
    protected HtmlMin $htmlMin;

    /**
     * ViewTwig constructor.
     */
    public function __construct() {
        $this->init();
    }

    protected function init(): void {
        $this->setComponentPool(new ComponentPool());

        $rootDirectory = Kernel::getInstance()->getRootDirectory();

        $twigLoader = new FilesystemLoader($rootDirectory . "/Template");
        $twig = new Environment($twigLoader, $this->getTwigConfig());

        $twig->addExtension(new CashewExtension());

        $this->setTwig($twig);

        $this->setHtmlMin(new HtmlMin());
    }

    /**
     * Get Twig config.
     *
     * @return array Twig config.
     */
    protected function getTwigConfig(): array {
        $kernel = Kernel::getInstance();

        $rootDirectory = $kernel->getRootDirectory();

        $twigConfig = [
            "cache" => $rootDirectory . "/Temporary/Twig/Cache",
            "strict_variables" => true
        ];

        if ($kernel->getConfig()->isDebug()) {
            $twigConfig = array_merge($twigConfig, [
                "debug" => true,
                "auto_reload" => true
            ]);
        }

        return $twigConfig;
    }

    public function renderComponents(): string {
        return $this->doMinify($this->getComponentPool()->render());
    }

    /**
     * Minify HTML.
     *
     * @param string $html HTML to minify.
     * @return string Minified HTML.
     */
    protected function doMinify(string $html): string {
        if (!Kernel::getInstance()->getConfig()->isDebug()) {
            return $this->getHtmlMin()->minify($html);
        }

        return $html;
    }

    /**
     * @return HtmlMin
     */
    public function getHtmlMin(): HtmlMin {
        return $this->htmlMin;
    }

    /**
     * @param HtmlMin $htmlMin
     */
    public function setHtmlMin(HtmlMin $htmlMin): void {
        $this->htmlMin = $htmlMin;
    }

    public function render(string $template, array $fields = []): string {
        return $this->getTwig()->render($template, $fields);
    }

    /**
     * @return Environment
     */
    protected function getTwig(): Environment {
        return $this->twig;
    }

    /**
     * @param Environment $twig
     */
    protected function setTwig(Environment $twig): void {
        $this->twig = $twig;
    }

    /**
     * @return ComponentPool
     */
    public function getComponentPool(): ComponentPool {
        return $this->componentPool;
    }

    /**
     * @param ComponentPool $componentPool
     */
    public function setComponentPool(ComponentPool $componentPool): void {
        $this->componentPool = $componentPool;
    }

    /**
     * @return string File extension.
     */
    public function getTemplateFileExtension(): string {
        return ".twig";
    }

}
