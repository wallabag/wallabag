<?php
namespace Dropbox;

/**
 * The base class for the two auth options.
 */
class WebAuthBase
{
    /**
     * Whatever AppInfo was passed into the constructor.
     *
     * @return AppInfo
     */
    function getAppInfo() { return $this->appInfo; }

    /** @var AppInfo */
    private $appInfo;

    /**
     * An identifier for the API client, typically of the form "Name/Version".
     * This is used to set the HTTP <code>User-Agent</code> header when making API requests.
     * Example: <code>"PhotoEditServer/1.3"</code>
     *
     * If you're the author a higher-level library on top of the basic SDK, and the
     * "Photo Edit" app's server code is using your library to access Dropbox, you should append
     * your library's name and version to form the full identifier.  For example,
     * if your library is called "File Picker", you might set this field to:
     * <code>"PhotoEditServer/1.3 FilePicker/0.1-beta"</code>
     *
     * The exact format of the <code>User-Agent</code> header is described in
     * <a href="http://tools.ietf.org/html/rfc2616#section-3.8">section 3.8 of the HTTP specification</a>.
     *
     * Note that underlying HTTP client may append other things to the <code>User-Agent</code>, such as
     * the name of the library being used to actually make the HTTP request (such as cURL).
     *
     * @return string
     */
    function getClientIdentifier() { return $this->clientIdentifier; }

    /** @var string */
    private $clientIdentifier;

    /**
     * The locale of the user of your application.  Some API calls return localized
     * data and error messages; this "user locale" setting determines which locale
     * the server should use to localize those strings.
     *
     * @return null|string
     */
    function getUserLocale() { return $this->userLocale; }

    /** @var string */
    private $userLocale;

    /**
     * Constructor.
     *
     * @param AppInfo $appInfo
     *     See {@link getAppInfo()}
     * @param string $clientIdentifier
     *     See {@link getClientIdentifier()}
     * @param null|string $userLocale
     *     See {@link getUserLocale()}
     */
    function __construct($appInfo, $clientIdentifier, $userLocale = null)
    {
        AppInfo::checkArg("appInfo", $appInfo);
        Checker::argStringNonEmpty("clientIdentifier", $clientIdentifier);
        Checker::argStringNonEmptyOrNull("userLocale", $userLocale);

        $this->appInfo = $appInfo;
        $this->clientIdentifier = $clientIdentifier;
        $this->userLocale = $userLocale;
    }

    protected function _getAuthorizeUrl($redirectUri, $state)
    {
        return RequestUtil::buildUrl(
            $this->userLocale,
            $this->appInfo->getHost()->getWeb(),
            "1/oauth2/authorize",
            array(
                "client_id" => $this->appInfo->getKey(),
                "response_type" => "code",
                "redirect_uri" => $redirectUri,
                "state" => $state,
            ));
    }

    protected function _finish($code, $originalRedirectUri)
    {
        $url = RequestUtil::buildUri($this->appInfo->getHost()->getApi(), "1/oauth2/token");
        $params = array(
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => $originalRedirectUri,
            "locale" => $this->userLocale,
        );

        $curl = RequestUtil::mkCurlWithoutAuth($this->clientIdentifier, $url);

        // Add Basic auth header.
        $basic_auth = $this->appInfo->getKey() . ":" . $this->appInfo->getSecret();
        $curl->addHeader("Authorization: Basic ".base64_encode($basic_auth));

        $curl->set(CURLOPT_POST, true);
        $curl->set(CURLOPT_POSTFIELDS, RequestUtil::buildPostBody($params));

        $curl->set(CURLOPT_RETURNTRANSFER, true);
        $response = $curl->exec();

        if ($response->statusCode !== 200) throw RequestUtil::unexpectedStatus($response);

        $parts = RequestUtil::parseResponseJson($response->body);

        if (!array_key_exists('token_type', $parts) or !is_string($parts['token_type'])) {
            throw new Exception_BadResponse("Missing \"token_type\" field.");
        }
        $tokenType = $parts['token_type'];
        if (!array_key_exists('access_token', $parts) or !is_string($parts['access_token'])) {
            throw new Exception_BadResponse("Missing \"access_token\" field.");
        }
        $accessToken = $parts['access_token'];
        if (!array_key_exists('uid', $parts) or !is_string($parts['uid'])) {
            throw new Exception_BadResponse("Missing \"uid\" string field.");
        }
        $userId = $parts['uid'];

        if ($tokenType !== "Bearer" && $tokenType !== "bearer") {
            throw new Exception_BadResponse("Unknown \"token_type\"; expecting \"Bearer\", got  "
                                            .Client::q($tokenType));
        }

        return array($accessToken, $userId);
    }
}
