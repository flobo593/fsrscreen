<?php
header("Content-Type: text/css");
?>
<style>
	body {
		/* Needs to be here so that the first selector works. Dunno why... */
	}

	.fsrscreen_nextDepartureBar {
		display: inline;
		position: fixed;
		bottom: 0;
		left: 0;
		z-index: 100;
		width: 100vw;
		height: 3.7vw;
		padding-bottom: 5px;
		color: white;
		font-size: 3.2vw;
		font-family: 'Century Gothic', sans-serif;
		overflow: hidden;
		white-space: nowrap;
		align-items: center;
		background-color: black;
		max-width: unset;
	}

	.fsrscreen_nextDepartureBar *:not(sub) {
		/*display: inline;*/
		float: left;
	}

	.fsrscreen_lineContainer {
		border-color: #fff;
		border-style: solid;
		border-width: 0 .075em 0 0;
		background-color: black;
	}

	.fsrscreen_lineContainer:last-of-type {
		border: none;
	}

	.fsrscreen_lineNr {
		text-align: center;
		font-size: .8em;
		font-weight: bold;
		padding: .1em;
		margin: 0;
		width: 3.3vw;
		height: 4vw;
		line-height: 1.3em;
	}

	/* This generates the statements providing the line numbers with their specific color as defined in the lines.json */
    <?php
    function fsrscreen_readLinesCSV () : array
{
	$linesFile = "lines.json";
	try {
		if (!file_exists($linesFile)) {
			throw new Exception('lines.json not found');
		}
	}
	catch (Exception $e) {
		die($e->getMessage());
	}
	
	try {
		$configFile = json_decode(file_get_contents($linesFile), true);
		if (!$configFile) {
			throw new Exception('lines.json malformed');
		}
	}
	catch (Exception $e) {
		die($e->getMessage());
	}
	return $configFile;
}
    
    foreach (fsrscreen_readLinesCSV() as $item) {
        $line = $item['line'];
        $color = $item['color'];
        echo ".fsrscreen_lineNr#fsrscreen_line_$line { background-color: $color; }\n";
    }
 ?>

	.fsrscreen_mainDirectionContainer {
		padding: 0 .2em 0 0;
		border-right: .05em solid #aaa;
	}

	.fsrscreen_mainDirectionContainer:last-of-type {
		border: none;
	}

	.fsrscreen_mainDirectionName {
		text-align: center;
		font-size: .5em;
		font-weight: normal;
		padding: .55em 0 .55em .4em;
		margin: 0;
		height: 4vw;
		letter-spacing: -.2ex;
		line-height: 1.2em;
	}

	.fsrscreen_singleDepartureContainer {
		text-align: right;
		font-size: .8em;
		font-weight: bolder;
		padding: .1em;
		margin: 0;
		height: 4vw;
	}

	.fsrscreen_singleDepartureTimeRemaining {
		width: 3.5vw;
		height: 100%;
		text-align: right;
		line-height: .8em;
		font-weight: normal;
		position: relative;
		right: 0;
	}

	/*.fsrscreen_singleDepartureReplacement::after {
		content: 'EV';
		font-size: .5em;
		top: 0;
	}*/

	.fsrscreen_noReplacement::after {
		content: "'";
		position: relative;
		top: -1ex;
		font-size: .8em;
		color: #aaa;
	}
	.fsrscreen_singleDepartureReplacement::after {
		all: unset;
		content: "EV";
		vertical-align: text-top;
		font-size: .4em;
		font-weight: bolder;
		color: #F02293;
		position: relative;
		top: -1ex;
		left: 0;
		overflow: visible;
	}

	.fsrscreen_time {
		text-align: right;
		vertical-align: center;
		width: 100%;
		height: 100%;
		padding-top: .2em;
	}

	.fsrscreen_singleDepartureDestination {
		text-align: end;
		font-size: .35em;
		font-weight: bolder;
		vertical-align: sub;
		padding-top: .6em;
		letter-spacing: -.3ex;
		position: relative;
		left: 0;
	}

	.fsrscreen_nextbikeIcon {
		padding: .1em .2em .55em .2em;
		height: 100%;
		line-height: 1em;
	}

	.fsrscreen_nextbikeCount {
		font-size: .8em;
		text-align: center;
		font-weight: normal;
		padding: .1em 0 .55em .4em;
		margin: 0;
		height: 4vw;
		letter-spacing: -.2ex;
		line-height: 1.2em;
	}
</style>