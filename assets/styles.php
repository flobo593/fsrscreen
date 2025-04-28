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
        z-index: 100;
		width: 100vw;
		height: 4vw;
        padding-bottom: 5px;
		color: white;
        font-size: 3.5vw;
		font-family: 'Century Gothic', sans-serif;
        overflow: hidden;
        white-space: nowrap;
        align-items: center;
        background-color: black;
	}
    
    .fsrscreen_lineContainer {
        float: left;
		border-color: #fff;
        border-style: solid;
        border-width: 0 .075em 0 0;
        background-color: black;
    }
    
    .fsrscreen_lineContainer:last-of-type {
        border: none;
    }
    
    .fsrscreen_lineNr {
		float: left;
        text-align: center;
        font-size: .8em;
        font-weight: bold;
        padding: .1em;
        margin: 0;
        width: 4vw;
        height: 4vw;
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
		float: left;
        padding: 0 .3em 0 0;
        border-right: .05em solid #aaa;
    }
    
    .fsrscreen_mainDirectionContainer:last-of-type {
        border: none;
    }
    
    .fsrscreen_mainDirectionName {
        float: left;
		text-align: center;
		font-size: .5em;
		font-weight: normal;
		padding: .55em 0 .55em .4em;
		margin: 0;
		height: 4vw;
        letter-spacing: -.1ex;
    }
    
    .fsrscreen_singleDepartureContainer {
		float: left;
		text-align: center;
		font-size: .8em;
		font-weight: bolder;
		padding: .1em;
		margin: 0;
		height: 4vw;
    }
    
    .fsrscreen_singleDepartureTimeRemaining {
		float: left;
        width: 3.5vw;
        text-align: right;
    }
    
    .fsrscreen_singleDepartureTimeRemaining::after {
        content: "'";
        vertical-align: text-top;
        font-size: .8em;
        color: #aaa;
    }
    
    .fsrscreen_singleDepartureDestination {
		/*float: left;*/
        text-align: end;
        /*writing-mode: vertical-rl;*/
        font-size: .3em;
        font-weight: bolder;
        vertical-align: sub;
        padding-top: .6em;
    }
</style>