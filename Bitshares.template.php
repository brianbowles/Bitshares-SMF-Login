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
function template_main() {
}

function template_gppro() {
    global $context, $scripturl, $authUrl, $modSettings, $sc, $txt;
     bitshares_init_auth_url();   
	echo'
	<div class="cat_bar"> 
		<h3 class="catbg"> 
			<span class="ie6_header floatleft">
				'.$txt['bts_bitshares'].'
			</span> 
		</h3> 
	</div>';
	echo'<p class="windowbg description">'.$txt['bts_app_profilegp'].'</p> 
			<div class="windowbg2"> 
				<span class="topslice"><span></span></span> 
				<div class="content"> 
	       <dl class="register_form" >
		   <dt>';
			
		echo' 
				<dt><label for="impf">'.$txt['bts_app_profilegp3'].'<div class="smalltext">'.$txt['bts_app_profilegp1'].'</div></label></dt>
					<dd>
					<button type="button"  onclick="javascript:window.location.href = \''. $authUrl, '\';">'.$txt['bts_app_profilegp2'].'</button>
				</dd>
			</dl><hr />
		  <div align="center"><a href="'.$scripturl. '?action=bitshares;area=unsync;sesc='.$sc.'" onclick="return confirm(\''.$txt['bts_app_diso_account_confirm'].'\');">'.$txt['bts_app_diso_account'].'</a></div>
		</div>
				<span class="botslice"><span></span></span> 
			</div> ';
}

function template_bitshares_logsync() {
global $txt, $scripturl, $context;

    echo' 
	<form action="', $scripturl, '?action=login2;syncgp" name="frmLogin" id="frmLogin" method="post" accept-charset="', $context['character_set'], '" ', empty($context['disable_login_hashing']) ? ' onsubmit="hashLoginPassword(this, \'' . $context['session_id'] . '\');"' : '', '>';
	    echo' 
		<span class="upperframe"><span></span></span>
	        <div class="roundframe centertext">';
				if(isset($_GET['nt'])){
				     echo'<div class="error">'.$txt['bts_app_regonlyonce4'].' <strong>'.(!empty($_GET['u']) ? $_GET['u'] : '').'</strong> '.$txt['bts_app_regonlyonce5'].'</div><br />';
				}
				echo'
				<dl class="register_form" >
					<dt><strong>'.$txt['username'].':</strong></dt>
					<dd><input type="text" name="user" size="20" value="', $context['default_username'], '" class="input_text" /></dd>
					<dt><strong>'.$txt['password'].':</strong></dt>
					<dd><input type="password" name="passwrd" value="', $context['default_password'], '" size="20" class="input_password" /></dd>
				</dl>
			<p><input type="submit" value="', $txt['login'], '" class="button_submit" /></p>';
		echo'</div>
	    <span class="lowerframe"><span></span></span>
   </form>';
}
/* Print out user agreemet */
function template_bitshares_agree()
{
	global $context, $scripturl, $txt;

	echo '
		<form action="', $scripturl, '?action=bitshares;area=connect" method="post" accept-charset="', $context['character_set'], '" id="registration">
			<div class="cat_bar">
				<h3 class="catbg">', $txt['registration_agreement'], '</h3>
			</div>
			<span class="upperframe"><span></span></span>
			<div class="roundframe">
				<p>', $context['agreement'], '</p>
			</div>
			<span class="lowerframe"><span></span></span>
			<div id="confirm_buttons">
			<input type="submit" name="accept_agreement" value="', $txt['agreement_agree'], '" class="button_submit" />
			</div>
			<input type="hidden" name="accept_agreement" value="1" />
		</form>';
}
/* This is the code that gives extra fields when login mode is set to maual. */
function template_bitshares_cconnect() {
global $txt, $scripturl, $context;

echo'<form action="'.$scripturl.'?action=bitshares;area=connect;register" method="post" accept-charset="', $context['character_set'], '" >'; 
	   echo' 
	               <div class="cat_bar">
		              <h3 class="catbg">
		                 '.$txt['bts_bitsharesreg'].'
	                  </h3>
				   </div>';
				   
				  echo'<div class="information centertext"><strong>'.$txt['bts_app_regonlyonce'].'</strong><br /><br />
	            <strong>
	                  <div class="error">'.$txt['bts_app_regonlyonce1'].'<a href="'.$scripturl.'?action=bitshares;area=logsync"> '.$txt['bts_app_regonlyonce2'].'</a> '.$txt['bts_app_regonlyonce2'].'</div>
	            </strong></div>';
		
		echo'<span class="upperframe"><span></span></span>
	            <div class="roundframe centertext">';	   
       echo'<dl class="register_form" >
				<dt>
					<strong>'.$txt['bts_bitsharesreg1'].'</strong>
				</dt>
				<dd>
				<input type="text" name="real_name" value="'.$_SESSION['gplusdata']['name'] .'" readonly="readonly" style="color:#888;" />
				</dd>
			</dl>';
	  
	  echo'<dl class="register_form" >
			   <dt>
				    <strong>'.$txt['bts_bitsharesreg2'].'</strong>
			   </dt>
			   <dd>
					<input type="text" name="email" value="" />
			   </dd>
			</dl>';
   
      echo'<dl class="register_form" >
			   <dt>
					<strong>'.$txt['bts_bitsharesreg3'].'</strong>
			   </dt>
			   <dd>
					<input type="password" name="passwrd1" value="" />
			   </dd>
		   </dl>';
		   
      echo'<dl class="register_form" >
			   <dt>
					<strong>'.$txt['bts_bitsharesreg4'].'</strong>
			   </dt>
			   <dd>
					<input type="password" name="passwrd2" value="" />
			   </dd>
		   </dl>';
		   
		       echo'<input type="submit" name="submit" value="'.$txt['bts_bitsharesreg5'].'" />
			   <input type="hidden" name="accept_agreement" value="1" />';
					
	   echo'</div>
	          <span class="lowerframe"><span></span></span>';
}

function template_bitshares_log() {
    template_show_list('bts_list');
}

?>