<?php
/**
* Plugin Name: Wallmessage
* Plugin URI: https://wallmessage.com
* Description: Wallmessage plugin for WordPress allows you to experience faster and better communication with your users especially your customers in WooCommerce.You can easily install this plugin and connect to the wallmessage api in the shortest time.Through this service, you can automatically manage many processes of communication with your customers through WhatsApp and increase the trust and loyalty of your customers.
* Version: 1.0
* Author: wm Group
* Text Domain: wallmessage
* Domain Path: /languages
* License: GPLv3
* Requires at least: 5.6
* Requires PHP: 7.0
*/


/*@TODO: add license description and copyright
some other license and usage warning and notices
Copyright 2021-2022 wallmessage, Inc.
*/

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Access! No NO NO..');
	exit;
}



// Load Plugin default values
define('WPWHATSAPPPM__FILE__',__FILE__);
require_once 'inc/kwm_defvar.php';


// Load Activate/Deactivate actions
require_once WPWHATSAPPPM_INC.'kwm_active_deactive_actions.php';


// Load plugin Functions
require_once WPWHATSAPPPM_INC . 'kwm_functions.php';


//base class for calling apis and sending pm
require_once WPWHATSAPPPM_INC . 'class_kwm_Sender.php';


// Get plugin config
$wpwhatsapppm_configs = get_option( 'wallmessage_settings' );


// create Wallmessage ClassInstance
$wapp_pm = kwm_wallmessage_initial();


//woocomerce integeration class
require_once WPWHATSAPPPM_INC . 'class-kwm-woocommerce.php';



/*
* admin dashboard classes
*/
if(is_admin()){
 
	// admin general class.
    require_once WPWHATSAPPPM_INC . 'admin/kwm_admin.php';

	//Wallmessage class for calling apis and sending pm
    require_once WPWHATSAPPPM_INC . 'class_kwm_send_wallmessage.php';

    // Send pm page class
    require_once WPWHATSAPPPM_INC . 'admin/kwm_send_pm.php';

    // Setting page class
    require_once WPWHATSAPPPM_INC . 'admin/kwm_settings.php';
}


