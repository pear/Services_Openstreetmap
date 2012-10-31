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
 * @todo
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
    protected $value;

    /**
     * __construct
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
     * @param double $time A numeric value representing a time. If null, the current time is used.
     *
     * @link http://wiki.openstreetmap.org/wiki/Key:opening_hours
     * @return null|boolean
     */
    public function isOpen($time = null)
    {
        if ($this->value === null) {
            return null;
        }
        if ($this->value == '24/7') {
            return true;
        }

        if ($time === null) {
            $time = time();
        }
        if ($this->value == 'sunrise-sunset') {
            $sunrise = date_sunrise($time);
            $sunset = date_sunset($time);
            $starthour = substr($sunrise, 0, 2);
            $startmin = substr($sunrise, 3);
            $start = $starthour * 60 + $startmin;
            $endhour = substr($sunset, 0, 2);
            $endmin = substr($sunset, 3);
            $end = $endhour * 60 + $endmin;
            $d = getdate($time);
            $ctime = $d['hours'] * 60 + $d['minutes'];
            return ($ctime >= $start && $ctime <= $end );
        }
        // other simple test would be sunrise-sunset, but there are also
        // offsets that would need to be taken into account.
        $rule_sequences = explode(';', $this->value);
        foreach ($rule_sequences as $rule_sequence) {
            $rule_sequence = trim($rule_sequence);
            $portions = explode(' ', $rule_sequence);
            $open = $this->_openTimeSpec($portions, $time);
            if ($open) {
                return true;
            } elseif ($open === false) {
                return false;
            }
        }
        return false;
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
        foreach ($days as $rday) {
            if ($rday == $day) {
                //day is a match
                $time_spec = trim($portions[1]);
                if (strpos($time_spec, '-') && (strpos($time_spec, ',') === false)) {
                    // specified starting and end times for just one range - not
                    // comma delimited.
                    $startend_times = explode('-', $time_spec);
                    $starthour = substr($startend_times[0], 0, 2);
                    $startmin = substr($startend_times[0], 3);
                    $start = $starthour * 60 + $startmin;
                    $endhour = substr($startend_times[1], 0, 2);
                    $endmin =  substr($startend_times[1], 3);
                    $end = $endhour * 60 + $endmin;
                    $d = getdate($time);
                    $ctime = $d['hours'] * 60 + $d['minutes'];
                    return ($ctime >= $start && $ctime <= $end);
                } elseif (strpos($time_spec, '-') && (strpos($time_spec, ','))) {
                    $times = explode(',', $time_spec);
                    $d = getdate($time);
                    $ctime = $d['hours'] * 60 + $d['minutes'];
                    foreach ($times as $time_spec) {
                        $startend_times = explode('-', trim($time_spec));
                        $starthour = substr($startend_times[0], 0, 2);
                        $startmin = substr($startend_times[0], 3);
                        $start = $starthour * 60 + $startmin;
                        $endhour = substr($startend_times[1], 0, 2);
                        $endmin =  substr($startend_times[1], 3);
                        $end = $endhour * 60 + $endmin;
                        if ($ctime >= $start && $ctime <= $end) {
                            return true;
                        }
                    }
                    return false;
                } elseif (preg_match('/^[0-2][0-9]:[0-5][0-9]\+$/', $time_spec)) {
                    // open-ended.
                    $starthour = substr($time_spec, 0, 2);
                    $startmin = substr($time_spec, 3, 2);
                    $start = $starthour * 60 + $startmin;
                    $d = getdate($time);
                    $ctime = $d['hours'] * 60 + $d['minutes'];
                    if ($ctime < $start) {
                        return false;
                    }
                }
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
        $days = array('mo','tu', 'we','th','fr', 'sa', 'su');
        $spec = trim(strtolower($day_specification));
        if ($pos = strpos($spec, '-')) {
            $start_day = substr($spec, 0, $pos);
            $end_day = substr($spec, $pos + 1);
            if ($start_day != 'mo') {
                foreach ($days as $day) {
                    if ($day != $start_day) {
                        $off = array_shift($days);
                    } else {
                        break;
                    }
                }
            }
            $rdays = array_reverse($days);
            if ($end_day != 'su') {
                foreach ($rdays as $day) {
                    if ($day != $end_day) {
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
                return array($spec);
            }
        } elseif (strpos($spec, ',')) {
            $delimited = explode(',', $spec);
            $ret = array();
            foreach ($delimited as $item) {
                if (in_array($item, $days)) {
                    $ret[] = $item;
                }
            }
            return $ret;
        }
    }
}

// vim:set et ts=4 sw=4:
?>
