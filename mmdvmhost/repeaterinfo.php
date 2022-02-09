<?php

if (!isset($_SESSION) || !is_array($_SESSION)) {
    session_id('pistardashsess');
    session_start();
    
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
    include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';        // Translation Code
    checkSessionValidity();
}

include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';          // MMDVMDash Config
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/tools.php';        // MMDVMDash Tools
include_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';    // MMDVMDash Functions
include_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';	      // Translation Code
require_once($_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php');

// Check if the config file exists
if (file_exists('/etc/pistar-css.ini')) {
    // Use the values from the file
    $piStarCssFile = '/etc/pistar-css.ini';
    if (fopen($piStarCssFile,'r')) {
        $piStarCss = parse_ini_file($piStarCssFile, true);
        // Set the Values from the config file
        if (isset($piStarCss['Background']['TableRowBgEvenColor'])) {
            $tableRowEvenBg = $piStarCss['Background']['TableRowBgEvenColor'];
        } else {
            // Default values
            $tableRowEvenBg = "#FFFFFF";
        }
    }
} else { // no css file...
    // Default values
    $tableRowEvenBg = "#FFFFFF";
}

function FillConnectionHosts(&$destArray, $remoteEnabled, $remotePort) {
    if (($remoteEnabled == 1) && ($remotePort != 0)) {
	$remoteOutput = null;
	$remoteRetval = null;
	exec('cd /var/log/pi-star; /usr/local/bin/RemoteCommand '.$remotePort.' hosts', $remoteOutput, $remoteRetval);
	if (($remoteRetval == 0) && (count($remoteOutput) >= 2)) {
	    $expOutput = preg_split('/"[^"]*"(*SKIP)(*F)|\x20/', $remoteOutput[1]);
	    foreach ($expOutput as $entry) {
		$keysValues = explode(":", $entry);
		$destArray[$keysValues[0]] = $keysValues[1];
	    }
	}
    }
}

function FillConnectionStatus(&$destArray, $remoteEnabled, $remotePort) {
    if (($remoteEnabled == 1) && ($remotePort != 0)) {
	$remoteOutput = null;
	$remoteRetval = null;
	exec('cd /var/log/pi-star; /usr/local/bin/RemoteCommand '.$remotePort.' status', $remoteOutput, $remoteRetval);
	if (($remoteRetval == 0) && (count($remoteOutput) >= 2)) {
	    $tok = strtok($remoteOutput[1], " \n\t");
	    while ($tok !== false) {
		$keysValues = explode(":", $tok);
		$destArray[$keysValues[0]] = $keysValues[1];
		$tok = strtok(" \n\t");
	    }
	}
    }
}

function GetActiveConnectionStyle($masterStates, $key) {
    global $tableRowEvenBg;
    if (count($masterStates)) {
	    if (isset($masterStates[$key])) {
	        if (($masterStates[$key] == "n/a") || ($masterStates[$key] == "disc")) {
		        return "class=\"inactive-mode-cell\"";
	        }
	    }
    }
    return "style='background: $tableRowEvenBg;'";
}

//
// Grab networks status from remote commands
//
$remoteMMDVMResults = [];
$remoteDMRGResults = [];
$remoteYSFGResults = [];
$remoteP25GResults = [];
$remoteNXDNGResults = [];
$remoteM17GResults = [];

if (isProcessRunning("MMDVMHost")) {
    $cfgItemEnabled = getConfigItem("Remote Control", "Enable", $_SESSION['MMDVMHostConfigs']);
    $cfgItemPort = getConfigItem("Remote Control", "Port", $_SESSION['MMDVMHostConfigs']);
    FillConnectionStatus($remoteMMDVMResults, (isset($cfgItemEnabled) ? $cfgItemEnabled : 0), (isset($cfgItemPort) ? $cfgItemPort : 0));
}

if (isProcessRunning("DMRGateway")) {
    $remoteCommandEnabled = (isset($_SESSION['DMRGatewayConfigs']['Remote Control']) ? $_SESSION['DMRGatewayConfigs']['Remote Control']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['DMRGatewayConfigs']['Remote Control']) ? $_SESSION['DMRGatewayConfigs']['Remote Control']['Port'] : 0);
    FillConnectionStatus($remoteDMRGResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("YSFGateway")) {
    $remoteCommandEnabled = (isset($_SESSION['YSFGatewayConfigs']['Remote Commands']) ? $_SESSION['YSFGatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['YSFGatewayConfigs']['Remote Commands']) ? $_SESSION['YSFGatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteYSFGResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("P25Gateway")) {
    $remoteCommandEnabled = (isset($_SESSION['P25GatewayConfigs']['Remote Commands']) ? $_SESSION['P25GatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['P25GatewayConfigs']['Remote Commands']) ? $_SESSION['P25GatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteP25GResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("NXDNGateway")) {
    $remoteCommandEnabled = (isset($_SESSION['NXDNGatewayConfigs']['Remote Commands']) ? $_SESSION['NXDNGatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['NXDNGatewayConfigs']['Remote Commands']) ? $_SESSION['NXDNGatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteNXDNGResults, $remoteCommandEnabled, $remoteCommandPort);
}

if (isProcessRunning("M17Gateway")) {
    $remoteCommandEnabled = (isset($_SESSION['M17GatewayConfigs']['Remote Commands']) ? $_SESSION['M17GatewayConfigs']['Remote Commands']['Enable'] : 0);
    $remoteCommandPort = (isset($_SESSION['M17GatewayConfigs']['Remote Commands']) ? $_SESSION['M17GatewayConfigs']['Remote Commands']['Port'] : 0);
    FillConnectionStatus($remoteM17GResults, $remoteCommandEnabled, $remoteCommandPort);
}

?>

<div class="divTable">
  <div class="divTableHead"><?php echo $lang['modes_enabled'];?></div>
  <div class="divTableBody">
    <div class="divTableRow">
      <div class="divTableCell">
	<?php if (isPaused("D-Star")) { echo '<div class="paused-mode-cell" title="Mode Paused">D-Star</div>'; } else {showMode("D-Star", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
      <div class="divTableCell">
	<?php if (isPaused("DMR")) { echo '<div class="paused-mode-cell" title="Mode Paused">DMR</div>'; } else { showMode("DMR", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
	<?php if (isPaused("YSF")) { echo '<div class="paused-mode-cell" title="Mode Paused">YSF</div>'; } else { showMode("System Fusion", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
      <div class="divTableCell">
	<?php if (isPaused("P25")) { echo '<div class="paused-mode-cell" title="Mode Paused">P25</div>'; } else { showMode("P25", $_SESSION['MMDVMHostConfigs']); }?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
	<?php showMode("YSF X-Mode", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
	<?php if (isPaused("NXDN")) { echo '<div class="paused-mode-cell" title="Mode Paused">NXDN</div>'; } else { showMode("NXDN", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
	<?php showMode("DMR X-Mode", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
	<?php if (isPaused("POCSAG")) { echo '<div class="paused-mode-cell" title="Mode Paused">POCSAG</div>'; } else { showMode("POCSAG", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
	<?php if (isPaused("M17")) { echo '<div class="paused-mode-cell" title="Mode Paused">M17</div>'; } else { showMode("M17", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
      <div class="divTableCell">
	<?php if (isPaused("AX 25")) { echo '<div class="paused-mode-cell" title="Mode Paused">AX.25</div>'; } else { showMode("AX 25", $_SESSION['MMDVMHostConfigs']); } ?>
      </div>
    </div>
  </div>
</div>

<br />

<div class="divTable">
  <div class="divTableHead"><?php echo $lang['net_status'];?></div>
  <div class="divTableBody">
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("D-Star Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php showMode("DMR Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("System Fusion Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php if (isPaused("YSF")) { echo '<div class="paused-mode-cell" title="YSF Mode Paused">DG-ID Net</div>'; } else { showMode("DG-ID Network", $_SESSION['DGIdGatewayConfigs']); } ?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("YSF2DMR Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php showMode("YSF2NXDN Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("YSF2P25 Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php showMode("P25 Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("NXDN Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php showMode("M17 Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("DMR2NXDN Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php showMode("DMR2YSF Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
    </div>
    <div class="divTableRow">
      <div class="divTableCell">
        <?php showMode("POCSAG Network", $_SESSION['MMDVMHostConfigs']);?>
      </div>
      <div class="divTableCell">
        <?php if (isPaused("APRS")) { echo '<div class="paused-mode-cell" title="Service Paused">APRS Net</div>'; } else { showMode("APRS Network", $_SESSION['APRSGatewayConfigs']); }?>
      </div>
    </div>
  </div>
</div>

<br />

<div class="divTable">
  <div class="divTableHead"><?php echo $lang['radio_info'];?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class="divTableHeadCell">TX/RX</div>
      <div class="divTableCell hwinfo">
        <?php
        // TRX Status code
        if (isset($lastHeard[0])) {
            $isTXing = false;

            // Go through the whole LH array, backward, looking for transmission.
            for (end($lastHeard); (($currentKey = key($lastHeard)) !== null); prev($lastHeard)) {                                                                         
                    $listElem = current($lastHeard);
                    if ($listElem[2] && ($listElem[6] == null) && ($listElem[5] !== 'RF')) {                                                                              
                        $isTXing = true;
                        // Get rid of 'Slot x' for DMR, as it is meaningless, when 2 slots are txing at the same time.
                        $txMode = preg_split('#\s+#', $listElem[1])[0];
                        echo "<div style=\"background:#F012BE; color:#ffffff; font-weight:bold;\">TX: $txMode</div>";
                        break;
                    }
            }
            if ($isTXing == false) {
                    $listElem = $lastHeard[0];
                if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'idle') {
                    echo "<div style=\"background:#0b0; color:#000;font-weight:bold\">Idle</div>";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === NULL) {
                    if (isProcessRunning("MMDVMHost")) {
                        echo "<div style=\"background:#0b0; color:#000;font-weight:bold\">Idle</div>";
                    }
                    else {
                        echo "<div style=\"background:#606060; color:#b0b0b0;font-weight:bold\">OFFLINE</div>";
                    }
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'D-Star') {
                    echo "<div style=\"background:#4aa361;font-weight:bold\">RX: D-Star</div>";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'D-Star') {
                    echo "<div style=\"background:#ade;font-weight:bold\">Standby: D-Star</div>";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'DMR') {
                    echo "<div style=\"background:#4aa361; color:#ffffff; font-weight:bold\">RX: DMR</div>";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'DMR') {
                    echo "<div style=\"background:#f93;font-weight:bold\">Standby: DMR</div>";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'YSF') {
                    echo "<div style=\"background:#4aa361; color:#ffffff; font-weight:bold\">RX: YSF</div>";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'YSF') {
                    echo "<div style=\"background:#ff9;font-weight:bold\">Standby: YSF</div>";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'P25') {
                    echo "<div style=\"background:#4aa361;font-weight:bold\">RX: P25</div>";
                }
                else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'M17') {
                    echo "<div style=\"background:#4aa361;\">RX M17</div>";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'M17') {
                    echo "<div style=\"background:#c9f;\">Listening M17</div>";
                }
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'P25') {
                    echo "<div style=\"background:#f9f;font-weight:bold\">Standby: P25</div>"; 
                }   
                        else if ($listElem[2] && $listElem[6] == null && getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'NXDN') {
                    echo "<div style=\"background:#4aa361;font-weight:bold\">RX: NXDN</div>";
                }   
                else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'NXDN') {
                    echo "<div style=\"background:#c9f;font-weight:bold\">Standby: NXDN</div>"; 
                }   
                        else if (getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs']) === 'POCSAG') {
                    echo "<div style=\"color:#fff; background:#F012BE; font-weight:bold\">POCSAG Activity</div>";
                }   
                else {
                    echo "<div>".getActualMode($lastHeard, $_SESSION['MMDVMHostConfigs'])."</div>";
                }   
            }   
        }   
        else {
            echo "<div style=\"background:#0b0; color:#000;font-weight:bold\">Idle</div>";
        }
        ?>
      </div>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">TX</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getMHZ(getConfigItem("Info", "TXFrequency", $_SESSION['MMDVMHostConfigs'])); ?></div>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">RX</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getMHZ(getConfigItem("Info", "RXFrequency", $_SESSION['MMDVMHostConfigs'])); ?></div>
    </div>
    <?php
        if (isset($_SESSION['DvModemFWVersion'])) {
    ?>
    <div class="divTableRow center">
      <div class="divTableHeadCell">FW</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo $_SESSION['DvModemFWVersion']; ?></div>
    </div>
    <?php } ?>
    <div class="divTableRow center">
      <div class="divTableHeadCell">TCXO</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo $_SESSION['DvModemTCXOFreq']; ?></div>
    </div>
  </div>
</div>

<br />

	<?php
	$testMMDVModeDSTAR = getConfigItem("D-Star", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( $testMMDVModeDSTAR == 1 || isPaused("D-Star") ) { //Hide the D-Star Reflector information when D-Star Network not enabled.
 	    $linkedTo = getActualLink($reverseLogLinesMMDVM, "D-Star");
	?>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dstar_repeater'];?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class="divTableHeadCell">RPT1</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo str_replace(' ', '&nbsp;', $_SESSION['DStarRepeaterConfigs']['callsign']); ?></div>
    </div>    
    <div class="divTableRow center">
      <div class="divTableHeadCell">RPT2</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo str_replace(' ', '&nbsp;', $_SESSION['DStarRepeaterConfigs']['gateway']); ?></div>
    </div>	
  </div>
</div>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dstar_net']; ?></div>
  <div class="divTableBody">
    <?php
        if (isPaused("D-Star")) {
                echo "<div class='divTableRow center'><div class='divTableCell hwinfo' style=\"background: $tableRowEvenBg;\">Mode Paused</div></div>\n";                 
        } else {
                echo "<div class='divTableRow center'><div class='divTableCell hwinfo' ".GetActiveConnectionStyle($remoteMMDVMResults, "dstar")." title=\"".$linkedTo."\">".$linkedTo."</div></div>\n";
        }
        if ($_SESSION['ircDDBConfigs']['aprsEnabled'] == 1) {
	        echo "<div class='divTableRow center'><div class='divTableHeadCell'>APRS</div></div><div class='divTableRow center'><div class='divTableCell hwinfo' style=\"background: $tableRowEvenBg;\">".substr($_SESSION['ircDDBConfigs']['aprsHostname'], 0, 18)."</div></div>\n";
        }
        if ($_SESSION['ircDDBConfigs']['ircddbEnabled'] == 1) {
	        echo "<div class='divTableRow center'><div class='divTableHeadCell'>ircDDB</div></div><div class='divTableRow center'><div class='divTableCell hwinfo' style=\"background: $tableRowEvenBg;\">".substr($_SESSION['ircDDBConfigs']['ircddbHostname'], 0 ,18)."</div></div>\n";
        }
	?>
  </div>
</div>

<br />

<?php 

}
	$testMMDVModeDMR = getConfigItem("DMR", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( $testMMDVModeDMR == 1 || isPaused("DMR") ) { //Hide the DMR information when DMR mode not enabled.
		if (isPaused("DMR")) {
			$dmrMasterHost = "Mode Paused";
			$dmrMasterHostTooltip = $dmrMasterHost;
		} else {
	    $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
	    $dmrMasterHost = getConfigItem("DMR Network", "Address", $_SESSION['MMDVMHostConfigs']);
	    $dmrMasterPort = getConfigItem("DMR Network", "Port", $_SESSION['MMDVMHostConfigs']);
	    if ($dmrMasterHost == '127.0.0.1') {
		if (isset($_SESSION['DMRGatewayConfigs']['XLX Network 1']['Address'])) {
		    $xlxMasterHost1 = $_SESSION['DMRGatewayConfigs']['XLX Network 1']['Address'];
		}
		else {
		    $xlxMasterHost1 = "";
		}
		$dmrMasterHost1 = $_SESSION['DMRGatewayConfigs']['DMR Network 1']['Address'];
		$dmrMasterHost2 = $_SESSION['DMRGatewayConfigs']['DMR Network 2']['Address'];
		$dmrMasterHost3 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 3']['Name']);
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name'])) {
		    $dmrMasterHost4 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 4']['Name']);
		}
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name'])) {
		    $dmrMasterHost5 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 5']['Name']);
		}
		if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 6']['Name'])) {
		    $dmrMasterHost6 = str_replace('_', ' ', $_SESSION['DMRGatewayConfigs']['DMR Network 6']['Name']);
		}
        if (isset($configdmrgateway['DMR Network 6']['Name'])) {$dmrMasterHost6 = str_replace('_', ' ', $configdmrgateway['DMR Network 6']['Name']);}
		while (!feof($dmrMasterFile)) {
		    $dmrMasterLine = fgets($dmrMasterFile);
		    $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		    if ((count($dmrMasterHostF) >= 2) && (strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if ((strpos($dmrMasterHostF[0], 'XLX_') === 0) && ($xlxMasterHost1 == $dmrMasterHostF[2])) {
			    $xlxMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
			if ((strpos($dmrMasterHostF[0], 'BM_') === 0) && ($dmrMasterHost1 == $dmrMasterHostF[2])) {
			    $dmrMasterHost1 = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
			if ((strpos($dmrMasterHostF[0], 'DMR+_') === 0) && ($dmrMasterHost2 == $dmrMasterHostF[2])) {
			    $dmrMasterHost2 = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
		    }
		}
		
		$xlxMasterHost1Tooltip = $xlxMasterHost1;
		$dmrMasterHost1Tooltip = $dmrMasterHost1;
		$dmrMasterHost2Tooltip = $dmrMasterHost2;
		$dmrMasterHost3Tooltip = $dmrMasterHost3;
		if (isset($dmrMasterHost4)) {
		    $dmrMasterHost4Tooltip = $dmrMasterHost4;
		}
		if (isset($dmrMasterHost5)) {
		    $dmrMasterHost5Tooltip = $dmrMasterHost5;
		}
        if (isset($dmrMasterHost6)) {
            $dmrMasterHost6Tooltip = $dmrMasterHost6;
        }
		if (strlen($xlxMasterHost1) > 20) {
		    $xlxMasterHost1 = substr($xlxMasterHost1, 0, 15) . '..';
		}
		if (strlen($dmrMasterHost1) > 20) {
		    $dmrMasterHost1 = substr($dmrMasterHost1, 0, 15) . '..';
		}
		if (strlen($dmrMasterHost2) > 20) {
		    $dmrMasterHost2 = substr($dmrMasterHost2, 0, 15) . '..';
		}
		if (strlen($dmrMasterHost3) > 20) {
		    $dmrMasterHost3 = substr($dmrMasterHost3, 0, 15) . '..';
		}
		if (isset($dmrMasterHost4)) {
		    if (strlen($dmrMasterHost4) > 20) {
			    $dmrMasterHost4 = substr($dmrMasterHost4, 0, 15) . '..';
		    }
		}
		if (isset($dmrMasterHost5)) {
		    if (strlen($dmrMasterHost5) > 20) {
			    $dmrMasterHost5 = substr($dmrMasterHost5, 0, 15) . '..';
		    }
		}
        if (isset($dmrMasterHost6)) { if (strlen($dmrMasterHost6) > 20) { $dmrMasterHost6 = substr($dmrMasterHost6, 0, 15) . '..'; } }
	    }
	    else {
		while (!feof($dmrMasterFile)) {
		    $dmrMasterLine = fgets($dmrMasterFile);
                    $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
		    if ((count($dmrMasterHostF) >= 4) && (strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
			if (($dmrMasterHost == $dmrMasterHostF[2]) && ($dmrMasterPort == $dmrMasterHostF[4])) {
			    $dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]);
			}
		    }
		}
		$dmrMasterHostTooltip = $dmrMasterHost;
		if (strlen($dmrMasterHost) > 20) {
		    $dmrMasterHost = substr($dmrMasterHost, 0, 15) . '..';
		}
	    }
	    fclose($dmrMasterFile);
	    }
	    ?>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dmr_repeater'];?></div>
  <div class="divTableBody">
    <div class="divTableRow center">
      <div class="divTableHeadCell">DMR ID</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getConfigItem("General", "Id", $_SESSION['MMDVMHostConfigs']); ?></div>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">DMR CC</div>
      <div class="divTableCell hwinfo" style="background: <?php echo $tableRowEvenBg; ?>;"><?php echo getConfigItem("DMR", "ColorCode", $_SESSION['MMDVMHostConfigs']); ?></div>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">TS1</div>
	    <?php
	    if (getConfigItem("DMR Network", "Slot1", $_SESSION['MMDVMHostConfigs']) == 1) {
		    echo "<div class='divTableCell hwinfo'><div class=\"active-mode-cell\" title='Time Slot 1 Enabled'>".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."</div></div>\n";
		    //echo "<tr><td style=\"background: $tableRowEvenBg;\" colspan=\"2\">".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."/".substr(getActualReflector($reverseLogLinesMMDVM, "DMR Slot 1"), -10)."</td></tr>\n";    }
	    } else {
		    echo "<div class='divTableCell hwinfo'><div class=\"inactive-mode-cell\" title='Time Slot 1 disabled'>Disabled</div></div>\n";
	    }
	    ?>
    </div>
    <div class="divTableRow center">
      <div class="divTableHeadCell">TS2</div>
           <?php
	    if (getConfigItem("DMR Network", "Slot2", $_SESSION['MMDVMHostConfigs']) == 1) {
		    echo "<div class='divTableCell hwinfo'><div class=\"active-mode-cell\" title='Time Slot 2 Enabled'>".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."</div></div>\n";
		    //echo "<tr><td style=\"background: $tableRowEvenBg;\" colspan=\"2\">".substr(getActualLink($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."/".substr(getActualReflector($reverseLogLinesMMDVM, "DMR Slot 2"), -10)."</td></tr>\n"    }
	    } else {
		    echo "<div class='divTableCell hwinfo'><div class=\"inactive-mode-cell\" title='Time Slot 2 disabled'>Disabled</div></div>>\n";
	    }
	    ?>
    </div>
  </div>
</div>
<div class="divTable">
  <div class="divTableHead"><?php echo $lang['dmr_master'];?></div>
  <div class="divTableBody">
	    <?php
	    if (getEnabled("DMR Network", $_SESSION['MMDVMHostConfigs']) == 1) {
		if ($dmrMasterHost == '127.0.0.1') {
		    if (isProcessRunning("DMRGateway")) {
			if ( !isset($_SESSION['DMRGatewayConfigs']['XLX Network 1']['Enabled']) && isset($_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled']) && $_SESSION['DMRGatewayConfigs']['XLX Network']['Enabled'] == 1) {
			    $xlxMasterHostLinkState = "";
			    
                            if (file_exists("/var/log/pi-star/DMRGateway-".gmdate("Y-m-d").".log")) {
				$xlxMasterHostLinkState = exec('grep \'XLX, Linking\|XLX, Unlinking\|XLX, Logged\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d").'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\'');
			    }
			    else {
				$xlxMasterHostLinkState = exec('grep \'XLX, Linking\|XLX, Unlinking\|XLX, Logged\' /var/log/pi-star/DMRGateway-'.gmdate("Y-m-d", time() - 86340).'.log | tail -1 | awk \'{print $5 " " $8 " " $9}\'');
			    }
			    
			    if ($xlxMasterHostLinkState != "") {
				if ( strpos($xlxMasterHostLinkState, 'Linking') !== false ) {
				    $xlxMasterHost1 = str_replace('Linking ', '', $xlxMasterHostLinkState);
				}
				else if ( strpos($xlxMasterHostLinkState, 'Unlinking') !== false ) {
				    $xlxMasterHost1 = "XLX Not Linked";
				}
				else if ( strpos($xlxMasterHostLinkState, 'Logged') !== false ) {
				    $xlxMasterHost1 = "XLX Not Linked";
				}
			    }
			    else {
				// There is no trace of XLX in the logfile.
				$xlxMasterHost1 = "".$xlxMasterHost1." ".$_SESSION['DMRGatewayConfigs']['XLX Network']['Module']."";
			    }
			    
			    echo "<div class='divTableRow center'><div class='divTableCell hwinfo'><div " .GetActiveConnectionStyle($remoteDMRGResults, "xlx")." title=\"".$xlxMasterHost1Tooltip."\">".$xlxMasterHost1."</div></div></div>\n";
			}
			if ($_SESSION['DMRGatewayConfigs']['DMR Network 1']['Enabled'] == 1) {
			    echo "<div class='divTableRow center'><div class='divTableCell hwinfo'><div " .GetActiveConnectionStyle($remoteDMRGResults, "net1")." title=\"".$dmrMasterHost1Tooltip."\">".$dmrMasterHost1."</div></div></div>\n";
			}
			if ($_SESSION['DMRGatewayConfigs']['DMR Network 2']['Enabled'] == 1) {
			    echo "<div class='divTableRow center'><div class='divTableCell hwinfo'<div ".GetActiveConnectionStyle($remoteDMRGResults, "net2")." title=\"".$dmrMasterHost2Tooltip."\">".$dmrMasterHost2."</div></div></div>\n";
			}
			if ($_SESSION['DMRGatewayConfigs']['DMR Network 3']['Enabled'] == 1) {
			    echo "<div class='divTableRow center'><div class='divTableCell hwinfo'><div ".GetActiveConnectionStyle($remoteDMRGResults, "net3")." title=\"".$dmrMasterHost3Tooltip."\">".$dmrMasterHost3."</div></div></div>\n";
			}
			if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'])) {
			    if ($_SESSION['DMRGatewayConfigs']['DMR Network 4']['Enabled'] == 1) {
				echo "<div class='divTableRow center'><div class='divTableCell hwinfo'><div ".GetActiveConnectionStyle($remoteDMRGResults, "net4")." title=\"".$dmrMasterHost4Tooltip."\">".$dmrMasterHost4."</div></div></div>\n";
			    }
			}
			if (isset($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'])) {
			    if ($_SESSION['DMRGatewayConfigs']['DMR Network 5']['Enabled'] == 1) {
				echo "<div class='divTableRow center'><div class='divTableCell hwinfo'><div ".GetActiveConnectionStyle($remoteDMRGResults, "net5")." title=\"".$dmrMasterHost5Tooltip."\">".$dmrMasterHost5."</div></div></div>\n";
			    }
			}
		    }
		    else {
			echo "<div class='divTableRow center'><div class='divTableCell hwinfo' style=\"background: $tableRowEvenBg;\">Service Not Started</div></div>\n";
		    }
		}
		else {
		    echo "<div class='divTableRow center'><div class='divTableCell hwinfo'><div ".GetActiveConnectionStyle($remoteDMRGResults, "dmr")." title=\"".$dmrMasterHostTooltip."\">".$dmrMasterHost."</div></div></div>\n";
		}
	    }
	    else {
		echo "<div class='divTableRow center'><div class='divTableCell hwinfo' style=\"background:#606060; color:#b0b0b0;\">No DMR Network</div></div>\n";
	    }
        ?>
      </div>
    </div>
<br />
<?php
}

	$testMMDVModeYSF = getConfigItem("System Fusion Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( isset($_SESSION['DMR2YSFConfigs']['Enabled']['Enabled']) ) {
	    $testDMR2YSF = $_SESSION['DMR2YSFConfigs']['Enabled']['Enabled'];
	}
	if ( $testMMDVModeYSF == 1 || isPaused("YSF") || (isset($testDMR2YSF) && $testDMR2YSF == 1) ) { //Hide the YSF information when System Fusion Network mode not enabled.
		if (isPaused("YSF")) {
			$ysfLinkedTo = "Mode Paused";
			$ysfLinkStateTooltip = $ysfLinkedTo;
		} else {
            $ysfLinkedTo = getActualLink($reverseLogLinesYSFGateway, "YSF");
		}
	    if ($ysfLinkedTo == 'Not Linked' || $ysfLinkedTo == 'Service Not Started') {
                $ysfLinkedToTxt = $ysfLinkedTo;
		$ysfLinkState = '';
		$ysfLinkStateTooltip = $ysfLinkedTo;
	    }
	    else {
                $ysfHostFile = fopen("/usr/local/etc/YSFHosts.txt", "r");
                $ysfLinkedToTxt = "null";
                while (!feof($ysfHostFile)) {
                    $ysfHostFileLine = fgets($ysfHostFile);
                    $ysfRoomTxtLine = preg_split('/;/', $ysfHostFileLine);
		    
		    if (empty($ysfRoomTxtLine[0]) || empty($ysfRoomTxtLine[1]))
			continue;
		    
                    if (($ysfRoomTxtLine[0] == $ysfLinkedTo) || ($ysfRoomTxtLine[1] == $ysfLinkedTo)) {
			$ysfRoomNo = "YSF".$ysfRoomTxtLine[0];
                        $ysfLinkedToTxt = $ysfRoomTxtLine[1];
                        break;
                    }
                }
		fclose($ysfHostFile);
                $fcsHostFile = fopen("/usr/local/etc/FCSHosts.txt", "r");
                $ysfLinkedToTxt = "null";
                while (!feof($fcsHostFile)) {
                    $ysfHostFileLine = fgets($fcsHostFile);
                    $ysfRoomTxtLine = preg_split('/;/', $ysfHostFileLine);

                    if (empty($ysfRoomTxtLine[0]) || empty($ysfRoomTxtLine[1]))
                        continue;

                    if (($ysfRoomTxtLine[0] == $ysfLinkedTo) || ($ysfRoomTxtLine[1] == $ysfLinkedTo)) {
                        $ysfLinkedToTxt = $ysfRoomTxtLine[1];
			$ysfRoomNo = $ysfRoomTxtLine[0];
                        break;
                    }
                }
		fclose($fcsHostFile);

		if ($ysfLinkedToTxt != "null") {
		    //$ysfLinkedToTxt = "Room: ".$ysfLinkedToTxt;
		    $ysfLinkState = ' [In Room]';
		    $ysfLinkStateTooltip = 'In Room: ';
		}
		else {
		    //$ysfLinkedToTxt = "Linked to: ".$ysfLinkedTo;
		    $ysfLinkedToTxt = $ysfLinkedTo;
		    $ysfLinkState = ' [Linked]';
		    $ysfLinkStateTooltip = 'Linked to ';
		}
		
                $ysfLinkedToTxt = str_replace('_', ' ', $ysfLinkedToTxt);
            }

            if (empty($ysfRoomNo) || ($ysfRoomNo == "null")) {
	        $ysfTableData = $ysfLinkedToTxt;
            } else {
                $ysfTableData = $ysfLinkedToTxt."<br />(".$ysfRoomNo.")";
	    }
	    $ysfLinkedToTooltip = $ysfLinkStateTooltip.$ysfLinkedToTxt;
            if (strlen($ysfLinkedToTxt) > 20) {
		$ysfLinkedToTxt = substr($ysfLinkedToTxt, 0, 15) . '..';
	    }
        echo "<table>\n";
        if (isPaused("YSF")) {
	    echo "<tr><th colspan=\"2\">".$lang['ysf_net']."</th></tr>\n";
        } else {
            echo "<tr><th colspan=\"2\">".$lang['ysf_net']."".$ysfLinkState."</th></tr>\n";
        }
	echo "<tr><td colspan=\"2\" ".GetActiveConnectionStyle($remoteYSFGResults, "ysf")." title=\"".$ysfLinkedToTooltip."\">".$ysfTableData."</td></tr>\n";
        echo "</table>\n";
	}

    if (getServiceEnabled('/etc/dgidgateway') == 1 )  { // Hide DGId GW info when GW not enabled
        echo "<br />\n";
        echo "<table>\n";
        echo "<tr><th colspan='2'>DG-ID Gateway Status</th></tr>\n";
        echo "<tr><th colspan='2'>Current DG-ID</th></tr>\n";
        if (isPaused("YSF")) {
            echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"YSF Mode Paused\">YSF Mode Paused</td></tr>\n";
        }
          else if (isProcessRunning("DGIdGateway")) {
            echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"".getDGIdLinks()."\">".getDGIdLinks()."</td></tr>\n";
        } else {
            echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"Service Not Started\">Service Not Started</td></tr>\n";
        }
        echo "</table>\n";
    }

	$testYSF2DMR = 0;
	if ( isset($_SESSION['YSF2DMRConfigs']['Enabled']['Enabled']) ) {
	    $testYSF2DMR = $_SESSION['YSF2DMRConfigs']['Enabled']['Enabled'];
	}
	if ($testYSF2DMR == 1) { //Hide the YSF2DMR information when YSF2DMR Network mode not enabled.
            $dmrMasterFile = fopen("/usr/local/etc/DMR_Hosts.txt", "r");
            $dmrMasterHost = $_SESSION['YSF2DMRConfigs']['DMR Network']['Address'];
            while (!feof($dmrMasterFile)) {
                $dmrMasterLine = fgets($dmrMasterFile);
                $dmrMasterHostF = preg_split('/\s+/', $dmrMasterLine);
                if ((count($dmrMasterHostF) >= 2) && (strpos($dmrMasterHostF[0], '#') === FALSE) && ($dmrMasterHostF[0] != '')) {
                    if ($dmrMasterHost == $dmrMasterHostF[2]) {
			$dmrMasterHost = str_replace('_', ' ', $dmrMasterHostF[0]);
		    }
                }
            }
	    $dmrMasterHostTooltip = $dmrMasterHost;
            if (strlen($dmrMasterHost) > 25) {
		$dmrMasterHost = substr($dmrMasterHost, 0, 23) . '..';
	    }
            fclose($dmrMasterFile);
	    
            echo "<br />\n";
            echo "<table>\n";
            echo "<tr><th colspan=\"2\">YSF2DMR</th></tr>\n";
	    echo "<tr><th>DMR ID</th><td style=\"background: $tableRowEvenBg;\">".$_SESSION['YSF2DMRConfigs']['DMR Network']['Id']."</td></tr>\n";
	    echo "<tr><th colspan=\"2\">YSF2".$lang['dmr_master']."</th></tr>\n";
            echo "<tr><td colspan=\"2\"style=\"background: $tableRowEvenBg;\" title=\"".$dmrMasterHostTooltip."\">".$dmrMasterHost."</td></tr>\n";
            echo "</table>\n";
	}
	
	$testMMDVModeP25 = getConfigItem("P25 Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( isset($_SESSION['YSF2P25Configs']['Enabled']['Enabled']) ) { $testYSF2P25 = $_SESSION['YSF2P25Configs']['Enabled']['Enabled']; }
	if ( $testMMDVModeP25 == 1 || $testYSF2P25 || isPaused("P25") ) { //Hide the P25 information when P25 Network mode not enabled.
	    echo "<br />\n";
	    echo "<table>\n";
	    if (getConfigItem("P25", "NAC", $_SESSION['MMDVMHostConfigs'])) {
		echo "<tr><th colspan=\"2\">".$lang['p25_radio']."</th></tr>\n";
		echo "<tr><th style=\"width:70px\">NAC</th><td>".getConfigItem("P25", "NAC", $_SESSION['MMDVMHostConfigs'])."</td></tr>\n";
	    }
	    echo "<tr><th colspan=\"2\">".$lang['p25_net']."</th></tr>\n";
		if (isPaused("P25")) {
	    	echo "<tr><td colspan=\"2\"style=\"background: $tableRowEvenBg;\">Mode Paused</td></tr>\n";
		} else {
		    echo "<tr><td colspan=\"2\" ".GetActiveConnectionStyle($remoteP25GResults, "p25").">".getActualLink($logLinesP25Gateway, "P25")."</td></tr>\n";

		}
	    echo "</table>\n";
	}
	
	$testMMDVModeNXDN = getConfigItem("NXDN Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( isset($_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled']) ) {
	    if ($_SESSION['YSF2NXDNConfigs']['Enabled']['Enabled'] == 1) {
		$testYSF2NXDN = 1;
	    }
	}
	if ( isset($_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled']) ) {
	    if ($_SESSION['DMR2NXDNConfigs']['Enabled']['Enabled'] == 1) {
		$testDMR2NXDN = 1;
	    }
	}
	if ( $testMMDVModeNXDN == 1 || isset($testYSF2NXDN) || isset($testDMR2NXDN) || isPaused("NXDN") ) { //Hide the NXDN information when NXDN Network mode not enabled.
	    echo "<br />\n";
	    echo "<table>\n";
	    if (getConfigItem("NXDN", "RAN", $_SESSION['MMDVMHostConfigs'])) {
		echo "<tr><th colspan=\"2\">".$lang['nxdn_radio']."</th></tr>\n";
		echo "<tr><th style=\"width:70px\">RAN</th><td>".getConfigItem("NXDN", "RAN", $_SESSION['MMDVMHostConfigs'])."</td></tr>\n";
	    }
	    echo "<tr><th colspan=\"2\">".$lang['nxdn_net']."</th></tr>\n";
	    if (isPaused("NXDN")) {
		echo "<tr><td colspan=\"2\"style=\"background: $tableRowEvenBg;\">Mode Paused</td></tr>\n";
	    } else {
	    	echo "<tr><td colspan=\"2\" ".GetActiveConnectionStyle($remoteNXDNGResults, "nxdn")." >".getActualLink($logLinesNXDNGateway, "NXDN")."</td></tr>\n";
	    }
	    echo "</table>\n";
	}

	$testMMDVModeM17 = getConfigItem("M17", "Enable", $_SESSION['MMDVMHostConfigs']);
        $configm17gateway = $_SESSION['M17GatewayConfigs'];
	if ( $testMMDVModeM17 == 1 || isPaused("M17") ) { //Hide the M17 Reflector information when M17 Network not enabled.
		echo "<br />\n";
		echo "<table>\n";
		echo "<tr><th colspan=\"2\">M17 Repeater</th></tr>\n";
		echo "<tr><th>RPT</th><td style=\"background: $tableRowEvenBg;\">".str_replace(' ', '&nbsp;', $configm17gateway['General']['Callsign'])."&nbsp;".str_replace(' ', '&nbsp;', $configm17gateway['General']['Suffix'])."</td></tr>\n";
		echo "<tr><th colspan=\"2\">M17 Network</th></tr>\n";
                if (isPaused("M17")) {
                    echo "<tr><td colspan=\"2\"style=\"background: $tableRowEvenBg;\">Mode Paused</td></tr>\n";
                } else {
		    echo "<tr><td colspan=\"2\" ".GetActiveConnectionStyle($remoteM17GResults, "m17").">".getActualLink($reverseLogLinesM17Gateway, "M17")."</td></tr>\n";
                }
		echo "</table>\n";
	}
	
	$testMMDVModePOCSAG = getConfigItem("POCSAG Network", "Enable", $_SESSION['MMDVMHostConfigs']);
	if ( $testMMDVModePOCSAG == 1 || isPaused("POCSAG")) { //Hide the POCSAG information when POCSAG Network mode not enabled.
	    echo "<br />\n";
	    echo "<table>\n";
	    echo "<tr><th colspan=\"2\">POCSAG Status</th></tr>\n";
	    echo "<tr><th>TX</th><td style=\"background: $tableRowEvenBg;\">".getMHZ(getConfigItem("POCSAG", "Frequency", $_SESSION['MMDVMHostConfigs']))."</td></tr>\n";
            if (isPaused("POCSAG")) {
		$dapnetGatewayRemoteAddr = "Mode Paused";
		$dapnetGatewayRemoteTooltip = $dapnetGatewayRemoteAddr;
	    } else {
		if (isset($_SESSION['DAPNETGatewayConfigs']['DAPNET']['Address'])) {
		    $dapnetGatewayRemoteAddr = $_SESSION['DAPNETGatewayConfigs']['DAPNET']['Address'];
		    $dapnetGatewayRemoteTooltip = $dapnetGatewayRemoteAddr;
		    if (strlen($dapnetGatewayRemoteAddr) > 20) {
		        $dapnetGatewayRemoteAddr = substr($dapnetGatewayRemoteAddr, 0, 15) . '..';
		    }
		}
	    }
	    echo "<tr><th colspan=\"2\">DAPNET Master</th></tr>\n";
	    if (isProcessRunning("DAPNETGateway")) {
		echo "<tr><td colspan=\"2\" style=\"background: $tableRowEvenBg;\" title=\"".$dapnetGatewayRemoteTooltip."\">".$dapnetGatewayRemoteAddr."</td></tr>\n";
	    }
	    else {
		echo "<tr><td colspan=\"2\" style=\"background: $tableRowEvenBg;\">Service Not Started</td></tr>\n";
	    }
	    echo "</table>\n";
	}

    $testAPRSdmr = $_SESSION['DMRGatewayConfigs']['APRS']['Enable'];
    $testAPRSysf = $_SESSION['YSFGatewayConfigs']['APRS']['Enable'];
    $testAPRSm17 = $_SESSION['M17GatewayConfigs']['APRS']['Enable'];
    $testAPRSnxdn = $_SESSION['M17GatewayConfigs']['APRS']['Enable'];
    $testAPRSdgid = $_SESSION['M17GatewayConfigs']['APRS']['Enable'];
    $testAPRSircddb = $_SESSION['ircDDBConfigs']['aprsEnabled'];
    if (getServiceEnabled('/etc/aprsgateway') == 1 || isPaused("APRS"))  { // Hide APRS-IS GW info when GW not enabled
        echo "<br />\n";
        echo "<table>\n";
        echo "<tr><th colspan='2'>APRS Gateway Status</th></tr>\n";
        echo "<tr><th colspan='2' >Host Pool</th></tr>\n";
        if (isPaused("APRS")) {
		echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"Service Paused\">Service Paused</td></tr>\n"; 
	} else {
		echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"".$_SESSION['APRSGatewayConfigs']['APRS-IS']['Server']."\">".substr($_SESSION['APRSGatewayConfigs']['APRS-IS']['Server'], 0, 23)."<br /><small>(".getAPRSISserver().")</small></td></tr>\n";
		if ($testAPRSdmr == 0 && $testAPRSircddb == 0 && $testAPRSysf == 0 && $testAPRSdgid == 0 && $testAPRSnxdn == 0 && $testAPRSm17 == 0) {
			echo "<tr><th colspan='2'>APRS Being Sent To</th></tr>\n";
			echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"None Selected\">None Selected</td></tr>\n";
			echo "</table>\n";
		} else {
			echo "<tr><th colspan='2'>APRS Being Sent To</th></tr>\n";
			if ($testAPRSdmr == 1) {
				echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"DMR\">DMR</td></tr>\n";
			}
			if ($testAPRSircddb == 1) {
				echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"ircDDB\">ircDDB</td></tr>\n";
			}
			if ($testAPRSysf == 1) {
				echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"YSF\">YSF</td></tr>\n";
			}
			if ($testAPRSdgid == 1) {
				echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"DGId\">DGId</td></tr>\n";
			}
			if ($testAPRSnxdn == 1) {
				echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"NXDN\">NXDN</td></tr>\n";
			}
			if ($testAPRSm17 == 1) {
				echo "<tr><td colspan='2' style=\"background: $tableRowEvenBg;\" title=\"M17\">M17</td></tr>\n";
			}
		}
	}
        echo "</table>\n";
    }

?>
