<?php
/**
 * Nominatim.php
 * 20-Mar-2012
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Nominatim.php
 */

/**
 * Services_OpenStreetMap_Nominatim
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Nominatim.php
 */
class Services_OpenStreetMap_Nominatim
{
    // http://wiki.openstreetmap.org/wiki/Nominatim

    /**
     * The server to connect to
     *
     * @var string
     */
    protected $server = 'http://nominatim.openstreetmap.org/';

    /**
     * Format to perform queries in (xml|json|html). Defaults to 'xml'
     *
     * @var string
     */
    protected $format = 'xml';

    /**
     * If 1, include a breakdown of the address into elements.
     *
     * @var int
     */
    protected $addresssdetails = 0;

    /**
     * Preferred language order. Standard rfc2616 string or a simple comma
     * seperated list of language codes.
     *
     * @var string
     */
    protected $accept_language = 'en';

    /**
     * Output polygon outlines for items found.
     *
     * @var null|boolean
     */
    protected $polygon = null;

    /**
     * The preferred area to find search results
     * <left>,<top>,<right>,<bottom>
     *
     * @var null|string
     */
    protected $viewbox = null;

    /**
     * If true, restrict results to those within the bounding box/view box.
     *
     * @var null|boolean
     */
    protected $bounded = null;

    /**
     * Remove duplicates?
     *
     * @var null|boolean
     */
    protected $dedupe = null;

    /**
     * Maximum number of entries to retrieve.
     *
     * @var int
     */
    protected $limit = null;

    /**
     * The transport to use
     *
     * @var Services_OpenStreetMap_Transport
     */
    protected $transport = null;

    /**
     * __construct
     *
     * @param Services_OpenStreetMap_Transport $transport Transport instance.
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function __construct($transport)
    {
        $this->setTransport($transport);
    }

    /**
     * Build query portion for request.
     *
     * @param string $place Name of location/place to search for
     *
     * @return string
     */
    private function _buildQuery($place)
    {
        $format = $this->format;
        $limit = $this->limit;
        $accept_language = $this->accept_language;
        $polygon = $this->polygon;
        $viewbox = $this->viewbox;
        $bounded = $this->bounded;
        $dedupe = $this->dedupe;

        $q = $place;

        $query = http_build_query(
            compact(
                'q',
                'accept_language',
                'format',
                'limit',
                'polygon',
                'viewbox',
                'bounded',
                'dedupe'
            )
        );
        return $query;
    }

    /**
     * search
     *
     * @param string  $place Name of place to geocode
     * @param integer $limit Maximum number of results to retrieve (optional)
     *
     * @return void
     */
    public function search($place, $limit = null)
    {
        if ($limit !== null) {
            $this->setLimit($limit);
        }

        $format = $this->format;
        $query = $this->_buildQuery($place);
        $url = $this->server . 'search?' . $query;

        $response = $this->getTransport()->getResponse($url);
        if ($format == 'xml') {
            $xml = simplexml_load_string($response->getBody());
            $places = $xml->xpath('//place');
            return $places;
        } elseif ($format == 'json' ) {
            $places = json_decode($response->getBody());
            return $places;
        } elseif ($format == 'html') {
            return $response->getBody();
        }
    }

    /**
     * setFormat
     *
     * @param string $format Set format for data to be received in (html, json, xml)
     *
     * @return Services_OpenStreetMap_Nominatim
     * @throws Services_OpenStreetMap_RuntimeException If the specified format
     *                                                 is not supported.
     */
    public function setFormat($format)
    {
        switch($format) {
        case 'html':
        case 'json':
        case 'xml':
            $this->format = $format;
            break;
        default:
            throw new Services_OpenStreetMap_RuntimeException(
                sprintf('Unrecognised format (%s)', $format)
            );
        }
        return $this;
    }

    /**
     * get which format is set for this instance (xml, json, html)
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * setLimit
     *
     * @param integer $limit Maximum number of entries to retrieve
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setLimit($limit)
    {
        if (is_numeric($limit)) {
            $this->limit = $limit;
        } else {
            throw new Services_OpenStreetMap_RuntimeException(
                'Limit must be a numeric value'
            );
        }
        return $this;
    }

    /**
     * get Limit
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * set Transport object.
     *
     * @param Services_OpenStreetMap_Transport $transport transport object
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Get current Transport object.
     *
     * @return Services_OpenStreetMap_Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * Set which server to connect to.
     *
     * Possible values are 'nominatim', 'mapquest' and any other valid
     * endpoint specified as an URL.
     *
     * @param string $server Server URL or shorthand (nominatim / mapquest)
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setServer($server)
    {
        switch($server) {
        case 'nominatim':
            $this->server = 'http://nominatim.openstreetmap.org/';
            return $this;
            break;
        case 'mapquest':
            $this->server = 'http://open.mapquestapi.com/nominatim/v1/';
            return $this;
            break;
        default:
            $parsed = parse_url($server);
            if (isset($parsed['scheme'])
                && isset($parsed['host'])
                && isset($parsed['path'])
            ) {
                $this->server = $server;
            } else {
                throw new Services_OpenStreetMap_RuntimeException(
                    'Server endpoint invalid'
                );
            }
            return $this;
        }
    }

    /**
     * Retrieve server endpoint.
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }
}

?>
