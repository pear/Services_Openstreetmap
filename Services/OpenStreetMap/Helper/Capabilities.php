<?php
/**
 * Xml.php
 * 20-May-2020
 *
 * PHP Version 7
 *
 * @category Xml
 * @package  Xml
 * @author   Ken Guest <ken@linux.ie>
 * @license  GPL (see http://www.gnu.org/licenses/gpl.txt)
 * @version  CVS: <cvs_id>
 * @link     Xml.php
 */

class Services_OpenStreetMap_Helper_Capabilities
{
    protected $api_version = null;
    protected $details = [];

    public function setApiVersion($version): void
    {
        $this->api_version = $version;
    }

    public function extract(string $capabilities): bool
    {
        if ($capabilities === '') {
            return false;
        }
        $xml = simplexml_load_string($capabilities);
        if (!$xml) {
            return false;
        }

        $helper = new Services_OpenStreetMap_Helper_Xml();
        $minVersion = (float) $helper->getValue($xml, 'version', 'minimum');
        $maxVersion = (float) $helper->getValue($xml, 'version', 'maximum');
        if ($minVersion > $this->api_version
            || $this->api_version > $maxVersion
        ) {
            throw new Services_OpenStreetMap_Exception(
                'Specified API Version ' . $this->api_version . ' not supported.'
            );
        }
        $timeout = (int) $helper->getValue($xml, 'timeout', 'seconds');

        //changesets
        $changesetMaximumElements = (int) $helper->getValue(
            $xml,
            'changesets',
            'maximum_elements'
        );

        // Maximum number of nodes per way.
        $waynodesMaximum = (int) $helper->getValue($xml, 'waynodes', 'maximum');

        // Number of tracepoints per way.
        $tracepointsPerPage = (int) $helper->getValue(
            $xml,
            'tracepoints',
            'per_page'
        );

        // Max size of area that can be downloaded in one request.
        $areaMaximum = (float) $helper->getValue($xml, 'area', 'maximum');

        $noteAreaMaximum = (int) $helper->getValue(
            $xml,
            'note_area',
            'maximum'
        );

        $databaseStatus = $helper->getValue($xml, 'status', 'database');
        $apiStatus = $helper->getValue($xml, 'status', 'api');
        $gpxStatus = $helper->getValue($xml, 'status', 'gpx');

        // What generated the XML.
        $generator = '' . $helper->getValue(
            $xml,
            'osm',
            'generator',
            'OpenStreetMap server'
        );

        $this->details = [
            'areaMaximum' => $areaMaximum,
            'apiStatus' => $apiStatus,
            'changesetMaximumElements' => $changesetMaximumElements,
            'databaseStatus' => $databaseStatus,
            'generator' => $generator,
            'gpxStatus' => $gpxStatus,
            'maxVersion' => $maxVersion,
            'minVersion' => $minVersion,
            'noteAreaMaximum' => $noteAreaMaximum,
            'timeout' => $timeout,
            'tracepointsPerPage' => $tracepointsPerPage,
            'waynodesMaximum' => $waynodesMaximum,
        ];

        return true;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
