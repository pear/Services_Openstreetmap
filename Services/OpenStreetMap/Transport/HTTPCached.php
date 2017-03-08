<?php
/**
 * HTTPCached.php
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
 * Load in the [PEAR] Cache package.
 */
require_once 'Cache.php';

/**
 * Using HTTP Transport.
 */
require_once 'Services/OpenStreetMap/Transport/HTTP.php';

/**
 * Services_OpenStreetMap_Transport_HTTPCached
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     HTTPCached.php
 */
class Services_OpenStreetMap_Transport_HTTPCached
    extends Services_OpenStreetMap_Transport_HTTP
{

    /**
     * Cache object
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Constructor, setting file Cache object.
     *
     * @return Services_OpenStreetMap_Transport_HTTPCached
     */
    public function __construct()
    {
        parent::__construct();

        $this->setCache(new Cache('file'));
    }

    /**
     * Set the cache object
     *
     * @param Cache $cache Cache object
     *
     * @return Services_OpenStreetMap_Transport_HTTPCached
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
        return $this;
    }

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
     * @todo   Consider just returning the content?
     * @throws Services_OpenStreetMap_Exception If something unexpected has
     *                                          happened while conversing with
     *                                          the server.
     */
    public function getResponse(
        $url,
        $method = HTTP_Request2::METHOD_GET,
        $user = null,
        $password = null,
        $body = null,
        array $post_data = null,
        array $headers = null
    ) {
        $arguments = [
            $url,
            $method,
            $user,
            $password,
            $body,
            implode(":", (array) $post_data),
            implode(":", (array) $headers)
        ];
        $id = md5(implode(":", $arguments));

        $data = $this->cache->get($id);
        if ($data) {
            $response = new HTTP_Request2_Response();
            $response->setStatus(200);
            $response->setBody($data);

            return $response;
        }

        $response = parent::getResponse(
            $url,
            $method,
            $user,
            $password,
            $body,
            $post_data,
            $headers
        );

        $this->cache->save($id, $response->getBody());

        return $response;
    }
}
?>
