<?php
//$DEBUG=true;

$skipJSsettings = 1;
//include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

$pluginName = "FalconSystemMonitor";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';

include_once 'version.inc';

$myPid = getmypid();

$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Falcon-System-Monitor.git";

//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$sequenceExtension = ".fseq";

logEntry("plugin update file: ".$pluginUpdateFile);

//logEntry("open log file: ".$logFile);

$ALL_HARDWARE_VALUES = array("UPTIME" => "fldUptime","Processor Temp" => "fldChipTemp", "Temp 1" => "fldTemp1", "Temp 2" => "fldTemp2", "Voltage 1" => "fldV1", "Voltage 2" => "fldV2");

$DEBUG = false;

if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);
	
	echo $updateResult."<br/> \n";
}

if(isset($_POST['submit']))
{
	

	$HARDWARE_VALUES_SELECTED = array();
	
	//$ENABLED=$_POST["ENABLED"];
	$HARDWARE_VALUES= $_POST["HARDWARE_VALUES"];
	
	foreach ($HARDWARE_VALUES as $key => $value) {
		array_push($HARDWARE_VALUES_SELECTED, $value);
	}

	//	echo "Writring config fie <br/> \n";

	$HARDWARE_VALUES_TO_SAVE =  implode(',', $HARDWARE_VALUES_SELECTED);
	WriteSettingToFile("CONTROLLER_IPS",urlencode($_POST["CONTROLLER_IPS"]),$pluginName);
	WriteSettingToFile("HARDWARE_VALUES",$HARDWARE_VALUES_TO_SAVE,$pluginName);
	

} 


sleep(1);

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

	$DEBUG = urldecode($pluginSettings['DEBUG']);
	
	if($DEBUG)
		print_r($HARDWARE_VALUES);
	
	
	//$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);
	$ENABLED = urldecode($pluginSettings['ENABLED']);
	$CONTROLLER_IPS = urldecode($pluginSettings['CONTROLLER_IPS']);
	$HARDWARE_VALUES = $pluginSettings['HARDWARE_VALUES'];
	
	//test variables
	$IP_ADDRESS = "10.0.0.106";
?>

<html>
<head>
</head>

<div id="plugin" class="settings">
<fieldset>
<legend>Falcon System Monitor Support/Install Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>


<p>Configuration:
<ul>
<li>Configure the IP addresses that you want to monitor (comma separated)</li>
<li>Select the values that you want to monitor from the selection box</li>
</ul>

<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">
<br>
<p/>

<?

echo "VER: ".$VERSION;
echo "<br/> \n";

echo "ENABLE PLUGIN: ";

//if($ENABLED == "on" || $ENABLED == 1) {
//	echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
	PrintSettingCheckbox($pluginName, "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//} else {
//	echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
//}
echo "<p/>\n";

echo "Values to include in monitoring: <br/> \n";
//print the hardware values available and wanting to see
printHardwareValues($HARDWARE_VALUES);
echo "<p/>\n";
//get a list of falcon controllers
echo "<table border=\"1\" cellspacing=\"3\" cellpadding=\"3\"> \n";

$HARDWARE_VALUES_READ = explode(",",$HARDWARE_VALUES);
if($DEBUG) {
	echo "<pre> \n";
	print_r($ALL_HARDWARE_VALUES);
	print_r($HARDWARE_VALUES_READ);
	echo "</pre> \n";
}
//print_r($PLUGINS_READ);





if($CONTROLLER_IPS != "" || $CONTROLLER_IPS != null) {
	
	$FALCON_IPS = explode(",",$CONTROLLER_IPS);
	echo "<th colspan=\"4\"> \n";
	echo "Falcon System Monitoring \n";
	echo "</th> \n";
	echo "<tr> \n";
	echo "<td> \n";
	echo "IP Address \n";
	echo "</td> \n";
	echo "<td> \n";
	echo "Hostname \n";
	echo "</td> \n";
	
	//	for($i=0;$i<=count($ALL_HARDWARE_VALUES)-1;$i++) {
	foreach($ALL_HARDWARE_VALUES as $key => $value) {
			if(in_array($value,$HARDWARE_VALUES_READ)) {
			
				
				echo "<td> \n";
				echo $key;
				echo "</td> \n";
			} else {
			
			//
			}
		
		
	}
	
	echo "</tr> \n";
	
	
	foreach ($FALCON_IPS as $IP_ADDRESS) {
		
		//get the falcon data for this IP address
		$falconSystemData = getAllFalconObjects($IP_ADDRESS);
		echo "<tr> \n";
		echo "<td> \n";
		PrintFalconSystemsSelect();
		echo $IP_ADDRESS;
		echo "</td> \n";
		
		echo "<td> \n";
		echo tryGetHost($IP_ADDRESS);
		echo "</td> \n";
		
	//	for($i=0;$i<=count($ALL_HARDWARE_VALUES)-1;$i++) {
	foreach($ALL_HARDWARE_VALUES as $key => $value) {
		if(in_array($value,$HARDWARE_VALUES_READ)) {
	
			
			if($DEBUG) {
				logEntry("Looking for the word TEMP in: ".$value);
			}
				$pos = strpos(strtoupper($value), "TEMP");
				
				
				//if($value == "fldChipTemp") {
				//if the value is a temp - then run it through the processing of the temp to show the values
				if($pos) {
				
					if($DEBUG) {
						logEntry("We have a temperatur - calculating");
					}
				
					$temp_processor = getFalconObjectValueFromData($falconSystemData, $value, "td");
					//getFalconObjectValue($falconData, "fldChipTemp", "td");
					//$temp_processor = getFalconObjectValue($IP_ADDRESS, "fldChipTemp", "td");
					$farenheight_temp_processor = celciusToFarenheight($temp_processor);
					echo "<td> \n";
					if(($temp_processor != "" || $temp_processor != null) && $temp_processor != "-24")  {
						echo $temp_processor;
						echo "(C) \n";
						
						echo $farenheight_temp_processor;
						echo "(F) \n";
						echo "</td> \n";
					} else {
						//-24C is the default when there is no temp probe
						echo "N/A \n";
					}
				} else {
					if($DEBUG) {
						logEntry("We do not have a temperatur - no calculating");
					}
					echo "<td> \n";
					echo getFalconObjectValueFromData($falconSystemData, $value, "td");
					echo "</td> \n";
					
				}
				
			}
			
		}
		
		echo "</tr> \n";
	}
	
		
		//echo "</td> \n";
		//echo "<td> \n";
		//echo getFalconObjectValueFromData($falconSystemData, "lblUniverseCount", "label");
		//echo "</td> \n";
		//echo "</tr> \n";
	
} else {
	echo "<th colspan=\"3\"> \n";
	echo "No controllers configured for monitoring, use the box below to enter them. Comma separated \n";
	echo "</th> \n";
}
echo "</table> \n";
echo "<table border=\"1\" cellspacing=\"1\" cellpadding=\"2\"> \n";
echo "<tr> \n";
echo "<td> \n";
echo "Controller IPs to monitor (comma separated): \n";
echo "</td> \n";
echo "<td> \n";
echo "<input type=\"text\" size=\"90\" name=\"CONTROLLER_IPS\" value=\"".$CONTROLLER_IPS."\"> \n";
echo "</td> \n";
echo "</tr> \n";
echo "</table> \n";


?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
</form>


<p>To report a bug, please file it against the  plug-in project on Git: <? echo $gitURL;?>
<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
</form>
</fieldset>
</div>
<br />
</html>
