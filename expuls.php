<?php
/**
 * Plugin Name: Expuls
 * Plugin URI: https://elementor.thememasters.club/expuls/
 * Description: Royalty Free Photos And Videos Provided By Pexels
 * Version: 1.0
 * Author: ThemeMasters
 * Author URI: http://codecanyon.net/user/egemenerd
 * Text Domain: expuls
 * Domain Path: /languages/
 *
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'EXPULS_PLUGIN_URL' ) ) {
	define( 'EXPULS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

/* ---------------------------------------------------------
Custom Metaboxes - github.com/WebDevStudios/CMB2
----------------------------------------------------------- */

// Check for PHP version
$expulsdir = ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) ? __DIR__ : dirname( __FILE__ );

if ( file_exists(  $expulsdir . '/cmb2/init.php' ) ) {
    require_once($expulsdir . '/cmb2/init.php');
} elseif ( file_exists(  $expulsdir . '/CMB2/init.php' ) ) {
    require_once($expulsdir . '/CMB2/init.php');
}

include_once('settingsClass.php');

/* ---------------------------------------------------------
Include required files
----------------------------------------------------------- */

include_once('mainClass.php');