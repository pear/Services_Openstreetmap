<?php
/**
 * Chaangeset.php
 * 30-May-2020
 *
 * PHP Version 5
 *
 * @category Chaangeset
 * @package  Chaangeset
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Chaangeset.php
 */

/**
 * Services_OpenStreetMap_Validator_Changeset
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Changeset.php
 */
class Services_OpenStreetMap_Validator_Changeset
{
    /**
     * Is value a valid Changeset Id.
     *
     * @param mixed $value Possible changeset id
     *
     * @return void
     */
    public function validate($value)
    {
        if ((!is_numeric($value)) && ($value !== null)) {
            $msg = 'Changeset ID of unexpected type. (';
            $msg .= var_export($value, true) . ')';
            throw new Services_OpenStreetMap_RuntimeException($msg);
        }
    }

    /**
     * Validate changeset got posted ok
     *
     * @param mixed $responseCode HTTP Response code
     *
     * @return void
     */
    public function validateChangesetPostedOk($responseCode): void
    {
        if (Services_OpenStreetMap_Transport::OK != $responseCode) {
            throw new Services_OpenStreetMap_Exception(
                'Error posting changeset',
                $responseCode
            );
        }
    }

    /**
     * Validate that the changeset closed ok
     *
     * @param mixed $responseCode HTTP Response Code
     *
     * @return void
     */
    public function validateChangesetClosedOk($responseCode): void
    {
        if (Services_OpenStreetMap_Transport::OK != $responseCode) {
            throw new Services_OpenStreetMap_Exception(
                'Error closing changeset',
                $responseCode
            );
        }
    }
}
