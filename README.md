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

Optional - lower the max amounts of connections for the wallet so lower server load.

Go to the bitshares login screen and set it up as desired.  Help is available in the popups.

Make sure curl is installed 
apt-get install php5-curl

Note - You may consider turning off name changes.  Document this!

----- Issues 
The admin configuration seems to have refresh issues due to caching.  The values are saved internally but will not show up then the Save button is clicked until the cache expires. It is purely a display issue.

loginredirect.php has a hardcoded domain because it doesnt load up the full SMF environment


TODO -
Look at token cookie and exceptions
Create a bug report after verifying behavior of the behavior requireing loginredirect.php

grep through looking for changes
gplus/gp/google/error_log at the end

TESTING LIST
Make sure SSL works
Make sure that you can not change the name when set to mamual login	
check out behavior of token.. and logout etc.. does it operator as a boolean?