<?php
namespace Dropbox;

/**
 * Helper functions for security-related things.
 */
class Security
{
    /**
     * A string equality function that compares strings in a way that isn't suceptible to timing
     * attacks.  An attacker can figure out the length of the string, but not the string's value.
     *
     * Use this when comparing two strings where:
     * - one string could be influenced by an attacker
     * - the other string contains data an attacker shouldn't know
     *
     * @param string $a
     * @param string $b
     * @return bool
     */
    static function stringEquals($a, $b)
    {
        // Be strict with arguments.  PHP's liberal types could get us pwned.
        if (func_num_args() !== 2) {
            throw \InvalidArgumentException("Expecting 2 args, got ".func_num_args().".");
        }
        Checker::argString("a", $a);
        Checker::argString("b", $b);

        if (strlen($a) !== strlen($b)) return false;
        $result = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $result |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $result === 0;
    }

    /**
     * Returns cryptographically strong secure random bytes (as a PHP string).
     *
     * @param int $numBytes
     *    The number of bytes of random data to return.
     *
     * @return string
     */
    static function getRandomBytes($numBytes)
    {
        Checker::argIntPositive("numBytes", $numBytes);

        // openssl_random_pseudo_bytes had some issues prior to PHP 5.3.4 
        if (function_exists('openssl_random_pseudo_bytes')
                && version_compare(PHP_VERSION, '5.3.4') >= 0) {
            $s = openssl_random_pseudo_bytes($numBytes, $isCryptoStrong);
            if ($isCryptoStrong) return $s;
        }

        if (function_exists('mcrypt_create_iv')) {
            return mcrypt_create_iv($numBytes);
        }

        // Hopefully the above two options cover all our users.  But if not, there are
        // other platform-specific options we could add.
        assert(False, "no suitable random number source available");
    }
}
