<?php
namespace Dropbox;

/**
 * OAuth 2 "authorization code" flow.  (This SDK does not support the "token" flow.)
 *
 * Use {@link WebAuth::start()} and {@link WebAuth::finish()} to guide your
 * user through the process of giving your app access to their Dropbox account.
 * At the end, you will have an access token, which you can pass to {@link Client}
 * and start making API calls.
 *
 * Example:
 *
 * <code>
 * use \Dropbox as dbx;
 *
 * function getWebAuth()
 * {
 *    $appInfo = dbx\AppInfo::loadFromJsonFile(...);
 *    $clientIdentifier = "my-app/1.0";
 *    $redirectUri = "https://example.org/dropbox-auth-finish";
 *    $csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
 *    return new dbx\WebAuth($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore, ...);
 * }
 *
 * // ----------------------------------------------------------
 * // In the URL handler for "/dropbox-auth-start"
 *
 * $authorizeUrl = getWebAuth()->start();
 * header("Location: $authorizeUrl");
 *
 * // ----------------------------------------------------------
 * // In the URL handler for "/dropbox-auth-finish"
 *
 * try {
 *    list($accessToken, $userId, $urlState) = getWebAuth()->finish($_GET);
 *    assert($urlState === null);  // Since we didn't pass anything in start()
 * }
 * catch (dbx\WebAuthException_BadRequest $ex) {
 *    error_log("/dropbox-auth-finish: bad request: " . $ex->getMessage());
 *    // Respond with an HTTP 400 and display error page...
 * }
 * catch (dbx\WebAuthException_BadState $ex) {
 *    // Auth session expired.  Restart the auth process.
 *    header('Location: /dropbox-auth-start');
 * }
 * catch (dbx\WebAuthException_Csrf $ex) {
 *    error_log("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
 *    // Respond with HTTP 403 and display error page...
 * }
 * catch (dbx\WebAuthException_NotApproved $ex) {
 *    error_log("/dropbox-auth-finish: not approved: " . $ex->getMessage());
 * }
 * catch (dbx\WebAuthException_Provider $ex) {
 *    error_log("/dropbox-auth-finish: error redirect from Dropbox: " . $ex->getMessage());
 * }
 * catch (dbx\Exception $ex) {
 *    error_log("/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage());
 * }
 *
 * // We can now use $accessToken to make API requests.
 * $client = dbx\Client($accessToken, ...);
 * </code>
 *
 */
class WebAuth extends WebAuthBase
{
    /**
     * The URI that the Dropbox server will redirect the user to after the user finishes
     * authorizing your app.  This URI must be HTTPS-based and
     * <a href="https://www.dropbox.com/developers/apps">pre-registered with Dropbox</a>,
     * though "localhost"-based and "127.0.0.1"-based URIs are allowed without pre-registration
     * and can be either HTTP or HTTPS.
     *
     * @return string
     */
    function getRedirectUri() { return $this->redirectUri; }

    /** @var string */
    private $redirectUri;

    /**
     * A object that lets us save CSRF token string to the user's session.  If you're using the
     * standard PHP <code>$_SESSION</code>, you can pass in something like
     * <code>new ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token')</code>.
     *
     * If you're not using $_SESSION, you might have to create your own class that provides
     * the same <code>get()</code>/<code>set()</code>/<code>clear()</code> methods as
     * {@link ArrayEntryStore}.
     *
     * @return ValueStore
     */
    function getCsrfTokenStore() { return $this->csrfTokenStore; }

    /** @var object */
    private $csrfTokenStore;

    /**
     * Constructor.
     *
     * @param AppInfo $appInfo
     *     See {@link getAppInfo()}
     * @param string $clientIdentifier
     *     See {@link getClientIdentifier()}
     * @param null|string $redirectUri
     *     See {@link getRedirectUri()}
     * @param null|ValueStore $csrfTokenStore
     *     See {@link getCsrfTokenStore()}
     * @param null|string $userLocale
     *     See {@link getUserLocale()}
     */
    function __construct($appInfo, $clientIdentifier, $redirectUri, $csrfTokenStore, $userLocale = null)
    {
        parent::__construct($appInfo, $clientIdentifier, $userLocale);

        Checker::argStringNonEmpty("redirectUri", $redirectUri);

        $this->csrfTokenStore = $csrfTokenStore;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Starts the OAuth 2 authorization process, which involves redirecting the user to the
     * returned authorization URL (a URL on the Dropbox website).  When the user then
     * either approves or denies your app access, Dropbox will redirect them to the
     * <code>$redirectUri</code> given to constructor, at which point you should
     * call {@link finish()} to complete the authorization process.
     *
     * This function will also save a CSRF token using the <code>$csrfTokenStore</code> given to
     * the constructor.  This CSRF token will be checked on {@link finish()} to prevent
     * request forgery.
     *
     * @param string|null $urlState
     *    Any data you would like to keep in the URL through the authorization process.
     *    This exact state will be returned to you by {@link finish()}.
     *
     * @return array
     *    The URL to redirect the user to.
     *
     * @throws Exception
     */
    function start($urlState = null)
    {
        Checker::argStringOrNull("urlState", $urlState);

        $csrfToken = self::encodeCsrfToken(Security::getRandomBytes(16));
        $state = $csrfToken;
        if ($urlState !== null) {
            $state .= "|";
            $state .= $urlState;
        }
        $this->csrfTokenStore->set($csrfToken);

        return $this->_getAuthorizeUrl($this->redirectUri, $state);
    }

    private static function encodeCsrfToken($string)
    {
        return strtr(base64_encode($string), '+/', '-_');
    }

    private static function decodeCsrfToken($string)
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }

    /**
     * Call this after the user has visited the authorize URL ({@link start()}), approved your app,
     * and was redirected to your redirect URI.
     *
     * @param array $queryParams
     *    The query parameters on the GET request to your redirect URI.
     *
     * @return array
     *    A <code>list(string $accessToken, string $userId, string $urlState)</code>, where
     *    <code>$accessToken</code> can be used to construct a {@link Client}, <code>$userId</code>
     *    is the user ID of the user's Dropbox account, and <code>$urlState</code> is the
     *    value you originally passed in to {@link start()}.
     *
     * @throws Exception
     *    Thrown if there's an error getting the access token from Dropbox.
     * @throws WebAuthException_BadRequest
     * @throws WebAuthException_BadState
     * @throws WebAuthException_Csrf
     * @throws WebAuthException_NotApproved
     * @throws WebAuthException_Provider
     */
    function finish($queryParams)
    {
        Checker::argArray("queryParams", $queryParams);

        $csrfTokenFromSession = $this->csrfTokenStore->get();
        Checker::argStringOrNull("this->csrfTokenStore->get()", $csrfTokenFromSession);

        // Check well-formedness of request.

        if (!isset($queryParams['state'])) {
            throw new WebAuthException_BadRequest("Missing query parameter 'state'.");
        }
        $state = $queryParams['state'];
        Checker::argString("queryParams['state']", $state);

        $error = null;
        if (isset($queryParams['error'])) {
            $error = $queryParams['error'];
            Checker::argString("queryParams['error']", $error);
            $errorDescription = null;
            if (isset($queryParams['error_description'])) {
                $errorDescription = $queryParams['error_description'];
                Checker::argString("queryParams['error_description']", $errorDescription);
            }
        }

        $code = null;
        if (isset($queryParams['code'])) {
            $code = $queryParams['code'];
            Checker::argString("queryParams['code']", $code);
        }

        if ($code !== null && $error !== null) {
            throw new WebAuthException_BadRequest("Query parameters 'code' and 'error' are both set;".
                                                 " only one must be set.");
        }
        if ($code === null && $error === null) {
            throw new WebAuthException_BadRequest("Neither query parameter 'code' or 'error' is set.");
        }

        // Check CSRF token

        if ($csrfTokenFromSession === null) {
            throw new WebAuthException_BadState();
        }
        // Sanity check to make sure something hasn't gone terribly wrong.
        assert(strlen($csrfTokenFromSession) > 20);

        $splitPos = strpos($state, "|");
        if ($splitPos === false) {
            $givenCsrfToken = $state;
            $urlState = null;
        } else {
            $givenCsrfToken = substr($state, 0, $splitPos);
            $urlState = substr($state, $splitPos + 1);
        }
        if (!Security::stringEquals($csrfTokenFromSession, $givenCsrfToken)) {
            throw new WebAuthException_Csrf("Expected ".Client::q($csrfTokenFromSession).
                                           ", got ".Client::q($givenCsrfToken).".");
        }
        $this->csrfTokenStore->clear();

        // Check for error identifier

        if ($error !== null) {
            if ($error === 'access_denied') {
                // When the user clicks "Deny".
                if ($errorDescription === null) {
                    throw new WebAuthException_NotApproved("No additional description from Dropbox.");
                } else {
                    throw new WebAuthException_NotApproved("Additional description from Dropbox: $errorDescription");
                }
            } else {
                // All other errors.
                $fullMessage = $error;
                if ($errorDescription !== null) {
                    $fullMessage .= ": ";
                    $fullMessage .= $errorDescription;
                }
                throw new WebAuthException_Provider($fullMessage);
            }
        }

        // If everything went ok, make the network call to get an access token.

        list($accessToken, $userId) = $this->_finish($code, $this->redirectUri);
        return array($accessToken, $userId, $urlState);
    }
}
