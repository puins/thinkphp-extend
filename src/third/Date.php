<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace third;

use DateTime;
use DateTimeZone;

class Date
{
    // Second amounts for various time increments
    const YEAR = 31556926;
    const MONTH = 2629744;
    const WEEK = 604800;
    const DAY = 86400;
    const HOUR = 3600;
    const MINUTE = 60;

    // Available formats for Date::months()
    const MONTHS_LONG = '%B';
    const MONTHS_SHORT = '%b';

    /**
     * Default timestamp format for formatted_time
     * @var  string
     */
    public static $timestamp_format = 'Y-m-d H:i:s';

    /**
     * Timezone for formatted_time
     * @link http://uk2.php.net/manual/en/timezones.php
     * @var  string
     */
    public static $timezone;

    /**
     * Returns the offset (in seconds) between two time zones. Use this to
     * display dates to users in different time zones.
     *
     *     $seconds = Date::offset('America/Chicago', 'GMT');
     *
     * [!!] A list of time zones that PHP supports can be found at
     * <http://php.net/timezones>.
     *
     * @param string $remote timezone that to find the offset of
     * @param string $local  timezone used as the baseline
     * @param mixed  $now    UNIX timestamp or date string
     *
     * @return integer
     */
    public static function offset($remote, $local = null, $now = null)
    {
        if ($local === null) {
            // Use the default timezone
            $local = date_default_timezone_get();
        }

        if (is_int($now)) {
            // Convert the timestamp into a string
            $now = date(DateTime::RFC2822, $now);
        }

        // Create timezone objects
        $zone_remote = new DateTimeZone($remote);
        $zone_local = new DateTimeZone($local);

        // Create date objects from timezones
        $time_remote = new DateTime($now, $zone_remote);
        $time_local = new DateTime($now, $zone_local);

        // Find the offset
        $offset = $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);

        return $offset;
    }

    /**
     * Number of seconds in a minute, incrementing by a step. Typically used as
     * a shortcut for generating a list that can used in a form.
     *
     *     $seconds = Date::seconds(); // 01, 02, 03, ..., 58, 59, 60
     *
     * @param   integer $step   amount to increment each step by, 1 to 30
     * @param   integer $start  start value
     * @param   integer $end    end value
     * @return  array   A mirrored (foo => foo) array from 1-60.
     */
    public static function seconds($step = 1, $start = 0, $end = 60)
    {
        // Always integer
        $step = (int) $step;

        $seconds = array();
        for ($i = $start; $i < $end; $i += $step) {
            $seconds[$i] = sprintf('%02d', $i);
        }

        return $seconds;
    }

    /**
     * Number of minutes in an hour, incrementing by a step. Typically used as
     * a shortcut for generating a list that can be used in a form.
     *
     *     $minutes = Date::minutes(); // 05, 10, 15, ..., 50, 55, 60
     *
     * @uses    Date::seconds
     * @param   integer $step   amount to increment each step by, 1 to 30
     * @return  array   A mirrored (foo => foo) array from 1-60.
     */
    public static function minutes($step = 5)
    {
        // Because there are the same number of minutes as seconds in this set,
        // we choose to re-use seconds(), rather than creating an entirely new
        // function. Shhhh, it's cheating! ;) There are several more of these
        // in the following methods.
        return static::seconds($step);
    }

    /**
     * Number of hours in a day. Typically used as a shortcut for generating a
     * list that can be used in a form.
     *
     *     $hours = Date::hours(); // 01, 02, 03, ..., 10, 11, 12
     *
     * @param   integer $step   amount to increment each step by
     * @param   boolean $long   use 24-hour time
     * @param   integer $start  the hour to start at
     * @return  array   A mirrored (foo => foo) array from start-12 or start-23.
     */
    public static function hours($step = 1, $long = false, $start = null)
    {
        // Set the default start if none was specified.
        if (!$start) {
            $start = $long ? 0 : 1;
        }

        // 24-hour time has 24 hours, instead of 12
        $size = $long ? 23 : 12;
        $step = (int) $step;

        $hours = array();
        for ($i = $start; $i <= $size; $i += $step) {
            $hours[$i] = (string) $i;
        }

        return $hours;
    }

    /**
     * Returns AM or PM, based on a given hour (in 24 hour format).
     *
     *     $type = Date::ampm(12); // PM
     *     $type = Date::ampm(1);  // AM
     *
     * @param   integer $hour   number of the hour
     * @return  string
     */
    public static function ampm($hour)
    {
        // Always integer
        $hour = (int) $hour;

        return ($hour > 11) ? 'PM' : 'AM';
    }

    /**
     * Adjusts a non-24-hour number into a 24-hour number.
     *
     *     $hour = Date::adjust(3, 'pm'); // 15
     *
     * @param   integer $hour   hour to adjust
     * @param   string  $ampm   AM or PM
     * @return  string
     */
    public static function adjust($hour, $ampm)
    {
        $hour = (int) $hour;
        $ampm = strtolower($ampm);

        switch ($ampm) {
            case 'am':
                if ($hour == 12) {
                    $hour = 0;
                }
                break;
            case 'pm':
                if ($hour < 12) {
                    $hour += 12;
                }
                break;
        }

        return sprintf('%02d', $hour);
    }

    /**
     * Number of days in a given month and year. Typically used as a shortcut
     * for generating a list that can be used in a form.
     *
     *     Date::days(4, 2010); // 1, 2, 3, ..., 28, 29, 30
     *
     * @param   integer $month  number of month
     * @param   integer $year   number of year to check month, defaults to the current year
     * @return  array   A mirrored (foo => foo) array of the days.
     */
    public static function days($month, $year = null)
    {
        static $months;

        if (!isset($year)) {
            // Use the current year by default
            $year = date('Y');
        }

        // Always integers
        $month = (int) $month;
        $year = (int) $year;

        // We use caching for months, because time functions are used
        if (empty($months[$year][$month])) {
            // Use date to find the number of days in the given month
            $total = date('t', mktime(1, 0, 0, $month, 1, $year)) + 1;

            $months[$year][$month] = array();
            for ($i = 1; $i < $total; $i++) {
                $months[$year][$month][$i] = (string) $i;
            }
        }

        return $months[$year][$month];
    }

    /**
     * Number of months in a year. Typically used as a shortcut for generating
     * a list that can be used in a form.
     *
     * By default a mirrored array of $month_number => $month_number is returned
     *
     *     Date::months();
     *     // aray(1 => 1, 2 => 2, 3 => 3, ..., 12 => 12)
     *
     * But you can customise this by passing in either Date::MONTHS_LONG
     *
     *     Date::months(Date::MONTHS_LONG);
     *     // array(1 => 'January', 2 => 'February', ..., 12 => 'December')
     *
     * Or Date::MONTHS_SHORT
     *
     *     Date::months(Date::MONTHS_SHORT);
     *     // array(1 => 'Jan', 2 => 'Feb', ..., 12 => 'Dec')
     *
     * @uses    Date::hours
     * @param   string  $format The format to use for months
     * @return  array   An array of months based on the specified format
     */
    public static function months($format = null)
    {
        $months = array();

        if ($format === static::MONTHS_LONG || $format === static::MONTHS_SHORT) {
            for ($i = 1; $i <= 12; ++$i) {
                $months[$i] = strftime($format, mktime(0, 0, 0, $i, 1));
            }
        } else {
            $months = static::hours();
        }

        return $months;
    }

    /**
     * Returns an array of years between a starting and ending year. By default,
     * the the current year - 5 and current year + 5 will be used. Typically used
     * as a shortcut for generating a list that can be used in a form.
     *
     *     $years = Date::years(2000, 2010); // 2000, 2001, ..., 2009, 2010
     *
     * @param   integer $start  starting year (default is current year - 5)
     * @param   integer $end    ending year (default is current year + 5)
     * @return  array
     */
    public static function years($start = false, $end = false)
    {
        // Default values
        $start = ($start === false) ? (date('Y') - 5) : (int) $start;
        $end = ($end === false) ? (date('Y') + 5) : (int) $end;

        $years = array();
        for ($i = $start; $i <= $end; $i++) {
            $years[$i] = (string) $i;
        }

        return $years;
    }

    /**
     * Returns time difference between two timestamps, in human readable format.
     * If the second timestamp is not given, the current time will be used.
     * Also consider using [Date::fuzzy_span] when displaying a span.
     *
     *     $span = Date::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     *     $span = Date::span(60, 182, 'minutes'); // 2
     *
     * @param   integer $remote timestamp to find the span of
     * @param   integer $local  timestamp to use as the baseline
     * @param   string  $output formatting string
     * @return  string   when only a single output is requested
     * @return  array    associative list of all outputs requested
     */
    public static function span($remote, $local = null, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        // Normalize output
        $output = trim(strtolower((string) $output));

        if (!$output) {
            // Invalid output
            return false;
        }

        // Array with the output formats
        $output = preg_split('/[^a-z]+/', $output);

        // Convert the list of outputs to an associative array
        $output = array_combine($output, array_fill(0, count($output), 0));

        // Make the output values into keys
        extract(array_flip($output), EXTR_SKIP);

        if ($local === null) {
            // Calculate the span from the current time
            $local = time();
        }

        // Calculate timespan (seconds)
        $timespan = abs($remote - $local);

        if (isset($output['years'])) {
            $timespan -= static::YEAR * ($output['years'] = (int) floor($timespan / static::YEAR));
        }

        if (isset($output['months'])) {
            $timespan -= static::MONTH * ($output['months'] = (int) floor($timespan / static::MONTH));
        }

        if (isset($output['weeks'])) {
            $timespan -= static::WEEK * ($output['weeks'] = (int) floor($timespan / static::WEEK));
        }

        if (isset($output['days'])) {
            $timespan -= static::DAY * ($output['days'] = (int) floor($timespan / static::DAY));
        }

        if (isset($output['hours'])) {
            $timespan -= static::HOUR * ($output['hours'] = (int) floor($timespan / static::HOUR));
        }

        if (isset($output['minutes'])) {
            $timespan -= static::MINUTE * ($output['minutes'] = (int) floor($timespan / static::MINUTE));
        }

        // Seconds ago, 1
        if (isset($output['seconds'])) {
            $output['seconds'] = $timespan;
        }

        if (count($output) === 1) {
            // Only a single output was requested, return it
            return array_pop($output);
        }

        // Return array
        return $output;
    }

    /**
     * Returns the difference between a time and now in a "fuzzy" way.
     * Displaying a fuzzy time instead of a date is usually faster to read and understand.
     *
     *     $span = Date::fuzzy_span(time() - 10); // "moments ago"
     *     $span = Date::fuzzy_span(time() + 20); // "in moments"
     *
     * A second parameter is available to manually set the "local" timestamp,
     * however this parameter shouldn't be needed in normal usage and is only
     * included for unit tests
     *
     * @param   integer $timestamp          "remote" timestamp
     * @param   integer $local_timestamp    "local" timestamp, defaults to time()
     * @return  string
     */
    public static function fuzzySpan($timestamp, $local_timestamp = null)
    {
        $local_timestamp = ($local_timestamp === null) ? time() : (int) $local_timestamp;

        // Determine the difference in seconds
        $offset = abs($local_timestamp - $timestamp);

        if ($offset <= static::MINUTE) {
            $span = 'moments';
        } elseif ($offset < (static::MINUTE * 20)) {
            $span = 'a few minutes';
        } elseif ($offset < static::HOUR) {
            $span = 'less than an hour';
        } elseif ($offset < (static::HOUR * 4)) {
            $span = 'a couple of hours';
        } elseif ($offset < static::DAY) {
            $span = 'less than a day';
        } elseif ($offset < (static::DAY * 2)) {
            $span = 'about a day';
        } elseif ($offset < (static::DAY * 4)) {
            $span = 'a couple of days';
        } elseif ($offset < static::WEEK) {
            $span = 'less than a week';
        } elseif ($offset < (static::WEEK * 2)) {
            $span = 'about a week';
        } elseif ($offset < static::MONTH) {
            $span = 'less than a month';
        } elseif ($offset < (static::MONTH * 2)) {
            $span = 'about a month';
        } elseif ($offset < (static::MONTH * 4)) {
            $span = 'a couple of months';
        } elseif ($offset < static::YEAR) {
            $span = 'less than a year';
        } elseif ($offset < (static::YEAR * 2)) {
            $span = 'about a year';
        } elseif ($offset < (static::YEAR * 4)) {
            $span = 'a couple of years';
        } elseif ($offset < (static::YEAR * 8)) {
            $span = 'a few years';
        } elseif ($offset < (static::YEAR * 12)) {
            $span = 'about a decade';
        } elseif ($offset < (static::YEAR * 24)) {
            $span = 'a couple of decades';
        } elseif ($offset < (static::YEAR * 64)) {
            $span = 'several decades';
        } else {
            $span = 'a long time';
        }

        if ($timestamp <= $local_timestamp) {
            // This is in the past
            return $span . ' ago';
        } else {
            // This in the future
            return 'in ' . $span;
        }
    }

    /**
     * Converts a UNIX timestamp to DOS format. There are very few cases where
     * this is needed, but some binary formats use it (eg: zip files.)
     * Converting the other direction is done using {@link Date::dos2unix}.
     *
     *     $dos = Date::unix2dos($unix);
     *
     * @param   integer $timestamp  UNIX timestamp
     * @return  integer
     */
    public static function unix2dos($timestamp = null)
    {
        $timestamp = getdate($timestamp);

        if ($timestamp['year'] < 1980) {
            return (1 << 21 | 1 << 16);
        }

        $timestamp['year'] -= 1980;

        // What voodoo is this? I have no idea... Geert can explain it though,
        // and that's good enough for me.
        return ($timestamp['year'] << 25 | $timestamp['mon'] << 21 |
            $timestamp['mday'] << 16 | $timestamp['hours'] << 11 |
            $timestamp['minutes'] << 5 | $timestamp['seconds'] >> 1);
    }

    /**
     * Converts a DOS timestamp to UNIX format.There are very few cases where
     * this is needed, but some binary formats use it (eg: zip files.)
     * Converting the other direction is done using {@link Date::unix2dos}.
     *
     *     $unix = Date::dos2unix($dos);
     *
     * @param   integer $timestamp  DOS timestamp
     * @return  integer
     */
    public static function dos2unix($timestamp = false)
    {
        $sec = 2 * ($timestamp & 0x1f);
        $min = ($timestamp >> 5) & 0x3f;
        $hrs = ($timestamp >> 11) & 0x1f;
        $day = ($timestamp >> 16) & 0x1f;
        $mon = ($timestamp >> 21) & 0x0f;
        $year = ($timestamp >> 25) & 0x7f;

        return mktime($hrs, $min, $sec, $mon, $day, $year + 1980);
    }

    /**
     * Returns a date/time string with the specified timestamp format
     *
     *     $time = Date::formatted_time('5 minutes ago');
     *
     * @link    http://www.php.net/manual/datetime.construct
     * @param   string  $datetime_str       datetime string
     * @param   string  $timestamp_format   timestamp format
     * @param   string  $timezone           timezone identifier
     * @return  string
     */
    public static function formattedTime($datetime_str = 'now', $timestamp_format = null, $timezone = null)
    {
        if (!$timestamp_format) {
            $timestamp_format = static::$timestamp_format;
        }

        if (!$timezone) {
            $timezone = static::$timezone;
        }

        $tz = new DateTimeZone($timezone);
        $time = new DateTime($datetime_str, $tz);

        if ($time->getTimeZone()->getName() !== $tz->getName()) {
            $time->setTimeZone($tz);
        }

        return $time->format($timestamp_format);
    }

    /**
     * 格式化 UNIX 时间戳为人易读的字符串
     *
     * @param int    Unix 时间戳
     * @param mixed $local 本地时间
     *
     * @return    string    格式化的日期字符串
     */
    public static function human($remote, $local = null)
    {
        $time_diff = (is_null($local) || $local ? time() : $local) - $remote;
        $tense = $time_diff < 0 ? 'after' : 'ago';
        $time_diff = abs($time_diff);
        $chunks = [
            [60 * 60 * 24 * 365, 'year'],
            [60 * 60 * 24 * 30, 'month'],
            [60 * 60 * 24 * 7, 'week'],
            [60 * 60 * 24, 'day'],
            [60 * 60, 'hour'],
            [60, 'minute'],
            [1, 'second'],
        ];
        $name = 'second';
        $count = 0;

        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($time_diff / $seconds)) != 0) {
                break;
            }
        }
        return __("%d $name%s $tense", $count, ($count > 1 ? 's' : ''));
    }

    /**
     * 获取一个基于时间偏移的Unix时间戳
     *
     * @param string $type     时间类型，默认为day，可选minute,hour,day,week,month,quarter,year
     * @param int    $offset   时间偏移量 默认为0，正数表示当前type之后，负数表示当前type之前
     * @param string $position 时间的开始或结束，默认为begin，可选前(begin,start,first,front)，end
     * @param int    $year     基准年，默认为null，即以当前年为基准
     * @param int    $month    基准月，默认为null，即以当前月为基准
     * @param int    $day      基准天，默认为null，即以当前天为基准
     * @param int    $hour     基准小时，默认为null，即以当前年小时基准
     * @param int    $minute   基准分钟，默认为null，即以当前分钟为基准
     * @return int 处理后的Unix时间戳
     */
    public static function unixtime($type = 'day', $offset = 0, $position = 'begin', $year = null, $month = null, $day = null, $hour = null, $minute = null)
    {
        $year = is_null($year) ? (int) date("Y") : (int) $year;
        $month = is_null($month) ? (int) date("n") : (int) $month;
        $day = is_null($day) ? (int) date("j") : (int) $day;
        $hour = is_null($hour) ? (int) date("H") : (int) $hour;
        $minute = is_null($minute) ? (int) date("i") : (int) $minute;
        $position = in_array($position, array('begin', 'start', 'first', 'front'));

        $baseTime = mktime(0, 0, 0, $month, $day, $year);

        switch ($type) {
            case 'minute':
                $time = $position ? mktime($hour, $minute + $offset, 0, $month, $day, $year) : mktime($hour, $minute + $offset, 59, $month, $day, $year);
                break;
            case 'hour':
                $time = $position ? mktime($hour + $offset, 0, 0, $month, $day, $year) : mktime($hour + $offset, 59, 59, $month, $day, $year);
                break;
            case 'day':
                $time = $position ? mktime(0, 0, 0, $month, $day + $offset, $year) : mktime(23, 59, 59, $month, $day + $offset, $year);
                break;
            case 'week':
                $weekIndex = date("w", $baseTime);
                $time = $position ?
                strtotime($offset . " weeks", strtotime(date('Y-m-d', strtotime("-" . ($weekIndex ? $weekIndex - 1 : 6) . " days", $baseTime)))) :
                strtotime($offset . " weeks", strtotime(date('Y-m-d 23:59:59', strtotime("+" . (6 - ($weekIndex ? $weekIndex - 1 : 6)) . " days", $baseTime))));
                break;
            case 'month':
                $_timestamp = mktime(0, 0, 0, $month + $offset, 1, $year);
                $time = $position ? $_timestamp : mktime(23, 59, 59, $month + $offset, self::days_in_month(date("m", $_timestamp), date("Y", $_timestamp)), $year);
                break;
            case 'quarter':
                $_month = date("m", mktime(0, 0, 0, (int) (ceil((int) date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) * 3, $day, $year));
                $time = $position ?
                mktime(0, 0, 0, 1 + (int) ((ceil((int) date('n', $baseTime) / 3) + $offset) - 1) * 3, 1, $year) :
                mktime(23, 59, 59, (int) (ceil((int) date('n', $baseTime) / 3) + $offset) * 3, self::days_in_month((ceil(date('n', $baseTime) / 3) + $offset) * 3, $year), $year);
                break;
            case 'year':
                $time = $position ? mktime(0, 0, 0, 1, 1, $year + $offset) : mktime(23, 59, 59, 12, 31, $year + $offset);
                break;
            default:
                $time = mktime($hour, $minute, 0, $month, $day, $year);
                break;
        }
        return $time;
    }

    /**
     * 获取指定年月拥有的天数
     * @param int $month
     * @param int $year
     * @return false|int|string
     */
    public static function days_in_month($month, $year)
    {
        if (function_exists("cal_days_in_month")) {
            return cal_days_in_month(CAL_GREGORIAN, $month, $year);
        } else {
            return date('t', mktime(0, 0, 0, $month, 1, $year));
        }
    }
}
