<?php
/*
 * Copyright 2010 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
  This is a basic drop-in replacement for a subset of the Google API PHP Client
  It should allow use of Bitshares based blockchain login in place of OAuth authentication,
  allowing a relative straight forward port of any plugin that relies on the Google-Plus api.

  * Note that this is a small subset of oauth *
  * https://github.com/google/google-api-php-client
 
  Previous code would have something like this.  The Service class is ignored and not implemented

  $client = new apiClient();
  $oauth2 = new apiOauth2Service($client); // This is no longer used.

*/
// Check for the required json and curl extensions, the Google API PHP Client won't function without them.
if (!function_exists('json_decode')) {
    throw new Exception('Bitshares PHP API Client requires the JSON PHP extension');
}
/*
if (!function_exists('http_build_query')) {
    throw new Exception('Bitshares PHP API Client requires http_build_query()');
}
*/
if (!ini_get('date.timezone') && function_exists('date_default_timezone_set')) {
    date_default_timezone_set('UTC');
}

// hack around with the include paths a bit so the library 'just works'
$cwd = dirname(__FILE__);
set_include_path("$cwd" . PATH_SEPARATOR . get_include_path());


require_once "easybitcoin.php";

class apiClient {

    // THere isnt a constructor written for these directly.. 
    private $RPC_SERVER_PATH = "rpc";
    private $RPC_SERVER_ADDRESS;
    private $RPC_SERVER_PORT;
    private $RPC_SERVER_USER;
    private $RPC_SERVER_PASS;
    private $RPC_SERVER_WALLET;
    private $RPC_SERVER_WALLET_PASS;
    private $BITSHARES_USER_NAME;
    private $SITE_DOMAIN;

    private $authenticated = false;
    private $uid = - 1;
    private $userinfo = null;
    private $authenticateUnRegisteredBlockchain = true; // let em through the gate?

    public function __construct($config = array()) { // TODO is this a php constructor or garbage LOL
        global $apiConfig, $modSettings;
    }

    /*
    This should likely be a constructor, but 8 parameters is excessive and just as likely to add problems. meh
    */
    public function configInstance_forSMF() {

        global $modSettings;

        // wallet settings
        $this->RPC_SERVER_ADDRESS = $modSettings['bts_app_wallet_server'];
        $this->RPC_SERVER_PORT = $modSettings['bts_app_wallet_port'];
        $this->RPC_SERVER_USER = $modSettings['bts_app_wallet_user'];
        $this->RPC_SERVER_PASS = $modSettings['bts_app_wallet_pass'];
        $this->RPC_SERVER_WALLET = $modSettings['bts_app_wallet_walletname'];
        $this->RPC_SERVER_WALLET_PASS = $modSettings['bts_app_wallet_walletpass'];
        $this->BITSHARES_USER_NAME = $modSettings['bts_app_wallet_site_account'];
        $this->SITE_DOMAIN = $modSettings['bts_app_wallet_site_domain'];

        //
        $this->authenticateUnRegisteredBlockchain = $modSettings['bts_app_register_unregistered'];
    }

    /*
    We determined that a troll-athon might happen if people can register with unregistered accounts and no email verification, so we are adding
    this to the plugin
    */
    public function setAuthenticateUnRegisteredBlockchain($v) {
    	   $authenticateUnRegisteredBlockchain = $v;
    }

    /*
    SMF has some rule that enforces a valid email.  If auto is turned on, there is no email in the field so we need
    a function to create a junk placeholder OR figure out how to  disable the email check ...

    Since the auto mode is just a leftover from previous plugin and manual lets people actually put in emails which seems preferred
    I am not going to fix this ...
    */
    private function createRandomValidEmail() {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        $password = '';
        $alt = time() % 2;
        for ($i = 0;$i < 15;$i++) {
            if ($alt == 1) {
                $password.= $consonants[(rand() % strlen($consonants)) ];
                $alt = 0;
            } else {
                $password.= $vowels[(rand() % strlen($vowels)) ];
                $alt = 1;
            }
        }
        return $password . "@gmail.com";
    }

    /* this was the original array used by the gplus/oauth plugin.  We need to determine what
     determined these and if they can be removed/ modified, but for now we leave it alone to maintain backwards compat
    */

    private function init_userinfo() {

        return array(id => '', // make it into the pubic id
	    email => $this->createRandomValidEmail(), // this has to be valid or it is caught in registration
       	verified_email => true,
		name => '', 
		given_name => '', 
		family_name => '', 
		link => '', // Appears to be google profile
       	picture => '',
		gender => '', 
		locale => '');
    }

    // TODO grep for throw and look at exceptions, try to implement them in the same way
    // review 'token' php variable and make sure we are using it properly TODO
    /*
     * The functionality this is supposed to duplicate either returns a token string or does an exception
     */
    public function authenticate() {

        $this->authenticated = false;
        $bitshares = new Bitcoin($this->RPC_SERVER_USER, $this->RPC_SERVER_PASS, $this->RPC_SERVER_ADDRESS, $this->RPC_SERVER_PORT, $this->RPC_SERVER_PATH);// TODO act upon the return code in bitshares

        $bitshares->open($this->RPC_SERVER_WALLET);
        if ($bitshares->status != 200) {
            throw new Exception("Failed open wallet " . $bitshares->error);
        }

        $bitshares->unlock(5, $this->RPC_SERVER_WALLET_PASS);
        if ($bitshares->status != 200) {
            throw new Exception("Failed unlock wallet " . $bitshares->error);
        }

        //  _GET has client_key,client_name,server_key,signed_secret
        if (isset($_GET["client_key"])) {

            //  inspect loginPackage .. has user_account_key  and shared_secret

            $loginPackage = $bitshares->wallet_login_finish($_REQUEST["server_key"], $_REQUEST["client_key"], $_REQUEST["signed_secret"]);
            if ($bitshares->status != 200) {
                throw new Exception("wallet_login_finish failed");
            }
            if (! empty($bitshares->error) ) {
                throw Exception($bitshares->error);
            }

            $this->userinfo = $this->init_userinfo();
            $this->authenticated = (bool)$loginPackage; // TODO look at return code in php and trigger off working value and trigger off !=

            if ($this->authenticated == false) {
                throw new Exception("Authentication failed.");
            }

            if (isset($_REQUEST['signed_secret'])) {
                $this->setAccessToken($_REQUEST['signed_secret']); // So well set the token to be signed_secret.. dont have a better solution
            }

            $this->uid = $loginPackage["user_account_key"]; // Is this used anywhere? TODO
            $this->userinfo['id'] = $this->uid; // later this turns into the btsid or gid or somesuch

            // if userAccount is null it may be because the account is not yet registered.
            $userAccount = $bitshares->blockchain_get_account($_GET['client_name']);

            if (empty($userAccount)) {
                // Account isnt registered, so use name passed
                $this->userinfo['bitsharesregistered'] = false;

                if ($this->authenticateUnRegisteredBlockchain) {
                   $this->userinfo['name'] = $_GET['client_name'];
                } else {
                    throw new Exception("The BitShares account does not appear to be registered on the blockchain. Please register the account");
                }
            } else {
                $this->userinfo['name'] = $userAccount['name'];
                $this->userinfo['bitsharesregistered'] = true;
            }

            $this->userinfo['picture'] = 'http://robohash.org/' . $this->userinfo['name'];
            if (isset($userAccount["delegate_info"])) { // TODO add to test case
                $this->userinfo->given_name = "Delegate";
            }
            return $this->getAccessToken();
        } else {
            throw new Exception("URL is malformed. Stop hacking.");
        }

    }

    /* Original function from oAuth 
    * Set the OAuth 2.0 access token using the string that resulted from calling createAuthUrl()
    * or Google_Client#getAccessToken().
    * @param string $accessToken JSON encoded string containing in the following format:
    * {"access_token":"TOKEN", "refresh_token":"TOKEN", "token_type":"Bearer",
    *  "expires_in":3600, "id_token":"TOKEN", "created":1320790426}
     However we are not receiving JSON so this is our simplified version
    */
    public function setAccessToken($accessToken) {
        if ($accessToken == null || 'null' == $accessToken) {
            $accessToken = null;
            throw new apiAuthException('Access Token not valid');
        }
        $this->accessToken = $accessToken;
    }

    public function getAccessToken() {
        $token = $this->accessToken;
        return (null == $token || 'null' == $token) ? null : $token;
    }

    /*
    This replaces code in the Services class that was migrated into the client class
    Original code was something like $oauth2->userinfo->get()
    */
    public function userinfo_get() {
    	// userinfo is a 10 element array into $user id,email,verifiedemail,name,givenname,faimlyname,link,picture, generic,locale 
        return $this->userinfo; 
        
    }

    /*
    This is the code that calls the wallet to generate the authentication URL.
    That URL should be something like "bts://login ..." which will then load up the local wallet if the machine is configured properly

    It has a side effect of setting g_authurl_error which is a global. perhaps this should be the arguement to an exception

    */
    public function createAuthUrl() {

        global $g_authurl_error; 

        $bitshares = new Bitcoin($this->RPC_SERVER_USER, $this->RPC_SERVER_PASS, $this->RPC_SERVER_ADDRESS, $this->RPC_SERVER_PORT, $this->RPC_SERVER_PATH);
        $bitshares->open($this->RPC_SERVER_WALLET);
        if ($bitshares->status != 200) {
            $g_authurl_error = $bitshares->error;
            return false;
        }
        $bitshares->unlock(2, $this->RPC_SERVER_WALLET_PASS); // with invalid password this doesnt trigger error
        if ($bitshares->status != 200) {
            $g_authurl_error = $bitshares->error;
            return false;
        }
        //return $bitshares->wallet_login_start($BITSHARES_USER_NAME) . $SITE_DOMAIN . "/index.php?action=bitshares"; // probably works but bitshares code behaves weird
        $loginStart = $bitshares->wallet_login_start($this->BITSHARES_USER_NAME);
        if (($bitshares->status != 200) || empty($loginStart) || ($loginStart == 'null')) {
            $g_authurl_error = $bitshares->error;
            return false;
        }
        //loginredirect is needed to recreate URL variables that are stripped when the bts wallet calls the browser back
        return $loginStart . $this->SITE_DOMAIN . "/loginredirect.php";
    }
}
