<?php

/**
Plugin Name: FSR Screen
Plugin URI: http://github.com/fsrverkehr/fsrscreen
Description: This implements the Widgets shown on the FSR Monitor.
Version: indev
Author: FSR Verkehr
Author URI: https://github.com/fsrverkehr/fsrscreen/blob/main/AUTHORS
License: GPL 3.0
*/

declare(strict_types=1);

// Include components
include "components/departureBar/departureBar.php";

// Add WP actions
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
