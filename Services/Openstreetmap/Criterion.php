<?php
/**
 * Criterion.php
 * 25-Nov-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Criterion.php
 */

/**
 * Services_Openstreetmap_Criterion
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Criterion.php
 */
class Services_Openstreetmap_Criterion
{
    protected $type  = null;
    protected $value = null;

    /**
     * __construct
     *
     * @return Services_Openstreetmap_Criterion
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
                throw new Services_Openstreetmap_InvalidArgumentException(
                    'User UID must be numeric'
                );
            }
            break;
        case 'bbox':
            $minLon = $args[1];
            $minLat = $args[2];
            $maxLon = $args[3];
            $maxLat = $args[4];
            $node = new Services_Openstreetmap_Node();
            try {
                $node->setLon($minLon);
                $node->setLat($minLat);
                $node->setLon($maxLon);
                $node->setLat($maxLat);
            } catch(Services_Openstreetmap_InvalidArgumentException $ex) {
                throw new Services_Openstreetmap_InvalidArgumentException(
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
                $t = strtotime($after);
                if ($t == -1 or $t === false) {
                    throw new Services_Openstreetmap_InvalidArgumentException(
                        'Invalid time value'
                    );
                }
                $after = gmstrftime("%Y-%m-%dT%H:%M:%SZ", $t);
            }
            if (isset($args[2])) {
                $before = $args[2];
                $t = strtotime($before);
                if ($t == -1 or $t === false) {
                    throw new Services_Openstreetmap_InvalidArgumentException(
                        'Invalid time value'
                    );
                }
                $before = gmstrftime("%Y-%m-%dT%H:%M:%SZ", $t);
            }
            if (!is_null($before)) {
                $this->value = "$after,$before";
            } else {
                $this->value = $after;
            }
            break;
        default:
            $this->type = null;
            throw new Services_Openstreetmap_InvalidArgumentException(
                'Unknown constraint type'
            );
        }
    }

    /**
     * Create the required query string portion
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
            return http_build_query(array($this->type => $this->value));
        }
    }

    /**
     * Return the criterion type (closed, open, bbox, display_name, or user)
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
