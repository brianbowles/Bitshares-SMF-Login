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

These functions are all called in the URL by GET variables

*/

if (!defined('SMF')) die('Hacking attempt...');

function Bitshares() {

    global $txt, $modSettings, $context;

    loadTemplate('Bitshares');
    if (empty($modSettings['bts_app_enabled'])) fatal_lang_error('bts__app_error1', false);
    $subActions = array('main' => 'bitshares_main', 'connect' => 'bitshares_connect', 'auto' => 'bitshares_connectAuto', 'connectlog' => 'bitshares_connectlog', 'sync' => 'bitshares_sync', 'unsync' => 'bitshares_unsync', 'logsync' => 'bitshares_logsync',);
    $_REQUEST['area'] = isset($_REQUEST['area']) && isset($subActions[$_REQUEST['area']]) ? $_REQUEST['area'] : 'main';
    $context['page_title'] = $txt['bts_bitshares'];
    $context['sub_action'] = $_REQUEST['area'];
    $subActions[$_REQUEST['area']]();
}

function bitshares_logsync() {

    global $context;

    $context['sub_template'] = 'bitshares_logsync';
    $context['default_username'] = & $_REQUEST['u'];
    $context['default_password'] = '';
}
function bitshares_sync() {

    global $user_info;

    checkSession('get');
    $gdata = $_SESSION['bitsharesdata'];
    $_SESSION['bitshares']['id'] = $gdata['id'];
    $_SESSION['bitshares']['name'] = $gdata['name'];
    updateMemberData($user_info['id'], array('btsid' => $_SESSION['bitshares']['id'], 'btsname' => $_SESSION['bitshares']['name'],));
    redirectexit('action=profile');
}
function bitshares_unsync() {

    global $user_info;

    checkSession('get');
    updateMemberData($user_info['id'], array('btsid' => '', 'btsname' => '',));
    redirectexit('action=profile');
}
function bitshares_main() {

    global $context, $sc, $user_info, $user_settings, $modSettings;

    $err = bitshares_init_auth(); // first pass this doesnt return and sets up the auth
    if (!empty($_SESSION['bitsharesdata']) && isset($_REQUEST['auth']) && $_REQUEST['auth'] == 'done') { // after first redirect enter this as auth is done
        $me = !empty($_SESSION['bitsharesdata']) ? $_SESSION['bitsharesdata'] : '';
        $_SESSION['bitshares']['id'] = $me['id'];
        if ($context['user']['is_logged']) {
            if (empty($user_settings['btsid'])) {
                redirectexit('action=bitshares;area=sync;sesc=' . $sc . '');
            } else {
                redirectexit('action=profile;u=' . $user_info['id'] . '');
            }
        } else {
            $member_load = bitshares_loadUser($_SESSION['bitshares']['id'], 'btsid');
            if (empty($_SESSION['login_url']) && isset($_SESSION['old_url']) && strpos($_SESSION['old_url'], 'dlattach') === false && preg_match('~(board|topic)[=,]~', $_SESSION['old_url']) != 0) $_SESSION['login_url'] = $_SESSION['old_url'];
            if ($member_load['btsid']) {

                redirectexit('action=bitshares;area=connectlog'); // This is the working path on the LAST redirect.. but btsid == '' WTF

            } else {

                if (!empty($modSettings['requireAgreement'])) { // after initial auth and no account being loaded, send them to the registration agreement
                    $mode = empty($modSettings['bts_reg_auto']) ? 'connect' : 'auto';
                    redirectexit('action=bitshares;area=' . $mode . ';agree');
                } else {
                    $mode = empty($modSettings['bts_reg_auto']) ? 'connect' : 'auto';
                    redirectexit('action=bitshares;area=' . $mode . '');
                }

            }
        }
    } else {
	if (empty($modSettings['bts_app_printerrorsatfailure'])) {
            fatal_lang_error('bts__app_error2', false); // This is Did you try to skip authorization?	
	} else {
            setup_fatal_error_context($err); // we might leak wallet info here.. 
	}
        
    }
}
function bitshares_connectlog() {

    global $scripturl, $modSettings, $sourcedir;

    $_SESSION['bitshares']['id'] = $_SESSION['bitshares']['idm'];

    if (empty($_SESSION['bitshares']['id'])) 
       fatal_lang_error('bts__app_error3', false);
    $member_load = bitshares_loadUser($_SESSION['bitshares']['id'], 'btsid');
    $modSettings['cookieTime'] = 3153600;
    
    require_once ($sourcedir . '/Subs-Auth.php');
    include_once ($sourcedir . '/LogInOut.php');
    
    setLoginCookie(60 * $modSettings['cookieTime'], $member_load['id_member'], sha1($member_load['passwd'] . $member_load['password_salt']));
    unset($_SESSION['bitshares']['id']);
    unset($_SESSION['bitshares']['name']);
    unset($_SESSION['bitsharesdata']);

    redirectexit($scripturl);
}
function bitshares_createRandomPassword($length = 8, $strength = 15) {

    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants.= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels.= "AEUY";
    }
    if ($strength & 4) {
        $consonants.= '23456789';
    }
    if ($strength & 8) {
        $consonants.= '@#$%';
    }
    $password = '';
    $alt = time() % 2;
    for ($i = 0;$i < $length;$i++) {
        if ($alt == 1) {
            $password.= $consonants[(rand() % strlen($consonants)) ];
            $alt = 0;
        } else {
            $password.= $vowels[(rand() % strlen($vowels)) ];
            $alt = 1;
        }
    }
    return $password;
}
/*
 * This is a kludge that uses the profile upload pic from remote server code that already exists in SMF
 * So instead of writing my own code that would be fragile without understanding everything I kludged up
 * environment and call the functionality that already exists to do this
 */
function synchRoboHash($memberID) {

    global $context, $sourcedir, $modSettings, $user_info;

    require_once ($sourcedir . '/Profile-Modify.php');

    $user_info['permissions'][] = 'profile_remote_avatar';
    $pushUPP = $_POST['userpicpersonal'];
    $_POST['userpicpersonal'] = bitshares_robohashURL();
    $tmp = 'external';
    $pushContext = $context['id_member'];
    $context['id_member'] = $memberID;
    $pushMS = $modSettings['avatar_download_external'];
    $modSettings['avatar_download_external'] = 1;

    profileSaveAvatarData($tmp);

    // ok restore/pop the variables that might possibly have side effects
    $_POST['userpicpersonal'] = $pushUPP;
    $context['id_member'] = $pushContext;
    $modSettings['avatar_download_external'] = $pushMS;
}

/*
 * This is called on signup when the auto option is set.  The user will not be prompted for a password and email.  THe email will be something random.
 * This moves SESSION data from [bitsharesdata] to [bitshares], attempts to load the user, if it loads then it gives error else is registers.
*/
function bitshares_connectAuto() {
    
    global $modSettings, $sourcedir;
    
    // So move SESSION bitsharesdata to bitshares .. WHY
    $gdata = !empty($_SESSION['bitsharesdata']) ? $_SESSION['bitsharesdata'] : '';
    $_SESSION['bitshares']['id'] = $gdata['id'];
    $_SESSION['bitshares']['name'] = $gdata['name'];
    $_SESSION['bitshares']['email'] = $gdata['email'];

    if (empty($gdata)) fatal_lang_error('bts__app_error3', false);

    $member_load = bitshares_loadUser($_SESSION['bitshares']['name'], 'real_name');
    if ($member_load['real_name']) { // add the nt which throws up a screen with a this user is already in db message
        redirectexit('action=bitshares;area=logsync;nt;u=' . $member_load['real_name'] . '');
    }

    $pass = bitshares_createRandomPassword(12,15);
    $email = bitshares_createRandomPassword(10,3).'@thiscanneverbeavaliddomainifyouwanttochangeyouremailtosomethingvalidpleasechangeit.com';
    $user = un_htmlspecialchars($_SESSION['bitshares']['name']);

    // Ok lets escape out name AND SESSION'id' ?, email and pw are created in code
    $regOptions = array('interface' => 'guest', 'auth_method' => 'password', 'username' => $user, 'email' => $email, 'require' => 'nothing', 'password' => $pass, 'password_check' => $pass, 'password_salt' => substr(md5(mt_rand()), 0, 4), 'send_welcome_email' => !empty($modSettings['send_welcomeEmail']), 'check_password_strength' => false, 'check_email_ban' => false, 'extra_register_vars' => array('id_group' => !empty($modSettings['bts_app_detait_gid']) ? $modSettings['bts_app_detait_gid'] : '0',),);

    // ok if not registered on blockchain but it is bitshares login, try alt membergroup
    if (isset($_SESSION['bitsharesdata']['bitsharesregistered']) && (!$_SESSION['bitsharesdata']['bitsharesregistered'])) {
        $regOptions['extra_register_vars'] = array('id_group' => !empty($modSettings['bts_app_detait_gid2']) ? $modSettings['bts_app_detait_gid2'] : '0',);
    }
    require_once ($sourcedir . '/Subs-Members.php');
    $memberID = registerMember($regOptions);
    updateMemberData($memberID, array('btsid' => $_SESSION['bitshares']['id'], 'btsname' => $user));

    synchRoboHash($memberID);
    redirectexit('action=bitshares;auth=done');
}

/* If registration is set to manual and not auto then we call this ,
 * sets off registration agreement then after checking off goes here
 This is called before registration agreemeent,-- this cmment is likely wrong, it is called within this function
This code is required to process the function found in the template file.. originally had 2 fields for password, but we
decided to remove that as the user experience/flow is lacking.  So we create a regular account with an unknown password.
So now the template has the username grayed out and actually not submitted .. and passwords removed, but email is still there
However if the user wishes to put in a blank email then we create a fake one that will never be valid

  */
function bitshares_connect() {
    
    global $modSettings, $sourcedir, $context;

    $context['sub_template'] = 'bitshares_cconnect';
    $gdata = !empty($_SESSION['bitsharesdata']) ? $_SESSION['bitsharesdata'] : '';
    $_SESSION['bitshares']['id'] = $gdata['id'];
    $_SESSION['bitshares']['name'] = $gdata['name'];

    if (empty($gdata)) fatal_lang_error('bts__app_error3', false);

    bitshares_do_agree();

    if (isset($_REQUEST['register'])) {
        $member_load = bitshares_loadUser($_POST['real_name'], 'real_name');
        if ($member_load['real_name']) { // nt throws up screen telling user the user already exists
            redirectexit('action=bitshares;area=logsync;nt;u=' . $member_load['real_name'] . '');
        }
        $pass = bitshares_createRandomPassword(12,15);
        $user = un_htmlspecialchars($_SESSION['bitshares']['name']);
        $email = un_htmlspecialchars($_POST['email']);

        if (empty($_POST['email'])) {
            // So the SMF code has a hardcoded regex that checks every email and also checks empty(email)
            // There is no way to disable this without rewriting our own implementation of registerMember
            // So our solution is to allow a user to submit a blank email an we stuff in an email that can never be legal
            $email = bitshares_createRandomPassword(10,3).'@thiscanneverbeavaliddomainifyouwanttochangeyouremailtosomethingvalidpleasechangeit.com';
        }

        // user = ESCAPE, pass = created in script , email = ESCAPE
        $regOptions = array('interface' => 'guest', 'auth_method' => 'password', 'username' => $user, 'email' => $email, 'require' => 'nothing', 'password' => $pass, 'password_check' => $pass, 'password_salt' => substr(md5(mt_rand()), 0, 4), 'send_welcome_email' => !empty($modSettings['send_welcomeEmail']), 'check_password_strength' => false, 'check_email_ban' => false, 'extra_register_vars' => array('id_group' => !empty($modSettings['bts_app_detait_gid']) ? $modSettings['bts_app_detait_gid'] : '0',),);
        // ok if not registered on blockchain but it is bitshares login, try alt membergroup

        if (isset($_SESSION['bitsharesdata']['bitsharesregistered']) && (!$_SESSION['bitsharesdata']['bitsharesregistered'])) {
            $regOptions['extra_register_vars'] = array('id_group' => !empty($modSettings['bts_app_detait_gid2']) ? $modSettings['bts_app_detait_gid2'] : '0',);
        }
        require_once ($sourcedir . '/Subs-Members.php');

        $memberID = registerMember($regOptions);
        updateMemberData($memberID, array('btsid' => $_SESSION['bitshares']['id'], 'btsname' => $user));

        synchRoboHash($memberID);
        redirectexit('action=bitshares;auth=done');
    }
}

function bitshares_robohashURL() {
    return "http://robohash.org/" . $_SESSION['bitshares']['name'] . ".png";
}

function bitshares_do_agree() {

    global $sourcedir, $context, $boarddir, $boardurl, $user_info, $modSettings;

    require_once ($sourcedir . '/Subs-Package.php');

    if (isset($_GET['agree'])) {
        loadLanguage('Login');
        $context['sub_template'] = 'bitshares_agree';
        if (file_exists($boarddir . '/agreement.' . $user_info['language'] . '.txt')) $context['agreement'] = parse_bbc(fetch_web_data($boardurl . '/agreement.' . $user_info['language'] . '.txt'), true, 'agreement_' . $user_info['language']);
        elseif (file_exists($boarddir . '/agreement.txt')) $context['agreement'] = parse_bbc(fetch_web_data($boardurl . '/agreement.txt'), true, 'agreement');
        else $context['agreement'] = '';
    } else {
        if (!isset($_POST['accept_agreement']) && !empty($modSettings['requireAgreement'])) redirectexit('action=bitshares;area=connect;agree');
    }
}
?>