<?php

require_once("Client.php");
require_once("GrantType/IGrantType.php");
require_once("GrantType/AuthorizationCode.php");

/**
 * Created by JariZ.pro
 * Basic Reddit OAuth client
 * Very, very basic, but it gets the job done
 */
class RedditOAuth
{
    private $client;

    private $authorizeUrl = 'https://ssl.reddit.com/api/v1/authorize';
    private $accessTokenUrl = 'https://ssl.reddit.com/api/v1/access_token';
    private $realmUrl = "https://oauth.reddit.com/";
    private $redirectUrl;

    private $scope;
    private $modHash;

    public $authorized = false;

    public function __construct($clientId, $clientSecret, $redirectUrl, $scope=array("identity"))
    {
        $this->scope = $scope;
        $this->client = new OAuth2\Client($clientId, $clientSecret, OAuth2\Client::AUTH_TYPE_AUTHORIZATION_BASIC);
        $this->redirectUrl = $redirectUrl;
    }

    public function setAccessToken($token)
    {
        $this->authorized = true;
        $this->client->setAccessToken($token);
        $this->client->setAccessTokenType(OAuth2\Client::ACCESS_TOKEN_BEARER);
    }

    public function Fetch($url, $parameters = array(), $http_method = \OAuth2\Client::HTTP_METHOD_GET, array $http_headers = array(), $form_content_type = \OAuth2\Client::HTTP_FORM_CONTENT_TYPE_MULTIPART) {
        $f = $this->client->fetch($this->realmUrl.$url, $parameters, $http_method, $http_headers, $form_content_type);
        if(isset($f["result"]["data"]["modhash"])) $this->modHash = $f["result"]["data"]["modhash"];
        return $f;
    }

    private function buildScope() {
        $i = -1;
        $s = "";
        foreach($this->scope as $scope) {
            $i++;
            if($i == 0) $s = $scope;
            else $s .= ",$scope";
        }
        return $s;
    }

    public function Auth()
    {
        if (!isset($_GET["code"])) {
            $authUrl = $this->client->getAuthenticationUrl($this->authorizeUrl, $this->redirectUrl, array("scope" => $this->buildScope(), "state" => str_shuffle("abcdefghijkl123456789")));
            header("Location: " . $authUrl);
            return false;
        } else {
            $params = array("code" => $_GET["code"], "redirect_uri" => $this->redirectUrl);
            $response = $this->client->getAccessToken($this->accessTokenUrl, \OAuth2\Client::GRANT_TYPE_AUTH_CODE, $params);
            $accessTokenResult = $response["result"];
            if (!isset($accessTokenResult["error"])) {
                $this->setAccessToken($accessTokenResult["access_token"]);
                return $accessTokenResult["access_token"];
            } else return false;
        }
    }
}
