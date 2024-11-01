<?php
/*
 * Plugin Name: WP Courseware addon for Restrict Content Pro
 * Version: 1.0.0
 * Plugin URI: http://flyplugins.com
 * Description: The official extension for WP Courseware to add integration for Restrict Content Pro.
 * Author: Fly Plugins
 * License:     GPL v2 or later
 * Text Domain: wpcw-rcp-addon
 * Domain Path: /languages
 *
 * @package WPCW_RCP_Addon
 * @since 1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Constants.
define( 'WPCW_RCP_ADDON_VERSION', '1.0.0' );

/**
 * WP Courseware Restrict Content Pro Addon.
 *
 * @since 1.0.0
 */
function wpcw_rcp_addon() {
	// Plugin Path.
	$plugin_path = plugin_dir_path( __FILE__ );

	// Required Files.
	require_once $plugin_path . 'includes/functions.php';
	require_once $plugin_path . 'includes/class-wpcw-rcp-members.php';
	require_once $plugin_path . 'includes/class-wpcw-rcp-membership.php';
	require_once $plugin_path . 'includes/class-wpcw-rcp-addon.php';

	// Load Plugin Textdomain.
	load_plugin_textdomain( 'wpcw-rcp-addon', false, basename( dirname( __FILE__ ) ) . '/languages' );

	// Initalize Add-On.
	WPCW_RCP_Addon::init();
}
add_action( 'plugins_loaded', 'wpcw_rcp_addon' );
