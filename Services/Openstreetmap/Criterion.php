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
    protected $type = null;
    protected $uid = null;
    protected $display_name = null;

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
        case 'uid':
            if (is_numeric($args[1])) {
                $this->uid = $args[1];
            } else {
                throw new InvalidArgumentException('UID must be numeric');
            }
            break;
        case 'display_name':
            $this->display_name = $args[1];
            break;
        case 'open':
            break;
        default:
            $this->type = null;
            throw new InvalidArgumentException('Unknown constraint type');
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
        case 'uid':
            return http_build_query(array($this->type => $this->uid));
        case 'display_name':
            return http_build_query(array($this->type => $this->display_name));
        case 'open':
            return 'open';
        }
    }
}

// vim:set et ts=4 sw=4:
?>
