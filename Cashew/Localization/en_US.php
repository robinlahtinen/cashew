<?php


namespace Cashew\Localization;

use Carbon\Carbon;
use Cashew\Kernel\Localization\AbstractLocalization;

/**
 * Class en-US
 * @package Cashew\Localization
 */
class en_US extends AbstractLocalization {

    protected string $lang = "en-US";

    protected array $strings = [
        "app" => "Cashew"
    ];

    public function init(): void {
        Carbon::setLocale("en");
    }

}
