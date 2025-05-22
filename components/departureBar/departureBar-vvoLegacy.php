<?php
declare(strict_types=1);

/**
 * Handles data sourcing and processing for VVO legacy API
 * @param array $providerConfig Array of provider configuration as returned by fsrscreen_readProviderConfig()
 * @return array Sanitized array usable by the layout generator
 */
function fsrscreen_getDataFromVVOLegacy (array $providerConfig) : array
{
	$data = fsrscreen_retrieveDepartureDataFromVVOLegacy($providerConfig);
	return fsrscreen_sanitizeVVOLegacyData($data);
}

/**
 * Sends request to VVO legacy endpoint.
 * Returns an array of departure arrays.
 * Each departure array contains three values: [0}: line number; [1]: destination; [2]: minutes until departure
 * @param array $dataProviderConfig Array of provider configuration as returned by fsrscreen_readProviderConfig()
 * @return array
 */
function fsrscreen_retrieveDepartureDataFromVVOLegacy (array $dataProviderConfig) : array
{
	$uri = $dataProviderConfig['uri'];
	$method = $dataProviderConfig['method'];
	$flags = "?".http_build_query($dataProviderConfig['flags']);
	
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
 * Sanitizes VVO-legacy data and returns an array only showing the necessary departures and sorted by line, main direction, and then minutes until departure.
 * @param array $sourceArray An array as given by the VVO-legacy Api
 * @return array
 */
function fsrscreen_sanitizeVVOLegacyData (array $sourceArray) : array
{
	$workingArray = array();
	foreach ($sourceArray as $departure) {
		
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
		if (!key_exists($mainDirection[0], $workingArray[$departure[0]])) {
			$workingArray[$departure[0]][$mainDirection[0]] = array();
		}
		
		// Skip if already 2 departures are in array
		if (count($workingArray[$departure[0]][$mainDirection[0]]) >= 2) continue;
		
		// If departing immediately set minutesRemaining to 0
		if ($departure[2] == "") {
			$departure[2] = "0";
		}
		
		// Add processed data to workingArray
		$workingArray[$departure[0]][$mainDirection[0]][] = array($departure[2], $mainDirection[1]);
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
 * Finds the main direction for a given destination-line-pair.
 * @param string $destination Destination of the trip
 * @param string $lineNr Line number
 * @return array|null|false Returns array of name and footnote of the main direction, if not found null, or if set to display=false: false.
 */
function fsrscreen_findMainDirectionForDestination (string $destination, string $lineNr) : array|null|false {
	$lineDirections = fsrscreen_readConfig()['lines'][$lineNr]['directions'];
	foreach ($lineDirections as $directionId => $lineDirection) {
		
		// Check if display is set to false
		if (key_exists('display', $lineDirection)) {
			if(!$lineDirection['display']) return false;
		}
		
		// Check, if destination == main direction
		if ($destination == $lineDirection['default']) return array($lineDirection['default'], "");
		
		// Skip if 'other' is not defined
		if (!key_exists('other', $lineDirection)) continue;
		
		// Search 'other' sub array for destination
		if (key_exists($destination, $lineDirection['other'])) return array($lineDirection['default'], $lineDirection['other'][$destination]);
	}
	return null;
}
