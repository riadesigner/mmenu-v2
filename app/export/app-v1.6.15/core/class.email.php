<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// HOW TO USE
//
// $m = new Email("ru");
// $m->add_title("Привет!");
// $m->add_paragraph("Поздравляем!");
// $m->add_button("Начать редактировать","https://chefsmenu.ru/help");
// $m->add_space();
// $m->add_title("Справка:");
// $m->add_paragraph("По всем вопросам обращайтесь на <mail>support@chefsmenu.ru</mail>.");
// $m->add_paragraph("Дополнительная информация находится на странице помощи сайта: <link>https://chefsmenu.ru/help</link>");
// $m->add_paragraph("<eimg>path/to/image.jpg</eimg>");
// $m->add_paragraph("<maxwidth 200px><eimg>path/to/image.jpg</eimg></maxwidth>");
// $m->add_paragraph("ссылка на страницу: <nmlink>http://google.ru|google!</nmlink>, и вот <nmlink>http://yandex.ru|еще одна</nmlink>");
// $m->add_paragraph("<link>http://google.ru</link>");
// $m->add_paragraph("<maxwidth 100px><img>https://storage.yandexcloud.net/photobox/300nqn/300nqn-qrcode.png</img></maxwidth>");
// $m->add_paragraph("<eimg>favicon.png</eimg>");
// $m->add_paragraph("<eimg>core/email/email_img.gif</eimg>");
// $m->send($email,"Меню создано");


class Email{

	protected $body = "";
	protected $str = "";
	protected $file = false;
	protected $dirlib = "";
	protected $embeddedImages = [];
	protected $recipient_name="";

    public function __construct(protected $lang='ru'){
    	global $CFG; 		
    	$this->dirlib = $CFG->dirroot.APP_DIR."/core/email/";
    	$this->recipient_name = $this->lang=='ru'?'Администратору':'Administrator';
    	$this->mail_logfile_open();
    }

    public function add_title($msg): void{
    	$html = file_get_contents($this->dirlib.'email_tpl_title.htm', true);
    	$html = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$html);
        $html = $this->_parse_general($html);
    	$this->str .= $html;
    }

    public function add_title2($msg): void{
        $html = file_get_contents($this->dirlib.'email_tpl_title3.htm', true);
        $html = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$html);
        $html = $this->_parse_general($html);
        $this->str .= $html;
    }

    public function add_title3($msg): void{
    	$html = file_get_contents($this->dirlib.'email_tpl_title2.htm', true);
    	$html = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$html);
        $html = $this->_parse_general($html);
    	$this->str .= $html;
    }

    public function add_title3_bright($msg): void{
    	$html = file_get_contents($this->dirlib.'email_tpl_title2_bright.htm', true);
    	$html = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$html);
        $html = $this->_parse_general($html);
    	$this->str .= $html;
    }

    public function add_title_help($msg): void{
    	$html = file_get_contents($this->dirlib.'email_tpl_title_help.htm', true);
    	$html = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$html);
    	$this->str .= $html;
    }    

    public function add_short_code($msg): void{
    	$html = file_get_contents($this->dirlib.'email_tpl_short_code.htm', true);
    	$html = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$html);
    	$this->str .= $html;
    }

    public function add_raw_code($msg): void{    	
    	
    	$msg = str_replace("'", "‘", (string) $msg);
    	$msg = str_replace("<", "&lt;", $msg);
    	$msg = str_replace(">", "&gt;", $msg);
    	$msg = str_replace(":", "&#58;", $msg); // hide link
    	
    	$html = file_get_contents($this->dirlib.'email_tpl_raw_code.htm', true);
    	$html = str_ireplace("%{SECTION_TEXT}%",$msg,$html);
    	$this->str .= $html;
    } 

    public function add_space(): void{
    	$this->str .= "<tr><td height='10px'></td></tr>";
    }

    public function add_line(): void{
    	$line = file_get_contents($this->dirlib.'email_tpl_line.htm', true);
    	$this->str .= $line;
    }    

    public function add_button($btnTitle,$btnLink="#"): void{
    	$button = file_get_contents($this->dirlib.'email_tpl_btn.htm', true);
    	$button = str_ireplace("%{BUTTON_TITLE}%",(string) $btnTitle,$button);
    	$button = str_ireplace("%{BUTTON_LINK}%",(string) $btnLink,$button);
    	$this->str .= $button;
    }

    public function add_paragraph($msg,$zerofont=false): void{    	
    	if($zerofont){
			$paragraph = file_get_contents($this->dirlib.'email_tpl_paragraph_zerofont.htm', true);	
    	}else{
    		$paragraph = file_get_contents($this->dirlib.'email_tpl_paragraph.htm', true);	
    	}
    	
    	$paragraph = str_ireplace("%{SECTION_TEXT}%",(string) $msg,$paragraph);    	
		$paragraph = $this->_parse_all_images($paragraph);    
		$paragraph = $this->_parse_all_embedded_images($paragraph);
    	
        $paragraph = $this->_parse_emails($paragraph);
    	$paragraph = $this->_parse_links($paragraph);
		$paragraph = $this->_parse_named_links($paragraph);

		$paragraph = $this->_parse_maxwidth($paragraph);
    	$this->str .= $paragraph;
    }

    public function add_strong($msg): void{        
        $this->add_paragraph("<strong>{$msg}</strong>");
    }

    public function add_default_bottom(): void{
    	global $CFG;

    	$this->add_line();

    	if($this->lang=="ru"){
			$this->add_title_help("Справка:");
			$this->add_paragraph(implode("", [
 				"Всю необходимую информацию для работы вы найдете в разделе «Помощь» Панели Управления, а также на сайте: ",
 				"<nmlink>{$CFG->http}{$CFG->wwwroot}|{$CFG->wwwroot}</nmlink>"
 				]));
			$this->add_paragraph("По дополнительным вопросам пишите по адресу: <email>".$CFG->support_email."</email>");
    	}else{
			$this->add_title_help("Help:");
			$this->add_paragraph(implode("", [
 				"You will find all the necessary information for work in the Help section of the Control Panel, as well as on the website: ",
 				"<nmlink>{$CFG->http}{$CFG->wwwroot}|{$CFG->wwwroot}</nmlink>"
 				]));
			$this->add_paragraph("For additional questions, write to: <email>".$CFG->support_email."</email>");    		
    	}
    }

    public function send($email,$subject){
        return $this->_send_smpt($email,$subject);
    }

    //     ____  ____  _____    _____  ____________
    //    / __ \/ __ \/  _/ |  / /   |/_  __/ ____/
    //   / /_/ / /_/ // / | | / / /| | / / / __/
    //  / ____/ _, _// /  | |/ / ___ |/ / / /___
    // /_/   /_/ |_/___/  |___/_/  |_/_/ /_____/

    private function _parse_general($msg){
        $msg = $this->_parse_emails($msg);
        $msg = $this->_parse_links($msg);
        $msg = $this->_parse_named_links($msg);                
        return $msg;
    }

    private function _parse_emails($paragraph){
		$paragraph = preg_replace('|<email>(.+)</email>|isU', '<a style="color:#3f3f3f;font-weight:bold;" href="mailto:$1" rel="noopener" target="_blank">$1</a>', (string) $paragraph);
    	return $paragraph;
    }

    private function _parse_links($paragraph){
		$paragraph = preg_replace('|<link>(.+)</link>|isU', '<a style="color:#3f3f3f;font-weight:bold;" href="$1" rel="noopener" target="_blank">$1</a>', (string) $paragraph);
    	return $paragraph;
    }    

    private function _parse_named_links($paragraph){
		$paragraph = preg_replace('|<nmlink>(.+)\||isU', '<a style="color:#3f3f3f;font-weight:bold;" href="$1" rel="noopener" target="_blank">|', (string) $paragraph);
		$paragraph = preg_replace('|\|(.+)</nmlink>|isU', '$1</a>', (string) $paragraph);		
    	return $paragraph;
    }

    private function _parse_all_images($paragraph){		
		preg_match_all("|<img>(.+)</img>|isU",(string) $paragraph,$images);
		$paragraph = preg_replace('|<img>(.+)</img>|isU','<img src="$1" style="width:100%;max-width:100%;height:auto;">',(string) $paragraph);
    	return $paragraph;
    }

    private function _parse_all_embedded_images($paragraph){		
		preg_match_all("|<eimg>(.+)</eimg>|isU",(string) $paragraph,$images);
		$paragraph = preg_replace('|<eimg>(.+)</eimg>|isU','<img src="$1" style="width:100%;max-width:100%;height:auto;">',(string) $paragraph);
		foreach ($images[1] as $src){ 
			$paragraph = str_replace($src,'cid:'.md5((string) $src),(string) $paragraph);
			$this->embeddedImages[$src] = md5((string) $src);
		}
    	return $paragraph;
    }    

    private function _parse_maxwidth($paragraph){
		$paragraph = preg_replace('|<maxwidth(.\d+.+)>|isU','<div style="max-width:$1;">',(string) $paragraph);
		$paragraph = str_replace('</maxwidth>','</div>',(string) $paragraph); 
    	return $paragraph;
    }

    private function _get_top_banner_url(){    	
    	$banner_name = $this->lang=='ru'?'email_img_ru.gif':'email_img.gif';	
    	return $this->dirlib.''.$banner_name;
    }
    
    private function _get_help_icon_url(){    	
    	$ico_name = 'email_img_help.gif';
    	return $this->dirlib.''.$ico_name;
    }

    private function _get_body(){
	
		global $CFG;

		// default top of email
		$header = file_get_contents($this->dirlib.'email_tpl_header.htm', true);		
		$topbanner = file_get_contents($this->dirlib.'email_tpl_topbanner.htm', true);
		
		// default footer
		$footer = file_get_contents($this->dirlib.'email_tpl_footer.htm', true);		
		$footer = str_ireplace("%{SUPPORT_EMAIL}%",(string) $CFG->support_email,$footer);
		$footer = str_ireplace("%{SITE_URL}%",(string) $CFG->base_app_url,$footer);
		
		$this->body = $header;
		$this->body .= $topbanner;
		$this->body .= $this->str;
		$this->body .= $footer;

		// $this->save_into_logfile();		
		return $this->body;
    }

    private function _send_smpt($email,$subject){

		global $CFG;
		
		// the true param means it will throw exceptions on errors, which we need to catch	
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


	    foreach ($this->embeddedImages as $src => $cid) {
			$mail->addEmbeddedImage($src,$cid);
	    }

		$mail->addEmbeddedImage($this->_get_top_banner_url(),"top_banner");
		$mail->addEmbeddedImage($this->_get_help_icon_url(),"help_icon");

	    $mail->addAddress($email, $this->recipient_name);
	    $mail->Subject = $subject;    
	    $mail->MsgHTML($this->_get_body());  

		// $mail->smtpConnect($CFG->smtp_ssl_options);

		try { 

			return $mail->send();

		} catch (Exception) {			
			glog('err mailsend, '.$mail->ErrorInfo);
			return false;
		}
    }
    
    private function save_into_logfile(): void{
    	if(J_ENV_TEST && J_ENV_LOCAL){
    		// $this->file && fwrite($this->file,$this->body);	
    	}
    }

    private function mail_logfile_open(): void{
    	if(J_ENV_TEST && J_ENV_LOCAL){
			// $this->file = fopen($this->dirlib."email_log.htm","w+");
			// if(!$this->file) glog("Can not open maillog file!");
    	}
    }

    public function __destruct(){
		$this->file && fclose($this->file);    	
    }

}



?>