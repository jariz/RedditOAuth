<?php

require_once("Client.php");
require_once("GrantType/IGrantType.php");
require_once("GrantType/AuthorizationCode.php");

/**
 * Created by JariZ.pro
 * Basic Reddit OAuth client
 */
class RedditOAuth
{
    private $client;

    private $authorizeUrl = 'https://ssl.reddit.com/api/v1/authorize';
    private $accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
    private $realmUrl = "https://oauth.reddit.com/";
    private $redirectUrl;

    private $modHash;

    public function __construct($clientId, $clientSecret, $redirectUrl)
    {
        $this->client = new OAuth2\Client($clientId, $clientSecret, OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $this->redirectUrl = $redirectUrl;
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
        $this->client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
    }

    public function Fetch($url) {
        $f = $this->client->fetch($this->realmUrl.$url);
        if(isset($f["result"]["data"]["modhash"])) $this->modHash = $f["result"]["data"]["modhash"];
        return $f;
    }

    //sadly not possible, should be though.
    /*public function Revoke() {
        $res = $this->client->fetch($this->realmUrl."api/revokeapp.json", array("client_id" => $this->client->getClientId(), "uh" => $this->modHash));
        return $res["code"] == 200;
    }*/

    public function Auth()
    {
        if (!isset($_GET["code"])) {
            $authUrl = $this->client->getAuthenticationUrl($this->authorizeUrl, $this->redirectUrl, array("scope" => "mysubreddits,modconfig,identity", "state" => random_string(), "duration" => "permanent"));
            header("Location: " . $authUrl);
            return false;
        } else {
            $params = array("code" => $_GET["code"], "redirect_uri" => $this->redirectUrl);
            $response = $this->client->getAccessToken($this->accessTokenUrl, "authorization_code", $params);
            $accessTokenResult = $response["result"];
            if (!isset($accessTokenResult["error"])) {
                $this->client->setAccessToken($accessTokenResult["access_token"]);
                $this->client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
                return $accessTokenResult["access_token"];
            } else return false;
        }
    }
}
