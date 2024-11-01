<?php 

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}


// Plugin path and url 
define('WPWHATSAPPPM_DIR',trailingslashit(plugin_dir_path(dirname(__FILE__))));
define('WPWHATSAPPPM_URL',trailingslashit(plugin_dir_url(dirname(__FILE__))));



// Check get_plugin_data function exist
if (!function_exists('get_plugin_data')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}


// Get plugin data
$plugin_data = get_plugin_data(WPWHATSAPPPM_DIR . 'wallmessage.php');


// define constants for wallmessage


/*
path and urls
*/


// Wallmessage about us url
if ( ! defined( 'WALLMESSAGE_ABOUT_SITE' ) ) {
	define('WALLMESSAGE_ABOUT_SITE', 'https://wallmessage.com/about-us/' );
}



// Wallmessage iframe source url
if ( ! defined( 'WALLMESSAGE_LEFT_MENU_IFRAME' ) ) {
	define('WALLMESSAGE_LEFT_MENU_IFRAME', '');
}

// Wallmessage about us url
if ( ! defined( 'WALLMESSAGE_HOW_TO_USE' ) ) {
	define('WALLMESSAGE_HOW_TO_USE', 'https://wallmessage.com/plugin/how-to-use-wallmessage-plugin-for-woocommerce/' );
}

// Wallmessage website url
if ( ! defined( 'WALLMESSAGE_SITE' ) ) {
	define('WALLMESSAGE_SITE', 'https://wallmessage.com/' );
}

// WallMessage WP Plufin Version
if ( ! defined( 'WPWHATSAPPPM_VERSION' ) ) {
	define('WPWHATSAPPPM_VERSION', $plugin_data['Version'] );
}

// Minimum Version Required
if ( ! defined( 'WPWHATSAPPPM__MINIMUM_WP_VERSION' ) ) {
	define('WPWHATSAPPPM__MINIMUM_WP_VERSION', '5.0' );
}

// WP admin dashbord url
if ( ! defined( 'WPWHATSAPPPM_ADMIN_URL' ) ) {
	define('WPWHATSAPPPM_ADMIN_URL', get_admin_url());
}

// Language Files Directory
if ( ! defined( 'WPWHATSAPPPM_LANG' ) ) {
	define('WPWHATSAPPPM_LANG',trailingslashit(WPWHATSAPPPM_DIR.'languages'));
}

// inc folder dirctory path
if ( ! defined( 'WPWHATSAPPPM_INC' ) ) {
	define('WPWHATSAPPPM_INC',trailingslashit(WPWHATSAPPPM_DIR.'inc'));
}

// template Folder directory path
if ( ! defined( 'WPWHATSAPPPM_TPL' ) ) {
	define('WPWHATSAPPPM_TPL',trailingslashit(WPWHATSAPPPM_DIR.'tpl'));
}

// assets Folder directory URL
if ( ! defined( 'WPWHATSAPPPM_ASSETS' ) ) {
	define('WPWHATSAPPPM_ASSETS',trailingslashit(WPWHATSAPPPM_URL.'assets'));
}

// css assets Folder directory URL
if ( ! defined( 'WPWHATSAPPPM_CSS' ) ) {
	define('WPWHATSAPPPM_CSS',trailingslashit(WPWHATSAPPPM_URL.'assets'.'/'.'css'));
}

// js assets Folder directory URL
if ( ! defined( 'WPWHATSAPPPM_JS' ) ) {
	define('WPWHATSAPPPM_JS',trailingslashit(WPWHATSAPPPM_URL.'assets'.'/'.'js'));
}

// image assets Folder directory URL
if ( ! defined( 'WPWHATSAPPPM_IMG' ) ) {
	define('WPWHATSAPPPM_IMG',trailingslashit(WPWHATSAPPPM_URL.'assets'.'/'.'images'));
}



/*
*	useful 
*/

// mobile validator Regx
if ( ! defined( 'WPWHATSAPPPM_MOB_REGEX' ) ) {
	define('WPWHATSAPPPM_MOB_REGEX', '/^[\+|\(|\)|\d|\- ]*$/');
}


//current date and time
if ( ! defined( 'WPWHATSAPPPM_CURRENT_DATE' ) ) {
	define('WPWHATSAPPPM_CURRENT_DATE', current_datetime()->format('Y-m-d H:i:s'));
}