<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Email_tracking extends MY_Controller {
    
    public function __construct(){
        parent::__construct();
    }
    
    public function SNS_callback(){
        $json = file_get_contents('php://input');
        $json = json_decode($json,1);
        if (!empty($json)) {
            if($json['Type'] == 'SubscriptionConfirmation' || $json['Type'] == 'UnsubscribeConfirmation') { //Subcribe OR Unsubscribe Confirmation
                $this->subscribeEvent($json);
            }
            
            if($json['Type'] == 'Notification') { //All Email Callback
                $this->sendEvent($json['Message']);
            }
        }
    }
    
    public function subscribeEvent($json){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $json['SubscribeURL']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch); curl_close($ch); 
    }
    
    public function sendEvent($json_msg){
        $json = json_decode($json_msg,1); 
        $insert_array = array();
        switch ($json['eventType']){
            case 'Send':
                $insert_array = array();
                break;
            case 'Delivery':
                $insert_array  = array(
                    'company_id' =>($json['mail']['headers'][7]['name']=='company_id') ? $json['mail']['headers'][7]['value'] : $json['mail']['headers'][8]['value'] ,
                    'msg_id'=> $json['mail']['messageId'],
                    'type'=> $json['eventType'],
                    'subtype'=> '',
                    'detail'=> '',
                    'recipient'=> str_replace(',',', ', implode(",", $json['mail']['commonHeaders']['to'])), //str_replace(array('<','>'),'',$json['mail']['headers'][4]['value']),
                    'from_address'=> $json['mail']['commonHeaders']['from'][0], // str_replace(array('<','>'),'',$json['mail']['headers'][2]['value']),
                    'subject'=>  $json['mail']['commonHeaders']['subject'], //$json['mail']['headers'][5]['value'],
                    'ses_responce'=>$json['delivery']['smtpResponse'],
                    'processing_time_ms'=>$json['delivery']['processingTimeMillis'],
                    'send_timestamp'=>date('Y-m-d H:i:s',  strtotime($json['mail']['timestamp'])),
                    'delivery_timestamp'=>date('Y-m-d H:i:s',  strtotime($json['delivery']['timestamp'])),
                    'created_date_time'=>CURRENT_DATE_TIME 
                );
                break;
            case 'Bounce':
                $insert_array  = array(
                    'company_id' => ($json['mail']['headers'][7]['name']=='company_id') ? $json['mail']['headers'][7]['value'] : $json['mail']['headers'][8]['value'] ,
                    'msg_id'=> $json['mail']['messageId'],
                    'type'=> $json['eventType'],
                    'subtype'=> $json['bounce']['bounceType'],
                    'detail'=> $json['bounce']['bounceSubType'],
                    'recipient'=> str_replace(',',', ',$json['bounce']['bouncedRecipients'][0]['emailAddress']),
                    'from_address'=> $json['mail']['commonHeaders']['from'][0],//str_replace(array('<','>'),'',$json['mail']['headers'][2]['value']),
                    'subject'=>  $json['mail']['commonHeaders']['subject'],//$json['mail']['headers'][5]['value'],
                    'ses_responce'=>$json['bounce']['bounceSubType'],
                    'processing_time_ms'=>'',
                    'send_timestamp'=>date('Y-m-d H:i:s',  strtotime($json['mail']['timestamp'])),
                    'delivery_timestamp'=>date('Y-m-d H:i:s',  strtotime($json['bounce']['timestamp'])),
                    'created_date_time'=>CURRENT_DATE_TIME 
                );
                break;
            case 'Complaint':
                $insert_array  = array(
                    'company_id' =>($json['mail']['headers'][7]['name']=='company_id') ? $json['mail']['headers'][7]['value'] : $json['mail']['headers'][8]['value'] ,
                    'msg_id'=> $json['mail']['messageId'],
                    'type'=> $json['eventType'],
                    'subtype'=> '',
                    'detail'=> $json['complaint']['complaintFeedbackType'],
                    'recipient'=> str_replace(',',', ',$json['complaint']['complainedRecipients'][0]['emailAddress']),
                    'from_address'=> $json['mail']['commonHeaders']['from'][0],//$json['mail']['headers'][2]['value'],
                    'subject'=>  $json['mail']['commonHeaders']['subject'],//$json['mail']['headers'][3]['value'],
                    'ses_responce'=>$json['complaint']['complaintFeedbackType'],
                    'processing_time_ms'=>'',
                    'send_timestamp'=>date('Y-m-d H:i:s',  strtotime($json['mail']['timestamp'])),
                    'delivery_timestamp'=>date('Y-m-d H:i:s',  strtotime($json['complaint']['timestamp'])),
                    'created_date_time'=>CURRENT_DATE_TIME 
                );
                break;
            case 'Reject':
                $insert_array  = array(
                    'company_id' => 989,
                    'msg_id'=> $json['mail']['messageId'],
                    'type'=> $json['eventType'],
                    'subtype'=> '',
                    'detail'=> '',
                    'recipient'=> str_replace(',',', ',$json['mail']['headers'][1]['value']),
                    'from_address'=> $json['mail']['headers'][2]['value'],
                    'subject'=>  $json['mail']['headers'][3]['value'],
                    'ses_responce'=>'',
                    'processing_time_ms'=>'',
                    'send_timestamp'=>'',
                    'delivery_timestamp'=>'',
                    'created_date_time'=>CURRENT_DATE_TIME 
                );
                break;
        }
        if(!empty($insert_array)){
              $this->admin_model->custom_insert_record('email_tracking',$insert_array);
        }
    }
}    