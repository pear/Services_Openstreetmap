<?php
/**
 * Paswordfile.php
 * 26-May-2020
 *
 * PHP Version 5
 *
 * @category Paswordfile
 * @package  Paswordfile
 * @author   Ken Guest <ken@linux.ie>
 * @license  GPL (see http://www.gnu.org/licenses/gpl.txt)
 * @version  CVS: <cvs_id>
 * @link     Paswordfile.php
 * @todo
 */

class Services_OpenStreetMap_Passwordfile
{
    public $user = null;
    public $passwordfile = null;

    /**
     * __construct
     *
     * @param string $file Filename
     * @param string $user Username to search for associated password
     *
     * @return void
     */
    public function __construct($file = '', $user = null)
    {
        if ($file === '') {
            return $this;
        }
        if ($file === null) {
            return $this;
        }
        $this->user = $user;
        if (!file_exists($file)) {
            throw new Services_OpenStreetMap_Exception(
                'Could not read password file'
            );
        }
        $lines = @file($file);
        if ($lines === false) {
            throw new Services_OpenStreetMap_Exception(
                'Could not read password file'
            );
        }
        $lines = array_map('trim', $lines);

        if (count($lines) === 1 && strpos($lines[0], '#') !== 0) {
            list($this->user, $this->password) = explode(':', $lines[0]);
        } elseif (count($lines) === 2) {
            list($user, $password) = $this->userPasswordFromTwolines($lines);
            $this->password = $password;
            if ($user !== null) {
                $this->user = $user;
            }
        } else {
            list($user, $password) = $this->userPasswordFromLines($lines);
            $this->password = $password;
        }
        return $this;
    }

    /**
     * Get user
     *
     * @return string|null Username
     */
    public function getUser():? string
    {
        return $this->user;
    }

    /**
     * Get password
     *
     * @return string|null Password
     */
    public function getPassword():? string
    {
        return $this->password;
    }

    /**
     * Extract username and password from two line/array password file.
     *
     * @param array $lines Passwordfile content
     *
     * @return array Array of username and password
     */
    public function userPasswordFromTwolines($lines)
    {
        $rUser = null;
        $rPassword = null;
        if ((strpos($lines[0], '#') === 0) && (strpos($lines[1], '#') !== 0)) {
            list($rUser, $rPassword) = explode(':', $lines[1]);
        }
        return [$rUser, $rPassword];
    }

    /**
     * Extract username and password from contents read from password file.
     *
     * @param array $lines Array of lines from reading password file
     *
     * @return array Array of username and password
     */
    public function userPasswordFromLines($lines)
    {
        $rUser = null;
        $rPassword = null;
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }
            list($user, $password) = explode(':', $line);
            if ($user == $this->user) {
                $rPassword = $password;
                $rUser = $user;
            }
        }
        return [$rUser, $rPassword];
    }

}
