<?php
/**
 * OAuthHelper.php
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     OAuthHelper.php
 */

/**
 * Services_OpenStreetMap_OAuthHelper
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Valery Khvalov <khvalov@tut.by>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     OAuthHelper.php
 */
class Services_OpenStreetMap_OAuthHelper
{
    /**
     * Return time-stamp for oauth use
     *
     * @return int
     */
    public static function getOauthTimestamp(): int
    {
        return time();
    }

    /**
     * Return an oauth nonce
     *
     * @return string
     */
    public static function getOauthNonce(): string
    {
        return md5(uniqid('', true));
    }

    /**
     * Generate a HMAC SHA1 signature for specified key and value/data.
     *
     * @param string $key  Key
     * @param mixed  $data Value/Data
     *
     * @return string
     */
    private static function _hmacSha1(string $key, $data): string
    {
        return base64_encode(hash_hmac('sha1', $data, $key, true));
    }

    /**
     * Return a raw url encoded HMAC SHA1 signature for specified key and value/data.
     *
     * @param string $key  Key
     * @param mixed  $data Value/Data
     *
     * @return string
     */
    public static function getOauthSignature(string $key, $data): string
    {
        return rawurlencode(self::_hmacSha1($key, $data));
    }

    /**
     * Convert associative array to a string
     *
     * @param array  $arr  Array to convert
     * @param string $glue Glue character
     * @param string $sep  Value separator
     * @param string $wrap What to wrap the value with
     *
     * @return string|bool
     */
    public static function assocArrayToString(
        array $arr,
        string $glue = '=',
        string $sep = '&',
        string $wrap = ''
    ) {
        $str = '';
        $i = 0;
        if (is_array($arr)) {
            $count = count($arr);
            foreach ($arr as $key => $value) {
                ++$i;
                $str .= $key . $glue . $wrap . $value . $wrap;
                if ($i < $count) {
                    $str .= $sep;
                }
            }
            return $str;
        }
        return false;
    }
}
