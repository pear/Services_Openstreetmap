# Services_OpenStreetMap
OpenStreetMap is a global project with an aim of collaboratively collecting map
data. This package aims to make communicating with the OSM API intuitive.

## Usage

### Initialisation

Simply require and initialize the Services_OpenStreetMap class:

    require_once 'Services/OpenStreetMap.php';
    $osm = new Services_OpenStreetMap();

### Downloading Data, saving to an OSM file

    $osm->get(-8.3564758, 52.821022799999994, -7.7330017, 53.0428644);
    file_put_contents("area_covered.osm", $osm->getXml());


### Search for a specific POI, in a saved OSM file
    $osm = new Services_OpenStreetMap();

    $osm->loadXml("./osm.osm");
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
    require_once 'Services/OpenStreetMap.php';

    $osm = new Services_OpenStreetMap();

    var_dump($osm->getNode(52245107));

    Getting specific changesets, ways etc follow the same pattern.


### Updating a way, or several.
    require_once 'Services/OpenStreetMap.php';

    // A password file, is a colon delimited file.
    // Eg. fred@example.com:yabbadabbado
    $config = array('passwordfile' => './credentials');
    $osm = new Services_OpenStreetMap($config);

    $changeset = $osm->createChangeset();
    $changeset->begin("These ways are lit");
    $ways = $osm->getWays($wayId, $way2Id);
    foreach ($ways as $way) {
        $way->setTag('highway', 'residential');
        $way->setTag('lit', 'yes');
        $changeset->add($way);
    }
    $changeset->commit();

### Creating a node.

    /*
     * If you are going to connect to the live API server to run a quick
     * test that adds new data, such as POIS, with test/imaginary values
     * please be responsible and delete them afterwards.
     */

    require_once 'Services/OpenStreetMap.php';

    $config = array(
        // A password file, is a colon delimited file.
        // Eg. fred@example.com:yabbadabbado
        'passwordfile' => './credentials',
        // The live API server is api.openstreetmap.org
        'server'       => 'http://api06.dev.openstreetmap.org/',
        );
    $osm = new Services_OpenStreetMap($config);

    $changeset = $osm->createChangeset();
    $changeset->begin("Added Acme Vets.");
    // The latitude and longitude values here are intentionally invalid, see
    // note above.
    $lat = 182.8638729;
    $lon = -188.1983611;
    $node = $osm->createNode($lat, $lon, array(
        'name' => 'Acme Vets',
        'building' => 'yes',
        'amenity' => 'vet')
    );
    $changeset->add($node);
    $changeset->commit();

### Working with user information.

The getUser() method retrieves information for the current user.

    $config = array(
        'user' => 'fred@example.com',
        'password' => 'w1lma4evah'
    );

    $osm = new Services_OpenStreetMap($config);
    $user = $osm->getUser();

    echo 'My OSM Mugshot is at ', $user->getImage(), "\n";

The getUserById() method retrieves information for the specified user.

    $osm = new Services_OpenStreetMap();
    $user = $osm->getUserById(1);

## What can Services_OpenStreetMap_OpeningHours parse?

* General syntax "Mo 08:00-24:00; Tu-Fr 00:00-24:00; Sa 00:00-22:00; Su 10:00-20:00"
* 24/7 always evaluates to true/open.
* a null value always equates to a null response.
* Sunrise/sunset, eg mo-su: sunrise-sunset
* Day Off, eg: "Tu off; Mo-Sa 10:00-20:00"
* Month Off, For example: "24/7; Aug off"
* Exceptions such as: "24/7; Aug 10:00-14:00" and "Mo-Sa 10:00-18:00; Jun 23 11:15-13:30"
* Multiple times specified for days: mo-fr 9:00-13:00, 14:00-17:30; sa 9:00-13:00

At the moment, sunrise-sunset is the only sunrise/sunset spec that's tested.
e.g. 14:00-sunset isn't tested, nor is sunrise-13:37 for that matter.

Also it only recognises English month names in the values but this is per spec specification cf http://wiki.openstreetmap.org/wiki/Opening_times
It doesn't currently support parsing Public Holidays or School Holidays.
