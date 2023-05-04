<?php


namespace Cashew\Kernel\View;

use Cashew\Kernel\Component\ComponentPool;

/**
 * Interface View
 * @package Cashew\Kernel\View
 * @author Robin Lahtinen
 */
interface ViewInterface {

    public function __construct();

    public function render(string $template, array $fields = []): string;

    public function renderComponents(): string;

    public function getComponentPool(): ComponentPool;

    public function setComponentPool(ComponentPool $componentPool): void;

    public function getTemplateFileExtension(): string;

}
