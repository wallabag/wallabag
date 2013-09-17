<?php
/**
 * Session management class
 *
 * http://www.developpez.net/forums/d51943/php/langage/sessions/
 * http://sebsauvage.net/wiki/doku.php?id=php:session
 * http://sebsauvage.net/wiki/doku.php?id=php:shaarli
 * 
 * Features:
 * - Everything is stored on server-side (we do not trust client-side data,
 *   such as cookie expiration)
 * - IP addresses are checked on each access to prevent session cookie hijacking
 *   (such as Firesheep)
 * - Session expires on user inactivity (Session expiration date is
 *   automatically updated everytime the user accesses a page.)
 * - A unique secret key is generated on server-side for this session
 *   (and never sent over the wire) which can be used to sign forms (HMAC)
 *   (See $_SESSION['uid'])
 * - Token management to prevent XSRF attacks
 * - Brute force protection with ban management
 *
 * TODOs
 * - Replace globals with variables in Session class
 *
 * How to use:
 * - http://tontof.net/kriss/php5/session
 */
class Session
{
    // Personnalize PHP session name
    public static $sessionName = '';
    // If the user does not access any page within this time,
    // his/her session is considered expired (3600 sec. = 1 hour)
    public static $inactivityTimeout = 3600;
    // If you get disconnected often or if your IP address changes often.
    // Let you disable session cookie hijacking protection
    public static $disableSessionProtection = false;
    // Ban IP after this many failures.
    public static $banAfter = 4;
    // Ban duration for IP address after login failures (in seconds).
    // (1800 sec. = 30 minutes)
    public static $banDuration = 1800;
    // File storage for failures and bans. If empty, no ban management.
    public static $banFile = '';

    /**
     * Initialize session
     */
    public static function init()
    {
        // Force cookie path (but do not change lifetime)
        $cookie = session_get_cookie_params();
        // Default cookie expiration and path.
        $cookiedir = '';
        if (dirname($_SERVER['SCRIPT_NAME'])!='/') {
            $cookiedir = dirname($_SERVER["SCRIPT_NAME"]).'/';
        }
        $ssl = false;
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $ssl = true;
        }
        session_set_cookie_params($cookie['lifetime'], $cookiedir, $_SERVER['HTTP_HOST'], $ssl);
        // Use cookies to store session.
        ini_set('session.use_cookies', 1);
        // Force cookies for session  (phpsessionID forbidden in URL)
        ini_set('session.use_only_cookies', 1);
        if (!session_id()) {
            // Prevent php to use sessionID in URL if cookies are disabled.
            ini_set('session.use_trans_sid', false);
            if (!empty(self::$sessionName)) {
                session_name(self::$sessionName);
            }
            session_start();
        }
    }

    /**
     * Returns the IP address
     * (Used to prevent session cookie hijacking.)
     *
     * @return string IP addresses
     */
    private static function _allIPs()
    {
        $ip = $_SERVER["REMOTE_ADDR"];
        $ip.= isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? '_'.$_SERVER['HTTP_X_FORWARDED_FOR'] : '';
        $ip.= isset($_SERVER['HTTP_CLIENT_IP']) ? '_'.$_SERVER['HTTP_CLIENT_IP'] : '';

        return $ip;
    }

    /**
     * Check that user/password is correct and then init some SESSION variables.
     *
     * @param string $login        Login reference
     * @param string $password     Password reference
     * @param string $loginTest    Login to compare with login reference
     * @param string $passwordTest Password to compare with password reference
     * @param array  $pValues      Array of variables to store in SESSION
     *
     * @return true|false          True if login and password are correct, false
     *                             otherwise
     */
    public static function login (
        $login,
        $password,
        $loginTest,
        $passwordTest,
        $pValues = array())
    {
        self::banInit();
        if (self::banCanLogin()) {
            if ($login === $loginTest && $password === $passwordTest) {
                self::banLoginOk();
                // Generate unique random number to sign forms (HMAC)
                $_SESSION['uid'] = sha1(uniqid('', true).'_'.mt_rand());
                $_SESSION['ip'] = self::_allIPs();
                $_SESSION['username'] = $login;
                // Set session expiration.
                $_SESSION['expires_on'] = time() + self::$inactivityTimeout;

                foreach ($pValues as $key => $value) {
                    $_SESSION[$key] = $value;
                }

                return true;
            }
            self::banLoginFailed();
        }

        return false;
    }

    /**
     * Unset SESSION variable to force logout
     */
    public static function logout()
    {
        unset($_SESSION['uid'], $_SESSION['ip'], $_SESSION['expires_on']);
    }

    /**
     * Make sure user is logged in.
     *
     * @return true|false True if user is logged in, false otherwise
     */
    public static function isLogged()
    {
        if (!isset ($_SESSION['uid'])
            || (self::$disableSessionProtection === false
                && $_SESSION['ip'] !== self::_allIPs())
            || time() >= $_SESSION['expires_on']) {
            self::logout();

            return false;
        }
        // User accessed a page : Update his/her session expiration date.
        $_SESSION['expires_on'] = time() + self::$inactivityTimeout;
        if (!empty($_SESSION['longlastingsession'])) {
                $_SESSION['expires_on'] += $_SESSION['longlastingsession'];
        }

        return true;
    }

    /**
     * Create a token, store it in SESSION and return it
     *
     * @param string $salt to prevent birthday attack
     *
     * @return string Token created
     */
    public static function getToken($salt = '')
    {
        if (!isset($_SESSION['tokens'])) {
            $_SESSION['tokens']=array();
        }
        // We generate a random string and store it on the server side.
        $rnd = sha1(uniqid('', true).'_'.mt_rand().$salt);
        $_SESSION['tokens'][$rnd]=1;

        return $rnd;
    }

    /**
     * Tells if a token is ok. Using this function will destroy the token.
     *
     * @param string $token Token to test
     *
     * @return true|false   True if token is correct, false otherwise
     */
    public static function isToken($token)
    {
        if (isset($_SESSION['tokens'][$token])) {
            unset($_SESSION['tokens'][$token]); // Token is used: destroy it.

            return true; // Token is ok.
        }

        return false; // Wrong token, or already used.
    }

    /**
     * Signal a failed login. Will ban the IP if too many failures:
     */
    public static function banLoginFailed()
    {
        if (self::$banFile !== '') {
            $ip = $_SERVER["REMOTE_ADDR"];
            $gb = $GLOBALS['IPBANS'];

            if (!isset($gb['FAILURES'][$ip])) {
                $gb['FAILURES'][$ip] = 0;
            }
            $gb['FAILURES'][$ip]++;
            if ($gb['FAILURES'][$ip] > (self::$banAfter - 1)) {
                $gb['BANS'][$ip]= time() + self::$banDuration;
            }

            $GLOBALS['IPBANS'] = $gb;
            file_put_contents(self::$banFile, "<?php\n\$GLOBALS['IPBANS']=".var_export($gb, true).";\n?>");
        }
    }

    /**
     * Signals a successful login. Resets failed login counter.
     */
    public static function banLoginOk()
    {
        if (self::$banFile !== '') {
            $ip = $_SERVER["REMOTE_ADDR"];
            $gb = $GLOBALS['IPBANS'];
            unset($gb['FAILURES'][$ip]); unset($gb['BANS'][$ip]);
            $GLOBALS['IPBANS'] = $gb;
            file_put_contents(self::$banFile, "<?php\n\$GLOBALS['IPBANS']=".var_export($gb, true).";\n?>");
        }
    }

    /**
     * Ban init
     */
    public static function banInit()
    {
        if (self::$banFile !== '') {
            if (!is_file(self::$banFile)) {
                file_put_contents(self::$banFile, "<?php\n\$GLOBALS['IPBANS']=".var_export(array('FAILURES'=>array(), 'BANS'=>array()), true).";\n?>");
            }
            include self::$banFile;
        }
    }

    /**
     * Checks if the user CAN login. If 'true', the user can try to login.
     *
     * @return boolean true if user is banned, false otherwise
     */
    public static function banCanLogin()
    {
        if (self::$banFile !== '') {
            $ip = $_SERVER["REMOTE_ADDR"];
            $gb = $GLOBALS['IPBANS'];
            if (isset($gb['BANS'][$ip])) {
                // User is banned. Check if the ban has expired:
                if ($gb['BANS'][$ip] <= time()) {
                    // Ban expired, user can try to login again.
                    unset($gb['FAILURES'][$ip]);
                    unset($gb['BANS'][$ip]);
                    file_put_contents(self::$banFile, "<?php\n\$GLOBALS['IPBANS']=".var_export($gb, true).";\n?>");

                    return true; // Ban has expired, user can login.
                }

                return false; // User is banned.
            }
        }

        return true; // User is not banned.
    }
}
