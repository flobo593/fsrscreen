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
		height: 5vw;
        padding-bottom: 5px;
		background: black;
		color: white;
        font-size: 4.5vw;
		font-family: 'Century Gothic', sans-serif;
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
        width: 5vw;
        height: 5vw;
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
    
    .fsrscreen_singleDepartureContainer {
		float: left;
		text-align: center;
		font-size: .8em;
		font-weight: bolder;
		padding: .1em;
		margin: 0;
		width: 6.5vw;
		height: 5vw;
    }
    
    .fsrscreen_singeDepartureTimeRemaining {
		float: left;
    }
    
    .fsrscreen_singleDepartureDestination {
		float: left;
        text-align: end;
        writing-mode: vertical-rl;
        font-size: 4.5vw;
        font-weight: lighter;
        color: #999;
    }
</style>