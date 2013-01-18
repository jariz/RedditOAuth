<?php

//Create your app here: https://ssl.reddit.com/prefs/apps/
session_start();
require_once("RedditOAuth.php");
$example = new Example();

class Example {
    public $reddit;
    public function __construct() {
        $this->reddit = new RedditOAuth(
            //Your client ID (the bold string of alphanumeric characters under the title of your app)
            "xxxxx",

            //client secret also called 'secret' in the app settings
            "xxxxx",

            //Redirect url. NOTE: MUST BE THE SAME AS THE REDIRECT URL YOU DEFINE IN APP SETTINGS
            //Without ending backslash
            "http://mysite.com/Example.php/auth",

            //OAuth scopes your app requires. See reddit wiki for more info.
            array(
                "identity"
            )
        );
        if(@$_SERVER['PATH_INFO'] == "/auth") {
            if(($a = $this->reddit->Auth()) != false) {
                $_SESSION["auth"] = $a;
            }
        } else if(@$_SERVER['PATH_INFO'] == "/destroy") {
            session_destroy();
            die("Session destroyed.");
        }
        if(isset($_SESSION["auth"])) {
            $this->reddit->setAccessToken($_SESSION["auth"]);
        }
    }
} ?>

<?if(!$example->reddit->authorized) { ?>
<button onclick="window.location = 'Example.php/auth'">Authorize!</button>
<? } else { ?>
<?
    $z = $example->reddit->fetch("api/v1/me.json");
    $z = $z["result"];
    echo "<p>Well hello there <b>{$z["name"]}</b> created at <b>".date("r", $z["created"])."</b> with <b>{$z["link_karma"]}|{$z["comment_karma"]}</b> karma!</p>"
    ?></b>
<button onclick="window.location = 'Example.php/destroy'">Destroy session</button>
<? } ?>