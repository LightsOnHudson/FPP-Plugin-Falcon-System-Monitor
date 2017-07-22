<?php

//function to get all the falcon system elements
function PrintFalconSystemsSelect() {
	
	return;
}


//get the processor temp
//get all items and then get the processor temp
function getProcessorTemp($IP_ADDRESS) {
	
	$elements = getAllFalconObjects($IP_ADDRESS);
	
	foreach($elements->find('element') as $ele) {
		
		print_r($ele);
		
	}
	
}


//get all the falcon telements
function getAllFalconObjects($IP_ADDRESS) {
	
	global $DEBUG;
	logEntry("Inside getting all falcon objects for ip address: ".$IP_ADDRESS);
	
	//for the falcon board
	//index.htm
	
	$URL = $IP_ADDRESS."/index.htm";
	//$elements= file_get_html($URL);
	$elements = file_get_contents($URL);
	return $elements;
	//foreach($elements->find('element') as $ele) {
		
	//	print_r($ele);
		
	//}
	
	//return or output
}

function sendTCP($IP, $PORT, $cmd) {
	
	
/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    logEntry("socket_create() failed: reason: " . socket_strerror(socket_last_error()));
} else {
   logEntry("TCPIP Socket Created");
}


$result = socket_connect($socket, $IP, $PORT);
if ($result === false) {
    logEntry("socket_connect() failed. Reason: ($result) " . socket_strerror(socket_last_error($socket)));
} else {
    logEntry("TCPIP CONNECTED");
}


socket_write($socket, $cmd, strlen($cmd));


logEntry("Reading response");
while ($out = socket_read($socket, 2048)) {
    logEntry($out);
}

logEntry("Closing socket...");
socket_close($socket);
logEntry("OK");

}
function hex_dump($data, $newline="\n")
{
  static $from = '';
  static $to = '';

  static $width = 16; # number of bytes per line

  static $pad = '.'; # padding for non-visible characters

  if ($from==='')
  {
    for ($i=0; $i<=0xFF; $i++)
    {
      $from .= chr($i);
      $to .= ($i >= 0x20 && $i <= 0x7E) ? chr($i) : $pad;
    }
  }

  $hex = str_split(bin2hex($data), $width*2);
  $chars = str_split(strtr($data, $from, $to), $width);

$HEX_OUT ="";
  $offset = 0;
  foreach ($hex as $i => $line)
  {
    $HEX_OUT.= sprintf('%6X',$offset).' : '.implode(' ', str_split($line,2)) . ' [' . $chars[$i] . ']';
    $offset += $width;
  }
return $HEX_OUT;
}

function decode_code($code)
{
    return preg_replace_callback('@\\\(x)?([0-9a-f]{2,3})@',
        function ($m) {
            if ($m[1]) {
                $hex = substr($m[2], 0, 2);
                $unhex = chr(hexdec($hex));
		echo "UNHEX: ".$unhex;
                if (strlen($m[2]) > 2) {
                    $unhex .= substr($m[2], 2);
                }
                return $unhex;
            } else {
                return chr(octdec($m[2]));
            }
        }, $code);
}


function logEntry($data) {

	global $logFile,$myPid,$callBackPid;
	
	if($callBackPid != "") {
		$data = $_SERVER['PHP_SELF']." : [".$callBackPid.":".$myPid."] ".$data;
	} else { 
	
		$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	}
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}


function escapeshellarg_special($file) {
	return "'" . str_replace("'", "'\"'\"'", $file) . "'";
}


function processCallback($argv) {

	global $DEBUG,$pluginName;
	
	
	if($DEBUG)
		print_r($argv);
	//argv0 = program
	
	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK");
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
	
				switch ($type) {
						
					case "sequence":
	
						//$sequenceName = ;
						processSequenceName($obj->{'Sequence'});
							
						break;
					case "media":
							
						logEntry("We do not understand type media at this time");
							
						exit(0);
	
						break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
				
		default:
			exit(0);
	
	}
	


}
?>
