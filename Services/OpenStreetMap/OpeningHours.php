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
     *
     * @return Services_OpenStreetMap_OpeningHours
     */
    public function __construct($value = null)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Set opening_hours value.
     *
     * @param string $value An opening_hours value
     *
     * @return Services_OpenStreetMap_OpeningHours
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Return true, false or null depending on whether the [opening hours]
     * value explicitly indicates an open, closed or undecided result.
     *
     * @param double $time A numeric value representing a time. If null, the
     *                     current time is used.
     *
     * @link   http://wiki.openstreetmap.org/wiki/Key:opening_hours
     * @return null|boolean
     */
    public function isOpen($time = null)
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
            $start = $this->_startTime(date_sunrise($time));
            $end = $this->_endTime(date_sunset($time));
            $d = getdate($time);
            $ctime = $d['hours'] * 60 + $d['minutes'];
            return ($ctime >= $start && $ctime <= $end );
        }
        // other simple test would be sunrise-sunset - with
        // offsets that would need to be taken into account

        // time specs...
        $rule_sequences = explode(';', $this->value);
        $day = strtolower(substr(date('D', $time), 0, 2));
        $retval = false;
        foreach ($rule_sequences as $rule_sequence) {
            $rule_sequence = strtolower(trim($rule_sequence));
            // If the day is explicitly specified in the rule sequence then
            // processing it takes precedence.
            if (preg_match('/' . $day .'/', $rule_sequence)) {
                // @fixme: brittle. use preg_replace with \w
                $portions = explode(' ', str_replace(', ', ',', $rule_sequence));
                return $this->_openTimeSpec($portions, $time);
            }
            // @fixme: brittle. use preg_replace with \w
            $portions = explode(' ', str_replace(', ', ',', $rule_sequence));
            $open = $this->_openTimeSpec($portions, $time);
            if ($open) {
                $retval = true;
            } elseif ($open === false) {
                $retval = false;
            }
        }
        return $retval;
    }

    /**
     * Return true/false/null if a valid portion of an opening_hours value
     * indicates whether a venue is open/closed or not incalculable.
     *
     * @param mixed $portions Part of an opening_hours specification
     * @param mixed $time     The time to calculate against.
     *
     * @return null|boolean
     */
    private function _openTimeSpec($portions, $time)
    {
        if ($time === null) {
            $time = time();
        }

        $day = strtolower(substr(date('D', $time), 0, 2));
        $days = $this->_daySpecToArray($portions[0]);
        $pattern = '/^[0-2][0-9]:[0-5][0-9]\+$/';
        if (is_array($days)) {
            foreach ($days as $rday) {
                if ($rday === $day) {
                    //day is a match
                    $time_spec = trim($portions[1]);
                    if (strtolower($time_spec) == 'off') {
                        return false;
                    }
                    if (strpos($time_spec, '-')
                        && (strpos($time_spec, ',') === false)
                    ) {
                        // specified starting and end times for just one range - not
                        // comma delimited.
                        $startend_times = explode('-', $time_spec);
                        $start = $this->_startTime($startend_times[0]);
                        $end = $this->_endTime($startend_times[1]);
                        $d = getdate($time);
                        $ctime = $d['hours'] * 60 + $d['minutes'];
                        return ($ctime >= $start && $ctime <= $end);
                    } elseif (strpos($time_spec, '-') && (strpos($time_spec, ','))) {
                        $times = explode(',', $time_spec);
                        $d = getdate($time);
                        $ctime = $d['hours'] * 60 + $d['minutes'];
                        foreach ($times as $time_spec) {
                            $startend_times = explode('-', trim($time_spec));
                            $start = $this->_startTime($startend_times[0]);
                            $end = $this->_endTime($startend_times[1]);
                            if ($ctime >= $start && $ctime <= $end) {
                                return true;
                            }
                        }
                        return false;
                    } elseif (preg_match($pattern, $time_spec)) {
                        // open-ended.
                        if ($this->_evaluateOpenEnded($time_spec) === false) {
                            return false;
                        }
                    }
                }
            }
        } else {
            // here we go again... need to refactor/decide a better algorithm.
            $months = [
                'jan', 'feb', 'mar', 'apr', 'may', 'jun',
                'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
            ];
            if (in_array($portions[0], $months)) {
                $month = strtolower(date('M', $time));
                $time_spec = trim($portions[1]);
                if ($portions[0] == $month && is_numeric($portions[1])) {
                    $startend_times = explode('-', $portions[2]);
                    $start = $this->_startTime($startend_times[0]);
                    $end = $this->_endTime($startend_times[1]);
                    $atime = getdate($time);
                    $ctime = ($atime['hours'] * 60) + $atime['minutes'];
                    return ($ctime >= $start && $ctime <= $end);
                } elseif ($portions[0] === $month && $time_spec === 'off') {
                    return false;
                }
            }
            if ($portions[0] === '24/7') {
                return true;
            }
        }
    }

    /**
     * Convert a day list, such as mo-sa, into an array indicating
     * which days have been specified.
     *
     * @param string $day_specification Day list, eg "mo-sa" or "mo,we"
     *
     * @return array
     */
    private function _daySpecToArray($day_specification)
    {
        $days = ['mo', 'tu', 'we', 'th', 'fr', 'sa', 'su'];
        $spec = trim(strtolower($day_specification));
        if ($pos = strpos($spec, '-')) {
            $start_day = substr($spec, 0, $pos);
            $end_day = substr($spec, $pos + 1);
            if ($start_day !== 'mo') {
                foreach ($days as $day) {
                    if ($day !== $start_day) {
                        $off = array_shift($days);
                    } else {
                        break;
                    }
                }
            }
            $rdays = array_reverse($days);
            if ($end_day !== 'su') {
                foreach ($rdays as $day) {
                    if ($day !== $end_day) {
                        $off = array_shift($rdays);
                    } else {
                        break;
                    }
                }
                $days = array_reverse($rdays);
            }
            return $days;
        } elseif (strlen($spec) == 2) {
            if (in_array($spec, $days)) {
                return [$spec];
            }
        } elseif (strpos($spec, ',')) {
            $delimited = explode(',', $spec);
            $ret = [];
            foreach ($delimited as $item) {
                if (in_array($item, $days)) {
                    $ret[] = $item;
                }
            }
            return $ret;
        }
    }

    /**
     * Return true/false depending on whether a given time_spec value is
     * open-ended.
     *
     * @param string $time_spec Timespec
     *
     * @return bool
     */
    private function _evaluateOpenEnded($time_spec)
    {
        $start = $this->_startTime($time_spec);
        $d = getdate($start);
        $ctime = $d['hours'] * 60 + $d['minutes'];
        if ($ctime < $start) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Return number of seconds representing the start time in
     * the provided time_spec string.
     *
     * @param string $time_spec Timespec
     *
     * @return int
     */
    private function _startTime($time_spec)
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
    private function _endTime($time_spec)
    {
        $endhour = (int) substr($time_spec, 0, 2);
        $endmin = (int) substr($time_spec, 3);
        return $endhour * 60 + $endmin;
    }
}

// vim:set et ts=4 sw=4:
?>
