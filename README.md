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

Go into Sources/Bitshares/bitsharesClientApi.php and edit the following .  


private $RPC_SERVER_PORT = 57133; // Port in httpd_endpoint
private $RPC_SERVER_USER = "sitetest"; // this is rpc_user
private $RPC_SERVER_PASS = "sitetestPW"; // rpc_password
private $RPC_SERVER_WALLET = "test"; // wallet name
private $RPC_SERVER_WALLET_PASS = "genericE55IE"; // wallet password
private $BITSHARES_USER_NAME = "testingtoday"; // this is a registered account created on the servers wallet

Make sure curl is installed 
apt-get install php5-curl

Inside Sources/includes make sure easybitcoin.php is installed - TODO clean this up

----- Issues 
The admin configuration seems to have refresh issues due to caching.  The values are saved internally but will not show up then the Save button is clicked until the cache expires.




TODO -
Fix the domain reference in loginredirect.php
Look at token cookie and exceptions
Create a bug report after verifying behavior of the behavior requireing loginredirect.php

grep through looking for changes
gplus/gp/google/error_log at the end

TESTING LIST
Make sure SSL works
Make sure that you can not change the name when set to mamual login	

- potential TODO 
remove settings to config wallet and put them in BitSharesAdmin.php and modsettings
remove the password fields from the manual login
