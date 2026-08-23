<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// HOW TO USE
//
// $m = new Email_simple();
// $m->send("Администратору","pogreb@inbox.ru","Письмо с сайта","<html>...</html>");


class Email_simple{

	protected $body = "";    
    protected $name_to = "";
    protected $email_to = "";
    protected $subject = "";

    public function __construct(){

    	return $this;
        	
    }

    public function send($name_to="", $email_to="", $subject="", $body="" ){
        global $CFG;
        
        if(empty($name_to) || 
            empty($email_to) ||
            empty($subject) ||
            empty($body) ){
                glogError("There are not enough arguments to send an email.");
            return false;
        }

        $this->name_to = $name_to;
        $this->email_to = $email_to;
        $this->subject = $subject;
        $this->body = $body;        

		return $this->_send_smpt();

    }

    private function _send_smpt(){

		global $CFG;

		$mail = new PHPMailer(true);		
	    $mail->isSMTP();
	    $mail->IsHTML(true);
	    $mail->CharSet = "UTF-8";
	    $mail->Mailer = 'smtp';
	    $mail->Host = $CFG->email_sender['host'];
	    $mail->Port = $CFG->email_sender['port'];
	    $mail->SMTPAuth = true;
	    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
	    $mail->Username = $CFG->email_sender['username']; 
	    $mail->Password = $CFG->email_sender['password']; 	    
		$mail->setFrom($CFG->email_sender['from']['email'], $CFG->email_sender['from']['name']);


	    $mail->addAddress($this->email_to, $this->name_to);
	    $mail->Subject = $this->subject;    
	    $mail->MsgHTML($this->body);  

		// $mail->smtpConnect($CFG->smtp_ssl_options);

		try { 

			return $mail->send();

		} catch (Exception) {			
			glog('err mailsend, '.$mail->ErrorInfo);
			return false;
		}
    }
    
    public function __destruct(){
		
    }

}

?>