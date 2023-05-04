<?php


namespace Cashew\Kernel\Component;

use Cashew\Kernel\Kernel;

/**
 * Class AbstractComponent
 * @package Cashew\Kernel\Component
 * @author Robin Lahtinen
 */
abstract class AbstractComponent implements ComponentInterface {
    /**
     * @var string Template folder.
     */
    protected string $templateFolder = "";

    /**
     * @var string Template file.
     */
    protected string $template = "";

    /**
     * @var ComponentPool Sub-component pool.
     */
    protected ComponentPool $subComponentPool;

    /**
     * @var array Template fields.
     */
    protected array $fields = [];

    protected string $id = "";

    /**
     * AbstractComponent constructor.
     * @param ComponentInterface[] $components Component instances.
     */
    public function __construct(array $components = []) {
        $this->setSubComponentPool(new ComponentPool($components));

        $this->constructCallback();
    }

    /**
     * Callback on construct.
     */
    public function constructCallback(): void {

    }

    /**
     * @return string
     */
    public function render(): string {
        $subRender = $this->subRender();

        $this->assign("subRender", $subRender);

        if (empty($this->getTemplate())) {
            return $subRender;
        }

        $this->renderCallback();

        $view = Kernel::getInstance()->getView();

        $prefix = "";
        $suffix = "";

        if (!empty($this->getTemplateFolder())) {
            $prefix = $this->getTemplateFolder() . "/";
        }

        if (!empty($view->getTemplateFileExtension())) {
            $suffix = $view->getTemplateFileExtension();
        }

        return $view->render($prefix . $this->getTemplate() . $suffix, $this->getFields());
    }

    /**
     * @return string
     */
    protected function subRender(): string {
        $rendered = "";

        foreach ($this->getSubComponentPool()->getComponents() as $component) {
            $rendered .= $component->render();
        }

        return $rendered;
    }

    /**
     * @return ComponentPool
     */
    public function getSubComponentPool(): ComponentPool {
        return $this->subComponentPool;
    }

    /**
     * @param ComponentPool $subComponentPool
     */
    public function setSubComponentPool(ComponentPool $subComponentPool): void {
        $this->subComponentPool = $subComponentPool;
    }

    /**
     * Assign a value to template field.
     *
     * @param string $field Field to assign.
     * @param mixed $value Value to assign.
     */
    public function assign(string $field, mixed $value): void {
        $fields = $this->getFields();

        $fields[$field] = $value;

        $this->setFields($fields);
    }

    /**
     * @return array
     */
    public function getFields(): array {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields(array $fields): void {
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function getTemplate(): string {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void {
        $this->template = $template;
    }

    /**
     * Callback before rendering.
     */
    public function renderCallback(): void {
        $this->assign("id", $this->getId());
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTemplateFolder(): string {
        return $this->templateFolder;
    }

    /**
     * @param string $templateFolder
     */
    public function setTemplateFolder(string $templateFolder): void {
        $this->templateFolder = $templateFolder;
    }
}
