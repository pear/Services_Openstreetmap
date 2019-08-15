<?php
/**
 * Transport.php
 * 08-Nov-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Transport
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Services_OpenStreetMap
 */

/**
 * Services_OpenStreetMap_Transport
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Transport.php
 */
interface Services_OpenStreetMap_Transport
{

    /**
     * #@+
     *
     * @link   http://tools.ietf.org/html/rfc2616
     * @access public
     */
    /**
     * Ok
     *
     * @var \int
     */
    const OK = 200;
    /**
     * Unauthorised, e.g. login credentials wrong.
     *
     * @var \int
     */
    const UNAUTHORISED = 401;
    /**
     * Resource not found.
     *
     * @var \int
     */
    const NOT_FOUND = 404;
    /**
     * Resource no longer available.
     *
     * @var \int
     */
    const GONE = 410;
    /**
     * #@-
     */


    /**
     * Get object of specified type and id, optionally of specified version.
     *
     * Returns false if the object is not found
     *
     * @param string $type    object type
     * @param string $id      id of object to retrieve
     * @param string $version version of object, optional
     * @param string $append  portion to append to request URL, optional
     *
     * @return Services_OpenStreetMap_Object
     * @throws Services_OpenStreetMap_Exception
     */
    public function getObject(
        string $type,
        string $id,
        string $version = null,
        string $append = null
    ): ?Services_OpenStreetMap_Object;

    /**
     * Get objects of specified type.
     *
     * @param string $type object type
     * @param array  $ids  ids of objects to retrieve
     *
     * @return Services_OpenStreetMap_Objects
     */
    public function getObjects(string $type, array $ids);

    /**
     * Send request to OSM server and return the response.
     *
     * @param string $url       URL
     * @param string $method    GET (default)/POST/PUT
     * @param string $user      user (optional for read-only actions)
     * @param string $password  password (optional for read-only actions)
     * @param string $body      body (optional)
     * @param array  $post_data (optional)
     * @param array  $headers   (optional)
     *
     * @access public
     * @return HTTP_Request2_Response
     * @throws Services_OpenStreetMap_Exception If something unexpected has
     *                                          happened while conversing with
     *                                          the server.
     */
    public function getResponse(
        string $url,
        string $method = HTTP_Request2::METHOD_GET,
        string $user = null,
        string $password = null,
        string $body = null,
        array $post_data = null,
        array $headers = null
    ): HTTP_Request2_Response;

    /**
     * Search Objects of specified type for certain criteria.
     *
     * @param string $type     object type (e.g. changeset)
     * @param array  $criteria array of criterion objects.
     *
     * @return Services_OpenStreetMap_Objects
     *
     * @see Services_OpenStreetMap_Criterion
     */
    public function searchObjects(
        string $type,
        array $criteria
    ):?Services_OpenStreetMap_Objects;
}
