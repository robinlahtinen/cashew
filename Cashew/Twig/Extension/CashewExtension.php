<?php


namespace Cashew\Twig\Extension;

use Cashew\Kernel\Helper\Helper;
use Cashew\Kernel\Kernel;
use DateTime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class CashewExtension
 * @package Cashew\Twig\Extension
 */
class CashewExtension extends AbstractExtension {

    /**
     * @return TwigFunction[]
     */
    public function getFunctions() {
        return [
            new TwigFunction("_", [$this, "getLocalization"])
        ];
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters() {
        return [
            new TwigFilter("ago", [$this, "parseAgo"], ["pre_escape" => "html", "is_safe" => ["html"]]),
            new TwigFilter("dateString", [$this, "parseDateString"], ["pre_escape" => "html", "is_safe" => ["html"]]),
            new TwigFilter("time", [$this, "parseTime"], ["pre_escape" => "html", "is_safe" => ["html"]]),
            new TwigFilter("dmy", [$this, "parseDmy"], ["pre_escape" => "html", "is_safe" => ["html"]])
        ];
    }

    /**
     * @param string $string
     * @return string
     */
    public function getLocalization(string $string): string {
        return Kernel::getInstance()->getLocalization()->getString($string);
    }

    /**
     * @param DateTime $dateTime
     * @return string
     * @throws \Exception
     */
    public function parseAgo(DateTime $dateTime): string {
        return Helper::parseAgo($dateTime);
    }

    /**
     * @param DateTime $dateTime
     * @return string
     * @throws \Exception
     */
    public function parseDmy(DateTime $dateTime): string {
        return Helper::parseDmy($dateTime);
    }

    /**
     * @param DateTime $dateTime
     * @return string
     * @throws \Exception
     */
    public function parseTime(DateTime $dateTime): string {
        return Helper::parseTime($dateTime);
    }

    /**
     * @param DateTime $dateTime
     * @return string
     */
    public function parseDateString(DateTime $dateTime): string {
        return Helper::parseDateString($dateTime);
    }

}
