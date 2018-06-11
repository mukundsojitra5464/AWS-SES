<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
include_once APPPATH.'third_party/SES/vendor/autoload.php';
 
class ses_lib {
    protected $CI;
    public function __construct(){
    	$this->CI =& get_instance();
    	$this->CI->load->config('ses_config');
    }

    function SesService(){
    	
    	$accesskey = $this->CI->config->item('ACCESS_KEY');
    	$secretkey = $this->CI->config->item('SECRET_KEY');
    	$region_endpoint = $this->CI->config->item('REGION_ENDPOINT');

 		return new SimpleEmailService($accesskey, $secretkey, $region_endpoint);
    }
}
?>