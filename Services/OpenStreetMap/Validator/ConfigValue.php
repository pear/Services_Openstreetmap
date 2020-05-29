<?php
/**
 * ConfigValue.php
 * 29-May-2020
 *
 * PHP Version 7
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     ConfigValue.php
 */

/**
 * Services_OpenStreetMap_Validator_ConfigValue
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     ConfigValue.php
 */
class Services_OpenStreetMap_Validator_ConfigValue
{
    /**
     * __construct
     *
     * @param string $value  Possible valid config key
     * @param array  $config Array of valid config keys
     *
     * @return void
     */
    public function __construct($value = '', array $config = [])
    {
        if ($value === '') {
            return;
        }
        $this->validate($value, $config);

    }

    /**
     * Validate potential config key
     *
     * @param string $value  Possible valid config key
     * @param array  $config Array of valid config keys
     *
     * @return void
     */
    public function validate(string $value, array $config): void
    {
        if (!array_key_exists($value, $config)) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                "Unknown config parameter '$value'"
            );
        }
    }
}
