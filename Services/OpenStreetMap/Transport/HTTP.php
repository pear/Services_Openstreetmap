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
        $this->setLog(new Log_null('null', ''));
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
     * @throws HTTP_Request2_LogicException
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
    ): \HTTP_Request2_Response {
        $response = null;
        $eMsg = null;
        if ($headers === null) {
            $headers = [];
        }
        $config = $this->getConfig();


        if ($config->getValue('verbose')) {
            $this->log->log($url);
        }

        $request = $this->getRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->setAdapter($config->getValue('adapter'));

        /* Issue 32 - SSL Config */
        $request->setConfig('ssl_verify_peer', $config->getValue('ssl_verify_peer'));
        $request->setConfig('ssl_verify_host', $config->getValue('ssl_verify_host'));
        $request->setConfig('ssl_cafile', $config->getValue('ssl_cafile'));
        $request->setConfig('ssl_local_cert', $config->getValue('ssl_local_cert'));
        $request->setConfig('ssl_passphrase', $config->getValue('ssl_passphrase'));

        $request->setHeader('User-Agent', $config->getValue('User-Agent'));

        if ($user !== null && $password !== null) {
            $request->setAuth($user, $password);
        }
        if ($post_data !== [] && $post_data !== null) {
            $request->setMethod(HTTP_Request2::METHOD_POST);
            $this->addPostParameters($post_data, $request);
        }
        $this->setHeaders($headers, $request);
        if ($body !== null) {
            $request->setBody($body);
        }
        $code = 0;
        try {
            $response = $request->send();
            $code = $response->getStatus();

            $this->log->log($response->getHeader());
            $this->log->log($response->getBody());

            if (self::OK === $code) {
                return $response;
            }

            $eMsg = sprintf(
                "Unexpected HTTP status: %s %s [for %s]", $code,
                $response->getReasonPhrase(), $response->getEffectiveUrl()
            );
            /**
             * What text for the error  was retrieved?
             *
             * @var string $error
             */
            $error = $response->getHeader('error');
            if (null !== $error) {
                $eMsg .= " ($error)";
            }
        } catch (HTTP_Request2_Exception $e) {
            $this->log->warning((string)$e);
            throw new Services_OpenStreetMap_Exception(
                $e->getMessage(),
                $code,
                $e
            );
        }
        if ($eMsg !== null) {
            throw new Services_OpenStreetMap_Exception($eMsg, $code);
        }
    }

    /**
     * Get HTTP_Request2 instance.
     *
     * @access public
     * @return HTTP_Request2
     */
    public function getRequest(): \HTTP_Request2
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
     * @return Services_OpenStreetMap_Transport_HTTP
     */
    public function setRequest(HTTP_Request2 $request): Services_OpenStreetMap_Transport_HTTP
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
    public function setLog(Log $log): Services_OpenStreetMap_Transport_HTTP
    {
        $this->log = $log;
        return $this;
    }

    /**
     * Get object of specified type and id.
     *
     * Returns false if the object is not found
     *
     * @param string $type    object type
     * @param string $id      id of object to retrieve
     * @param string $version version of object, optional
     * @param string $append  portion to append to request URL, optional
     *
     * @return Services_OpenStreetMap_Object|null
     * @throws HTTP_Request2_LogicException
     * @throws Services_OpenStreetMap_Exception
     */
    public function getObject(
        string $type,
        string $id,
        string $version = null,
        string $append = null
    ): ?Services_OpenStreetMap_Object {
        /*
        if (!in_array($type, $this->elements)) {
            throw new Services_OpenStreetMap_Exception(
                sprintf("Invalid Element Type '%s'", $type)
            );
        }*/

        $config = $this->getConfig()->asArray();
        $url = sprintf(
            "%sapi/%s/%s/%s", $config['server'], $config['api_version'], $type,
            $id
        );
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
            if (self::NOT_FOUND === $code) {
                return null;
            }
            if (self::GONE === $code) {
                return null;
            }
            throw $ex;
        }
        $class =  'Services_OpenStreetMap_' . ucfirst(strtolower($type));
        /** @var Services_OpenStreetMap_Object $obj */
        $obj = new $class();
        $xml = simplexml_load_string($response->getBody());
        if ($xml !== false) {
            $obj->setXml($xml);
        }
        return $obj;
    }

    /**
     * Get objects of specified type.
     *
     * @param string $type object type
     * @param array  $ids  ids of objects to retrieve
     *
     * @return Services_OpenStreetMap_Objects|false
     * @throws Services_OpenStreetMap_Exception
     * @throws HTTP_Request2_LogicException
     */
    public function getObjects(
        string $type,
        array $ids
    ) {
        /*
        if (!in_array($type, $this->elements)) {
            throw new Services_OpenStreetMap_Exception('Invalid Element Type');
        }
        */
        $response = null;
        $config = $this->getConfig();
        $url = sprintf(
            "%sapi/%s/%ss?%ss=%s", $config->getValue('server'),
            $config->getValue('api_version'), $type, $type, implode(',', $ids)
        );
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
        /** @var Services_OpenStreetMap_Objects $obj */
        $obj = new $class();
        $obj->setConfig($config);
        $obj->setTransport($this);
        $sxe = @simplexml_load_string($response->getBody());
        if (!$sxe) {
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
     * @return Services_OpenStreetMap_Transport
     */
    public function setConfig(Services_OpenStreetMap_Config $config): Services_OpenStreetMap_Transport
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get current Config object
     *
     * @return Services_OpenStreetMap_Config
     */
    public function getConfig(): \Services_OpenStreetMap_Config
    {
        return $this->config;
    }

    /**
     * Search objects of specified type for certain criteria.
     *
     * @param string $type     object type (e.g. changeset)
     * @param array  $criteria array of criterion objects.
     *
     * @return Services_OpenStreetMap_Objects|null
     * @throws HTTP_Request2_LogicException
     * @throws Services_OpenStreetMap_Exception
     */
    public function searchObjects(
        string $type,
        array $criteria
    ):?Services_OpenStreetMap_Objects {
        $response = null;
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
                return null;
            default:
                throw $ex;
            }
        }
        $class = 'Services_OpenStreetMap_' . ucfirst(strtolower($type)) . 's';
        /** @var Services_OpenStreetMap_Objects $obj */
        $obj = new $class();
        $sxe = @simplexml_load_string($response->getBody());
        if (!$sxe) {
            $obj->setVal(trim($response->getBody()));
        } else {
            $obj->setXml($sxe);
        }
        return $obj;
    }

    /**
     * Add headers to request
     *
     * @param array         $headers Associative key/val array of headers
     * @param HTTP_Request2 $request Request
     *
     * @return void
     * @throws HTTP_Request2_LogicException
     */
    public function setHeaders(array $headers, HTTP_Request2 $request): void
    {
        if ($headers !== []) {
            foreach ($headers as $header) {
                $request->setHeader($header[0], $header[1], $header[2]);
            }
        }
    }

    /**
     * Add post parameters to request object
     *
     * @param array         $post_data Associative key/val array of post parameters
     * @param HTTP_Request2 $request   Request
     *
     * @return void
     */
    public function addPostParameters(array $post_data, HTTP_Request2 $request): void
    {
        foreach ($post_data as $key => $value) {
            $request->addPostParameter($key, $value);
        }
    }
}
