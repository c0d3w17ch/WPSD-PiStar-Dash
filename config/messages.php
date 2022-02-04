<?php

$headers = stream_context_create(Array("http" => Array("method"  => "GET",
                                                       "timeout" => 1,
                                                       "header"  => "User-agent: WPSD-Messages",
                                                       'request_fulluri' => True /* without this option we get an HTTP error! */
				)));


$min_ver = "4.1.6";

$string = $_SESSION['PiStarRelease']['Pi-Star']['Version'];
if ($string < $min_ver) {
	$result = @file_get_contents('https://w0chp.net/WPSD/upgrade_required.html', false, $headers);
	echo $result;
}

?>