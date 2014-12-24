<?php
/* ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is http://www.sa-mods.info
 *
 * The Initial Developer of the Original Code is
 * wayne Mankertz.
 * Portions created by the Initial Developer are Copyright (C) 2011
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * ***** END LICENSE BLOCK ***** 
 *
This file contains all the hooks that will be called by the smf code.  The top of the smf documentation has been ripped out
to give context to when it is being called if someone is trying to learn their way around the code 


if (!defined('SMF')) die('Hacking attempt...');
/*

Called from: LogInOut.php, LogOut(), if the user is not a guest, after unsetting some session variables, but before clearing their entry in {db_prefix}log_online. They are, technically, still listed as online when the hook is called.
Purpose: To initiate logout in integrated code.

*/
function bitshares_integrate_logout() {
    if (isset($_SESSION['token'])) unset($_SESSION['token']);
}


/*
If syncbts is set in the URL, update the db with the bitshares specific columns

Called from: LogInOut.php, DoLogIn(), almost first instruction in the function - used during user actively logged in (session exists, more validating that than anything else) Register.php, Register2(), once registration is completed, just before setting up user cookie - so registration will log them in to both SMF and integrated app
Purpose: To log user in on both integrated code and in SMF at the same time.
*/
function bitshares_integrate_login($user, $hashPasswd, $cookieTime) {
    global $user_settings;
    if (isset($_GET['syncbts'])) {
        $gdata = $_SESSION['bitsharesdata'];
        $_SESSION['bitshares']['id'] = $gdata['id'];
        $_SESSION['bitshares']['name'] = $gdata['name'];
        updateMemberData($user_settings['id_member'], array('btsid' => $_SESSION['bitshares']['id'], 'btsname' => $_SESSION['bitshares']['name'],));
        unset($_SESSION['bitshares']['id']);
        unset($_SESSION['bitshares']['name']);
        unset($_SESSION['bitsharesdata']);
    } else {
        return;
    }
}


/*
calls bitshares_init_auth_url() and then substitutes out the resulting text ..  
This is the function that creates the button HTML

Called from: Subs.php, during obExit, used to add functions to be run on the content prior to it being sent to the user, in the spirit of last-minute widespread content changes.

*/
function ob_bitshares(&$buffer) {

    global $authUrl, $context, $modSettings, $txt,$g_authurl_error;

    if (empty($modSettings['bts_app_enabled']) || isset($_REQUEST['xml'])) 
       return $buffer;

    if (!$context['user']['is_logged']) { // ok if user is not logged in, then lets create the login buttons at top

        bitshares_init_auth_url(); // authUrl is side effect /return of this

        $txt['guestnew'] = sprintf($txt['welcome_guest'], $txt['guest_title']);

	// we also create g_authurl_error
        if ((empty($authUrl) || (!$authUrl))) { 
	        // change out the button with a span of the error
            if ((!empty($modSettings['bts_app_printerrorsatfailure'])) && $modSettings['bts_app_printerrorsatfailure']) {
                $buffer = preg_replace('~(' . preg_quote('<div class="info">' . $txt['guestnew'] . '</div>') . ')~', '<p>' . $g_authurl_error . '</p><div class="info">' . $txt['guestnew'] . '</div>', $buffer);
            }
            return $buffer; //lets not put a button up
        }


	
	// one does frontpage, one is for the forgot password page.... third?
        $buffer = preg_replace('~(' . preg_quote('<div class="info">' . $txt['guestnew'] . '</div>') . ')~', '<a href="' . $authUrl . '"><img src="' . $modSettings['bts_app_custon_logimg'] . '" alt="" /></a><div class="info">' . $txt['guestnew'] . '</div>', $buffer);
        $buffer = preg_replace('~(' . preg_quote($txt['forgot_your_password'] . '</a></p>') . ')~', $txt['forgot_your_password'] . '</a></p><div align="center"><a href="' . $authUrl . '"><img src="' . $modSettings['bts_app_custon_logimg'] . '" alt="" /></a></div>', $buffer);
        $buffer = preg_replace('~(' . preg_quote('<dt><strong><label for="smf_autov_username">' . $txt['username'] . ':</label></strong></dt>') . ')~', '<dt><strong>' . $txt['bts_app_rwf'] . ':</strong><div class="smalltext">' . $txt['bts_app_regmay'] . '</div></dt><dd><a href="' . $authUrl . '"><img src="' . $modSettings['bts_app_custon_logimg'] . '" alt="" /></a></dd><dt><strong><label for="smf_autov_username">' . $txt['username'] . ':</label></strong></dt>', $buffer);
    }
    return $buffer;
}
/*
This is called with /index.php?action=bitshares in the URL


Called from: index.php, just after the creation of the action array
Purpose: To allow add or remove actions from the action array.
Accepts: 1 function name.

*/
function bitshares_actions(&$actionArray) {
    $forum_version = 'SMF 2.0.9';
    $actionArray['bitshares'] = array('Bitshares/Bitshares.php', 'Bitshares');
}
/*


integrate_admin_areas
Called from: Admin.php, during AdminMain(), immediately after the default SMF admin areas have been defined.
Purpose: To allow code to modify the admin menu, adding or removing areas, sections or subsections from it.

*/
function bitshares_admin_areas(&$admin_areas) {
    global $scripturl, $txt;
    if (allowedTo('admin_forum')) {
        bitshares_array_insert($admin_areas, 'layout', array('sa_bitshares' => array('title' => $txt['bts_bitshares'], 'areas' => array('bitshares' => array('label' => $txt['bts_app_config'], 'file' => 'Bitshares/BitsharesAdmin.php', 'function' => 'bitsharesa', 'custom_url' => $scripturl . '?action=admin;area=bitshares', 'icon' => 'server.gif', 'subsections' => array('bitshares' => array($txt['bts_app_config']), 'bitshares_logs' => array($txt['bts_app_logs']),),),),),));
    }
}


/*
Called from: Profile.php, just after the definition of the default profile areas (i.e. the menu entries for the profile section)
Purpose: allows add or modify the menu in the profile area
*/
function bitshares_profile_areas(&$profile_areas) {

    global $user_settings, $txt, $authUrl, $modSettings, $sc;

    if (empty($user_settings['btsid']) && !empty($modSettings['bts_app_enabled'])) {
        bitshares_init_auth_url();
        bitshares_array_insert($profile_areas, 'profile_action', array('profile_bts' => array('title' => $txt['bts_bitshares'], 'areas' => array('gsettings' => array('label' => $txt['bts_app_aso_account'], 'custom_url' => $authUrl . '" onclick="return confirm(\'' . $txt['bts_app_aso_account_confirm'] . '\');"', 'sc' => $sc, 'permission' => array('own' => 'profile_view_own', 'any' => '',),),),),));
    }
    if (!empty($user_settings['btsid']) && !empty($modSettings['bts_app_enabled'])) {
        bitshares_array_insert($profile_areas, 'profile_action', array('profile_bts' => array('title' => $txt['bts_bitshares'], 'areas' => array('gsettings' => array('label' => 'Settings', 'file' => 'Bitshares/Bitshares.php', 'function' => 'bitshares_Profile', 'sc' => $sc, 'permission' => array('own' => 'profile_view_own', 'any' => '',),),),),));
    }
}


/*
Called from: Load.php, at the end of loadTheme(), to load any information that potentially affects top level loading of the theme itself.
Purpose: Designed to modify the layers to be loaded during page generation, for example to avoid calling the 'html' layer if the page is part of a CMS output (where there will already be a similar layer). Orstio expands on it a little more, covering off my initial theory that it was for exporting data to a CMS, rather than its actual use of pulling from.
*/
function bitshares_loadTheme() {

    global $modSettings, $user_info, $context;

    loadLanguage('Bitshares');
    if (empty($modSettings['allow_guestAccess']) && $user_info['is_guest'] && (isset($_REQUEST['action']) || in_array(isset($_REQUEST['action']), array('bitshares')))) {
        $modSettings['allow_guestAccess'] = 1;
    }

/*     This code looks at twit_USettings which doesnt exist ?
   if (isset($_SESSION['bitshares']['idm']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'login' && !empty($modSettings['bts_app_enabledautolog'])) {
        $context['bitshares_id'] = twit_USettings($_SESSION['bitshares']['idm'], 'id_member', 'btsid');
        if (!empty($context['bitshares_id'])) {
            redirectexit('action=bitshares;area=connectlog');
        }
    }
    */
    if (!isset($_REQUEST['xml'])) {
        $layers = $context['template_layers'];
        $context['template_layers'] = array();
        foreach ($layers as $layer) {
            $context['template_layers'][] = $layer;
            if ($layer == 'body' || $layer == 'main') $context['template_layers'][] = 'bitshares';
        }
    }
}

function template_bitshares_above() { // this function has to exist.. it is called
}
function template_bitshares_below() {
}
function bitshares_load() {
    global $boarddir;
    require_once ($boarddir . '/Sources/Bitshares/bitsharesApiClient.php');
}

/*
Sets the global variable authUrl that is created by the wallet via bitsharesApiClient
*/
function bitshares_init_auth_url() {
    global $authUrl;

    bitshares_load();
    try {
        $client = new apiClient();
        $client->configInstance_forSMF();
        $authUrl = $client->createAuthUrl();
    }
    catch(Exception $e) {
        $authUrl = '';
    }
}
/*
This is the basic security test function
*/
function bitshares_init_auth() {

    bitshares_load();

    try {
        $client = new apiClient();
        $client->configInstance_forSMF();
        //$oauth2 = $client; // weird but an attempt to maintain backwards compat without 2 classes

        if (isset($_GET['signed_secret'])) {
            // ok first try and not authenticated.  try it.. throws exception if it doesnt work
            $client->authenticate();
            $_SESSION['token'] = $client->getAccessToken();
        }
        if (isset($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
        }
        if ($client->getAccessToken()) {
            $user = $client->userinfo_get();
            $_SESSION['token'] = $client->getAccessToken();
        }

        if (isset($user) && isset($_GET['client_key'])) // this was 'code' for oauth, it signfies the first step after browser started process
        {
            // OK user is authenticated.  setup the session variable and do the redirect
            $_SESSION['bitsharesdata'] = $user;
            $_SESSION['bitshares']['idm'] = $user['id'];
            $_SESSION['bitshares']['pic'] = !empty($user['picture']) ? $user['picture'] : '';
            redirectexit('action=bitshares;auth=done');
        }
    } catch (Exception $e){
            return $e->getMessage();
    }
}
/* generates the html for the login auth button */
function bitshares_show_auth_login() {
    global $authUrl, $modSettings;
    bitshares_init_auth_url();
    echo '<a href="' . $authUrl . '"><img src="' . $modSettings['bts_app_custon_logimg'] . '" alt="" /></a>';
}
/* this takes member_id and loads from the main members table .. goes from bitshares -> smf  */
function bitshares_loadUser($member_id, $where_id) {
    global $smcFunc;
    $results = $smcFunc['db_query']('', '
		SELECT *
		FROM {db_prefix}members
		WHERE {raw:where_id} = {string:member_id}
		LIMIT 1', array('member_id' => $member_id, 'where_id' => $where_id,));
    $temp = $smcFunc['db_fetch_assoc']($results);
    $smcFunc['db_free_result']($results);
    return $temp;
}
/* This function is used for preprocessing the arrays for smf function inputs
*/
function bitshares_array_insert(&$input, $key, $insert, $where = 'before', $strict = false) {
    $position = array_search($key, array_keys($input), $strict);
    // Key not found -> insert as last
    if ($position === false) {
        $input = array_merge($input, $insert);
        return;
    }
    if ($where === 'after') $position+= 1;
    // Insert as first
    if ($position === 0) $input = array_merge($insert, $input);
    else $input = array_merge(array_slice($input, 0, $position), $insert, array_slice($input, $position));
}
?>