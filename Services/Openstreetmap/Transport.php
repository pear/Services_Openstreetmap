<?php
/**
 * Transport.php
 * 08-Nov-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Services_Openstreetmap
 */

/**
 * Services_Openstreetmap_Transport
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Transport.php
 */
class Services_Openstreetmap_Transport
{
    /**#@+
     * @link http://tools.ietf.org/html/rfc2616
     * @access public
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
     * Resource no longer available.
     */
    const GONE = 410;
    /**#@-*/


    /**
     * The HTTP_Request2 instance.
     *
     * Customise this for proxy settings etc with the getRequest/setRequest
     * methods if necessary.
     *
     * @var HTTP_Request2 $request
     * @internal
     * @see Services_Openstreetmap::getRequest
     * @see Services_Openstreetmap::setRequest
     */
    protected $request = null;

    protected $config = null;

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
     * @throws  Services_Openstreetmap_Exception If something unexpected has
     *                                           happened while conversing with
     *                                           the server.
     */
    function getResponse(
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
            echo $url, "\n";
        }
        /*
        $request = new HTTP_Request2(
            $url,
            $method,
            array('adapter' => $this->getConfig('adapter'))
        );
        */
        $request = $this->getRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->setAdapter($this->getConfig()->getValue('adapter'));


        $request->setHeader(
            'User-Agent',
            $this->getConfig()->getValue('User-Agent')
        );
        if ($user !== null && $password !== null) {
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
        if ($body !== null) {
            $request->setBody($body);
        }
        $code = 0;
        try {
            $response = $request->send();
            $code = $response->getStatus();

            if ($this->getConfig()->getValue('verbose')) {
                var_dump($response->getHeader());
                var_dump($response->getBody());
            }

            if (Services_Openstreetmap_Transport::OK == $code) {
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
            throw new Services_Openstreetmap_Exception(
                $e->getMessage(),
                $code,
                $e
            );
        }
        if ($eMsg != null) {
            throw new Services_Openstreetmap_Exception($eMsg, $code);
        }
    }

    /**
     * Get HTTP_Request2 instance.
     *
     * @access public
     * @return HTTP_Request2
     */
    function getRequest()
    {
        if ($this->request === null) {
            $this->request = new HTTP_Request2();
        }
        return $this->request;
    }

    /**
     * Set the HTTP_Request2 instance and return the Services_Openstreetmap
     * instance.
     *
     * Use this to inject a specific HTTP_Request2 instance.
     *
     * @param HTTP_Request2 $request The HTTP_Request2 instance to set.
     *
     * @return Services_Openstreetmap
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
     * @throws Services_Openstreetmap_Exception
     */
    public function getObject($type, $id, $version = null)
    {
        /*
        if (!in_array($type, $this->elements)) {
            throw new Services_Openstreetmap_Exception(
                sprintf("Invalid Element Type '%s'", $type)
            );
        }*/

        $config = $this->getConfig()->asArray();
        $url = $config['server']
            . 'api/'
            . $config['api_version']
            . '/' . $type . '/'
            . $id;
        if ($version !== null) {
            $url .= "/$version";
        }
        try {
            $response = $this->getResponse($url);
        } catch (Services_Openstreetmap_Exception $ex) {
            $code = $ex->getCode();
            if (Services_Openstreetmap_Transport::NOT_FOUND == $code) {
                return false;
            } elseif (Services_Openstreetmap_Transport::GONE == $code) {
                return false;
            } else {
                throw $ex;
            }
        }
        $class =  'Services_Openstreetmap_' . ucfirst(strtolower($type));
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
        /*
        if (!in_array($type, $this->elements)) {
            throw new Services_Openstreetmap_Exception('Invalid Element Type');
        }
        */
        $config = $this->getConfig();
        $url = $config->getValue('server')
            . 'api/'
            . $config->getValue('api_version')
            . '/' . $type . 's?' . $type . 's='
            . implode(',', $ids);
        try {
            $response = $this->getResponse($url);
        } catch (Services_Openstreetmap_Exception $ex) {
            switch ($ex->getCode()) {
            case Services_Openstreetmap_Transport::NOT_FOUND:
            case Services_Openstreetmap_Transport::UNAUTHORISED:
            case Services_Openstreetmap_Transport::GONE:
                return false;
            default:
                throw $ex;
            }
        }
        $class = 'Services_Openstreetmap_' . ucfirst(strtolower($type)) . 's';
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
     * Set Config object
     *
     * @param Services_Openstreetmap_Config $config Config settings.
     *
     * @return Services_Openstreetmap_API_V06
     */
    public function setConfig(Services_Openstreetmap_Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get current Config object
     *
     * @return Services_Openstreetmap_Config
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
     * @return Services_Openstreetmap_Objects
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
        } catch (Services_Openstreetmap_Exception $ex) {
            switch ($ex->getCode()) {
            case Services_Openstreetmap_Transport::NOT_FOUND:
            case Services_Openstreetmap_Transport::UNAUTHORISED:
            case Services_Openstreetmap_Transport::GONE:
                return false;
            default:
                throw $ex;
            }
        }
        $class = 'Services_Openstreetmap_' . ucfirst(strtolower($type)) . 's';
        $obj = new $class();
        $sxe = @simplexml_load_string($response->getBody());
        if ($sxe === false) {
            $obj->setVal(trim($response->getBody()));
        } else {
            $obj->setXml($sxe);
        }
        return $obj;

    }
}
// vim:set et ts=4 sw=4:
?>
