<?PHP
    // This is a hack to recreate the needed URL variables in the GET

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

    // ok so location.href forces you to same domain.. so only concatenates the end...
    // So we strip out the domain + logindirect
	// TODO fix this...  figure this out and submit bug report
    // until then weve left hostname hardcoded to not load up the full smf context to read variables..
    $absolute_url =  str_replace("bitsharesnation.org/loginredirect.php?","index.php?action=bitshares&",$absolute_url);

 ?>

	   <script type="text/javascript">location.href="<?=$absolute_url?>";</script> 
        <?PHP


?>
