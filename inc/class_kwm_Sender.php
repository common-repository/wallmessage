<?php
namespace WALLMESSAGE;

// deny directly access
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}	


/**
 *   Sender
 */
class Sender
{
   
	// 
    public $wallmessageFields = [
        'has_key'  => [
            'id'   => 'wallmessageapi_key',
            'name' => 'API key',
            'desc' => 'Enter API key of WallMessage'
		],
		'url' => [
            'id'   => 'wallmessageapi_url',
            'name' => 'API username',
            'desc' => 'Enter API URL of WallMessage',
        ],		
    ];

    
    public $has_key = true;
    public $validateNumber = true;
    public $help = '';
    public $to;
	public $url;
    public $msg;
    protected $db;
    protected $tb_prefix;
    public $configs;
    public $supportMedia = false;
    public $media = [];



    /**
     * @var
     */
    static $get_response;

    public function __construct()
    {
        global $wpdb;

        $this->db        = $wpdb;
        $this->tb_prefix = $wpdb->prefix;
        $this->configs   = Configs::getConfigs();
		$this->help 	 = __("Great Documentation avaible in our website.", 'wallmessage');
		
        // Check configs for add country code to prefix numbers
        if (isset($this->configs['county_code']) and $this->configs['county_code']) {
            add_filter('kwm_wallmessage_to', array($this, 'applyCountryCode'));
        }
		
        // format and trim recivers number 
        if (isset($this->configs['clean_numbers']) and $this->configs['clean_numbers']) {
            add_filter('kwm_wallmessage_to', array($this, 'trimNumbers'));
        }
		
		//clear Message For send and replace special chars
		add_filter( 'kwm_wallmessage_msg', array( $this, 'clearMessage' ) );

    }

	/**
	 *  initialize 
	 *
	 *
	 * @return sender class instance
	 */
	public static function initial()
	{

		// Include default Wallmessage		
		if (is_file( WPWHATSAPPPM_INC. 'class_kwm_send_wallmessage.php')) {
			include_once  WPWHATSAPPPM_INC. 'class_kwm_send_wallmessage.php';
		} else {
			#error @todo: Display some error
		}


		// Create object from the class
		$wapp_pm        = new \WALLMESSAGE\SENDER\wallmessage();


		// Set user configs
		$wapp_pm->url	   = Configs::getConfig('wallmessageapi_url');
		

		// Set api key
		$wallmessage_key   = Configs::getConfig('wallmessageapi_key');
		
		if ($wapp_pm->has_key && $wallmessage_key) {
			$wapp_pm->has_key = $wallmessage_key;
		}


		// Show help configuration in config page
		if ($wapp_pm->help) {
			add_action('wallmessage_after_sender', function () {
				echo wp_kses('<p class="description">' . esc_html($wapp_pm->help) . '</p>',array('p'=>array('class'=>array())));
			});
		}

		// Unset key field if not available in the Wallmessage class.
		add_filter('wallmessage_sender_configs', function ($filter) {
			global $wapp_pm;
			
				if (!$wapp_pm->has_key) {
					unset($filter['wallmessage_key']);
				}

			return $filter;
		});

		// Return object
		return $wapp_pm;
	}

	/**
	 * @param $sender
	 * @param $message
	 * @param $to
	 * @param $response
	 * @param string $status
	 * @param array $media
	 * @param string $comment
	 * @return false|int
	 */
	public function log($sender, $message, $to, $response, $status = 'success', $media = array(),$comment='')
	{
		
		if(is_array($to)){
			$recipient = implode(',', $to);
		}else{
			$recipient = $to;	
		}
		
		return $this->db->insert(
			$this->tb_prefix . "kwm_wpwhatsapppm",
			array(
				'date'      => WPWHATSAPPPM_CURRENT_DATE,
				'sender'    => $sender,
				'message'   => $message,
				'recipient' => $recipient,
				'response'  => serialize($response),
				'status'    => $status,
				'comment'   => serialize($comment),
			)
		);

	}


	/**
	 *  numbers prefix Country code
	 *
	 * @param $recipients
	 *
	 * @return array
	 */
	public function applyCountryCode($recipients = array())
	{
		$countryCode = $this->configs['mobile_county_code'];

		if (!$countryCode) {
			return $recipients;
		}

		$numbers = array();
		foreach ($recipients as $number) {
			$numbers[] = preg_replace('/^(?:\\' . $countryCode . '|0)?/', $countryCode, ($number));
		}

		return $numbers;
	}



	/**
	 * trim numbers
	 *
	 * @param array $recipients
	 *
	 * @return array
	 */
	public function trimNumbers($recipients = array())
	{
		$numbers = array();
		foreach ($recipients as $recipient) {
			$numbers[] = str_replace(' ', '', $recipient);
		}

		return $numbers;
	}



	/**
	 * Cleanup numbers
	 *
	 * @param array $recipients
	 *
	 * @return array
	 */
	public function clearMessage($msg = array())
	{

		$escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
		$replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
		$msg = str_replace($escapers, $replacements, $msg);
			
		return $msg;
	}


	/**
	 * @return mixed
	 */
	public static function help()
	{
		global $wapp_pm;

		// help
		$help     = $wapp_pm->help;
		$document =  esc_url(WALLMESSAGE_SITE)."docs/";
		return $document ? sprintf(__('%s <a href="%s" target="_blank">Documentation</a>', 'wallmessage'), $help, $document) : $help;
	}

}