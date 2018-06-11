<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Email_varification extends MY_Controller {
    
	public function __construct(){
        parent::__construct();  
        $this->load->library('ses_lib');
        $this->ses = $this->ses_lib->SesService();
    }

	// Get the addresses that have been verified in your AWS SES account
    public function get_verified_email_list(){
    	$response =  $this->ses->listVerifiedEmailAddresses();
    	echo json_encode($response);	
    }

	// Send a confirmation email in order to verify a new email
    public function send_varification_email(){
        $email = $this->input->post('email');
    	$this->ses->verifyEmailAddress($email);
        $data['status'] = TRUE;
        echo json_encode($data);
    }

    public function delete_varified_email($email){
    	$response = $this->ses->deleteVerifiedEmailAddress($email);
    }

}    