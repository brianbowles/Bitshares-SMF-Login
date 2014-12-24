This is based on the original Google Plus plugin for SMF.
http://custom.simplemachines.org/mods/index.php?mod=3278
aka 
Mod Name: SA Google+ intergration
Created By: SMFHacks.com Team 

------

You will need to install the Bitshares command line client and run it somewhere accessible to the forum.
Visit wiki.bitshares.org or bitsharestalk.org for this information.

Once installed edit the .Bitshares/config.json file and find this portion

  "rpc": {
    "enable": true,
    "rpc_user": "sitetest",
    "rpc_password": "sitetestPW",
    "rpc_endpoint": "127.0.0.1:13175",
    "httpd_endpoint": "127.0.0.1:57133",
    "htdocs": "./htdocs"

Add a rpc_user and rpc_password.  Turn enable to true.  Note the port number on httpd_endpoint

Run the client. Create a wallet and site account. Register the site account.

Optional - lower the max amounts of connections for the wallet so lower server load if that is an issue.

Go to the bitshares administration screen and set it up as desired.  Help is available in the popups.

Make sure curl is installed 
apt-get install php5-curl

Create a login button or copy the one out of the Package to a desired hosting location.  Change the url to reflect this inside the package.

Optional - A lot of SMF installations allow users to change their username.  If you allow this a person can impersonate others by registering under a blockchain registered name then changing their name. If you want trust in the username's created into the Bitshares membergroups then this functionality will need ot be disabled.  Find this in Allow users to edit their displayed name under Admin/Configuration/General

----- Issues 
The admin configuration seems to have refresh issues due to caching.  The values are saved internally but will not show up then the Save button is clicked until the cache expires. It is purely a display issue so very low priority.

loginredirect.php has a hardcoded domain because it doesnt load up the full SMF environment


TODO -
Look at token cookie and exceptions
Create a bug report after verifying behavior of the behavior requireing loginredirect.php

grep through looking for changes
gplus/gp/google/error_log/gsetting at the end

TESTING LIST
Make sure SSL works
Make sure that you can not change the name when set to mamual login	
check out behavior of token.. and logout etc.. does it operator as a boolean?
check out the syncing of accounts when an account already exists

Ok create a fresh account. Fail the email login then try it again.  It created an account but gave me a strange error.