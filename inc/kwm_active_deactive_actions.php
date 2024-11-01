<?php 

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}

//@TODO: some action may do after activate/deactivate
// write activation && deactivation hook'callback
function kwm_activate(){}
function kwm_deactivate(){}

register_activation_hook(__FILE__,'kwm_activate');
register_deactivation_hook(__FILE__,'kwm_deactivate');