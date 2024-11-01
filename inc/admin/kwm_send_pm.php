<?php 

namespace WALLMESSAGE;



// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}


/**
 * Class Send PM Page
 */
class PM_Send
{
    public $wapp_pm;
    protected $db;
    protected $tb_prefix;
    protected $configs;
    

    public function __construct()
    {
        global $wpdb, $wapp_pm;
        
        $this->db        = $wpdb;
        $this->tb_prefix = $wpdb->prefix;
        $this->wapp_pm       = $wapp_pm;
        $this->configs   = Configs::getConfigs();
    }

    /**
     * Sending PM from admin page
     *
     * @param Not param
     */
    public function render_page()
    {
		if($this->wapp_pm->url =='' OR $this->wapp_pm->has_key =='' ){
			// include_once WPWHATSAPPPM_TPL . "error.php";
			wp_safe_redirect(add_query_arg(array(
			'settings-updated' => false,
			'page'			   => 'wallmessage-configs',
			'tab'              => 'wallmessageapi',
		)));
			return;
		}
		
        $get_users_mobile        = $this->db->get_col("SELECT `meta_value` FROM `{$this->db->prefix}usermeta` WHERE `meta_key` = 'mobile' AND `meta_value` != '' ");
        $woocommerceCustomers    = [];

        if (class_exists('woocommerce')) {
            global $wpdb, $table_prefix;
			$woocommerceCustomers = $wpdb->get_col("SELECT DISTINCT `meta_value` FROM `" . $table_prefix . "postmeta` WHERE meta_value != '' and (`meta_key` = 'mobile' OR `meta_value` = '_billing_phone')");
			
        }
        
		$response = array();
        if (isset($_POST['SendPM'])) {
            if (isset($_POST['wp_get_message']) && !empty($_POST['wp_get_message'])) {
                if (isset($_POST['wp_send_to']) && sanitize_text_field($_POST['wp_send_to']) == "wp_users") {
                    $this->wapp_pm->to = $get_users_mobile;
                } else if(isset($_POST['wp_send_to']) && sanitize_text_field($_POST['wp_send_to']) == "wp_tellephone") {
					
                    $numbers = sanitize_text_field(wp_unslash($_POST['wp_get_number']));
                    if (strpos($numbers, ',') !== false) {
                        $this->wapp_pm->to = explode(",", $numbers);
                    } else {
                        $this->wapp_pm->to = explode("\n", str_replace("\r", "", $numbers));
                    }
                } else if (isset($_POST['wp_send_to']) && sanitize_text_field($_POST['wp_send_to']) == "wc_users") {
                    $this->wapp_pm->to = $woocommerceCustomers;
                } 
                

                $this->wapp_pm->msg  = sanitize_text_field($_POST['wp_get_message']);
                				
				
				// Send PM
				if (empty($this->wapp_pm->to)) {
					$response = new \WP_Error('error', __('The selected user list is empty, please select another valid users list from send to option.', 'wallmessage'));
				} else {
					$response = $this->wapp_pm->SendPM();
				}
				
			} else {
                $response['errors'] = "<div class='error'><p>" . __('Please enter your PM message.', 'wallmessage') . "</p></div>";
            }
			
		}
        
        
	
		
		$settings = new Settings();
		$tabs_array = $settings->get_tabs();
        $active_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs_array) ? sanitize_text_field($_GET['tab']) : 'sendpm';
        $page_title = $tabs_array[$active_tab];
        ob_start();
        ?>
        <div class="wrap wallmessage-wrap wallmessage-settings-wrap">
            <?php require_once  WPWHATSAPPPM_TPL. 'header.php'; ?>
			<div class="wallmessage-wrap__main">                
				<div class="wallmessage-tab-group">
					<?php require_once  WPWHATSAPPPM_TPL. 'left-menu.php'; ?>
					<?php
						if (is_wp_error($response)) {
							if (is_array($response->get_error_message())) {
								$response = print_r($response->get_error_message(), 1);
							} else {
								$response = $response->get_error_message();
							}
							echo wp_kses(
										"<div class='notice notice-error is-dismissible'><p>" . sprintf(__('<strong>Error!</br> WallMessage response:</strong><br> %s', 'wallmessage'), $response) . "</p></div>"
										,array(
											'div'=>array(
												'class'=>array(),
											),
											'br'=>array(
												'class'=>array(),
											),
											'p'=>array(
												'class'=>array(),
											),
											'strong'=>array(
												'class'=>array(),
											),
										)
								);
						} else {
							if(isset($response['successes']) && $response['successes'] <> ''){
								echo wp_kses(
											 "<div class='notice notice-success is-dismissible'><p>". sprintf(__('<strong>The PM sent successfully!</br> WallMessage response:</strong><br>  %s', 'wallmessage'), $response['successes']) . "</p></div>"
										,array(
											'div'=>array(
												'class'=>array(),
											),
											'br'=>array(
												'class'=>array(),
											),
											'p'=>array(
												'class'=>array(),
											),
											'strong'=>array(
												'class'=>array(),
											),
										)
								);
							}
							
							if(isset($response['errors']) && $response['errors'] <> ''){
								echo wp_kses(
											"<div class='notice notice-error is-dismissible'>  <p>". sprintf(__('<strong>Error!</br> WallMessage response:</strong><br> %s', 'wallmessage'), $response['errors']) . "</p></div>"
										,array(
											'div'=>array(
												'class'=>array(),
											),
											'br'=>array(
												'class'=>array(),
											),
											'p'=>array(
												'class'=>array(),
											),
											'strong'=>array(
												'class'=>array(),
											),
										)
								);
							}
							
							
						}
					?>
					<?php require_once  WPWHATSAPPPM_TPL. 'send-pm.php'; ?>
				</div>	
			</div>
        </div>
        <?php
        echo ob_get_clean();
		
        
    }
}
