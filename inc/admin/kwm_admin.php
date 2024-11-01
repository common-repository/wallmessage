<?php 

namespace WALLMESSAGE;

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}


/**
* admin dashboard class
*/
class Admin
{
    public $wapp_pm;
    protected $db;
    protected $tb_prefix;
    protected $Configs;

    public function __construct()
    {
        global $wpdb;

        $this->db        = $wpdb;
        $this->tb_prefix = $wpdb->prefix;
        $this->Configs   = Configs::getConfigs();

        $this->init();

        // Add Actions
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
        add_action('admin_bar_menu', array($this, 'admin_bar'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    /**
     * Include admin assets
     */
    public function admin_assets()
    {
        // Register admin-bar.css for whole admin area
        if (is_admin_bar_showing()) {
            wp_register_style('wkmwallmessage-admin-bar', WPWHATSAPPPM_CSS . 'admin-bar.css', true, WPWHATSAPPPM_VERSION);
            wp_enqueue_style('wkmwallmessage-admin-bar');
        }

        $screen = get_current_screen();
        
		

        /**
         * Whole setting page's assets
         */
        if (stristr($screen->id, 'wallmessage_page_wallmessage-configs')) {
            wp_enqueue_script('wkmwallmessage-admin',  WPWHATSAPPPM_JS.'admin.js',array('jquery'), WPWHATSAPPPM_VERSION, true);			

            if (is_rtl()) {
                wp_enqueue_style('wkmwallmessage-rtl', WPWHATSAPPPM_CSS.'rtl.css', true, WPWHATSAPPPM_VERSION);
            }
        }
		

        /**
         * Send PM page's assets
         */
		 
        if ($screen->id === 'toplevel_page_wallmessage' || $screen->id === 'wallmessage_page_wallmessage-pm') {
            
			
            wp_enqueue_script('wkmwallmessage-sendpm', WPWHATSAPPPM_JS.'send-pm.js', array('jquery'), WPWHATSAPPPM_VERSION);
        }
		
		wp_register_style('wkmwallmessage-admin', WPWHATSAPPPM_CSS.'admin.css', true, WPWHATSAPPPM_VERSION);
		wp_enqueue_style('wkmwallmessage-admin');
    }

    /**
     * Admin bar plugin
     */
    public function admin_bar()
    {
        global $wp_admin_bar;

        $wp_admin_bar->add_menu(array(
            'id'     => 'kwm-send-pm',
            'parent' => 'new-content',
            'title'  => __('Wallmessage', 'wallmessage'),
            'href'   => WPWHATSAPPPM_ADMIN_URL . '/admin.php?page=wallmessage'
        ));
    }

 
    /**
     * Administrator admin_menu
     */
    public function admin_menu()
    {

        add_menu_page(__('Wallmessage', 'wallmessage'), __('Wallmessage', 'wallmessage'), 'kmwwallmessage_sendpm', 'wallmessage', array($this, 'send_pm_callback'), /*'dashicons-whatsapp'*/
		'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 31 32" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="M29.151 23.828l2.207 8.051-8.246-2.207c-2.272 1.234-4.804 1.883-7.401 1.883-8.571 0-15.582-6.947-15.582-15.517 0-4.155 1.623-8.051 4.545-10.973s6.818-4.545 10.973-4.545c8.571 0 15.582 6.947 15.582 15.517 0 2.727-0.714 5.454-2.078 7.791zM8.424 19.099c1.601 0 2.899-1.298 2.899-2.899s-1.298-2.899-2.899-2.899c-1.601 0-2.899 1.298-2.899 2.899s1.298 2.899 2.899 2.899zM18.643 16.2c0-1.601-1.298-2.899-2.899-2.899s-2.899 1.298-2.899 2.899c0 1.601 1.298 2.899 2.899 2.899s2.899-1.298 2.899-2.899zM23.064 19.099c1.601 0 2.899-1.298 2.899-2.899s-1.298-2.899-2.899-2.899c-1.601 0-2.899 1.298-2.899 2.899s1.298 2.899 2.899 2.899z"/></svg>'));
		add_submenu_page('wallmessage', __('Send PM', 'wallmessage'), __('Send PM', 'wallmessage'),  'kmwwallmessage_sendpm',  'wallmessage-pm',	    array($this, 'send_pm_callback'));
        add_submenu_page('wallmessage', __('Outbox', 'wallmessage'),  __('Outbox', 'wallmessage'),  'kmwwallmessage_outbox',  'wallmessage-outbox',  array($this, 'outbox_callback') );
    }

    /**
     * Callback send PM page.
     */
    public function send_pm_callback()
    {
        $page = new PM_Send();
        $page->render_page();
    }

    /**
     * Callback outbox page.
     */
    public function outbox_callback()
    {
		require_once WPWHATSAPPPM_INC . 'admin/class-kwm-outbox.php';
        $page = new Outbox();
        $page->render_page();
        
    }



    /**
     * Adding new capability in the plugin
     */
    public function add_cap()
    {
        // Get administrator role
        $role = get_role('administrator');

        $role->add_cap('kmwwallmessage_sendpm');
        $role->add_cap('kmwwallmessage_outbox');
        $role->add_cap('kmwwallmessage_Configs');
    }

    /**
     * Initial plugin
     */
    private function init()
    {

        // Check exists require function
        if (!function_exists('wp_get_current_user')) {
            include(ABSPATH . "wp-includes/pluggable.php");
        }

        // Add plugin caps to admin role
        if (is_admin() and is_super_admin()) {
            $this->add_cap();
        }
    }

}

new Admin();