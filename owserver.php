<?php
// Allow error reporting if we are debugging
if (isset($argv[1]) && $argv[1] == "-d") {
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}

// This array defines the value to retrieve for common 1-wire families
// information for adding more families can be found at the link below
// http://owfs.org/index.php?page=family-code-lookup
$family = array( "10" => "temperature", // DS18S20 temp
                 "28" => "temperature", // DS18B20 temp
                 "24" => "date",        // DS1904 RTC
                 "27" => "itime",       // DS1904 RTC
                 "2C" => "wiper" );     // DS2890 digital pot
// Get a list of the sensors
$sensors = shell_exec('owdir');
// Break the sensor list up into an array
$devices = explode("\n", $sensors);

$dt = strftime('%Y-%m-%d %H-%M', time());
$xml = "<a updated='$dt'>\n";

foreach ($devices as $device) {
	// Only take devices with a family code
	if (strpos($device, ".") && $device != "/bus.0") {
	        $type = shell_exec("owread $device/type");
        	$fam = shell_exec("owread $device/family");
	        $temp = (!empty($fam)) ? shell_exec("owread $device/{$family[$fam]}") : "";
		$sen = shell_exec("owread $device/r_address");
		// Convert to fahrenheit
		$calc = sprintf("%.1f", $temp * 9 / 5 + 32);
		// Create the xml code for this device
		$xml = $xml . "<owd>\n";
		$xml = $xml . "<Name>$type</Name>\n";
		$xml = $xml . "<ROMId>$sen</ROMId>\n";
		$xml = $xml . "<Temperature>$calc</Temperature>\n";
		$xml = $xml . "</owd>\n";
	}
}

$xml = $xml . "</a>\n";

// Allow printing of the xml for debugging or output the details.xml file
if (isset($argv[1]) && $argv[1] == "-d") {
	// Debug print the generated xml
	echo $xml;
} else {
	// Write the details.xml file to the webserver root folder
	$file = "/var/www/html/details.xml";
	file_put_contents($file, $xml);
}

?>
