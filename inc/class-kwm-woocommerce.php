<?php

class wallmessage_Woocommerce
{

	public $wapp_pm;
	public $configs;

	public function __construct()
	{
		global  $wapp_pm;

		$this->wapp_pm = $wapp_pm;
		$this->configs = get_option("wallmessage_settings");

		if (isset($this->configs['wc_mobile_field'])) {
            if($this->configs['wc_mobile_field'] == 'add_new_field'){
                add_action('woocommerce_after_order_notes', array($this, 'checkout_field'));
                add_action('woocommerce_checkout_process', array($this, 'checkout_handler'));
                add_action('woocommerce_checkout_update_order_meta', array($this, 'update_order_meta'));
            }
		}

		if (isset($this->configs['wc_notify_product_enable'])) {
			add_action('transition_post_status', array($this, 'kwm_new_product_notification'),10,3);
		}

		if (isset($this->configs['wc_notify_order_enable'])) {
			add_action('woocommerce_new_order', array($this, 'admin_order_notification'));
		}

		if (isset($this->configs['wc_notify_customer_enable'])) {
			add_action('woocommerce_new_order', array($this, 'customer_order_notification'));
		}

		if (isset($this->configs['wc_notify_stock_enable'])) {
			add_action('woocommerce_low_stock', array($this, 'admin_low_stock_notification'));
		}

		if (isset($this->configs['wc_notify_status_enable'])) {
			add_action('woocommerce_order_status_changed', array($this, 'change_order_status_notification'));
		}
	}

	/**
	 * WooCommerce Features
	 * Add the field to the checkout page
	 */
	public function checkout_field($checkout)
	{
        
		woocommerce_form_field('mobile', array(
			'type' => 'text',
			'class' => array('wallmessage-checkout-field'),
			'label' => ($this->configs['wc_mobile_field_title']?$this->configs['wc_mobile_field_title']:__('Mobile Number', 'wallmessage')),
			'placeholder' => ($this->configs['wc_mobile_field_placeholder']?$this->configs['wc_mobile_field_placeholder']:__('mobile number to get order status in Watsapp', 'wallmessage')),
			'required' => true,
		),
			$checkout->get_value('mobile'));
	}

	/**
	 * WooCommerce Features
	 * Process the checkout
	 */
	public function checkout_handler()
	{
		// Check if the field is set, if not then show an error message.
		if(!isset($_POST['mobile']) || empty($_POST['mobile'])){			
			wc_add_notice(__('Please enter mobile number.', 'wallmessage'), 'error');
		}
	}

	/**
	 * WooCommerce Features
	 * Update the order meta with field value
	 */
	public function update_order_meta($order_id)
	{
		
		if(isset($_POST['mobile']) && !empty($_POST['mobile'])){
			$mobile = sanitize_text_field($_POST['mobile']);
			update_post_meta($order_id, 'mobile', $mobile);
		}
	}

	/**
	 * WooCommerce customer new product notification new product
	 */
	public function kwm_new_product_notification($new_status, $old_status, $post)
	{
 
		if( 
			$old_status != 'publish' 
			&& $new_status == 'publish' 
			&& !empty($post->ID) 
			&& in_array( $post->post_type, array( 'product') )
        ){
			global $wpdb, $table_prefix;

			$post_ID = $post->ID;
			
			if ($this->configs['wc_notify_product_receiver'] == 'users') {
				$this->wapp_pm->to = $wpdb->get_col("SELECT DISTINCT `meta_value` FROM `" . $table_prefix . "postmeta` WHERE meta_value != '' and (`meta_key` = 'mobile' OR `meta_value` = '_billing_phone')");
			}
			
			$product = wc_get_product( $post_ID );
			
			$template_vars = array(
				'%product_title%' 	=> $product->get_title(),
				'%product_url%' 	=> wp_get_shortlink($post_ID),
				'%product_date%' 	=> get_post_time('Y-m-d', true, $post_ID, true),
				'%product_price%' 	=> $product->get_regular_price(),
			);
			$message = str_replace(array_keys($template_vars), array_values($template_vars), $this->configs['wc_notify_product_message']);
			$this->wapp_pm->msg = $message;
			$this->wapp_pm->SendPM();
		}
	}

	/**
	 * WooCommerce admin new order notification
	 */
	public function admin_order_notification($order_id)
	{
		$this->wapp_pm->to = array($this->configs['wc_notify_order_receiver']);
		$order = new WC_Order($order_id);
        $products_name = '';
        foreach($order->get_items() as $item) {
            $products_name .= $item['name'];        
        }
        
		$template_vars = array(
            '%billing_first_name%' => $order->get_billing_first_name(),
            '%billing_last_name%' => $order->get_billing_last_name(),
            '%billing_company%'    => $order->get_billing_company(),
            '%billing_address%'    => $order->get_billing_address_1()." ".$order->get_billing_address_2(),
            '%billing_phone%'      => $order->get_billing_phone(),
            '%order_total%'        => $order->get_total(),
            '%order_edit_url%'     => $order->get_checkout_order_received_url(),
            '%order_items%'        => $products_name,
			'%order_id%'           => $order_id,
            '%order_number%'       => $order->get_order_number(),
			'%status%'             => $order->get_status()
		);
		$message = str_replace(array_keys($template_vars), array_values($template_vars), $this->configs['wc_notify_order_message']);
		$this->wapp_pm->msg = $message;
		$this->wapp_pm->SendPM();
	}

	/**
	 * WooCommerce customer new order notification
	 */
	public function customer_order_notification($order_id)
	{
		$order = new WC_Order($order_id);
		
		if($this->configs['wc_mobile_field'] == 'add_new_field'){
			$this->wapp_pm->to = array($_REQUEST['mobile']);
		}elseif($this->configs['wc_mobile_field'] == 'used_current_field'){
			$this->wapp_pm->to = array($order->get_billing_phone());
		}else{
			$this->wapp_pm->to =array();
		}
		
		if (count($this->wapp_pm->to) > 0) {
			
		
		
			$products_name = '';
			foreach($order->get_items() as $item) {
				$products_name .= $item['name'];        
			}
			
			$template_vars = array(
				'%order_id%'           => $order_id,
				'%order_number%'       => $order->get_order_number(),
				'%status%'             => $order->get_status(),
				'%order_items%'        => $products_name,
				'%order_total%'        => $order->get_total(),
				'%billing_first_name%' => $order->get_billing_first_name(),
				'%billing_last_name%'  => $order->get_billing_last_name(),
				'%order_edit_url%'     => $order->get_checkout_order_received_url(),
				'%order_pay_url%'      => $order->get_checkout_payment_url() ,
				
			);
			$message = str_replace(array_keys($template_vars), array_values($template_vars), $this->configs['wc_notify_customer_message']);
			$this->wapp_pm->msg = $message;
			$this->wapp_pm->SendPM();
		}
	}

	/**
	 * WooCommerce admin low stock notification 
	 */
	public function admin_low_stock_notification($stock)
	{
		$this->wapp_pm->to = array($this->configs['wc_notify_stock_receiver']);
		$template_vars = array(
			'%product_id%' => $stock->id,
			'%product_name%' => $stock->post->post_title
		);
		$message = str_replace(array_keys($template_vars), array_values($template_vars), $this->configs['wc_notify_stock_message']);
		$this->wapp_pm->msg = $message;
		$this->wapp_pm->SendPM();
	}

	/**
	 * WooCommerce customer notification change status
	 */
	public function change_order_status_notification($order_id)
	{
		$order = new WC_Order($order_id);
		
		if($this->configs['wc_mobile_field'] == 'add_new_field'){
			$get_mobile = get_post_meta($order_id, 'mobile', true);
		}elseif($this->configs['wc_mobile_field'] == 'used_current_field'){
			$get_mobile = $order->get_billing_phone();
		}else{
			$get_mobile =array();
		}

		if (!$get_mobile) {
			return;
		}

		$this->wapp_pm->to = array($get_mobile);
		$template_vars = array(
			'%status%' => $order->get_status(),
			'%order_number%' => $order->get_order_number(),
			'%customer_first_name%' => $order->billing_first_name,
			'%customer_last_name%' => $order->billing_last_name,
            '%order_view_url%'     => $order->get_checkout_order_received_url(),
            '%order_pay_url%'     => esc_url( $order->get_checkout_payment_url() ),
		);
		$message = str_replace(array_keys($template_vars), array_values($template_vars), $this->configs['wc_notify_status_message']);
		$this->wapp_pm->msg = $message;
		$this->wapp_pm->SendPM();
	}
}

new wallmessage_Woocommerce();