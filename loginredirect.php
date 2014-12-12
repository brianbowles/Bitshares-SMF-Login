<//-- This is a proof of concept web application for BitShares logins. -->
<?PHP


    //This code adapted from http://stackoverflow.com/questions/6768793/get-the-full-url-in-php
    function url_origin($s, $use_forwarded_host=false)
    {
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $host;
    }
    function full_url($s, $use_forwarded_host=false)
    {
        return url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];
    }
    $absolute_url = full_url($_SERVER);

    error_log($absolute_url."\n", 3, "/tmp/php.log");
    // ok so location.href forces you to same domain.. so only concatenates the end...

    // really we should just not have btsxtalk.org in the absolute_url but im too lazy to fix a hack

	// TODO fix this...  it is strange how we hacked this into working.. figure this out and submit bug report
    $absolute_url =  str_replace("bitsharesnation.org/loginredirect.php?","index.php?action=gplus&",$absolute_url);

//    error_log($absolute_url."\n", 3, "/tmp/php.log");

 ?>

	   <script type="text/javascript">location.href="<?=$absolute_url?>";</script> 
        <?PHP


?>
