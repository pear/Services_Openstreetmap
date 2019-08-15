<?php
/**
 * Nominatim.php
 * 20-Mar-2012
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Nominatim
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Nominatim.php
 */

/**
 * Services_OpenStreetMap_Nominatim
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Nominatim
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Nominatim.php
 */
class Services_OpenStreetMap_Nominatim
{
    // http://wiki.openstreetmap.org/wiki/Nominatim

    /**
     * The server to connect to
     *
     * @var string
     */
    protected $server = 'https://nominatim.openstreetmap.org/';

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
    protected $addressdetails = 0;

    /**
     * Preferred language order. Standard rfc2616 string or a simple comma
     * separated list of language codes.
     *
     * @var string
     */
    protected $accept_language = 'en';

    /**
     * Email address to be sent as a part of the query string, recommended to
     * be set if sending large numbers of requests/searches.
     *
     * @var string
     */
    protected $email_address;

    /**
     * Output polygon outlines for items found.
     *
     * @var null|boolean
     */
    protected $polygon;

    /**
     * The preferred area to find search results
     * <left>,<top>,<right>,<bottom>
     *
     * @var null|string
     */
    protected $viewbox;

    /**
     * If true, restrict results to those within the bounding box/view box.
     *
     * @var null|boolean
     */
    protected $bounded;

    /**
     * Remove duplicates?
     *
     * @var null|boolean
     */
    protected $dedupe;

    /**
     * Maximum number of entries to retrieve.
     *
     * @var int
     */
    protected $limit;

    /**
     * CSVs of valid country codes to restrict search to.
     *
     * @var string|null
     */
    protected $countryCodes;

    /**
     * The transport to use
     *
     * @var Services_OpenStreetMap_Transport
     */
    protected $transport;

    /**
     * Constructor
     *
     * @param Services_OpenStreetMap_Transport $transport Transport instance.
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function __construct(Services_OpenStreetMap_Transport $transport)
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
    private function _buildQuery(string $place): string
    {
        $params = [
            'q' => $place,
            'format' => $this->format,
            'limit' => $this->limit,
            'polygon' => $this->polygon,
            'viewbox' => $this->viewbox,
            'bounded' => $this->bounded,
            'dedupe' => $this->dedupe,
            'addressdetails' => $this->addressdetails
        ];
        $params['accept-language'] = $this->accept_language;
        if ($this->email_address !== null) {
            $params['email'] = $this->email_address;
        }
        if ($this->countryCodes !== null) {
            $params['countrycodes'] = $this->countryCodes;
        }
        return http_build_query($params);
    }

    /**
     * Reverse geocode a lat/lon pair.
     *
     * Perform a reverse search/geoencoding.
     *
     * @param string $lat            Latitude
     * @param string $lon            Longitude
     * @param int    $addressdetails Include address details, defaults to 1.
     * @param int    $zoom           Zoom level, defaults to 18.
     *
     * @return object|string
     *
     * @throws HTTP_Request2_Exception
     * @throws Services_OpenStreetMap_Exception
     * @see    setAcceptLanguage
     * @see    setFormat
     */
    public function reverseGeocode(
        string $lat,
        string $lon,
        int $addressdetails = 1,
        int $zoom = 18
    ) {
        $format = $this->format;
        if ($format === 'html') {
            throw new Services_OpenStreetMap_RuntimeException(
                'html format not accepted for reverseGeocode'
            );
        }
        $params = [
            'accept-language' => $this->accept_language,
            'addressdetails'  => $addressdetails,
            'format' => $format,
            'lat' => $lat,
            'lon' => $lon,
            'zoom' => $zoom
        ];
        if ($this->email_address !== null) {
            $params['email'] = $this->email_address;
        }
        $query = http_build_query($params);
        $url = $this->server . 'reverse?' . $query;

        $reversegeocode = null;
        $response = $this->getTransport()->getResponse($url);
        if ($format === 'xml') {
            $xml = simplexml_load_string($response->getBody());
            if ($xml !== false) {
                $reversegeocode = $xml->xpath('//reversegeocode');
            }
        } elseif ($format === 'json' || $format === 'jsonv2') {
            $reversegeocode = json_decode($response->getBody());
        }
        return $reversegeocode;
    }

    /**
     * Search
     *
     * @param string  $place Name of place to geocode
     * @param integer $limit Maximum number of results to retrieve (optional)
     *
     * @return mixed
     */
    public function search(string $place, int $limit = null)
    {
        if ($limit !== null) {
            $this->setLimit($limit);
        }

        $format = $this->format;
        $query = $this->_buildQuery($place);
        $url = $this->server . 'search?' . $query;

        $response = $this->getTransport()->getResponse($url);
        if ($format === 'xml') {
            $xml = simplexml_load_string($response->getBody());
            if ($xml !== false) {
                return $xml->xpath('//place');
            }
        } elseif ($format === 'json' || $format === 'jsonv2') {
            return json_decode($response->getBody());
        } elseif ($format === 'html') {
            return $response->getBody();
        }
    }

    /**
     * Set format for data to be received in.
     *
     * Format may be one of: html, json, jsonv2, xml
     *
     * @param string $format Format for data.
     *
     * @return Services_OpenStreetMap_Nominatim
     * @throws Services_OpenStreetMap_RuntimeException If the specified format
     *                                                 is not supported.
     */
    public function setFormat(string $format): Services_OpenStreetMap_Nominatim
    {
        switch ($format) {
        case 'html':
        case 'json':
        case 'jsonv2':
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
     * Get which format is set for this instance (xml, json, html)
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Set limit of entries to retrieve.
     *
     * @param integer $limit Maximum number of entries to retrieve
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setLimit(int $limit): Services_OpenStreetMap_Nominatim
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Get Limit
     *
     * @return integer
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set Transport object.
     *
     * @param Services_OpenStreetMap_Transport $transport transport object
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setTransport(
        Services_OpenStreetMap_Transport $transport
    ): Services_OpenStreetMap_Nominatim {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Get current Transport object.
     *
     * @return Services_OpenStreetMap_Transport
     */
    public function getTransport(): \Services_OpenStreetMap_Transport
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
     * @throws Services_OpenStreetMap_RuntimeException
     */
    public function setServer(string $server): Services_OpenStreetMap_Nominatim
    {
        switch($server) {
        case 'nominatim':
            $this->server = 'https://nominatim.openstreetmap.org/';
            return $this;
            break;
        case 'mapquest':
            $this->server = 'https://open.mapquestapi.com/nominatim/v1/';
            return $this;
            break;
        default:
            $parsed = parse_url($server);
            if ($parsed === false) {
                throw new Services_OpenStreetMap_RuntimeException(
                    'Could not parse Server URL'
                );
            }
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
     * Set referred language order for showing search results.
     *
     * This overrides the browser value.
     * Either uses standard rfc2616 accept-language string or a simple comma
     * separated list of language codes.
     *
     * @param string $language language code
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setAcceptLanguage(
        string $language
    ): Services_OpenStreetMap_Nominatim {
        $this->accept_language = $language;
        return $this;
    }

    /**
     * Retrieve server endpoint.
     *
     * @return string
     */
    public function getServer(): string
    {
        return $this->server;
    }

    /**
     * Set email address.
     *
     * @param string $email Valid email address
     *
     * @return Services_OpenStreetMap_Nominatim
     * @throws Services_OpenStreetMap_RuntimeException If email address invalid
     */
    public function setEmailAddress(string $email): Services_OpenStreetMap_Nominatim
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Services_OpenStreetMap_RuntimeException(
                sprintf("Email address '%s' is not valid", $email)
            );
        }
        $this->email_address = $email;
        return $this;
    }

    /**
     * Set country codes to limit search results to.
     *
     * @param string $codes CSV list of country codes.
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setCountryCodes(string $codes): Services_OpenStreetMap_Nominatim
    {
        $this->countryCodes = $codes === '' ? null : $codes;
        return $this;
    }

    /**
     * Retrieve set email address.
     *
     * From OSM documentation:
     * If you are making large numbers of request please include a valid
     * email address or alternatively include your email address as
     * part of the User-Agent string.  This information will be kept
     * confidential and only used to contact you in the event of a
     * problem, see Usage Policy for more details.
     *
     * @return string|null
     */
    public function getEmailAddress():?string
    {
        return $this->email_address;
    }

    /**
     * Retrieve addressdetails setting.
     *
     * @return int
     */
    public function getAddressdetails(): int
    {
        return $this->addressdetails;
    }

    /**
     * Signal that addressdetails are to be broken down into elements.
     *
     * @param int $addressdetails Whether to get address details as elements.
     *
     * @return Services_OpenStreetMap_Nominatim
     */
    public function setAddressdetails(
        int $addressdetails
    ): Services_OpenStreetMap_Nominatim {
        $this->addressdetails = $addressdetails;

        return $this;
    }
}
