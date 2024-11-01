<?php

namespace wallmessage;

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}

/********************************************\
		  config Class
\********************************************/


class Configs
{

    /**
     * Get Plugin Configs
     *
     * @param string $config_name
     *
     * @return mixed|void
     */
    public static function getConfigs( $config_name = '')
    {
        if (!$config_name) {
            global $wpwhatsapppm_configs;

            return $wpwhatsapppm_configs;
        }
		

        return get_option($config_name);
    }


    /**
     * Get Config
     *
     * @param $config_name
     * @param string $setting_name
     *
     * @return string
     */
    public static function getConfig($config_name,$setting_name = '')
    {
        if (!$setting_name) {

            global $wpwhatsapppm_configs;

            return isset($wpwhatsapppm_configs[$config_name]) ? $wpwhatsapppm_configs[$config_name] : '';
        }
        $configs = self::getConfigs($setting_name);

        return isset($configs[$config_name]) ? $configs[$config_name] : '';

    }

    /**
     * Add an config
     *
     * @param $config_name
     * @param $config_value
     */
    public static function addConfig($config_name, $config_value)
    {
        add_option($config_name, $config_value);
    }

    /**
     * Update Config
     *
     * @param $key
     * @param $value
     */
    public static function updateConfig($key, $value)
    {

        $config_name = 'wallmessage_settings';

        $configs       = self::getConfigs();
        $configs[$key] = $value;

        update_option($config_name, $options);
    }

}