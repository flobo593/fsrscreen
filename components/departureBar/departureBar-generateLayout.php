<?php
declare(strict_types=1);

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