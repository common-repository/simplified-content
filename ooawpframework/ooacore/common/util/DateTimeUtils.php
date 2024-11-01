<?php

/**
 * Generic helpful date time utils
 *
 * Class DateTimeUtils
 */
class DateTimeUtils {

    /**
     * Convert a date from one format to another
     *
     * @param $sourceFormat
     * @param $targetFormat
     * @param $value
     */
    public static function convertDate($sourceFormat, $targetFormat, $value) {
        $dateObject = date_create_from_format($sourceFormat, $value);
        if ($dateObject) {
            return $dateObject->format($targetFormat);
        } else {
            return null;
        }
    }


    /**
     * Convert seconds to an elapsed time value
     *
     * @param $seconds
     */
    public static function convertSecondsToElapsedTime($seconds) {

        // Hours component
        $hours = floor($seconds / 3600);

        // Minutes component
        $remainder = $seconds - ($hours * 3600);
        $minutes = floor($remainder / 60);

        // Seconds component
        $seconds = round($remainder - ($minutes * 60));


        return sprintf("%02d", $hours) . ":" . sprintf("%02d", $minutes) . ":" . sprintf("%02d", $seconds);

    }


} 