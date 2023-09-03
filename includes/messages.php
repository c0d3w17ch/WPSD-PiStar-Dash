<?php

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';         // Version Lib
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code

$headers = stream_context_create(Array("http" => Array("method"  => "GET",
                                                       "timeout" => 10,
                                                       "header"  => "User-agent: WPSD-Messages - $versionCmd",
                                                       'request_fulluri' => True )));
// older wpsd with very old uuid scheme
$UUID = $_SESSION['PiStarRelease']['Pi-Star']['UUID'];
$uuidNeedle = "-";
if (strpos($UUID, $uuidNeedle) !== false) {
    $result = @file_get_contents('https://repo.w0chp.net/WPSD-Dev/WPSD_Messages/raw/branch/master/update-req-uuid.html', false, $headers);
    echo $result;
}

// F1RMB detected
if( strpos(file_get_contents("/etc/pistar-release"),"-RMB") !== false) {
    $result = @file_get_contents('https://repo.w0chp.net/WPSD-Dev/WPSD_Messages/raw/branch/master/f1rmb-detected.html', false, $headers);
    echo $result;
}
?>
