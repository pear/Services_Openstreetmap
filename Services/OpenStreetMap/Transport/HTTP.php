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
 * Implementing the Services_OpenStreetMap_Transport interface...
 */
require_once 'Services/OpenStreetMap/Transport.php';

/**
 * Pull in the PEAR Log package.
 */
require_once 'Log.php';

/**
 * Using the null log handler, this is overridden elsewhere when required.
 *
 * @see setLog
 */
require_once 'Log/null.php';

/**
 * Using HTTP_Request2 for HTTP.
 */
require_once 'HTTP/Request2.php';

/**
 * Services_OpenStreetMap_Transport
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Transport
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Transport.php
 */
class Services_OpenStreetMap_Transport_HTTP
    implements Services_OpenStreetMap_Transport
{
    /**
     * Constructor
     *
     * @return Services_OpenStreetMap_Transport_HTTP
     */
    public function __construct()
    {
        $this->setConfig(new Services_OpenStreetMap_Config());
        $this->setRequest(new HTTP_Request2());
        $this->setLog(new Log_null(null, null));
    }

    /**
     * The HTTP_Request2 instance.
     *
     * Customise this for proxy settings etc with the getRequest/setRequest
     * methods if necessary.
     *
     * @var HTTP_Request2 $request
     * @see Services_OpenStreetMap::getRequest
     * @see Services_OpenStreetMap::setRequest
     *
     * @internal
     */
    protected $request = null;

    /**
     * Config object, contains setting on how to interact with API Endpoint
     *
     * @var Services_OpenStreetMap_Config $config
     */
    protected $config = null;

    /**
     * Log object
     *
     * @var Log $log
     */
    protected $log = null;



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
        $response = null;
        $eMsg = null;


        if ($this->getConfig()->getValue('verbose')) {
            $this->log->log($url);
        }

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
        if ($post_data != []) {
            $request->setMethod(HTTP_Request2::METHOD_POST);
            foreach ($post_data as $key => $value) {
                $request->addPostParameter($key, $value);
            }
        }
        if ($headers != []) {
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

            $this->log->log($response->getHeader());
            $this->log->log($response->getBody());

            if (self::OK == $code) {
                return $response;
            } else {
                $eMsg = 'Unexpected HTTP status: '
                    . $code . ' '
                    . $response->getReasonPhrase()
                    . ' [for ' .  $response->getEffectiveUrl() . ']';
                $error = $response->getHeader('error');
                if (!is_null($error)) {
                    $eMsg .= " ($error)";
                }
            }
        } catch (HTTP_Request2_Exception $e) {
            $this->log->warning((string)$e);
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
     * @access public
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
     * Set Log object.
     *
     * @param Log $log Log object
     *
     * @return Services_OpenStreetMap_Transport_HTTP
     */
    public function setLog(Log $log)
    {
        $this->log = $log;
    }

    /**
     * Get object of specified type and id.
     *
     * Returns false if the object is not found
     *
     * @param string $type    object type
     * @param mixed  $id      id of object to retrieve
     * @param mixed  $version version of object, optional
     * @param mixed  $append  portion to append to request URL, optional
     *
     * @return object
     * @throws Services_OpenStreetMap_Exception
     */
    public function getObject($type, $id, $version = null, $append = null)
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
        if ($version !== null) {
            $url .= "/$version";
            if ($append !== null) {
                $url .= "/$append";
            }
        }
        try {
            $response = $this->getResponse($url);
        } catch (Services_OpenStreetMap_Exception $ex) {
            $this->log->warning((string)$ex);

            $code = $ex->getCode();
            if (self::NOT_FOUND == $code) {
                return false;
            } elseif (self::GONE == $code) {
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
     * Get objects of specified type.
     *
     * @param string $type object type
     * @param array  $ids  ids of objects to retrieve
     *
     * @return void
     */
    public function getObjects($type, array $ids)
    {
        /*
        if (!in_array($type, $this->elements)) {
            throw new Services_OpenStreetMap_Exception('Invalid Element Type');
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
        } catch (Services_OpenStreetMap_Exception $ex) {
            $this->log->warning((string)$ex);
            switch ($ex->getCode()) {
            case self::NOT_FOUND:
            case self::UNAUTHORISED:
            case self::GONE:
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
     * Search objects of specified type for certain criteria.
     *
     * @param string $type     object type (e.g. changeset)
     * @param array  $criteria array of criterion objects.
     *
     * @return Services_OpenStreetMap_Objects
     */
    public function searchObjects($type, array $criteria)
    {
        $query = [];
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
            $this->log->warning((string)$ex);
            switch ($ex->getCode()) {
            case self::NOT_FOUND:
            case self::UNAUTHORISED:
            case self::GONE:
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
}
