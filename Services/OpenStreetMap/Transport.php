<?php
/**
 * Transport.php
 * 08-Nov-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Services_OpenStreetMap
 */


require_once 'Log.php';
require_once 'Log/null.php';

/**
 * Services_OpenStreetMap_Transport
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Transport.php
 */
class Services_OpenStreetMap_Transport
{
    /**#@+
     * @link http://tools.ietf.org/html/rfc2616
     */
    /**
     * Ok
     */
    const OK = 200;
    /**
     * Unauthorised, e.g. login credentials wrong.
     */
    const UNAUTHORISED = 401;
    /**
     * Resource not found.
     */
    const NOT_FOUND = 404;
    /**
     * Method not allowed.
     */
    const METHOD_NOT_ALLOWED = 405;
    /**
     * Conflict.
     */
    const CONFLICT = 409;
    /**
     * Resource no longer available.
     */
    const GONE = 410;
    /**
     * Precondition failed.
     */
    const PRECONDITION_FAILED = 412;
    /**
     * Internal server error.
     */
    const INTERNAL_SERVER_ERROR = 500;
    /**
     * Service unavailable.
     */
    const SERVICE_UNAVAILABLE = 503;
    /**
     * Bandwidth limited exceeded.
     * @link http://wiki.openstreetmap.org/wiki/API_v0.6
     */
    const BANDWIDTH_LIMIT_EXCEEDED = 509;
    /**#@-*/

    /**
     * The HTTP_Request2 instance.
     *
     * Customise this for proxy settings etc with the getRequest/setRequest
     * methods if necessary.
     *
     * @var HTTP_Request2 $request
     * @internal
     * @see Services_OpenStreetMap::getRequest
     * @see Services_OpenStreetMap::setRequest
     */
    protected $request = null;

    /**
     * @var Services_OpenStreetMap_Config $config
     */
    protected $config = null;

    /**
     * @var log $log
     */
    protected $log = null;

    /**
     * __construct
     *
     * @return Services_OpenStreetMap_Transport
     */
    public function __construct()
    {
        $this->setConfig(new Services_OpenStreetMap_Config());
        $this->setRequest(new HTTP_Request2());
        $this->setLog(new Log_null(null));
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
     * @return HTTP_Request2_Response
     * @throws  Services_OpenStreetMap_Exception If something unexpected has
     *                                           happened while conversing with
     *                                           the server.
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
        $response = null;
        $eMsg = null;

        if ($this->getConfig()->getValue('verbose')) {
            $this->log->debug($url);
        }
        $request = $this->getRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->setAdapter($this->getConfig()->getValue('adapter'));


        $request->setHeader(
            'User-Agent',
            $this->getConfig()->getValue('User-Agent')
        );
        if (!is_null($user) && !is_null($password)) {
            $request->setAuth($user, $password);
        }
        if ($post_data != array()) {
            $request->setMethod(HTTP_Request2::METHOD_POST);
            foreach ($post_data as $key => $value) {
                $request->addPostParameter($key, $value);
            }
        }
        if ($headers != array()) {
            foreach ($headers as $header) {
                $request->setHeader($header[0], $header[1], $header[2]);
            }
        }
        if (!is_null($body)) {
            $request->setBody($body);
        }
        $code = 0;
        try {
            $response = $request->send();
            $code = $response->getStatus();

            if ($this->getConfig()->getValue('verbose')) {
                $this->log->debug($response->getHeader());
                $this->log->debug($response->getBody());
            }

            if (Services_OpenStreetMap_Transport::OK == $code) {
                return $response;
            } else {
                $eMsg = 'Unexpected HTTP status: '
                    . $code . ' '
                    . $response->getReasonPhrase();
                $error = $response->getHeader('error');
                if (!is_null($error)) {
                    $eMsg .= " ($error)";
                }

            }
        } catch (HTTP_Request2_Exception $e) {
            $this->log->warning($response->getHeader());
            throw new Services_OpenStreetMap_Exception(
                $e->getMessage(),
                $code,
                $e
            );
        }
        if ($eMsg != null) {
            throw new Services_OpenStreetMap_Exception($eMsg, $code);
        }
    }

    /**
     * Get HTTP_Request2 instance.
     *
     * @return HTTP_Request2
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the HTTP_Request2 instance and return the Services_OpenStreetMap
     * instance.
     *
     * Use this to inject a specific HTTP_Request2 instance.
     *
     * @param HTTP_Request2 $request The HTTP_Request2 instance to set.
     *
     * @return Services_OpenStreetMap
     */
    public function setRequest(HTTP_Request2 $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * getObject
     *
     * Returns false if the object is not found
     *
     * @param string $type    object type
     * @param mixed  $id      id of object to retrieve
     * @param mixed  $version version of object
     *
     * @return object
     * @throws Services_OpenStreetMap_Exception
     */
    public function getObject($type, $id, $version = null)
    {
        /*
        if (!in_array($type, $this->elements)) {
            throw new Services_OpenStreetMap_Exception(
                sprintf("Invalid Element Type '%s'", $type)
            );
        }*/

        $config = $this->getConfig()->asArray();
        $url = $config['server']
            . 'api/'
            . $config['api_version']
            . '/' . $type . '/'
            . $id;
        if (!is_null($version)) {
            $url .= "/$version";
        }
        try {
            $response = $this->getResponse($url);
        } catch (Services_OpenStreetMap_Exception $ex) {
            $code = $ex->getCode();
            if (Services_OpenStreetMap_Transport::NOT_FOUND == $code) {
                return false;
            } elseif (Services_OpenStreetMap_Transport::GONE == $code) {
                return false;
            } else {
                throw $ex;
            }
        }
        $class =  'Services_OpenStreetMap_' . ucfirst(strtolower($type));
        $obj = new $class();
        $obj->setXml(simplexml_load_string($response->getBody()));
        return $obj;
    }

    /**
     * getObjects
     *
     * @param string $type object type
     * @param array  $ids  ids of objects to retrieve
     *
     * @return void
     *
     */
    public function getObjects($type, array $ids)
    {
        $config = $this->getConfig();
        $url = $config->getValue('server')
            . 'api/'
            . $config->getValue('api_version')
            . '/' . $type . 's?' . $type . 's='
            . implode(',', $ids);
        try {
            $response = $this->getResponse($url);
        } catch (Services_OpenStreetMap_Exception $ex) {
            if (isset($response)) {
                $this->log->warning($response->getHeader());
            }
            switch ($ex->getCode()) {
            case Services_OpenStreetMap_Transport::NOT_FOUND:
            case Services_OpenStreetMap_Transport::UNAUTHORISED:
            case Services_OpenStreetMap_Transport::GONE:
                return false;
            default:
                throw $ex;
            }
        }

        $class = 'Services_OpenStreetMap_' . ucfirst(strtolower($type)) . 's';
        $obj = new $class();
        if (!is_null($config)) {
            $obj->setConfig($config);
        }
        $obj->setTransport($this);
        $sxe = @simplexml_load_string($response->getBody());
        if ($sxe === false) {
            $obj->setVal(trim($response->getBody()));
        } else {
            $obj->setXml($sxe);
        }
        return $obj;
    }

    /**
     * Set Config object
     *
     * @param Services_OpenStreetMap_Config $config Config settings.
     *
     * @return Services_OpenStreetMap_API_V06
     */
    public function setConfig(Services_OpenStreetMap_Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get current Config object
     *
     * @return Services_OpenStreetMap_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * searchObjects
     *
     * @param string $type     object type (e.g. changeset)
     * @param array  $criteria array of criterion objects.
     *
     * @return Services_OpenStreetMap_Objects
     */
    public function searchObjects($type, array $criteria)
    {
        $query = array();
        foreach ($criteria as $criterion) {
            $query[] = $criterion->query();
        }
        $config = $this->getConfig();
        $url = $config->getValue('server')
            . 'api/'
            . $config->getValue('api_version')
            . '/' . $type . 's?' . implode('&', $query);
        try {
            $response = $this->getResponse($url);
        } catch (Services_OpenStreetMap_Exception $ex) {
            $this->log->warning($response->getHeader());
            switch ($ex->getCode()) {
            case Services_OpenStreetMap_Transport::NOT_FOUND:
            case Services_OpenStreetMap_Transport::UNAUTHORISED:
            case Services_OpenStreetMap_Transport::GONE:
                return false;
            default:
                throw $ex;
            }
        }
        $class = 'Services_OpenStreetMap_' . ucfirst(strtolower($type)) . 's';
        $obj = new $class();
        $sxe = @simplexml_load_string($response->getBody());
        if ($sxe === false) {
            $obj->setVal(trim($response->getBody()));
        } else {
            $obj->setXml($sxe);
        }
        return $obj;
    }

    /**
     * set Log object
     *
     * @param Log $log Log object
     *
     * @return Services_OpenStreetMap_Transport
     */
    public function setLog($log)
    {
        $this->log = $log;
        return $this;
    }
}
// vim:set et ts=4 sw=4:
?>
