<?php
/**
 * Criterion.php
 * 25-Nov-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Criterion.php
 */

/**
 * Services_OpenStreetMap_Criterion
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Criterion.php
 */
class Services_OpenStreetMap_Criterion
{
    /**
     * Criterion type.
     *
     * @var mixed
     */
    protected $type  = null;
    /**
     * Depending on type, value is null, a specified or generated value.
     *
     * @var mixed
     */
    protected $value = null;

    /**
     * A Criterion is used to specify a condition on how to search through
     * changesets.
     *
     * Search changesets by
     * A user id:
     * Services_OpenStreetMap_Criterion('user', 12345)
     *
     * A display/user name:
     * Services_OpenStreetMap_Criterion('display_name', 'fredflintstone')
     *
     * A bounding box:
     * Services_OpenStreetMap_Criterion(
     *      'bbox',
     *      -8.0590275,
     *      52.9347449,
     *      -7.9966939,
     *      52.9611999
     * )
     *
     * For open changesets only:
     * Services_OpenStreetMap_Criterion('open')
     *
     * For closed changesets only:
     * Services_OpenStreetMap_Criterion('closed')
     *
     * For changesets created after a specific time:
     * Services_OpenStreetMap_Criterion('time', '17/11/2011')
     *
     * For changesets created during a specific timespan:
     * Services_OpenStreetMap_Criterion('time', '17/11/2011', '29/11/2011')
     *
     * @return Services_OpenStreetMap_Criterion
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function __construct()
    {
        $args = func_get_args();
        $type = $args[0];
        $this->type = $type;
        switch($type) {
        case 'user':
            if (is_numeric($args[1])) {
                $this->value = $args[1];
            } else {
                throw new Services_OpenStreetMap_InvalidArgumentException(
                    'User UID must be numeric'
                );
            }
            break;
        case 'bbox':
            $minLon = $args[1];
            $minLat = $args[2];
            $maxLon = $args[3];
            $maxLat = $args[4];
            $node = new Services_OpenStreetMap_Node();
            try {
                $node->setLon($minLon);
                $node->setLat($minLat);
                $node->setLon($maxLon);
                $node->setLat($maxLat);
            } catch(Services_OpenStreetMap_InvalidArgumentException $ex) {
                throw new Services_OpenStreetMap_InvalidArgumentException(
                    $ex->getMessage()
                );
            }
            $this->value = "{$minLon},{$minLat},{$maxLon},{$maxLat}";
            break;
        case 'display_name':
            $this->value = $args[1];
            break;
        case 'closed':
        case 'open':
            break;
        case 'time':
            $before = null;
            $after = null;
            if (isset($args[1])) {
                $after = $args[1];
                $time = strtotime($after);
                if ($time == -1 or $time === false) {
                    throw new Services_OpenStreetMap_InvalidArgumentException(
                        'Invalid time value'
                    );
                }
                $after = gmstrftime('%Y-%m-%dT%H:%M:%SZ', $time);
            }
            if (isset($args[2])) {
                $before = $args[2];
                $time = strtotime($before);
                if ($time == -1 or $time === false) {
                    throw new Services_OpenStreetMap_InvalidArgumentException(
                        'Invalid time value'
                    );
                }
                $before = gmstrftime('%Y-%m-%dT%H:%M:%SZ', $time);
            }
            if (!is_null($before)) {
                $this->value = "{$after},{$before}";
            } else {
                $this->value = $after;
            }
            break;
        default:
            $this->type = null;
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Unknown constraint type'
            );
        }
    }

    /**
     * Create the required query string portion.
     *
     * @return string
     */
    public function query()
    {
        switch($this->type) {
        case 'bbox':
            return "bbox={$this->value}";
        case 'closed':
            return 'closed';
        case 'open':
            return 'open';
        case 'display_name':
        case 'time':
        case 'user':
            return http_build_query([$this->type => $this->value]);
        }
    }

    /**
     * Return the criterion type (closed, open, bbox, display_name, or user).
     *
     * @return string
     */
    public function type()
    {
        return $this->type;
    }
}

// vim:set et ts=4 sw=4:
?>
