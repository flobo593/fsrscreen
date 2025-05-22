<?php
declare(strict_types=1);

include 'departureBar-vvoLegacy.php';
include 'departureBar-vvoWebAPI.php';
include 'departureBar-generateLayout.php';

// Add short code
add_shortcode('fsrscreen_showNextDepartures', 'fsrscreen_showNextDepartures');

/**
 * Function that is called by the fsrscreen_showNextDepartures short code
 * @return string
 */
function fsrscreen_showNextDepartures () : string
{
	$dataProvider = fsrscreen_readConfig()['dataProvider'];
	$providerConfig = fsrscreen_readProviderConfig($dataProvider['providerId']);
	$sanitizedData = match ($dataProvider['providerId']) {
		'vvo-legacy' => fsrscreen_getDataFromVVOLegacy($providerConfig),
		'vvo-webAPI' => fsrscreen_getDataFromVVOWebAPI($providerConfig),
		default => [],
	};
	return fsrscreen_generateScreenLayout($sanitizedData);
}
