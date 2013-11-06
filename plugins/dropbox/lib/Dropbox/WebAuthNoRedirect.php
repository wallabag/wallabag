<?php
namespace Dropbox;

/**
 * OAuth 2 code-based authorization for apps that can't provide a redirect URI, typically
 * command-line example apps.
 *
 * Use {@link WebAuth::start()} and {@link WebAuth::getToken()} to guide your
 * user through the process of giving your app access to their Dropbox account.  At the end, you
 * will have an {@link AccessToken}, which you can pass to {@link Client} and start making
 * API calls.
 *
 * Example:
 *
 * <code>
 * use \Dropbox as dbx;
 * $appInfo = dbx\AppInfo::loadFromJsonFile(...);
 * $clientIdentifier = "my-app/1.0";
 * $webAuth = new dbx\WebAuthNoRedirect($appInfo, $clientIdentifier, ...);
 *
 * $authorizeUrl = $webAuth->start();
 *
 * print("1. Go to: $authorizeUrl\n");
 * print("2. Click "Allow" (you might have to log in first).\n");
 * print("3. Copy the authorization code.\n");
 * $code = \trim(\readline("4. Enter the authorization code here: "));
 *
 * try {
 *    list($accessToken, $userId) = $webAuth->finish($code);
 * }
 * catch (dbx\Exception $ex) {
 *    print("Error communicating with Dropbox API: " . $ex->getMessage() . "\n");
 * }
 *
 * $client = dbx\Client($accessToken, $clientIdentifier, ...);
 * </code>
 */
class WebAuthNoRedirect extends WebAuthBase
{
    /**
     * Returns the URL of the authorization page the user must visit.  If the user approves
     * your app, they will be shown the authorization code on the web page.  They will need to
     * copy/paste that code into your application so your app can pass it to
     * {@link finish}.
     *
     * @return string
     *    An authorization URL.  Redirect the user's browser to this URL.  After the user decides
     *    whether to authorize your app or not, Dropbox will show the user an authorization code,
     *    which the user will need to give to your application (e.g. via copy/paste).
     */
    function start()
    {
        return $this->_getAuthorizeUrl(null, null);
    }

    /**
     * Call this after the user has visited the authorize URL returned by {@link start()},
     * approved your app, was presented with an authorization code by Dropbox, and has copy/paste'd
     * that authorization code into your app.
     *
     * @param string $code
     *    The authorization code provided to the user by Dropbox.
     *
     * @return array
     *    A <code>list(string $accessToken, string $userId)</code>, where
     *    <code>$accessToken</code> can be used to construct a {@link Client} and
     *    <code>$userId</code> is the user ID of the user's Dropbox account.
     *
     * @throws Exception
     *    Thrown if there's an error getting the access token from Dropbox.
     */
    function finish($code)
    {
        Checker::argStringNonEmpty("code", $code);
        return $this->_finish($code, null);
    }
}

