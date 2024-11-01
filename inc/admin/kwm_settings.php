<?php 

namespace WALLMESSAGE;

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}


/*
* Settings and configs
*/
class Settings
{
    public $setting_name;
    public $configs = array();
    private $optionNames = 'wallmessage_settings';
    

    /**
     * @return string
     */
    private function getCurrentOptionName()
    {

        return $this->optionNames;
    }

    public function __construct()
    {
        $this->setting_name   = $this->getCurrentOptionName();

        $this->get_settings();
        $this->configs = get_option($this->setting_name);
        

        if (empty($this->configs)) {
            update_option($this->setting_name, array());
        }

		add_action('admin_menu', array($this, 'add_settings_menu'), 11);


        if (isset($_GET['page']) and sanitize_text_field($_GET['page']) == 'wallmessage-configs' or isset($_POST['option_page']) and (sanitize_text_field($_POST['option_page']) ==  $this->optionNames)) {
            add_action('admin_init', array($this, 'register_settings'));
        }

    }
    
	/**
     * Add Configs to admin dashboard
     * */
    public function add_settings_menu()
    {
        add_submenu_page('wallmessage', __('Configs', 'wallmessage'), __('Configs', 'wallmessage'), 'kmwwallmessage_Configs', 'wallmessage-configs', array($this, 'render_settings') );
    }

    /**
     * Gets saved settings from WP core
     *
     * @return array
     * @since 2.0
     */
    public function get_settings()
    {
        $settings = get_option($this->setting_name);
        if (!$settings) {
            update_option($this->setting_name, array(
                'rest_api_status' => 1,
            ));
        }

        return apply_filters('wallmessage_get_settings', $settings);
    }

    /**
     * Registers settings in WP core
     *
     * @return          void
     * @since           2.0
     */
    public function register_settings()
    {
        if (false == get_option($this->setting_name)) {
            add_option($this->setting_name);
        }
        foreach ($this->get_registered_settings() as $tab => $settings) {
            add_settings_section("{$this->setting_name}_{$tab}", __return_null(), '__return_false', "{$this->setting_name}_{$tab}");
            
            if (empty($settings)) {
                return;
            }

            foreach ($settings as $option) {
                
                $name     = isset($option['name']) ? $option['name'] : '';
                $optionId = $option['id'];

                add_settings_field("$this->setting_name[$optionId]", $name, array($this, "{$option['type']}_callback"), "{$this->setting_name}_{$tab}", "{$this->setting_name}_{$tab}",
                    array(
                        'id'          => $optionId ? $optionId : null,
                        'desc'        => !empty($option['desc']) ? $option['desc'] : '',
                        'name'        => isset($option['name']) ? $option['name'] : null,
                        'after_input' => isset($option['after_input']) ? $option['after_input'] : null,
                        'section'     => $tab,
                        'size'        => isset($option['size']) ? $option['size'] : null,
                        'options'     => isset($option['options']) ? $option['options'] : '',
                        'std'         => isset($option['std']) ? $option['std'] : '',
                        'doc'         => isset($option['doc']) ? $option['doc'] : '',
                        'class'       => "tr-{$option['type']}",
                        'label_for'   => true,
                    )
                );

                register_setting($this->setting_name, $this->setting_name, array($this, 'settings_sanitize'));
            }
        }
    }

    /**
     * Gets settings tabs
     *
     * @return   array Tabs list
     */
    public function get_tabs()
    {
        $tabs = array(
            /*
             * Main plugin tabs
             */
            'general'          => __('General Config', 'wallmessage'),
            'wallmessageapi'   => __('WallMessage Api Config', 'wallmessage'),
            'woocommerce'      => __('WooCommerce Config', 'wallmessage'),
			'sendpm'      	   => __('Send PM', 'wallmessage'),
        );

        return apply_filters("{$this->setting_name}_tabs", $tabs);
    }

    /**
     * Sanitizes and saves settings after submit
     *
     * @param array $input Settings input
     *
     * @return              array New settings
     *
     */
    public function settings_sanitize($input = array())
    {
        if (!isset($_POST['_wp_http_referer']) || empty($_POST['_wp_http_referer'])) {
            return $input;
        }
		
		$referrer_sanitized = sanitize_text_field($_POST['_wp_http_referer']);
        parse_str($referrer_sanitized, $referrer);

        $settings = $this->get_registered_settings();
        $tab      = isset($referrer['tab']) ? $referrer['tab'] : 'wp';

        $input = $input ? $input : array();
        $input = apply_filters("{$this->setting_name}_{$tab}_sanitize", $input);

        // Loop through each setting being saved and pass it through a sanitization filter
        foreach ($input as $key => $value) {
            // Get the setting type (checkbox, select, etc)
            $type = isset($settings[$tab][$key]['type']) ? $settings[$tab][$key]['type'] : false;

            if ($type) {
                // Field type specific filter
                $input[$key] = apply_filters("{$this->setting_name}_sanitize_{$type}", $value, $key);
            }

            // General filter
            $input[$key] = apply_filters("{$this->setting_name}_sanitize", $value, $key);
        }

        // Loop through the whitelist and unset any that are empty for the tab being saved
        if (!empty($settings[$tab])) {
            foreach ($settings[$tab] as $key => $value) {

                // settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
                if (is_numeric($key)) {
                    $key = $value['id'];
                }

                if (empty($input[$key])) {
                    unset($this->configs[$key]);
                }
            }
        }
		
		
		
        // Merge our new settings with the existing
        $output = array_merge($this->configs, $input);

		if(isset($_POST['test-api']) && !empty($_POST['test-api'])){
			$test_api_result = $this->test_api($input['wallmessageapi_key'],$input['wallmessageapi_url']);
			add_settings_error('wallmessage-notices', '', __('API test result: ', 'wallmessage').wp_kses_data($test_api_result['message']), wp_kses_data($test_api_result['is_online'])?'updated':'error');
			return $output;
		}
		
        add_settings_error('wallmessage-notices', '', __('Settings updated', 'wallmessage'), 'updated');

        return $output;
    }

    /**
     * Get settings fields
     *
     * @return          array Fields
     */
    public function get_registered_settings()
    {
        $options = array(
            'enable'  => __('Enable', 'wallmessage'),
            'disable' => __('Disable', 'wallmessage')
        );
        

        // Set WooCommerce settings
        if (class_exists('WooCommerce')) {
            $wc_settings = array(
                'wc_fields'                    => array(
                    'id'   => 'wc_fields',
                    'name' => __('General', 'wallmessage'),
                    'type' => 'header'
                ),
                'wc_mobile_field'              => array(
                    'id'      => 'wc_mobile_field',
                    'name'    => __('Choose the mobile field', 'wallmessage'),
                    'type'    => 'select',
                    'options' => array(
                        'disable'            => __('Disable (No field)', 'wallmessage'),
                        'add_new_field'      => __('Add a new field in the checkout form', 'wallmessage'),
                        'used_current_field' => __('Use the current phone field in the bill', 'wallmessage'),
                    ),
                    'desc'    => __('Choose from which field you get numbers for sending PM.', 'wallmessage')
                ),
				'wc_mobile_field_title'   => array(
                    'id'      => 'wc_mobile_field_title',
                    'name'    => __('Mobile field title', 'wallmessage'),
                    'type'    => 'text',
					'desc'    => __('In `Add a new field in the checkout form` mode title will be use.', 'wallmessage')
                ),
				'wc_mobile_field_placeholder'   => array(
                    'id'      => 'wc_mobile_field_placeholder',
                    'name'    => __('Mobile field placeholder', 'wallmessage'),
                    'type'    => 'text',
					'desc'    => __('In `Add a new field in the checkout form` mode title will be use.', 'wallmessage')
                ),
				
                'wc_notify_product'            => array(
                    'id'   => 'wc_notify_product',
                    'name' => __('Notify for new product', 'wallmessage'),
                    'type' => 'header'
                ),
                'wc_notify_product_enable'     => array(
                    'id'      => 'wc_notify_product_enable',
                    'name'    => __('Send PM', 'wallmessage'),
                    'type'    => 'checkbox',
                    'options' => $options,
                    'desc'    => __('Send PM when publish new a product', 'wallmessage')
                ),
                'wc_notify_product_receiver'   => array(
                    'id'      => 'wc_notify_product_receiver',
                    'name'    => __('PM receiver', 'wallmessage'),
                    'type'    => 'select',
                    'options' => array(
                        'users'      => __('Customers (Users)', 'wallmessage')
                    ),
                    'desc'    => __('Please select the receiver of PM', 'wallmessage')
                ),
                
                'wc_notify_product_message'    => array(
                    'id'   => 'wc_notify_product_message',
                    'name' => __('Message body', 'wallmessage'),
                    'type' => 'textarea',
                    'desc' => __('Enter the contents of the PM message.', 'wallmessage') . '<br>' .
                        sprintf(
                            __('Product title: %s, Product url: %s, Product date: %s, Product price: %s', 'wallmessage'),
                            '<code>%product_title%</code>',
                            '<code>%product_url%</code>',
                            '<code>%product_date%</code>',
                            '<code>%product_price%</code>'
                        )
                ),
                'wc_notify_order'              => array(
                    'id'   => 'wc_notify_order',
                    'name' => __('Notify for new order', 'wallmessage'),
                    'type' => 'header'
                ),
                'wc_notify_order_enable'       => array(
                    'id'      => 'wc_notify_order_enable',
                    'name'    => __('Send PM', 'wallmessage'),
                    'type'    => 'checkbox',
                    'options' => $options,
                    'desc'    => __('Send PM when submit new order', 'wallmessage')
                ),
                'wc_notify_order_receiver'     => array(
                    'id'   => 'wc_notify_order_receiver',
                    'name' => __('PM receiver', 'wallmessage'),
                    'type' => 'text',
                    'desc' => __('Please enter mobile number for get PM. You can separate the numbers with the Latin comma.', 'wallmessage')
                ),
                'wc_notify_order_message'      => array(
                    'id'   => 'wc_notify_order_message',
                    'name' => __('Message body', 'wallmessage'),
                    'type' => 'textarea',
                    'desc' => __('Enter the contents of the PM message.', 'wallmessage') . '<br>' .
                        sprintf(
                            __('Billing First Name: %s,Billing Last Name: %s, Billing Company: %s, Billing Address: %s, Billing Phone Number: %s, Order ID: %s, Order number: %s, Order Total: %s, Order edit URL: %s, Order Items: %s, Order status: %s', 'wallmessage'),
                            '<code>%billing_first_name%</code>',
                            '<code>%billing_last_name%</code>',
                            '<code>%billing_company%</code>',
                            '<code>%billing_address%</code>',
                            '<code>%billing_phone%</code>',
                            '<code>%order_id%</code>',
                            '<code>%order_number%</code>',
                            '<code>%order_total%</code>',
                            '<code>%order_edit_url%</code>',
                            '<code>%order_items%</code>',
                            '<code>%status%</code>'
                        )
                ),
                'wc_notify_customer'           => array(
                    'id'   => 'wc_notify_customer',
                    'name' => __('Notify to customer order', 'wallmessage'),
                    'type' => 'header'
                ),
                'wc_notify_customer_enable'    => array(
                    'id'      => 'wc_notify_customer_enable',
                    'name'    => __('Send PM', 'wallmessage'),
                    'type'    => 'checkbox',
                    'options' => $options,
                    'desc'    => __('Send PM to customer when submit the order', 'wallmessage')
                ),
                'wc_notify_customer_message'   => array(
                    'id'   => 'wc_notify_customer_message',
                    'name' => __('Message body', 'wallmessage'),
                    'type' => 'textarea',
                    'desc' => __('Enter the contents of the PM message.', 'wallmessage') . '<br>' .
                        sprintf(
                            __('Order ID: %s, Order number: %s, Order status: %s, Order Items: %s, Order Total: %s, Customer name: %s, Customer family: %s, Order view URL: %s, Order payment URL: %s', 'wallmessage'),
                            '<code>%order_id%</code>',
                            '<code>%order_number%</code>',
                            '<code>%status%</code>',
                            '<code>%order_items%</code>',
                            '<code>%order_total%</code>',
                            '<code>%billing_first_name%</code>',
                            '<code>%billing_last_name%</code>',
                            '<code>%order_edit_url%</code>',
                            '<code>%order_pay_url%</code>'
                        )
                ),
                'wc_notify_stock'              => array(
                    'id'   => 'wc_notify_stock',
                    'name' => __('Notify of stock', 'wallmessage'),
                    'type' => 'header'
                ),
                'wc_notify_stock_enable'       => array(
                    'id'      => 'wc_notify_stock_enable',
                    'name'    => __('Send PM', 'wallmessage'),
                    'type'    => 'checkbox',
                    'options' => $options,
                    'desc'    => __('Send PM when stock is low', 'wallmessage')
                ),
                'wc_notify_stock_receiver'     => array(
                    'id'   => 'wc_notify_stock_receiver',
                    'name' => __('PM receiver', 'wallmessage'),
                    'type' => 'text',
                    'desc' => __('Please enter mobile number for get PM. You can separate the numbers with the Latin comma.', 'wallmessage')
                ),
                'wc_notify_stock_message'      => array(
                    'id'   => 'wc_notify_stock_message',
                    'name' => __('Message body', 'wallmessage'),
                    'type' => 'textarea',
                    'desc' => __('Enter the contents of the PM message.', 'wallmessage') . '<br>' .
                        sprintf(
                            __('Product ID: %s, Product name: %s', 'wallmessage'),
                            '<code>%product_id%</code>',
                            '<code>%product_name%</code>'
                        )
                ),
                'wc_notify_status'             => array(
                    'id'   => 'wc_notify_status',
                    'name' => __('Notify of status', 'wallmessage'),
                    'type' => 'header'
                ),
                'wc_notify_status_enable'      => array(
                    'id'      => 'wc_notify_status_enable',
                    'name'    => __('Send PM', 'wallmessage'),
                    'type'    => 'checkbox',
                    'options' => $options,
                    'desc'    => __('Send PM to customer when status is changed', 'wallmessage')
                ),
                'wc_notify_status_message'     => array(
                    'id'   => 'wc_notify_status_message',
                    'name' => __('Message body', 'wallmessage'),
                    'type' => 'textarea',
                    'desc' => __('Enter the contents of the PM message.', 'wallmessage') . '<br>' .
                        sprintf(
                            __('Order status: %s, Order number: %s, Customer name: %s, Customer family: %s, Order view URL: %s, Order payment URL: %s', 'wallmessage'),
                            '<code>%status%</code>',
                            '<code>%order_number%</code>',
                            '<code>%customer_first_name%</code>',
                            '<code>%customer_last_name%</code>',
                            '<code>%order_view_url%</code>',
                            '<code>%order_pay_url%</code>'
                        )
                ),
            );
        } else {
            $wc_settings = array(
                'wc_fields' => array(
                    'id'   => 'wc_fields',
                    'name' => __('WooCommerce is Not active', 'wallmessage'),
                    'type' => 'notice',
                    'desc' => __('WooCommerce should be installed and activated to run this tab.', 'wallmessage')
                ));
        }

 
        # General Settings
        $settings = apply_filters('wallmessage_registered_settings', array(
            /**
             * General fields
             */
            'general'              => apply_filters('wallmessage_general_settings', array(
                'admin_title'         => array(
                    'id'   => 'admin_title',
                    'name' => __('General', 'wallmessage'),
                    'type' => 'header'
                ),
                'admin_mobile_number' => array(
                    'id'   => 'admin_mobile_number',
                    'name' => __('Admin mobile number', 'wallmessage'),
                    'type' => 'text',
                    'desc' => __('Admin mobile number for get any PM notifications', 'wallmessage')
                ),
                'mobile_county_code'  => array(
                    'id'   => 'mobile_county_code',
                    'name' => __('Mobile country code', 'wallmessage'),
                    'type' => 'text',
                    'desc' => __('Enter your mobile country code for prefix numbers.<br> For example if you enter +98 The final number will be +989123456789', 'wallmessage')
                ),
            )),

            /**
             * WallMessage Api fields
             */
            'wallmessageapi'           => apply_filters('wallmessage_apis_settings', array(
                // Wall Message Api
                'wallmessageapi_title'             => array(
                    'id'   => 'wallmessageapi_title',
                    'name' => __('WallMessage Api configuration', 'wallmessage'),
                    'type' => 'header'
                ),
                'wallmessageapi_help'              => array(
                    'id'      => 'wallmessageapi_help',
                    'name'    => __('WallMessage Api description', 'wallmessage'),
                    'type'    => 'html',
                    'options' => Sender::help(),
                ),
               
                'wallmessageapi_key'               => array(
                    'id'   => 'wallmessageapi_key',
                    'name' => __(' Api key', 'wallmessage'),
                    'type' => 'text',
                    'desc' => __('Enter API key of WallMessage', 'wallmessage')
                ),
                'wallmessageapi_url'               => array(
                    'id'   => 'wallmessageapi_url',
                    'name' => __(' Api URL', 'wallmessage'),
                    'type' => 'text',
                    'desc' => __('Enter API URL of WallMessage', 'wallmessage')
                ),
				'wallmessageapi_test_api'               => array(
                    'id'   => 'wallmessageapi_test_api',
                    'name' => __('Test Connectivity', 'wallmessage'),
                    'type' => 'test_botton',
                ),
            )),           
			
            'woocommerce'      => apply_filters('wallmessage_wc_settings', $wc_settings),
            
        ));

        return $settings;
    }

    
    private function isCurrentTab($tab)
    {
		$is_page = isset($_REQUEST['page']) && sanitize_text_field($_REQUEST['page']) == 'wallmessage-configs';
		$is_tab  = isset($_REQUEST['tab']) && sanitize_text_field($_REQUEST['tab']) == $tab;
        return  $is_page && $is_tab;
    }

   

	//header type field handeler
    public function header_callback($args)
    {
        
        $html = '';
        if (isset($args['desc'])) {
            $html .= $args['desc'];
        }
    }


	//html type field handeler
    public function html_callback($args)
    {
        echo wp_kses_post($args['options']);
    }


	//notic type field handeler
    public function notice_callback($args)
    {
        echo wp_kses(
				sprintf('%s', $args['desc'])
				,array(
					'p'=>array(
						'class'=>array(),
					),
					'div'=>array(
						'class'=>array(),
					),
					'a'=>array(
						'class'=>array(),
						'href'=>array(),
						'id'=>array(),
					),
				)
			);
    }


	//checkbox type field handeler
    public function checkbox_callback($args)
    {
        $checked = isset($this->configs[$args['id']]) ? checked(1, $this->configs[$args['id']], false) : '';
        $html    = sprintf('<input type="checkbox" id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s]" value="1" %2$s /><label for="' . $this->setting_name . '[%1$s]"> ' . __('Active', 'wallmessage') . '</label><p class="description">%3$s</p>', esc_attr($args['id']), esc_attr($checked), wp_kses_post($args['desc']));
        echo wp_kses(
					$html
				,array(
					'input'=>array(
						'id'=>array(),
						'name'=>array(),
						'type'=>array(),
						'class'=>array(),
						'value'=>array(),
						'checked'=>array(),
					),
					'p'=>array(
							'class'=>array(),
							
					),
					'label'=>array(
							'for'=>array(),
					),
				)
			);
    }


	//radio type field handeler
    public function radio_callback($args)
    {
        $html = '';
        foreach ($args['options'] as $key => $option) :
            $checked = false;

            if (isset($this->configs[$args['id']]) && $this->configs[$args['id']] == $key) {
                $checked = true;
            } elseif (isset($args['std']) && $args['std'] == $key && !isset($this->configs[$args['id']])) {
                $checked = true;
            }
            $html .= sprintf('<input name="' . $this->setting_name . '[%1$s]"" id="' . $this->setting_name . '[%1$s][%2$s]" type="radio" value="%2$s" %3$s /><label for="' . $this->setting_name . '[%1$s][%2$s]">%4$s</label>&nbsp;&nbsp;', esc_attr($args['id']), esc_attr($key), checked(true, $checked, false), $option);
        endforeach;
        $html .= sprintf('<p class="description">%1$s</p>', wp_kses_post($args['desc']));
          echo wp_kses(
					$html
				,array(
					'input'=>array(
						'id'=>array(),
						'name'=>array(),
						'type'=>array(),
						'class'=>array(),
						'value'=>array(),
						'checked'=>array(),
					),
					'p'=>array(
							'class'=>array(),
					),
					'label'=>array(
							'for'=>array(),
					),
				)
			);
    }


	//text type field handeler
    public function text_callback($args)
    {
        if (isset($this->configs[$args['id']]) and $this->configs[$args['id']]) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $after_input = (isset($args['after_input']) && !is_null($args['after_input'])) ? $args['after_input'] : '';
        $size        = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
        $html        = sprintf('<input dir="auto" type="text" class="%1$s-text" id="' . $this->setting_name . '[%2$s]" name="' . $this->setting_name . '[%2$s]" value="%3$s"/>%4$s<p class="description">%5$s</p>', esc_attr($size), esc_attr($args['id']), esc_attr(stripslashes($value)), $after_input, wp_kses_post($args['desc']));
        echo wp_kses(
					$html
				,array(
					'input'=>array(
						'id'=>array(),
						'name'=>array(),
						'type'=>array(),
						'class'=>array(),
						'dir'=>array(),
						'value'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
		
    }


	//number type field handeler
    public function number_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $max  = isset($args['max']) ? $args['max'] : 999999;
        $min  = isset($args['min']) ? $args['min'] : 0;
        $step = isset($args['step']) ? $args['step'] : 1;

        $size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
        $html = sprintf('<input dir="auto" type="number" step="%1$s" max="%2$s" min="%3$s" class="%4$s-text" id="' . $this->setting_name . '[%5$s]" name="' . $this->setting_name . '[%5$s]" value="%6$s"/><p class="description"> %7$s</p>', esc_attr($step), esc_attr($max), esc_attr($min), esc_attr($size), esc_attr($args['id']), esc_attr(stripslashes($value)), wp_kses_post($args['desc']));
        echo wp_kses(
					$html
				,array(
					'input'=>array(
						'id'=>array(),
						'name'=>array(),
						'type'=>array(),
						'class'=>array(),
						'dir'=>array(),
						'max'=>array(),
						'min'=>array(),
						'step'=>array(),
						'value'=>array(),
					),
					'div'=>array(
								'class'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }


	//textarea type field handeler
    public function textarea_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $html = sprintf('<textarea dir="auto" class="large-text" cols="50" rows="5" id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s]">%2$s</textarea><div class="description"> %3$s</div>', esc_attr($args['id']), esc_textarea(stripslashes($value)), wp_kses_post($args['desc']));
        echo wp_kses(
					$html
				,array(
					'textarea'=>array(
						'id'=>array(),
						'name'=>array(),
						'class'=>array(),
						'dir'=>array(),
						'cols'=>array(),
						'rows'=>array(),
					),
					'div'=>array(
								'class'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }


	//password type field handeler
    public function password_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
        $html = sprintf('<input type="password" class="%1$s-text" id="' . $this->setting_name . '[%2$s]" name="' . $this->setting_name . '[%2$s]" value="%3$s"/><p class="description"> %4$s</p>', esc_attr($size), esc_attr($args['id']), esc_attr($value), wp_kses_post($args['desc']));

          echo wp_kses(
					$html
				,array(
					'input'=>array(
						'id'=>array(),
						'name'=>array(),
						'class'=>array(),
						'type'=>array(),
						'value'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }

	//missing type field handeler
    public function missing_callback($args)
    {
        echo wp_kses($html,array('&ndash;'=>array()));
        return false;
    }

	//select type field handeler
    public function select_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $html = sprintf('<select id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s]">', esc_attr($args['id']));

        foreach ($args['options'] as $option => $name) {
            $selected = selected($option, $value, false);
            $html     .= sprintf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($option), esc_attr($selected), $name);
        }

        $html .= sprintf('</select><p class="description"> %1$s</p>', wp_kses_post($args['desc']));

         echo wp_kses(
					$html
				,array(
					'select'=>array(
						'id'=>array(),
						'name'=>array(),
					),
					'option'=>array(
						'value'=>array(),
						'selected'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }
	
	//multiselect type field handeler
    public function multiselect_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $html     = sprintf('<select id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s][]" multiple="true" class="js-wallmessage-select2"/>', esc_attr($args['id']));
        $selected = '';

        foreach ($args['options'] as $k => $name) :
            foreach ($name as $option => $name) :
                if (isset($value) and is_array($value)) {
                    if (in_array($option, $value)) {
                        $selected = " selected='selected'";
                    } else {
                        $selected = '';
                    }
                }
                $html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($option), esc_attr($selected), $name);
            endforeach;
        endforeach;

        $html .= sprintf('</select><p class="description"> %1$s</p>', wp_kses_post($args['desc']));

        echo wp_kses(
					$html
				,array(
					'select'=>array(
						'id'=>array(),
						'name'=>array(),
						'multiple'=>array(),
						'class'=>array(),
						'selected'=>array(),
					),
					'option'=>array(
						'value'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }


	//countryselect type field handeler
    public function countryselect_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $html     = sprintf('<select id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s][]" multiple="true" class="js-wallmessage-select2"/>', esc_attr($args['id']));
        $selected = '';

        foreach ($args['options'] as $option => $country) :
            if (isset($value) and is_array($value)) {
                if (in_array($country['code'], $value)) {
                    $selected = " selected='selected'";
                } else {
                    $selected = '';
                }
            }
            $html .= sprintf('<option value="%1$s" %2$s>%3$s</option>', esc_attr($country['code']), esc_attr($selected), $country['name']);
        endforeach;

        $html .= sprintf('</select><p class="description"> %1$s</p>', wp_kses_post($args['desc']));

        echo wp_kses(
					$html
				,array(
					'select'=>array(
						'id'=>array(),
						'class'=>array(),
						'name'=>array(),
						'selected'=>array(),
					),
					'option'=>array(
						'value'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }

	//color type field handeler
    public function color_select_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $html = sprintf('<select id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s]">', esc_attr($args['id']));

        foreach ($args['options'] as $option => $color) :
            $selected = selected($option, $value, false);
            $html     .= esc_attr('<option value="%1$s" %2$s>%3$s</option>', esc_attr($option), esc_attr($selected), $color['label']);
        endforeach;

        $html .= sprintf('</select><p class="description"> %1$s</p>', wp_kses_post($args['desc']));

        echo wp_kses(
					$html
				,array(
					'select'=>array(
						'id'=>array(),
						'name'=>array(),
					),
					'option'=>array(
						'value'=>array(),
					),
					'p'=>array(
								'class'=>array(),
					),
				)
			);
    }


	//rich_editor type field handeler
    public function rich_editor_callback($args)
    {
        global $wp_version;

        $id = $args['id'];

        if (isset($this->configs[$id])) {
            $value = $this->configs[$id];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        if ($wp_version >= 3.3 && function_exists('wp_editor')) {
            $html = wp_editor(stripslashes($value), "$this->setting_name[$id]", array('textarea_name' => "$this->setting_name[$id]"));
        } else {
            $html = sprintf('<textarea class="large-text" rows="10" id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s]">' . esc_textarea(stripslashes($value)) . '</textarea>', esc_attr($args['id']));
        }

        $html .= sprintf('<p class="description"> %1$s</p>', wp_kses_post($args['desc']));

        echo wp_kses(
					$html
				,array(
					'p'=>array(
						'class'=>array(),
					),
				)
			);
    }
	
	//upload type field handeler
    public function upload_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $size = (isset($args['size']) && !is_null($args['size'])) ? $args['size'] : 'regular';
        $html = sprintf('<input type="text" class="%1$s-text wallmessage_upload_field" id="' . $this->setting_name . '[%2$s]" name="' . $this->setting_name . '[%2$s]" value="%3$s"/><span>&nbsp;<input type="button" class="' . $this->setting_name . '_upload_button button-secondary" value="%4$s"/></span><p class="description"> %5$s</p>', esc_attr($size), esc_attr($args['id']), esc_attr(stripslashes($value)), __('Upload File', 'wallmessage'), wp_kses_post($args['desc']));

        echo wp_kses(
					$html
				,array(
					'span'=>array(
						'class'=>array(),
					),
					'p'=>array(
						'class'=>array(),
					),
					'input'=>array(
								'type'=>array(),
								'class'=>array(),
								'value'=>array(),
								'name'=>array(),
								'id'=>array(),
					),
				)
			);
    }


	//color type field handeler
    public function color_callback($args)
    {
        if (isset($this->configs[$args['id']])) {
            $value = $this->configs[$args['id']];
        } else {
            $value = isset($args['std']) ? $args['std'] : '';
        }

        $default = isset($args['std']) ? $args['std'] : '';
        $html    = sprintf('<input type="text" class="wallmessage-color-picker" id="' . $this->setting_name . '[%1$s]" name="' . $this->setting_name . '[%1$s]" value="%2$s" data-default-color="%3$s" /><p class="description"> %4$s</p>', esc_attr($args['id']), esc_attr($value), esc_attr($default), wp_kses_post($args['desc']));

        echo wp_kses(
					$html
				,array(
					'p'=>array(
						'class'=>array(),
					),
					'input'=>array(
								'type'=>array(),
								'class'=>array(),
								'value'=>array(),
								'name'=>array(),
								'id'=>array(),
								'data-default-color'=>array(),
					),
				)
			);
    }
	
	//color type field handeler
    public function test_botton_callback($args)
    {
	 submit_button('Click to test','test-api','test-api',false); 
	}

	//render setting page html
    public function render_settings()
    {
		$settings 	= new Settings(); 
		$tabs_array = $this->get_tabs();
        $active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs_array) ? sanitize_text_field($_GET['tab']) : 'general';
        $page_title = $tabs_array[$active_tab];
		
		if( $active_tab !='wallmessageapi' &&( !isset($this->configs['wallmessageapi_key']) || $this->configs['wallmessageapi_key'] =='' || !isset($this->configs['wallmessageapi_url']) || $this->configs['wallmessageapi_url'] =='' )){
			
			wp_safe_redirect(add_query_arg(array(
				'settings-updated' => false,
				'page'			   => 'wallmessage-configs',
				'tab'              => 'wallmessageapi',
			)));
			exit;
		}
        if( !isset($this->configs['wallmessageapi_key']) || $this->configs['wallmessageapi_key'] =='' || !isset($this->configs['wallmessageapi_url']) || $this->configs['wallmessageapi_url'] =='' ){
			add_settings_error('wallmessage-notices', '', __('Setting Wallmessage API is mandatory!', 'wallmessage'),'error');
		}
		ob_start();
        ?>
        <div class="wrap wallmessage-wrap wallmessage-settings-wrap">
		
            <?php require_once  WPWHATSAPPPM_TPL. 'header.php'; ?>
			<div class="wallmessage-wrap__main">                
				<div class="wallmessage-tab-group">
					<?php require_once  WPWHATSAPPPM_TPL. 'left-menu.php'; ?>
					
					<?php if($active_tab == 'sendpm'): ?>
						<?php require_once  WPWHATSAPPPM_TPL. 'send-pm.php'; ?>
					<?php else: ?>
						<?php require_once  WPWHATSAPPPM_TPL. 'settings.php'; ?>
					<?php endif; ?>
				</div>	
			</div>
        </div>
        <?php
        echo ob_get_clean();
    }

    /*
     * Get list Post Type
     */
    public function get_list_post_type($args = array())
    {
        // vars
        $post_types = array();

        // extract special arg
        $exclude   = array();

        $exclude[] = 'shop_order'; //WooCommerce Shop Order
        $exclude[] = 'shop_coupon'; //WooCommerce Shop coupon

        // get post type objects
        $objects = get_post_types($args, 'objects');
        foreach ($objects as $k => $object) {
            if (in_array($k, $exclude)) {
                continue;
            }
            if ($object->_builtin && !$object->public) {
                continue;
            }
            $post_types[] = array($object->cap->publish_posts . '|' . $object->name => $object->label);
        }

        // return
        return $post_types;
    }
	
	
	public function test_api($wallmessageapi_key,$wallmessageapi_url){
		$result = [
					'is_online'	=>false,
					'message'	=>'',
				];
		$response = wp_remote_post($wallmessageapi_url . "checkStatus?token=" . $wallmessageapi_key,
						[
							'headers'   => [ 'Content-Type' => 'application/json' ],
							'body'       => '{}',
							'data_format' => 'body'
						]
					);
		if (!is_wp_error($response) && empty($response->errors)){
			$arr_response = json_decode(sanitize_text_field($response['body']),true);
			if (json_last_error() === JSON_ERROR_NONE) {
				if(isset($arr_response['isSuccess']) && rest_sanitize_boolean($arr_response['isSuccess']) == true){
					if(isset($arr_response['value']['isOnline'])){
						$result['is_online'] = rest_sanitize_boolean($arr_response['value']['isOnline']);
						$result['message'] = sanitize_text_field($arr_response['value']['state'])." (".__('EXP Date: ','wallmessage').sanitize_text_field($arr_response['value']['expireDate']).")";
					}else{
						$result['is_online'] = false;
						$result['message'] = __('Unspecified Error in Responce.','wallmessage');
					}
				}else{
					$result['is_online'] = false;
					$result['message'] = __('Unspecified Error in Responce!','wallmessage');
				}
			}elseif(is_string($response['body']) == true){
				$result['is_online'] = false;
				$result['message'] = sanitize_text_field($response['body']);
			}else{
				$result['is_online'] = false;
				$result['message'] = __('Unspecified Error!','wallmessage');
			}
		}else{
			
			$result['is_online'] = false;
			if(isset($response->errors['http_request_failed']) && is_array($response->errors['http_request_failed'])){
				
				$result['message'] = implode('<br>',$response->errors['http_request_failed']);	
			}else{
				$result['message'] = __('Unspecified Error.','wallmessage');
			}
			
		}
		return $result;
	}
}
new Settings();
