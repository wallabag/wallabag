<?php
/**
 * Cookie Jar
 * 
 * PHP class for handling cookies, as defined by the Netscape spec: 
 * <http://curl.haxx.se/rfc/cookie_spec.html>
 *
 * This class should be used to handle cookies (storing cookies from HTTP response messages, and
 * sending out cookies in HTTP request messages). This has been adapted for FiveFilters.org 
 * from the original version used in HTTP Navigator. See http://www.keyvan.net/code/http-navigator/
 * 
 * This class is mainly based on Cookies.pm <http://search.cpan.org/author/GAAS/libwww-perl-5.65/
 * lib/HTTP/Cookies.pm> from the libwww-perl collection <http://www.linpro.no/lwp/>.
 * Unlike Cookies.pm, this class only supports the Netscape cookie spec, not RFC 2965.
 * 
 * @version 0.5
 * @date 2011-03-15
 * @see http://php.net/HttpRequestPool
 * @author Keyvan Minoukadeh
 * @copyright 2011 Keyvan Minoukadeh
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPL v3
 */

class CookieJar
{
    /**
    * Cookies - array containing all cookies.
    *
    * <pre>
    * Cookies are stored like this:
    *   [domain][path][name] = array
    * where array is:
    *   0 => value, 1 => secure, 2 => expires
    * </pre>
    * @var array
    * @access private
    */
    public $cookies = array();
	public $debug = false;

    /**
    * Constructor
    */
    function __construct() {
    }

	protected function debug($msg, $file=null, $line=null) {
		if ($this->debug) {
			$mem = round(memory_get_usage()/1024, 2);
			$memPeak = round(memory_get_peak_usage()/1024, 2);
			echo '* ',$msg;
			if (isset($file, $line)) echo " ($file line $line)";
			echo ' - mem used: ',$mem," (peak: $memPeak)\n";	
			ob_flush();
			flush();
		}
	}	
	
    /**
    * Get matching cookies
    *
    * Only use this method if you cannot use add_cookie_header(), for example, if you want to use
    * this cookie jar class without using the request class.
    *
    * @param array $param associative array containing 'domain', 'path', 'secure' keys
    * @return string
    * @see add_cookie_header()
    */
    public function getMatchingCookies($url)
    {
		if (($parts = @parse_url($url)) && isset($parts['scheme'], $parts['host'], $parts['path'])) {
			$param['domain'] = $parts['host'];
			$param['path'] = $parts['path'];
			$param['secure'] = (strtolower($parts['scheme']) == 'https');
			unset($parts);
		} else {
			return false;
		}
        // RFC 2965 notes:
        //  If multiple cookies satisfy the criteria above, they are ordered in
        //  the Cookie header such that those with more specific Path attributes
        //  precede those with less specific.  Ordering with respect to other
        //  attributes (e.g., Domain) is unspecified.
        $domain = $param['domain'];
        if (strpos($domain, '.') === false) $domain .= '.local';
        $request_path = $param['path'];
        if ($request_path == '') $request_path = '/';
        $request_secure = $param['secure'];
        $now = time();
        $matched_cookies = array();
        // domain - find matching domains
        $this->debug('Finding matching domains for '.$domain, __FILE__, __LINE__);
        while (strpos($domain, '.') !== false) {
            if (isset($this->cookies[$domain])) {
                $this->debug(' domain match found: '.$domain);
                $cookies =& $this->cookies[$domain];
            } else {
                $domain = $this->_reduce_domain($domain);
                continue;
            }
            // paths - find matching paths starting from most specific
            $this->debug('  - Finding matching paths for '.$request_path);
            $paths = array_keys($cookies);
            usort($paths, array($this, '_cmp_length'));
            foreach ($paths as $path) {
                // continue to next cookie if request path does not path-match cookie path
                if (!$this->_path_match($request_path, $path)) continue;
                // loop through cookie names
                $this->debug('     path match found: '.$path);
                foreach ($cookies[$path] as $name => $values) {
                    // if this cookie is secure but request isn't, continue to next cookie
                    if ($values[1] && !$request_secure) continue;
                    // if cookie is not a session cookie and has expired, continue to next cookie
                    if (is_int($values[2]) && ($values[2] < $now)) continue;
                    // cookie matches request
                    $this->debug('      cookie match: '.$name.'='.$values[0]);
                    $matched_cookies[] = $name.'='.$values[0];
                }
            }
            $domain = $this->_reduce_domain($domain);
        }
        // return cookies
        return implode('; ', $matched_cookies);
    }

    /**
    * Parse Set-Cookie values.
    *
    * Only use this method if you cannot use extract_cookies(), for example, if you want to use
    * this cookie jar class without using the response class.
    *
    * @param array $set_cookies array holding 1 or more "Set-Cookie" header values
    * @param array $param associative array containing 'host', 'path' keys
    * @return void
    * @see extract_cookies()
    */
    public function storeCookies($url, $set_cookies)
    {
        if (count($set_cookies) == 0) return;
		$param = @parse_url($url);
		if (!is_array($param) || !isset($param['host'])) return;
        $request_host = $param['host'];
        if (strpos($request_host, '.') === false) $request_host .= '.local';
        $request_path = @$param['path'];
        if ($request_path == '') $request_path = '/';
        //
        // loop through set-cookie headers
        //
        foreach ($set_cookies as $set_cookie) {
            $this->debug('Parsing: '.$set_cookie);
            // temporary cookie store (before adding to jar)
            $tmp_cookie = array();
            $param = explode(';', $set_cookie);
            // loop through params
            for ($x=0; $x<count($param); $x++) {
                $key_val = explode('=', $param[$x], 2);
                if (count($key_val) != 2) {
                    // if the first param isn't a name=value pair, continue to the next set-cookie
                    // header
                    if ($x == 0) continue 2;
                    // check for secure flag
                    if (strtolower(trim($key_val[0])) == 'secure') $tmp_cookie['secure'] = true;
                    // continue to next param
                    continue;
                }
                list($key, $val) = array_map('trim', $key_val);
                // first name=value pair is the cookie name and value
                // the name and value are stored under 'name' and 'value' to avoid conflicts
                // with later parameters.
                if ($x == 0) {
                    $tmp_cookie = array('name'=>$key, 'value'=>$val);
                    continue;
                }
                $key = strtolower($key);
                if (in_array($key, array('expires', 'path', 'domain', 'secure'))) {
                    $tmp_cookie[$key] = $val;
                }
            }
            //
            // set cookie
            //
            // check domain
            if (isset($tmp_cookie['domain']) && ($tmp_cookie['domain'] != $request_host) &&
                    ($tmp_cookie['domain'] != ".$request_host")) {
                $domain = $tmp_cookie['domain'];
                if ((strpos($domain, '.') === false) && ($domain != 'local')) {
                    $this->debug(' - domain "'.$domain.'" has no dot and is not a local domain');
                    continue;
                }
                if (preg_match('/\.[0-9]+$/', $domain)) {
                    $this->debug(' - domain "'.$domain.'" appears to be an ip address');
                    continue;
                }
                if (substr($domain, 0, 1) != '.') $domain = ".$domain";
                if (!$this->_domain_match($request_host, $domain)) {
                    $this->debug(' - request host "'.$request_host.'" does not domain-match "'.$domain.'"');
                    continue;
                }
            } else {
                // if domain is not specified in the set-cookie header, domain will default to
                // the request host
                $domain = $request_host;
            }
            // check path
            if (isset($tmp_cookie['path']) && ($tmp_cookie['path'] != '')) {
                $path = urldecode($tmp_cookie['path']);
                if (!$this->_path_match($request_path, $path)) {
                    $this->debug(' - request path "'.$request_path.'" does not path-match "'.$path.'"');
                    continue;
                }
            } else {
                $path = $request_path;
                $path = substr($path, 0, strrpos($path, '/'));
                if ($path == '') $path = '/';
            }
            // check if secure
            $secure = (isset($tmp_cookie['secure'])) ? true : false;
            // check expiry
            if (isset($tmp_cookie['expires'])) {
                if (($expires = strtotime($tmp_cookie['expires'])) < 0) {
                    $expires = null;
                }
            } else {
                $expires = null;
            }
            // set cookie
            $this->set_cookie($domain, $path, $tmp_cookie['name'], $tmp_cookie['value'], $secure, $expires);
        }
    }
	
	// return array of set-cookie values extracted from HTTP response headers (string $h)
	public function extractCookies($h) {
        $x = 0;
        $lines = 0;
        $headers = array();
        $last_match = false;
		$h = explode("\n", $h);
        foreach ($h as $line) {
			$line = rtrim($line);
            $lines++;

            $trimmed_line = trim($line);
            if (isset($line_last)) {
                // check if we have \r\n\r\n (indicating the end of headers)
                // some servers will not use CRLF (\r\n), so we make CR (\r) optional.
                // if (preg_match('/\015?\012\015?\012/', $line_last.$line)) {
                //     break;
                // }
                // As an alternative, we can check if the current trimmed line is empty
                if ($trimmed_line == '') {
                    break;
                }

                // check for continuation line...
                // RFC 2616 Section 2.2 "Basic Rules":
                // HTTP/1.1 header field values can be folded onto multiple lines if the
                // continuation line begins with a space or horizontal tab. All linear
                // white space, including folding, has the same semantics as SP. A
                // recipient MAY replace any linear white space with a single SP before
                // interpreting the field value or forwarding the message downstream.
                if ($last_match && preg_match('/^\s+(.*)/', $line, $match)) {
                    // append to previous header value
                    $headers[$x-1] .= ' '.rtrim($match[1]);
                    continue;
                }
            }
            $line_last = $line;

            // split header name and value
            if (preg_match('/^Set-Cookie\s*:\s*(.*)/i', $line, $match)) {
                $headers[$x++] = rtrim($match[1]);
                $last_match = true;
            } else {
                $last_match = false;
            }
        }
        return $headers;
	}

    /**
    * Set Cookie
    * @param string $domain
    * @param string $path
    * @param string $name cookie name
    * @param string $value cookie value
    * @param bool $secure
    * @param int $expires expiry time (null if session cookie, <= 0 will delete cookie)
    * @return void
    */
    function set_cookie($domain, $path, $name, $value, $secure=false, $expires=null)
    {
        if ($domain == '') return;
        if ($path == '') return;
        if ($name == '') return;
        // check if cookie needs to go
        if (isset($expires) && ($expires <= 0)) {
            if (isset($this->cookies[$domain][$path][$name])) unset($this->cookies[$domain][$path][$name]);
            return;
        }
        if ($value == '') return;
        $this->cookies[$domain][$path][$name] = array($value, $secure, $expires);
        return;
    }

    /**
    * Clear cookies - [domain [,path [,name]]] - call method with no arguments to clear all cookies.
    * @param string $domain
    * @param string $path
    * @param string $name
    * @return void
    */
    function clear($domain=null, $path=null, $name=null)
    {
        if (!isset($domain)) {
            $this->cookies = array();
        } elseif (!isset($path)) {
            if (isset($this->cookies[$domain])) unset($this->cookies[$domain]);
        } elseif (!isset($name)) {
            if (isset($this->cookies[$domain][$path])) unset($this->cookies[$domain][$path]);
        } elseif (isset($name)) {
            if (isset($this->cookies[$domain][$path][$name])) unset($this->cookies[$domain][$path][$name]);
        }
    }

    /**
    * Compare string length - used for sorting
    * @access private
    * @return int
    */
    function _cmp_length($a, $b)
    {
        $la = strlen($a); $lb = strlen($b);
        if ($la == $lb) return 0;
        return ($la > $lb) ? -1 : 1;
    }

    /**
    * Reduce domain
    * @param string $domain
    * @return string
    * @access private
    */
    function _reduce_domain($domain)
    {
        if ($domain == '') return '';
        if (substr($domain, 0, 1) == '.') return substr($domain, 1);
        return substr($domain, strpos($domain, '.'));
    }

    /**
    * Path match - check if path1 path-matches path2
    *
    * From RFC 2965: 
    *   <i>For two strings that represent paths, P1 and P2, P1 path-matches P2
    *   if P2 is a prefix of P1 (including the case where P1 and P2 string-
    *   compare equal).  Thus, the string /tec/waldo path-matches /tec.</i>
    * @param string $path1
    * @param string $path2
    * @return bool
    * @access private
    */
    function _path_match($path1, $path2)
    {
        return (substr($path1, 0, strlen($path2)) == $path2);
    }

    /**
    * Domain match - check if domain1 domain-matches domain2
    *
    * A few extracts from RFC 2965: 
    *  -  A Set-Cookie2 from request-host y.x.foo.com for Domain=.foo.com
    *     would be rejected, because H is y.x and contains a dot.
    *
    *  -  A Set-Cookie2 from request-host x.foo.com for Domain=.foo.com
    *     would be accepted.
    *
    *  -  A Set-Cookie2 with Domain=.com or Domain=.com., will always be
    *     rejected, because there is no embedded dot.
    *
    *  -  A Set-Cookie2 from request-host example for Domain=.local will
    *     be accepted, because the effective host name for the request-
    *     host is example.local, and example.local domain-matches .local.
    *
    * I'm ignoring the first point for now (must check to see how other browsers handle
    * this rule for Set-Cookie headers)
    *
    * @param string $domain1
    * @param string $domain2
    * @return bool
    * @access private
    */
    function _domain_match($domain1, $domain2)
    {
        $domain1 = strtolower($domain1);
        $domain2 = strtolower($domain2);
        while (strpos($domain1, '.') !== false) {
            if ($domain1 == $domain2) return true;
            $domain1 = $this->_reduce_domain($domain1);
            continue;
        }
        return false;
    }
}
?>