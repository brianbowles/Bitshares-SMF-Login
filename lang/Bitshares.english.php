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
 * ***** END LICENSE BLOCK ***** */
global $helptxt;
$helptxt['bts_app_register_unregistered'] = 'Setting this keeps users from having unregistered Bitshares accounts.  This enforces blockchain registration if there are too many spam accounts.  It also prevents people from faking accounts etc.';
$helptxt['bts_app_printerrorsatfailure'] = 'If you want the error message reported to the main screen when the login authenticator is failing.  THis could be a possible security issue if the error messages ever leak account info.';
$helptxt['bts_app_detait_gid'] = ' You may specify what membergroup new members that register with BitShares will belong to Use 0 to disable. Name a membergroup something like Bitshares Registered to signify registration on the blockchain';
$helptxt['bts_app_detait_gid2'] = ' You may specify what membergroup new members that register with BitShares will belong to Use 0 to disable. Use this for accounts that are not registered on the blockchain to show this status.';
$helptxt['bts_app_custon_logimg'] = 'In this section you MUST enter a Bitshares login image URL this will be the button guest click to login/register';
$helptxt['bts_app_enabled'] = 'Enable or disable BitShares mod from here';
$helptxt['bts_app_enabledautolog'] = 'Will attempt to auto log the user into forum when hitting the login page if accounts are synced and session id is found';
$helptxt['bts_reg_auto'] = 'Choose registration method auto or manual';

$helptxt[ 'bts_app_wallet_server'] = 'This is the server that hosts the bitshares client.  Usually localhost';
$helptxt[ 'bts_app_wallet_port'] = 'This is the httpd RPC port specified in the bitshares config file';
$helptxt[ 'bts_app_wallet_user'] = 'This is the username needed to do RPC calls. It is specified in the bitshares client config file';
$helptxt[ 'bts_app_wallet_pass'] = 'This is the password needed to do RPC calls. It is specified in the bitshares client config file';
$helptxt[ 'bts_app_wallet_walletname'] = 'This is the wallet created that hosts the sites account';
$helptxt[ 'bts_app_wallet_walletpass'] = 'This is the password to the wallet';
$helptxt[ 'bts_app_wallet_site_account'] = 'This is the name registered on the blockchain that will show up as the site name during the authentication process';
$helptxt[ 'bts_app_wallet_site_domain'] = 'This is the site domain.  Redundant from elsewhere.';


$txt['bts_app_register_unregistered'] = 'Allow reg. of Bitshares unregistered';
$txt['bts_app_printerrorsatfailure'] = 'Print Bitshares Wallet Error';
$txt['bts_reg_auto'] = 'Registration Method';
$txt['bts_dfbregauto1']  = 'Auto';
$txt['bts_dfbregauto']  = 'Manual';
$txt['bts_app_profilegp3'] = 'Import BitShares Avatar';
$txt['bts_app_profilegp2'] = 'Import';
$txt['bts_app_profilegp1'] = 'This Will Import Your BitShares Avatar';
$txt['bts_app_profilegp'] = 'From this page you can change your settings for BitShares.';
$txt['bts_app_enabledautolog'] = 'Enable Auto Login';
$txt['bts_error_notgpemaile'] = 'We caught an exception <br /><strong>Error Code</strong>: %1$s<br /> <strong>Message</strong>: Email is not a BitShares account';
$txt['fb_loguid'] = 'User ID';
$txt['bts_bitsharesreg'] ='BitShares Registration';
$txt['bts_bitsharesreg1'] ='User Name';
$txt['bts_bitsharesreg2'] ='Email - not verified but required';
$txt['bts_bitsharesreg3'] ='Password';
$txt['bts_bitsharesreg4'] ='Confirm Password';
$txt['bts_bitsharesreg5'] ='Register';
$txt['bts_bitshares'] ='BitShares';
$txt['bts__app_error1'] = 'BitShares is Disabled';
$txt['bts__app_error2'] = 'Did you try to skip authorization?';
$txt['bts__app_error3'] = 'No BitShares member data found';
$txt['bts_app_regmay'] = '(You may register with your BitShares account or complete the sections below for normal registration)';
$txt['bts_app_rwf'] = 'Register With BitShares';
$txt['bts_app_aso_account'] = 'Associate BitShares account';
$txt['bts_app_aso_account_confirm'] = 'Associate BitShares account?';
$txt['bts_app_diso_account'] = 'Disassociate BitShares account';
$txt['bts_app_diso_account_confirm'] = 'Disassociate BitShares account?';
$txt['bts_app_config'] = 'Configuration';
$txt['bts_app_regonlyonce4'] = 'The user name';
$txt['bts_app_regonlyonce5'] = 'was found in our database if this is your account <br />please enter you login details here to login and sync your account';
$txt['bts_app_regonlyonce'] = '(you only need to do this once)';
$txt['bts_app_regonlyonce1'] = 'Already registered? Click';
$txt['bts_app_regonlyonce2'] = 'Here';
$txt['bts_app_regonlyonce3'] = 'To Login';
$txt['bts_app_enabled'] = 'Enable BitShares';
$txt['bts_app_oauth'] = 'Oauth consumer key';
$txt['bts_app_oauth_secret'] = 'Oauth consumer secret';
$txt['bts_app_custon_logimg'] = 'Custom login Image';
$txt['bts_app_detait_gid'] = 'Default membergroup ID - blockchain registered';
$txt['bts_app_detait_gid2'] = 'Default membergroup ID - not registered';
$txt['bts_app_logs'] = 'Logs';
$txt['bts_app_logsnon'] = 'None';
$txt['bts_app_logs1'] = 'Member';
$txt['bts_app_logs2'] = 'BitShares name';
$txt['bts_app_logs3'] = 'BitShares id';
$txt['bts_app_logs4'] = 'Date Registered';
$txt['bts_app_logs5'] = 'BitShares Profile';
$txt['bts_app_logs6'] = 'Disassociate Selected';
$txt['bts_app_logs7'] = 'Disassociate All';


$txt[ 'bts_app_wallet_server'] = 'Wallet client server';
$txt[ 'bts_app_wallet_port'] = 'Wallet client port';
$txt[ 'bts_app_wallet_user'] = 'Wallet client RPC user';
$txt[ 'bts_app_wallet_pass'] = 'Wallet client RPC password';
$txt[ 'bts_app_wallet_walletname'] = 'Wallet Name';
$txt[ 'bts_app_wallet_walletpass'] = 'Wallet Password';
$txt[ 'bts_app_wallet_site_account'] = 'Blockchain Registered site account';
$txt[ 'bts_app_wallet_site_domain'] = 'Site domain';

?>