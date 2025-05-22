<?php
declare(strict_types=1);


function fsrscreen_getDataFromVVOWebAPI (array $providerConfig) : array
{
	// TODO
	
	return array();
}

/**
 * Gets the data from the VVO WebAPI
 * @param array $providerConfig Array of provider configuration as returned by fsrscreen_readProviderConfig()
 * @return array
 */
function fsrscreen_retrieveDataFromVVOWebAPI (array $providerConfig) : array
{
	$curl = curl_init();
	
	curl_setopt_array($curl, [
		CURLOPT_URL => $providerConfig['uri'],
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => $providerConfig['method'],
		CURLOPT_POSTFIELDS => json_encode($providerConfig['payload']),
		CURLOPT_HTTPHEADER => [
			"Content-Type: application/json"
		],
	]);
	
	$response = curl_exec($curl);
	
	curl_close($curl);
	
	return json_decode($response, true);
}



function fsrscreen_sanitizeDepartureData (array $data) : array
{
	$workingArray = array();
	foreach ($data['Departures'] as $departure) {
		$lineDir = fsrscreen_getDirectionIdFromMentzId($departure['Id']);
		
		// Create sub-array for line if not already exists.
		if (!key_exists($lineDir[0], $workingArray)) $workingArray[$lineDir[0]] = array();
		
		// Create sub-array for direction if not already exists
		if (!key_exists($lineDir[1], $workingArray)) $workingArray[$lineDir[0]][$lineDir[1]] = array();
		
		// Skip if 2 departures are already added for line and direction
		if (count($workingArray[$lineDir[0]][$lineDir[1]]) >= 2) continue;
	}
	
	// TODO
	
	return $workingArray;
}

/**
 * Separates line and direction identifier from ID returned by VVO WebAPI
 * @param string $mentzId
 * @return array
 */
function fsrscreen_getDirectionIdFromMentzId (string $mentzId) : array
{
	$data = explode(':', $mentzId);
	$line = strval(intval($data[1]) % 1000);
	return array($line, $data[4]);
}