<?php 

namespace WALLMESSAGE\sender;


// deny directly call
if ( !function_exists( 'do_action' )  || ! defined( 'ABSPATH' )) {
	echo esc_html('Directly Acess! No NO NO..');
	exit;
}


class wallmessage extends \WALLMESSAGE\Sender
{
    private $api_link;
    public $tariff = WALLMESSAGE_SITE;
    public $unitrial = true;
    public $unit;
    public $flash = "disable";
    public $isflash = false;

    public function __construct()
    {
        parent::__construct();
        $this->validateNumber =  __("The phone number must contain only digits together. It should not contain any other symbols such as (+) sign.  Instead  of  plus  sign,  please  put  (00)" . PHP_EOL . "e.g seperate numbers with comma: 00989123456789, 09123456789", 'wallmessage');
    }

    public function SendPM()
    {
		
		if($this->url =='' OR $this->has_key =='' ){
			return ['errors' =>__('API Config Not Set! First go to Wallmessage > Configs > WallMessage API tab, and configure WallMessage API key and API url.','wallmessage')];
		}


        /**
         * Modify Receiver number
         *
         * @param array $this ->to receiver number
         *
         * @since 3.4
         *
         */
        $this->to = apply_filters('kwm_wallmessage_to', $this->to);

        /**
         * Modify text message
         *
         * @param string $this ->msg text message.
         *
         * @since 3.4
         *
         */
		 $this->msg = apply_filters('kwm_wallmessage_msg', $this->msg);
		
        $responses = array();
		
        foreach ($this->to as $number) {
			
        
			$to  = $number;	
			
			$msg = $this->msg;
			
			$body = '{"mobile" : "'.$to.'","message" : "'. $msg .'"}';
			
			$responses[] = [
				'response_data'=>
					wp_remote_post($this->url . "sendMessage?token=" . $this->has_key,
						[
							'headers'   => [ 'Content-Type' => 'application/json' ],
							'body'       => $body,
							'data_format' => 'body'
						]
					),
				'send_data' =>[
					'to'=>$to,
					'message'=>$msg,
				]
			];
		}        
		
		
		$has_error = false;
		$errors = '';
		$results = [
					'successes' => '',
					'errors' => '',
				];
		
		if(is_array($responses) && count($responses)>0){
			
			
			foreach($responses as $response){
			
				
				if(!is_wp_error($response['response_data']) && isset($response['response_data']['body'])){
					$result = json_decode(sanitize_textarea_field($response['response_data']['body']));
					
					if ($result->isSuccess == true) {
						// Log the result
						$this->log($this->has_key, sanitize_textarea_field($response['send_data']['message']), sanitize_text_field($response['send_data']['to']), sanitize_textarea_field($result),'success',array(),sanitize_textarea_field($response));
						$results['successes'] .= sanitize_text_field($response['send_data']['to']).": ".sanitize_textarea_field($response['send_data']['message'])."<br>";
					} else {
						// Log the result
						
						$this->log($this->has_key,  sanitize_textarea_field($response['send_data']['message']), sanitize_textarea_field($response['send_data']['to']), sanitize_textarea_field($result), 'error',array(),sanitize_textarea_field($response));
						$results['errors'] .= sanitize_text_field($response['send_data']['to']).": ".sanitize_textarea_field($response['send_data']['message'])."<br>";
					}
				}else{
					$this->log($this->has_key,  sanitize_textarea_field($response['send_data']['message']), sanitize_text_field($response['send_data']['to']), $response['response_data']->get_error_message(), 'error',array(),$response);
					$results['errors'] .= sanitize_text_field($response['send_data']['to']).": ".sanitize_textarea_field($response['send_data']['message'])."(".$response['response_data']->get_error_message().")"."<br>";
				}
			}
		}else{
			$results['errors'] .="Some thing is wrong";
		}
		return $results;
    }


    /**
     * Clean number
     *
     * @param $number
     *
     * @return bool|string
     */
    private function clean_number($number)
    {
        $number = str_replace('+', '00', $number);
        $number = trim($number);

        return $number;
    }

    

}
