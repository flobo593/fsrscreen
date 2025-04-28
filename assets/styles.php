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
		background: black;
		color: white;
        font-size: 3.5vw;
		font-family: 'Century Gothic', sans-serif;
        overflow: hidden;
        white-space: nowrap;
	}
    
    .fsrscreen_lineContainer {
        float: left;
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
        padding: 0;
    }
    
    .fsrscreen_mainDirectionName {
        float: left;
		text-align: center;
		font-size: .5em;
		font-weight: normal;
		padding: .625em .5em;
		margin: 0;
		height: 4vw;
        text-transform: full-size-kana;
    }
    
    .fsrscreen_singleDepartureContainer {
		float: left;
		text-align: center;
		font-size: .8em;
		font-weight: bolder;
		padding: .1em;
		margin: 0;
		width: 5.5vw;
		height: 4vw;
    }
    
    .fsrscreen_singleDepartureTimeRemaining {
		float: left;
        width: fit-content;
    }
    
    .fsrscreen_singleDepartureDestination {
		float: left;
        text-align: end;
        /*writing-mode: vertical-rl;*/
        font-size: .5em;
        font-weight: lighter;
        color: #999;
    }
</style>