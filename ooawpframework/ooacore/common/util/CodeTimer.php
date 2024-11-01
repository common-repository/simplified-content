<?php

include_once OOA_CORE_ROOT . "/common/util/OOALogger.php";

/**
 * Static timing class which times a block of code passed as an anonymous function and logs the results using OOA Logger.
 * It additionally returns the duration in case logging is disabled.
 */
class CodeTimer {


    private static $groupTimings = array();

    /**
     * Time the passed function
     *
     * @param $function
     */
    public static function timeFunction($function, $description = null) {

        $duration = self::time($function);

        OOALogger::log("Code Block " . ($description ? '"' . $description . '"' : "") . " Executed in " . $duration . " Seconds");

        return $duration;
    }


    /**
     * Record the timing of a passed function against a group.  All Timings for a group are added up.
     *
     * @param $function
     * @param $group
     */
    public static function recordFunctionTimingForGroup($function, $group) {

        $functionTiming = CodeTimer::time($function);
        if (isset(CodeTimer::$groupTimings[$group])) {
            CodeTimer::$groupTimings[$group] += $functionTiming;
        } else {
            CodeTimer::$groupTimings[$group] = $functionTiming;
        }
    }


    /**
     * Report timings for a group
     *
     * @param $group
     */
    public static function reportTimingsForGroup($group) {
        if (isset(CodeTimer::$groupTimings[$group])) {
            OOALogger::log('Group "' . $group . '" Executed in ' . CodeTimer::$groupTimings[$group] . " Seconds");
            return CodeTimer::$groupTimings[$group];
        } else {
            OOALogger::log('No group timings recorded for group "' . $group . '"');
            return 0;
        }
    }


    /**
     * Time a function
     *
     * @param $function
     * @return mixed
     */
    private static function time($function) {
        $startTime = microtime(true);
        $function();
        $endTime = microtime(true);

        $duration = $endTime - $startTime;
        return $duration;
    }

}
