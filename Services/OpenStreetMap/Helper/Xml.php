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

class Services_OpenStreetMap_Helper_Xml
{
    /**
     * Given SimpleXMLElement, retrieve tag value.
     *
     * @param SimpleXMLElement $xml       Object
     * @param string           $tag       name of tag
     * @param string           $attribute name of attribute
     * @param mixed            $default   default value, optional
     *
     * @return string
     */
    public function getValue(
        SimpleXMLElement $xml,
        string $tag,
        string $attribute,
        $default = null
    ):?string {
        $obj = $xml->xpath('//' . $tag);
        if (empty($obj)) {
            return $default;
        }
        return $obj[0]->attributes()->$attribute;
    }
}
