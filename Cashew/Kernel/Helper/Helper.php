<?php


namespace Cashew\Kernel\Helper;

use Carbon\Carbon;
use Cashew\Kernel\Log\Log;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * Class Helper
 * @package Cashew\Kernel\Helper
 */
class Helper {

    /**
     * Remove first element of array.
     *
     * @param array $array Array.
     * @param bool $reorder True to re-order, false if otherwise.
     * @return array Given array.
     */
    public static function removeFirstElementOfArray(array $array, bool $reorder = true): array {
        if (!empty($array) && !empty($array[0])) {
            unset($array[0]);

            if ($reorder === true) {
                $array = array_values($array);
            }
        }

        return $array;
    }

    /**
     * Get array from ini.
     *
     * @param string $filename Ini file name.
     * @return array
     */
    public static function getArrayFromIni(string $filename): array {
        if (!file_exists($filename)) {
            Log::error("Requested ini-file doesn't exist: " . $filename);

            return [];
        }

        $array = parse_ini_file($filename, true, INI_SCANNER_TYPED);

        if (!is_array($array)) {
            Log::error("Parsing ini-file failed: " . $filename);

            return [];
        }

        return $array;
    }

    /**
     * @param DateTime $dateTime
     * @return string
     * @throws Exception
     */
    public static function parseAgo(DateTime $dateTime): string {
        $carbon = Carbon::instance($dateTime);
        $carbon->setTimezone(new DateTimeZone("UTC"));

        if ($carbon->diffInDays() > 1) {
            return $carbon->isoFormat("MMMM YYYY");
        }

        return $carbon->shortAbsoluteDiffForHumans();
    }

    /**
     * @param DateTime $dateTime
     * @return string
     */
    public static function parseDateString(DateTime $dateTime): string {
        return Carbon::instance($dateTime)->setTimezone(new DateTimeZone("UTC"))->isoFormat("MMMM YYYY");
    }

    /**
     * @param DateTime $dateTime
     * @return string
     */
    public static function parseDmy(DateTime $dateTime): string {
        return Carbon::instance($dateTime)->setTimezone(new DateTimeZone("UTC"))->toFormattedDateString();
    }

    /**
     * @param DateTime $dateTime
     * @return string
     */
    public static function parseTime(DateTime $dateTime): string {
        return Carbon::instance($dateTime)->setTimezone(new DateTimeZone("UTC"))->toTimeString("minutes");
    }

    /**
     * Get random bytes.
     *
     * @return string Random bytes.
     */
    public static function getRandomToken(int $length = 64): string {
        $bytes = "";

        try {
            $bytes = bin2hex(random_bytes($length / 2));
        } catch (Exception $exception) {
            Log::emergency("An appropriate source of randomness cannot be found.", $exception->getTrace());
        }

        return $bytes;
    }

}
