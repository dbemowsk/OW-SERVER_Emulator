<?php
// Allow error reporting if we are debugging
if (isset($argv[1]) && $argv[1] == "-d") {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

$hostname = shell_exec("hostname");
$hostname = trim($hostname);

// This array defines the value to retrieve for common 1-wire families
// information for adding more families can be found at the link below
// http://owfs.org/index.php?page=family-code-lookup
$family = array( "10" => array("getval" => "temperature", "description" => "Parasite power thermometer"), // DS18S20 temp
                 "28" => array("getval" => "temperature", "description" => "Parasite power thermometer"), // DS18B20 temp
                 "24" => array("getval" => "date", "description" => "Real time clock"),                   // DS1904 RTC
                 "27" => array("getval" => "time", "description" => "Real time clock"),                   // DS1904 RTC
                 "2C" => array("getval" => "wiper", "description" => "Digital potentiometer") );          // DS2890 digital pot

$temp_unit = shell_exec("owread /settings/units/temperature_scale");
$units = array( "C" => "Centigrade", "F" => "Fahrenheit");

// Get a list of the sensors
$sensors = shell_exec('owdir');
// Break the sensor list up into an array
$devices = explode("\n", $sensors);

// Device counter
$count = 0;

$dt = strftime('%Y-%m-%d %H-%M', time());

$devXml = "";

foreach ($devices as $device) {
	// Only take devices with a family code
	if (strpos($device, ".") && $device != "/bus.0") {
		$count ++;
	        $type = shell_exec("owread $device/type");
        	$fam = shell_exec("owread $device/family");
	        $temp = (!empty($fam)) ? shell_exec("owread $device/{$family[$fam]['getval']}") : "";
		$temp = trim($temp);


		$sen = shell_exec("owread $device/r_address");

		//  Create the xml code for this device
		$devXml .= "  <owd_$type Description=\"{$family[$fam]['description']}\">\n";
		$devXml .= "    <Name>$type</Name>\n";
		$devXml .= "    <Family>$fam</Family>\n";
		$devXml .= "    <ROMId>$sen</ROMId>\n";
		$devXml .= "    <PrimaryValue>$temp Deg $temp_unit</PrimaryValue>\n";
		$devXml .= "    <Temperature Units=\"{$units[$temp_unit]}\">$temp</Temperature>\n";
		$devXml .= "  </owd_$type>\n";
	}
}

$output =  "<Devices-Detail-Response xmlns=\"http://www/embededdatasystems.com/schema/owserver\" xmlns:xsi=\"htinstance\">\n";
$output .= "  <DevicesConnected>$count</DevicesConnected>\n";
$output .= "  <DataErrors>0</DataErrors>\n";
$output .= "  <DeviceName>OWServer Emulator V1</DeviceName>\n";
$output .= "  <HostName>$hostname</HostName>\n";
$output .= $devXml;
$output .= "</Devices-Detail-Response>\n";

// Allow printing of the xml for debugging or output the details.xml file
if (isset($argv[1]) && $argv[1] == "-d") {
	// Debug print the generated xml
	echo $output;
} else {
	// Write the details.xml file to the webserver root folder
	$file = "/var/www/html/details.xml";
	file_put_contents($file, $output);
}

?>
