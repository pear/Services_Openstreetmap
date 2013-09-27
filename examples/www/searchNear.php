<script>
jQuery.each(mapMarkers, function() {
    map.removeLayer(this);
});
</script>
<?php
require_once 'Services/OpenStreetMap.php';

const KILOMETERS = 6372.8;
const MILES = 3963.1676;
const NAUTUCAL_MILES = 3443.89849;
/**
 * Given two sets of lat/lon pairs, calculate the distance between them
 *
 * Distance defaults to being calculated in kilometers.
 *
 * @param mixed $aLon
 * @param mixed $aLat
 * @param mixed $bLon
 * @param mixed $bLat
 * @param mixed $unit
 *
 * @return float
 */
function calculateDistance($aLon, $aLat, $bLon, $bLat, $unit = KILOMETERS)
{
    $sinHalfDeltaLat = sin(deg2rad($bLat - $aLat) / 2.0);
    $sinHalfDeltaLon = sin(deg2rad($bLon - $aLon) / 2.0);
    $lonSqrd = $sinHalfDeltaLon * $sinHalfDeltaLon;
    $latSqrd = $sinHalfDeltaLat * $sinHalfDeltaLat;
    $angle = 2 * asin(
        sqrt($latSqrd + cos(deg2rad($aLat)) * cos(deg2rad($bLat)) * $lonSqrd)
    );
    return $unit * $angle;
}

/**
 * sortByDistance
 *
 * @return function
 */
function sortByDistance()
{
    return function ($a, $b) {
        if ($a->distance == $b->distance) {
            return 0;
        }
        return ($a->distance < $b->distance) ? -1 : 1;
    };
}

$lat = null;
$lon = null;
$k = null;
$v = null;

if (isset($_GET['q'])) {
    $v = strpos($_GET['q'], '|');
    if ($v != false) {
        list($k, $v) = explode('|', $_GET['q']);
        $k = trim($k);
        $v = trim($v);
    } else {
        die();
    }
}

if (isset($_GET['lat'])) {
    $lat = $_GET['lat'];
}
if (isset($_GET['lat'])) {
    $lon = $_GET['lon'];
}
$osm = new Services_OpenStreetMap();
$osm->loadXML("./map.osm");
$results = $osm->search(array($k => $v));
echo "List of $k/$v\n";
echo "==================\n\n";

$oh = new Services_OpenStreetMap_OpeningHours();
foreach ($results as $result) {
    if ($result->getType() == 'node') {
        $bLat = $result->getLat();
        $bLon = $result->getLon();
    } elseif ($result->getType() == 'way' && $result->isClosed()) {
        $nodes = $result->getNodes();
        array_pop($nodes);
        $bLat = 0;
        $bLon = 0;
        foreach ($nodes as $node) {
            $n = $osm->getNode($node);
            $bLat += $n->getLat();
            $bLon += $n->getLon();
        }
        $bLat = $bLat / sizeof($nodes);
        $bLon = $bLon / sizeof($nodes);
    }
    $distance = calculateDistance($lat, $lon, $bLat, $bLon);
    // $distance = $distance * 1000; // convert to metres
    $result->distance = $distance;
    $result->lat = $bLat;
    $result->lon = $bLon;
}

usort($results, sortByDistance());
foreach ($results as $result) {
    $tags = $result->getTags();
    $name = $tags['name'];
    $addrStreet = $tags['addr:street'];
    $addrCity = $tags['addr:city'];
    $addrCountry = $tags['addr:country'];
    $addrHouseName = $tags['addr:housename'];
    $addrHouseNumber = $tags['addr:housenumber'];
    $openingHours = $tags['opening_hours'];
    $phone = $tags['phone'];
    $bLat = $result->lat;
    $bLon = $result->lon;
    $oh->setValue($openingHours);
    $open = $oh->isOpen();

    $line1 = ($addrHouseNumber) ? $addrHouseNumber : $addrHouseName;
    if ($line1 != null) {
        $line1 .= ', ';
    }
    echo  "$name\n";
    $distance = $result->distance;
    echo "$bLat, ", $bLon, " (", number_format($distance, 4), "km)\n";
    echo "<script>";
    echo "var marker = L.marker([",$bLat  ,", ",$bLon," ]).addTo(map);";
    echo "marker.bindPopup(\"<b>", htmlspecialchars($name), "</b>\");";
    echo "mapMarkers.push(marker);";
    echo "</script>";
    if ($line1 != null && $addrStreet != null) {
        echo "{$line1}{$addrStreet}\n";
    }
    if ($phone != null) {
        echo "<a href='tel:$phone'>$phone</a>\n";
    }
    if ($openingHours !== null) {
        echo  "$openingHours\n";
        echo "Open?: ";
        if ($open === null) {
            echo "Maybe\n";
        } elseif ($open == true) {
            echo "Yes\n";
        } else {
            echo "No\n";
        }
    }
    echo "\n\n";
}

$result = $results[0];
$bLat = $result->lat;
$bLon = $result->lon;
echo "<script>";
echo "map.panTo([",$bLat,", ",$bLon," ]);";
echo "</script>";
