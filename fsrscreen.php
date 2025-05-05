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

// Include components
include "components/departureBar.php";

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
