# Services_Openstreetmap
OpenStreetMap is a global project with an aim of collaboratively collecting map
data. This package aims to make communicating with the OSM API intuitive.

## Usage

### Initialisation

Simply require and initialize the Services_Openstreetmap class:

    require_once 'Services/Openstreetmap.php';
    $osm = new Services_Openstreetmap();

### Downloading Data, saving to an OSM file

    $osm->get(-8.3564758, 52.821022799999994, -7.7330017, 53.0428644);
    file_put_contents("area_covered.osm", $osm->getXML());


### Search for a specific POI, in a saved OSM file
    $osm = new Services_Openstreetmap();

    $osm->loadXML("./osm.osm");
    $results = $osm->search(array("amenity" => "pharmacy"));
    echo "List of Pharmacies\n";
    echo "==================\n\n";

    foreach ($results as $result) {
        $name = null;
        $addr_street = null;
        $addr_city = null;
        $addr_country = null;
        $addr_housename = null;
        $addr_housenumber = null;
        $opening_hours = null;
        $phone = null;

        extract($result);
        $line1 = ($addr_housenumber) ? $addr_housenumber : $addr_housename;
        if ($line1 != null) {
            $line1 .= ', ';
        }
        echo  "$name\n{$line1}{$addr_street}\n$phone\n$opening_hours\n\n";
    }

### Get a specific Node
    require_once 'Services/Openstreetmap.php';

    $osm = new Services_Openstreetmap();

    var_dump($osm->getNode(52245107));

    Getting specific changesets, ways etc follow the same pattern.

