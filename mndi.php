<?php
/**
 * Plugin Name:       MyNewsDesk Importer
 * Plugin URI:        https://mischiefmanaged.se/mynewdesk-importer/
 * Description:       Imports news and press from MyNewsDesk.
 * Version:           0.0.1
 * Author:            Mischief Managed
 * Author URI:        https://mischiefmanaged.se/
 * Text Domain:       mndi
 * Domain Path:       /languages
 */

if ( !defined('ABSPATH') ) {
	die();
}

if ( !class_exists('Mndi') ) {

class Mndi {
	public function __construct() {
		define('MNDI_PATH', plugin_dir_path( __FILE__ ));

		require_once( MNDI_PATH . '/vendor/autoload.php' );
	}

	public function initialize() {
		include_once MNDI_PATH . 'includes/utils.php';
		include_once MNDI_PATH . 'includes/options.php';
	}
}

$mndi = new Mndi;
$mndi->initialize();

}