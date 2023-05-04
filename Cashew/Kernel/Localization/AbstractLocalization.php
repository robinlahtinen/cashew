<?php


namespace Cashew\Kernel\Localization;


use Cashew\Kernel\Log\Log;

abstract class AbstractLocalization {

    protected string $lang = "";

    protected array $strings = [];

    abstract public function init(): void;

    /**
     * Get string.
     *
     * @param string $id String id.
     * @return string Localized string.
     */
    public function getString(string $id): string {
        $string = $id;

        if (!empty($this->getStrings()[$id])) {
            $string = $this->getStrings()[$id];
        } else {
            Log::info("Requested string \"" . $string . "\" not found for \"" . $this->getLang() . "\" localization.");
        }

        return $string;
    }

    /**
     * @return array
     */
    public function getStrings(): array {
        return $this->strings;
    }

    /**
     * @param array $strings
     */
    public function setStrings(array $strings): void {
        $this->strings = $strings;
    }

    /**
     * @return string
     */
    public function getLang(): string {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang): void {
        $this->lang = $lang;
    }

}
