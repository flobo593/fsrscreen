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

add_shortcode('fsrscreen_showNextDepartures', 'fsrscreen_showNextDepartures');
add_action('wp_enqueue_scripts', 'fsrscreen_enqueueStylesAndScripts');

/**
 * Enqueues the style.php file as style document
 * @return void
 */
function fsrscreen_enqueueStylesAndScripts () : void
{
	wp_enqueue_style(
		'fsrscreen_pluginStyles',
		plugin_dir_url(__FILE__) . 'assets/styles.php',
		array()
	);
	
	wp_enqueue_script(
		'fsrscreen_pluginScripts',
		plugin_dir_url(__FILE__) . 'includes/scripts.js',
		array()
	);
	
	wp_enqueue_script(
		'jQuery',
		'https://code.jquery.com/jquery-3.6.0.min.js',
		array()
	);
}

/**
 * Function that is called by the fsrscreen_showNextDepartures short code
 * @return void
 */
function fsrscreen_showNextDepartures () : void
{
	$data = fsrscreen_retrieveDepartureDataFromVVO();
	$sanitizedData = fsrscreen_generateStructuredArrayWithNextDepartures($data);
	echo fsrscreen_generateScreenLayout($sanitizedData);
}

/**
 * Reads the config file at assets/config.json and returns an array
 * @return array
 */
function fsrscreen_readConfig () : array
{
	$configPath = plugin_dir_path(__FILE__)."assets/config.json";
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
function fsrscreen_retrieveDepartureDataFromVVO () : array
{
	$dataProviderConfig = fsrscreen_readConfig()['dataProvider'];
	
	switch($dataProviderConfig['providerId'])
	{
		case "VVO-Abfahrtsmonitor":
		{
			$uri = $dataProviderConfig['uri'];
			$method = $dataProviderConfig['method'];
			$flags = "?".http_build_query($dataProviderConfig['flags']);
		}
		default: break;
	}
	
	// just a curl query. Explains itself.
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL => $uri.$flags,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $method,
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
function fsrscreen_translateFullDestinationsToCodes (string $destination) : string
{
	$dests = fsrscreen_readConfig()['destinations'];
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
function fsrscreen_findMainDirectionForDestination (string $destination, string $lineNr) : string|null|false {
	$lineDirections = fsrscreen_readConfig()['lines'][$lineNr]['directions'];
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
function fsrscreen_generateStructuredArrayWithNextDepartures (array $sourceArray) : array
{
	$workingArray = array();
	foreach ($sourceArray as $departure) {
		
		// Translate all destinations to their code.
		$departure[1] = fsrscreen_translateFullDestinationsToCodes($departure[1]);
		
		// Remove leading E's
		$departure[0] = trim($departure[0], 'E');
		
		// Ignore lines told to ignore by the config.json
		if (in_array($departure[0], fsrscreen_readConfig()['linesToNotDisplay'])) continue;
		
		// Create line specific entry in workingArray, if not exists already
		if (!key_exists($departure[0], $workingArray)) {
			$workingArray[$departure[0]] = array();
		}
		
		// Find the main direction of the given destination
		$mainDirection = fsrscreen_findMainDirectionForDestination($departure[1], $departure[0]);
		if ($mainDirection === null) {
			continue;
		}
		
		// If display is set to false
		if ($mainDirection === false) continue;
		
		// Create main direction entry in workingArray -> line, if not exists already
		if (!key_exists($mainDirection, $workingArray[$departure[0]])) {
			$workingArray[$departure[0]][$mainDirection] = array();
		}
		
		// Skip if already 2 departures are in array
		if (count($workingArray[$departure[0]][$mainDirection]) >= 2) continue;
		
		// If departing immediately set minutesRemaining to 0
		if ($departure[2] == "") {
			$departure[2] = "0";
		}
		
		if ($departure[1] == $mainDirection) {
			$destination = "";
		} else {
			$destination = $departure[1];
		}
		
		$destination = str_replace(" ", "<br>", $destination);
		
		// Add processed data to workingArray
		$workingArray[$departure[0]][$mainDirection][] = array($departure[2], $destination);
	}
	
	// Sort array after line number
	ksort($workingArray);
	
	// Sort main directions
	foreach ($workingArray as $item) {
		ksort($item);
	}
	
	return $workingArray;
}

/**
 * Generates the <div> element for a single departure. Used by fsrscreen_generateSpanForSingleDirection
 * @param array $departureArray
 * @return string
 */
function fsrscreen_generateDivForSingeDeparture (array $departureArray) : string
{
	return "<div class='fsrscreen_singleDepartureContainer'><div class='fsrscreen_singleDepartureTimeRemaining'>$departureArray[0]<sub class='fsrscreen_singleDepartureDestination'>$departureArray[1]</sub></div></div>";
}

/**
 * Generates the <div> element for a single direction of a public transport line. Used by fsrscreen_generateSpanForSingleLine
 * @param array $departureArray
 * @param string $mainDirectionName
 * @return string
 */
function fsrscreen_generateDivForSingeDirection (array $departureArray, string $mainDirectionName) : string
{
	$outputString = "<div class='fsrscreen_mainDirectionContainer'><div class='fsrscreen_mainDirectionName'>$mainDirectionName</div>";
	foreach ($departureArray as $departure) {
		$outputString .= fsrscreen_generateDivForSingeDeparture($departure);
	}
	
	return $outputString."</div>";
}

/**
 * Generates the <div> element for a single public transport line. Used by fsrscreen_generateScreenLayout
 * @param array $departureArray
 * @param string $lineNr
 * @return string
 */
function fsrscreen_generateDivForSingleLine (array $departureArray, string $lineNr) : string
{
	ksort($departureArray);
	$outputString = "<div class='fsrscreen_lineContainer'><div class='fsrscreen_lineNr' id='fsrscreen_line_$lineNr'>$lineNr</div>";
	foreach ($departureArray as $direction => $departures) {
		$outputString .= fsrscreen_generateDivForSingeDirection($departures, $direction);
	}
	
	return $outputString."</div>";
}


/**
 * Generates the full HTML <div> for displaying the data on the site.
 * @param array $departureArray Array as given by generateStructuredArrayWithNextDepartures() function
 * @return string HTML string
 */
function fsrscreen_generateScreenLayout (array $departureArray) : string
{
	$outputString = "";
	foreach ($departureArray as $line => $directions) {
		$outputString .= fsrscreen_generateDivForSingleLine($directions, strval($line));
	}
	
	return "<div class='fsrscreen_nextDepartureBar' id='fsrscreen_nextDepartureBar'>$outputString</div>";
}