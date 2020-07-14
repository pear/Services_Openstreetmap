<?php
/**
 * OpeningHours.php
 * 23-Oct-2012
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     OpeningHours.php
 */

/**
 * Services_OpenStreetMap_OpeningHours
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     OpeningHours.php
 */
class Services_OpenStreetMap_OpeningHours
{
    /**
     * The value set against the OpeningHours tag
     *
     * @var string
     */
    protected $value;

    /**
     * Constructor
     *
     * @param string $value An opening_hours value
     */
    public function __construct(string $value = null)
    {
        $this->value = $value;
    }

    /**
     * Set opening_hours value.
     *
     * @param string $value An opening_hours value
     *
     * @return Services_OpenStreetMap_OpeningHours
     */
    public function setValue(string $value): Services_OpenStreetMap_OpeningHours
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Return true, false or null depending on whether the [opening hours]
     * value explicitly indicates an open, closed or undecided result.
     *
     * @param int $time A numeric value representing a time. If null, the
     *                  current time is used.
     *
     * @link   http://wiki.openstreetmap.org/wiki/Key:opening_hours
     * @return null|boolean
     */
    public function isOpen(?int $time = null): ?bool
    {
        if ($this->value === null) {
            return null;
        }
        if ($this->value === '24/7') {
            return true;
        }

        if ($time === null) {
            $time = time();
        }
        if ($this->value === 'sunrise-sunset') {
            return $this->_isBetweenSunriseAndSunset($time);
        }
        // other simple test would be sunrise-sunset - with
        // offsets that would need to be taken into account
        $matched = [];
        $isVariableSunRiseSunSet = preg_match(
            '/([^\(]?sunrise.*[^\)-]+).*-.*([^\(]?sunset.*[^\)])/u',
            $this->value,
            $matched
        );
        if ($isVariableSunRiseSunSet === 1) {
            return $this->_isBetweenVariableSunriseAndSunset($time, $matched);
        }
        // time specs...
        return $this->_evaluateComplexTimeSpec($time);
    }

    /**
     * Return true/false/null if a valid portion of an opening_hours value
     * indicates whether a venue is open/closed or not incalculable.
     *
     * @param array $portions Part of an opening_hours specification
     * @param int   $time     The time to calculate against.
     *
     * @return null|boolean
     */
    private function _openTimeSpec($portions, $time):? bool
    {
        $days = $this->_daySpecToArray(trim($portions[0], ":"));
        if (is_array($days)) {
            return $this->_openTimeSpecDays($portions, $days, $time);
        }
        return $this->_openTimeSpecMonths($portions, $time);
    }

    /**
     * Work on time-spec with day portion in spec
     *
     * @param array $portions Part of an opening_hour specification
     * @param array $days     Day spec converted to array
     * @param int   $time     time value to evaluate against
     *
     * @return null|boolean
     */
    private function _openTimeSpecDays($portions, $days, $time):? bool
    {
        foreach ($days as $day) {
            $result = $this->_openTimeSpecDay($portions, $day, $time);
            if (is_bool($result)) {
                return $result;
            }
        }
        return null;
    }

    /**
     * _openTimeSpecDay
     *
     * @param array  $portions Part of an opening_hour specification
     * @param string $day      Day spec
     * @param int    $time     time value to evaluate against
     *
     * @return null|bool
     */
    private function _openTimeSpecDay(array $portions, string $day, int $time):? bool
    {
        $daytime = strtolower(substr(date('D', $time), 0, 2));
        $pattern = '/^[0-2][0-9]:[0-5][0-9]\+$/';
        $open = null;

        if ($day === $daytime) {
            //day is a match
            $time_spec = trim($portions[1]);
            if (strtolower($time_spec) === 'off') {
                $open = false;
            } elseif (strpos($time_spec, '-') && (strpos($time_spec, ',') === false)) {
                // specified starting and end times for just one range - not
                // comma delimited.
                $startend_times = explode('-', $time_spec);
                $start = $this->_startTime($startend_times[0]);
                $end = $this->_endTime($startend_times[1]);
                $date = getdate($time);
                $ctime = $date['hours'] * 60 + $date['minutes'];
                $open = ($ctime >= $start && $ctime <= $end);
            } elseif (strpos($time_spec, '-') && (strpos($time_spec, ','))) {
                return $this->_timeIsBetweenTimeSpecTimes($time_spec, $time);
            } elseif (preg_match($pattern, $time_spec)) {
                // open-ended.
                if (!$this->_evaluateOpenEnded($time_spec)) {
                    $open = false;
                }
            }
        }
        return $open;
    }

    /**
     * _timeIsBetweenTimeSpecTimes
     *
     * @param string $time_spec Opening Hours specification
     * @param int    $time      The time to calculate against.
     *
     * @return bool
     */
    private function _timeIsBetweenTimeSpecTimes($time_spec, $time)
    {
        $times = explode(',', $time_spec);
        $date = getdate($time);
        $ctime = $date['hours'] * 60 + $date['minutes'];
        foreach ($times as $time_spec) {
            $startend_times = explode('-', trim($time_spec));
            $start = $this->_startTime($startend_times[0]);
            $end = $this->_endTime($startend_times[1]);
            if ($ctime >= $start && $ctime <= $end) {
                return true;
            }
        }
        return false;
    }

    /**
     * _evaluateComplexTimeSpec
     *
     * @param int $time Time to evaluate against
     *
     * @return bool
     */
    private function _evaluateComplexTimeSpec($time): bool
    {
        $rule_sequences = explode(';', $this->value);
        $day = strtolower(substr(date('D', $time), 0, 2));
        $retval = false;
        foreach ($rule_sequences as $rule_sequence) {
            $rule_sequence = strtolower(trim($rule_sequence));
            // If the day is explicitly specified in the rule sequence then
            // processing it takes precedence.
            if (preg_match('/' . $day . '/', $rule_sequence)) {
                // @fixme: brittle. use preg_replace with \w
                $portions = explode(' ', str_replace(', ', ',', $rule_sequence));
                return $this->_openTimeSpec($portions, $time);
            }
            // @fixme: brittle. use preg_replace with \w
            $portions = explode(' ', str_replace(', ', ',', $rule_sequence));
            $retval = $this->_openTimeSpec($portions, $time) !== false;
        }
        return $retval;
    }

    /**
     * OpenTimeSpecMonths
     *
     * @param array   $spec time-spec
     * @param integer $time time to evaluate against
     *
     * @return bool|null
     */
    private function _openTimeSpecMonths($spec, $time):? bool
    {
        $months = [
            'jan', 'feb', 'mar', 'apr', 'may', 'jun',
            'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
        ];
        $retVal = null;
        if (in_array($spec[0], $months)) {
            $month = strtolower(date('M', $time));
            $time_spec = trim($spec[1]);
            if ($spec[0] == $month && is_numeric($spec[1])) {
                $startend_times = explode('-', $spec[2]);
                $start = $this->_startTime($startend_times[0]);
                $end = $this->_endTime($startend_times[1]);
                $atime = getdate($time);
                $ctime = ($atime['hours'] * 60) + $atime['minutes'];
                $retVal = ($ctime >= $start && $ctime <= $end);
            } elseif ($spec[0] === $month && $time_spec === 'off') {
                $retVal = false;
            }
        }
        if ($spec[0] === '24/7') {
            $retVal = true;
        }
        return $retVal;
    }

    /**
     * Convert a day list, such as mo-sa, into an array indicating
     * which days have been specified.
     *
     * @param string $day_specification Day list, eg "mo-sa" or "mo,we"
     *
     * @return array
     */
    private function _daySpecToArray(string $day_specification): ?array
    {
        $days = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'];
        $spec = trim(strtolower($day_specification));
        $retVal = null;
        if (strpos($spec, '-')) {
            return $this->_narrowDayRange($days, $spec);
        } elseif (strlen($spec) === 2) {
            if (in_array($spec, $days)) {
                $retVal = [$spec];
            }
        } elseif (strpos($spec, ',')) {
            $delimited = explode(',', $spec);
            $retVal = [];
            foreach ($delimited as $item) {
                if (in_array($item, $days)) {
                    $retVal[] = $item;
                }
            }
        }
        return $retVal;
    }

    /**
     * Narrow day range
     *
     * @param array  $days Days
     * @param string $spec day specification
     *
     * @return array
     */
    private function _narrowDayRange(array $days, string $spec): array
    {
        $start_day = '';
        $pos = strpos($spec, '-');
        if ($pos !== false) {
            $start_day = substr($spec, 0, $pos);
        }
        $end_day = substr($spec, $pos + 1);
        if ($start_day !== 'mo') {
            foreach ($days as $day) {
                if ($day !== $start_day) {
                    array_shift($days);
                } else {
                    break;
                }
            }
        }
        $rdays = array_reverse($days);
        if ($end_day !== 'su') {
            foreach ($rdays as $day) {
                if ($day !== $end_day) {
                    array_shift($rdays);
                } else {
                    break;
                }
            }
            $days = array_reverse($rdays);
        }
        return $days;
    }

    /**
     * Return true/false depending on whether a given time_spec value is
     * open-ended.
     *
     * @param string $time_spec Timespec
     *
     * @return bool
     */
    private function _evaluateOpenEnded(string $time_spec): bool
    {
        $start = $this->_startTime($time_spec);
        $d = getdate($start);
        $ctime = $d['hours'] * 60 + $d['minutes'];
        return ($ctime >= $start);
    }

    /**
     * Return number of seconds representing the start time in
     * the provided time_spec string.
     *
     * @param string $time_spec Timespec
     *
     * @return int
     */
    private function _startTime(string $time_spec): int
    {
        $starthour = (int) substr($time_spec, 0, 2);
        $startmin  = (int) substr($time_spec, 3, 2);
        return $starthour * 60 + $startmin;
    }

    /**
     * Return number of seconds representing the end time in
     * the provided time_spec string.
     *
     * @param string $time_spec Timespec
     *
     * @return int
     */
    private function _endTime(string $time_spec): int
    {
        $endhour = (int) substr($time_spec, 0, 2);
        $endmin = (int) substr($time_spec, 3);
        return $endhour * 60 + $endmin;
    }

    /**
     * Determine if the time is between a sunrise and sunset on day of that time
     *
     * @param int $time Time to check against
     *
     * @return bool
     */
    private function _isBetweenSunriseAndSunset($time): bool
    {
        $start = $this->_startTime(date_sunrise($time));
        $end = $this->_endTime(date_sunset($time));
        $d = getdate($time);
        $ctime = $d['hours'] * 60 + $d['minutes'];
        return ($ctime >= $start && $ctime <= $end);
    }

    /**
     * Determine if the time is between a variable sunrise and sunset.
     *
     * @param int   $time    Time to check against
     * @param array $matched Matches from regexp
     *
     * @return bool
     */
    private function _isBetweenVariableSunriseAndSunset($time, $matched): bool
    {
        $term1 = $matched[1];
        $term1modifier = '';
        $bork = strpbrk($term1, "+-");
        $term1minutes = 0;
        if ($bork !== false) {
            $term1modifier = $bork[0];
            $term1segments = sscanf(trim(substr($bork, 1)), "%d:%d");
            $term1minutes = $term1segments[0] * 60 + $term1segments[1];
            if ($term1modifier === '-') {
                $term1minutes = -$term1minutes;
            }
        }

        $term2 = $matched[2];
        $bork2 = strpbrk($term2, "+-");
        $term2minutes = 0;
        if ($bork2 !== false) {
            $term2modifier = $bork2[0];
            $term2segments = sscanf(trim(substr($bork2, 1)), "%d:%d");
            $term2minutes = $term2segments[0] * 60 + $term2segments[1];
            if ($term2modifier === '-') {
                $term2minutes = -$term2minutes;
            }
        }
        $start = $this->_startTime(date_sunrise($time));

        $start += $term1minutes;
        $end = $this->_endTime(date_sunset($time));
        $end += $term2minutes;
        $d = getdate($time);
        $ctime = $d['hours'] * 60 + $d['minutes'];

        return ($ctime >= $start && $ctime <= $end );
    }
}
// vim:set et ts=4 sw=4:
