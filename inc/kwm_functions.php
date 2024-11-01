<?php 

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}

/********************************************\
		useful and global functions
\********************************************/


//initial wallmessage sender
function kwm_wallmessage_initial()
{
	require_once WPWHATSAPPPM_INC . 'class_kwm_config.php';
	
    return \WALLMESSAGE\Sender::initial();
}


//Translate
function kwm_translate_plugin() {
    load_plugin_textdomain( 'wallmessage', false ,WPWHATSAPPPM_LANG );
}
add_action('plugins_loaded', 'kwm_translate_plugin');



/**
 * create Table
 *
 */
 function kwm_create_tables()
{
	
	global $wpdb;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	$charset_collate = $wpdb->get_charset_collate();

	$table_name = $wpdb->prefix . 'kwm_wpwhatsapppm';
	if ($wpdb->get_var("show tables like '{$table_name}'") != $table_name) {
		$create_sms_send = ("CREATE TABLE IF NOT EXISTS {$table_name}(
            log_id int(10) NOT NULL auto_increment,
            date DATETIME,
            sender VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            recipient TEXT NOT NULL,
  			response TEXT NOT NULL,
  			status varchar(10) NOT NULL,
    		comment TEXT NULL DEFAULT NULL ,
            PRIMARY KEY(log_id)) $charset_collate");

		dbDelta($create_sms_send);
	}
}


register_activation_hook( WPWHATSAPPPM__FILE__, 'kwm_create_tables' );