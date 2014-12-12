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
 * This is a basic drop-in replacement for a subset of the Google API PHP Client
 * It should allow use of Bitshares based blockchain login in place of OAuth
 * allowig a relative straight forward port of any plugin that relies on the google api.
 */

// Check for the required json and curl extensions, the Google API PHP Client won't function without them.
if (! function_exists('json_decode')) {
  throw new Exception('Bitshares PHP API Client requires the JSON PHP extension');
}

if (! function_exists('http_build_query')) {
  throw new Exception('Bitshares PHP API Client requires http_build_query()');
}

if (! ini_get('date.timezone') && function_exists('date_default_timezone_set')) {
  date_default_timezone_set('UTC');
}

// hack around with the include paths a bit so the library 'just works'
$cwd = dirname(__FILE__);
set_include_path("$cwd" . PATH_SEPARATOR . get_include_path());

// TODO put the config.php back in ?
/*
require_once "config.php";
// If a local configuration file is found, merge it's values with the default configuration
if (file_exists($cwd . '/local_config.php')) {
  $defaultConfig = $apiConfig;
  require_once ($cwd . '/local_config.php');
  $apiConfig = array_merge($defaultConfig, $apiConfig);
}
*/



require_once "includes/easybitcoin.php";


class apiClient {


private $RPC_SERVER_ADDRESS = "localhost";
private $RPC_SERVER_PATH = "rpc";

private $RPC_SERVER_PORT = 57133;
private $RPC_SERVER_USER = "sitetest";
private $RPC_SERVER_PASS = "sitetestPW";
private $RPC_SERVER_WALLET = "test";
private $RPC_SERVER_WALLET_PASS = "genericE55IE";
private $BITSHARES_USER_NAME = "testingtoday"; // this is an account created on the servers wallet
private $SITE_DOMAIN = 'bitsharesnation.org';


private $authenticated = false;
private $uid = -1;
private $userinfo = null;

public function __construct($config = array()) {
    global $apiConfig,$modSettings;
}

private function createRandomValidEmail() {

        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < 15; $i++) {
            if ($alt == 1) {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            } else {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }
        return $password."@gmail.com";
    }
// TODO this was the original array used by the gplus/oauth plugin.  We need to determine what 
// determined these and if they can be removed/ modified, but for now we leave it alone
private function init_userinfo() {

    return array (
        id => '', // make it into the pubic id
        email => $this->createRandomValidEmail(),//'changeme@gmail.com', // this has to be valid or it is caught in registration
        verified_email => true,
        name => '',
        given_name => '',
        family_name => '',
        link => '', // Appears to be google profile
        picture => '',
        gender => '',
        locale => ''
    );
}

	// TODO grep for throw and look at exceptions, try to implement them in the same way
// review 'token' php variable and make sure we are using it properly TODO
  public function authenticate()
  {

      $this->authenticated = false; // TODO make sure this actually WORKS...  what is the point of the return ?
      $bitshares = new Bitcoin($this->RPC_SERVER_USER, $this->RPC_SERVER_PASS, $this->RPC_SERVER_ADDRESS, $this->RPC_SERVER_PORT, $this->RPC_SERVER_PATH);

      $bitshares->open($this->RPC_SERVER_WALLET);
      $bitshares->unlock(5, $this->RPC_SERVER_WALLET_PASS);


      //  _GET has client_key,client_name,server_key,signed_secret
      if (isset($_GET["client_key"])) {

          //  inspect loginPackage .. has user_account_key  and shared_secret
          $loginPackage = $bitshares->wallet_login_finish($_REQUEST["server_key"], $_REQUEST["client_key"], $_REQUEST["signed_secret"]);
          $this->userinfo = $this->init_userinfo();

          $this->authenticated = (bool)$loginPackage; // FIX THIS.  TEMP
          if ($this->authenticated == false) {
              return;
          }
          if (isset($_REQUEST['signed_secret'])) {
              $this->setAccessToken($_REQUEST['signed_secret']); // TODO is this a security issue?  maybe truncate the string
          }
          $this->uid = $loginPackage["user_account_key"]; // Is this used anywhere? TODO
          $this->userinfo['id'] = $this->uid; // later this turns into the btsid or gid or somesuch

          // if userAccount is null it may be because the account is not yet registered.
          $userAccount = $bitshares->blockchain_get_account($_GET['client_name']);
          if (empty($userAccount) || (!$userAccount)) {
              // Account isnt registered, so use name passed
              $this->userinfo['name'] = $_GET['client_name'];
              //$modSettings['bts_reg_auto'] = false; // perhaps we can change the registration from auto to manual right here !
	      $this->userinfo['bitsharesregistered'] = false;
          } else {
              $this->userinfo['name'] = $userAccount['name'];
	      $this->userinfo['bitsharesregistered'] = true;
          }
          $this->userinfo['picture'] = 'http://robohash.org/' . $this->userinfo['name'];


          if (isset($userAccount["delegate_info"])) {
              //echo "<p>This is the VIP section, for delegates only.</p>";
              $this->userinfo->given_name = "Delegate";
          }
      }
  }
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



  public function userinfo_get() {
       return $this->userinfo;// this puts a 10 element array into $user id,email,verifiedemail,name,givenname,faimlyname,link,picture, generic,locale 
  }


  public function createAuthUrl() {


  	 $bitshares = new Bitcoin($this->RPC_SERVER_USER, $this->RPC_SERVER_PASS, $this->RPC_SERVER_ADDRESS, $this->RPC_SERVER_PORT, $this->RPC_SERVER_PATH); 

	 $bitshares->open($this->RPC_SERVER_WALLET);
      if (! empty($bitshares->error)) {
        return false; // TODO maybe report the bitshares error to the login screen
      }


 	 $bitshares->unlock(2, $this->RPC_SERVER_WALLET_PASS);

	 //return $bitshares->wallet_login_start($BITSHARES_USER_NAME) . $SITE_DOMAIN . "/index.php?action=bitshares"; // probably works but bitshares code behaves weird
	 $loginStart = $bitshares->wallet_login_start($this->BITSHARES_USER_NAME);
	 if (empty($loginStart) || $loginStart == 'null') {
	    return false;
	 }   
	 return $loginStart . $SITE_DOMAIN."/loginredirect.php";
	

 }	


