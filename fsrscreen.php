<?php

/**
Plugin Name: fsrScreenWidgets
Plugin URI: http://github.com/flobo593/fsrscreen
Description: This implements the Widgets shown on the FSR Monitor.
Version: 0.1
Author: Florian Boden
Author URI: http://floribo.de
License: A "Slug" license name e.g. GPL2
*/

declare(strict_types=1);

/**
 * Reads the config file at assets/config.json and returns an array
 * @return array
 */
function readConfig () : array
{
	$configPath = 'assets/config.json';
	try {
		if (!file_exists($configPath)) {
			throw new Exception('config.json not found');
		}
	}
	catch (Exception $e) {
		die($e->getMessage());
	}
	
	try {
		$configFile = json_decode(file_get_contents($configPath), true);
		if (!$configFile) {
			throw new Exception('config.json malformed');
		}
	}
	catch (Exception $e) {
		die($e->getMessage());
	}
	return $configFile;
}

/**
 * Sends request to VVO endpoint.
 * Returns an array of departure arrays.
 * Each departure array contains three values: [0}: line number; [1]: destination; [2]: minutes until departure
 * @return array
 */
function retrieveDepartureDataFromVVO () : array
{
	
	// just a curl query. Explains itself.
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL => "http://widgets.vvo-online.de/abfahrtsmonitor/Abfahrten.do?hst=NUP&vz=0&ort=Dresden&lim=20",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_POSTFIELDS => "",
	]);
	
	$response = curl_exec($curl);
	
	curl_close($curl);
	
	return json_decode($response, true);
}

/**
 * Converts a given destination into its code using the config.json file. If there is no code for a destination given in the config.json file, it returns its input value
 * @param string $destination The name of the destination to be translated to its code.
 * @return string
 */
function translateFullDestinationsToCodes (string $destination) : string
{
	$dests = readConfig()['destinations'];
	if (key_exists($destination, $dests)) {
		return $dests[$destination];
	}
	return $destination;
}

/**
 * Finds the main direction for a given destination-line-pair.
 * @param string $destination Destination of the trip, as station code
 * @param string $lineNr Line number
 * @return string|null|false Returns id of the main direction, if not found null, or if set to display=false: false.
 */
function findMainDirectionForDestination (string $destination, string $lineNr) : string|null|false {
	$lineDirections = readConfig()['lines'][$lineNr]['directions'];
	foreach ($lineDirections as $directionId => $lineDirection) {
		
		// Check if display is set to false
		if (key_exists('display', $lineDirection)) {
			if(!$lineDirection['display']) return false;
		}
		
		// Check, if destination == main direction
		if ($destination == $lineDirection['default']) return $lineDirection['default'];
		
		// Skip if 'other' is not defined
		if (!key_exists('other', $lineDirection)) continue;
		
		// Search 'other' sub array for destination
		if (in_array($destination, $lineDirection['other'])) return $lineDirection['default'];
	}
	return null;
}


/**
 * Generates an array only showing the necessary departures and sorted by line, main direction, and then minutes until departure.
 * @param array $sourceArray An array as given by the VVO Api
 * @return array
 */
function generateStructuredArrayWithNextDepartures (array $sourceArray) : array
{
	$workingArray = array();
	foreach ($sourceArray as $departure) {
		
		// Translate all destinations to their code.
		$departure[1] = translateFullDestinationsToCodes($departure[1]);
		
		// Remove leading E's
		$departure[0] = trim($departure[0], 'E');
		
		// Ignore lines told to ignore by the config.json
		if (in_array($departure[0], readConfig()['linesToNotDisplay'])) continue;
		
		// Create line specific entry in workingArray, if not exists already
		if (!key_exists($departure[0], $workingArray)) {
			$workingArray[$departure[0]] = array();
		}
		
		// Find the main direction of the given destination
		$mainDirection = findMainDirectionForDestination($departure[1], $departure[0]);
		if ($mainDirection === null) {
			continue;
		}
		
		// If display is set to false
		if ($mainDirection === false) continue;
		
		// Create main direction entry in workingArray -> line, if not exists already
		if (!key_exists($mainDirection, $workingArray[$departure[0]])) {
			$workingArray[$departure[0]][$mainDirection] = array();
		}
		
		// If departing immediately set minutesRemaining to 0
		if ($departure[2] == "") {
			$departure[2] = "0";
		}
		
		// Add processed data to workingArray
		$workingArray[$departure[0]][$mainDirection][] = array($departure[2], $departure[1]);
	}
	return $workingArray;
}

function generateSpanForSingeDeparture (array $departureArray) : string
{
	return "<span class='fsrscreen_singleDepartureContainer'><span class='fsrscreen_singleDepartureTimeRemaining'>$departureArray[0]</span><span class='fsrscreen_singleDepartureDestination'>$departureArray[1]</span></span>";
}


function generateSpanForSingeDirection (array $departureArray) : string
{
	$outputString = "<span class='fsrscreen_mainDirectionContainer'>";
	foreach ($departureArray as $departure) {
		$outputString .= generateSpanForSingeDeparture($departure);
	}
	
	return $outputString."</span>";
}


function generateSpanForSingleLine (array $departureArray, string $lineNr) : string
{
	$outputString = "<span class='fsrscreen_lineContainer'><span class='fsrscreen_lineNr' id='fsrscreen_line_$lineNr'>$lineNr</span>";
	foreach ($departureArray as $direction) {
		$outputString .= generateSpanForSingeDirection($direction);
	}
	
	return $outputString."</span>";
}


function generateScreenLayout (array $departureArray) : string
{
	$outputString = "";
	foreach ($departureArray as $line => $directions) {
		$outputString .= generateSpanForSingleLine($directions, strval($line));
	}
	
	return "<div class='fsrscreen_nextDepartreBar'>$outputString</div>";
}