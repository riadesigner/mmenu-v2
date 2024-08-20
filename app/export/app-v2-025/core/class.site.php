<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
	ENTER TO SITE
*/

class Site{	

	static public $SITE_PAGE = "Index";	
	static public $SUB_DOMAIN = ""; // string 
	static public $USER_EMAIL = ""; // string
	static public $UNIQ_CAFE = ""; // string
	static public $LANG = ""; // string

	static public $Cafe = false; // Smart_object
	static public $User = false; // Smart_object
	static public $tpl = ""; // string

	static private $SITE_LINKS = []; 
	static private $ARR_PAGES = [];
	static private $ARR_BODY_CLASSES = [];	
	static private $ARR_BODY_DATA = [];	


	static public function init(): void{
		
		global $CFG;		
		
		self::$LANG = Lng_prefer::get();
		LNG::set(self::$LANG);

		self::$SITE_LINKS = $CFG->site_links;
		self::$ARR_PAGES = $CFG->site_templates;

		self::$User = User::from_cookie();
		// updating token expire date 
		if(self::$User){ User::auth_update(self::$User->email); }

		//if subdomail
		if(self::has_subdomain()){						

			if(self::$SUB_DOMAIN===$CFG->admin_sub){
				
				if(!RDSAdmin::authorised()){
					
					glog("!RDSAdmin::authorised");

					self::$SITE_PAGE = "RDSAdminEnter";
					self::add_body_classes("page-rds-admin__enter");
				}else{					
					$R = new Router();					
					switch ($R->get(0)) {
						case 'deluser':						
							if(!$R->get(1) || !$R->get(2)){
								self::$SITE_PAGE = "RDSAdmin404";
								self::add_body_classes("page-rds-admin__404");
								glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
							}else{
								self::$SITE_PAGE = "RDSAdminDelUser"; // test
								self::add_body_classes("page-rds-admin__del-user");
							}
							break;
						case 'add-contract':								
								self::$SITE_PAGE = "RDSAdminAddContract";
								self::add_body_classes("page-rds-admin__main");
								break;	
						default:
							self::$SITE_PAGE = "RDSAdminMain";
							self::add_body_classes("page-rds-admin__main");
							break;
					}
					
				}
			}else{
				if(self::check_allowed_subdomain()){
					if(self::real_cafe_subdomain()){
						self::$SITE_PAGE = "Cafe";
						self::add_body_classes("page-cafe");
						self::$UNIQ_CAFE = self::$Cafe->uniq_name; 						
					}else{
						self::$SITE_PAGE = "404";
						self::add_body_classes("page-404");
						glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
					}
				}else{
					self::$SITE_PAGE = "404";
					self::add_body_classes("page-404");
					glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
				}				
			}			
		}else{

			//if no subdomail			
			$R = new Router();			
			switch ($R->get(0)) {
				case 'admin':					
					if(!self::$User){
						self::$SITE_PAGE = 'Index';
						self::add_body_classes("page-home");
						self::add_body_classes("need-sign-in");
					}else{
						self::$SITE_PAGE = 'ControlPanel';
						self::add_body_classes("page-control-panel");
					}
				break;
				case 'cafe':					
					$uniq_cafe = substr((string) $R->get(1), 0, 255);					
					if(!$uniq_cafe){
						glogError("Requested cafe, uniq name unknown",__FILE__);
						self::$SITE_PAGE = '404';
						self::add_body_classes("page-404");
						glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
					}else{
						glog("Requested cafe {$uniq_cafe}",__FILE__);
						self::$UNIQ_CAFE = $uniq_cafe;						
						self::$SITE_PAGE = 'Menu';
						self::add_body_classes("page-menu");
						if($R->get(2)!==null && $R->get(2)=="table" && $R->get(3)!==null){
							$table_number = substr((string) $R->get(3), 0, 50);
							self::add_body_classes("mode-orderto-table");
							self::add_body_data("table-uniq", post_clean($table_number));			
						}
					}
				break;
				case 'confirmpass':
					$email = $R->get(1);
					$key = $R->get(2);
					if(!$email || !$key){
						self::$SITE_PAGE = '404';
						self::add_body_classes("page-404");
						glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
					}else{									
						self::$SITE_PAGE = 'Confirmpass';
						self::add_body_classes("page-confirmpass");
						$email = substr((string) $email, 0, 100);
						$key = substr((string) $key, 0, 100);						
						Password::confirm($email,$key);
					}
				break;
				case 'confirm-subdomain':
					$cafe_uniq_name =  $R->get(1);
					$new_subdomain =  $R->get(2);
					$key =  $R->get(3);
					if(!$cafe_uniq_name || !$new_subdomain || !$key){
						self::$SITE_PAGE = '404';
						self::add_body_classes("page-404");
						glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
					}else{									
						self::$SITE_PAGE = 'ConfirmSubdomain';
						self::add_body_classes("page-confirm-subdomain");
						$cafe_uniq_name = substr((string) $cafe_uniq_name, 0, 30);
						$new_subdomain = substr((string) $new_subdomain, 0, 30);
						$key = substr((string) $key, 0, 100);
						Subdomain::confirm($cafe_uniq_name, $new_subdomain, $key);
					}
				break;				
				case 'activate':
					if(!self::$User){ 
						if(Account::init($R->get(1),$R->get(2),$R->get(3))){
							if(Account::already_activated()){								
								self::$SITE_PAGE = 'Index';
								self::add_body_classes("page-home");
								self::add_body_classes("need-sign-in");
							}else{								
								self::$SITE_PAGE = "Activation";
								self::add_body_classes('page-activation');
							}
						}else{							
							self::$SITE_PAGE = '404';
							self::add_body_classes('page-404');
							glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
						}
					}else{
						// user already in cookies
						self::$SITE_PAGE = 'ControlPanel'; 
						self::add_body_classes('page-control-panel');
					}

				break;
				case 'help':					
					self::$SITE_PAGE = 'HelpPage';	
					self::add_body_classes('page-help');
				break;
				case 'features':					
					self::$SITE_PAGE = 'FeaturesPage';	
					self::add_body_classes('page-features');
				break;	
				case 'easy-open':
					self::$SITE_PAGE = 'EasyOpenPage';	
					self::add_body_classes('page-easy-open');
				break;				
				case 'to-developers':					
					self::$SITE_PAGE = 'ToDevelopersPage';	
					self::add_body_classes('to-developers');
				break;
				case 'price':					
					self::$SITE_PAGE = 'PricePage';	
					self::add_body_classes('page-price');
				break;
				case 'contacts':					
					self::$SITE_PAGE = 'ContactsPage';	
					self::add_body_classes('page-contacts');
				break;
				case 'privacy':					
					self::$SITE_PAGE = 'PrivacyPage';	
					self::add_body_classes('page-privacy');
				break;	
				case 'terms':					
					self::$SITE_PAGE = 'TermsPage';	
					self::add_body_classes('page-terms');
				break;
				case 'iiko-connection':					
					self::$SITE_PAGE = 'IikoConnectionPage';	
					self::add_body_classes('page-terms');
				break;				
				default:
					if(empty($R->get(0))){
						self::$SITE_PAGE = 'Index';
						self::add_body_classes("page-home");						
					}else{						
						self::$SITE_PAGE = '404';
						self::add_body_classes('page-404');
						glogError("Unknown path: ".$_SERVER['REQUEST_URI'],__FILE__);
					}
				break;
			}	

		}

	}
	
	static public function get_body_classes(){
		if(!count(self::$ARR_BODY_CLASSES)){
			return "";
		}else{
			return implode(' ',self::$ARR_BODY_CLASSES);
		}		
	}

	static public function get_body_data(){
		if(!count(self::$ARR_BODY_DATA)){
			return "";
		}else{
			return implode(' ',self::$ARR_BODY_DATA);
		}		
	}		

	static public function get_link($nm=""){		
		global $CFG;		
		if(isset(self::$SITE_LINKS[$nm])){
			return $CFG->http.self::$SITE_LINKS[$nm];
		}else{
			// if page not found, go home
			return self::$SITE_LINKS["home"];
		}
	}
	
	static public function get_current_url(){
		global $CFG;
		
		return $CFG->http.getenv('HTTP_HOST').getenv('REQUEST_URI');
	}

	static public function get_app_url(){
		global $CFG;
		return $CFG->http.getenv('HTTP_HOST')."/".APP_DIR;
	}	


	static public function get_lang(){
		return self::$LANG;
	}

	static public function get_locale(){
		return mb_strtolower((string) self::$LANG)."_".mb_strtolower((string) self::$LANG);
	}

	static public function is_rdsadmin(){
		return self::$SITE_PAGE === "RDSAdmin";
	}
	
	static public function get_title(){
		$lang = match (self::$LANG) {
      		'ru' => 1,
      		default => 0,
  		};
		return self::$ARR_PAGES[self::$SITE_PAGE]['title'];
	}

	static public function get_description(){
		$lang = match (self::$LANG) {
      		'ru' => 1,
      		default => 0,
  		};
		return self::$ARR_PAGES[self::$SITE_PAGE]['descr'];
	}	

	static public function get_template(){		
		$lang = match (self::$LANG) {
      		'ru' => 1,
      		default => 0,
  		};
		return self::$ARR_PAGES[self::$SITE_PAGE]['template'];
	}

	/* PRIVATE */

	static public function add_body_classes($class_name): void{
		if(!in_array($class_name, self::$ARR_BODY_CLASSES)){
			self::$ARR_BODY_CLASSES[] = $class_name;
		}
	}

	static public function add_body_data($data_name,$data): void{
		if(!in_array($data_name, self::$ARR_BODY_DATA)){
			self::$ARR_BODY_DATA[] = 'data-'.$data_name.'="'.$data.'"';
		}		
	}	

	static private function real_cafe_subdomain(){	
		$allcafe = new Smart_collect("cafe", "where subdomain='".self::$SUB_DOMAIN."'");
		if($allcafe->full()){ 
			self::$Cafe = $allcafe->get(0);
			return true;
		}else{
			return false;
		}
	}
		
	static private function check_allowed_subdomain(){	
		preg_match('/(admin)/', (string) self::$SUB_DOMAIN, $matches);
		return !isset($matches[1]);
	}
		
	static private function has_subdomain(){
		global $CFG;		 
		$url =  explode(":",$CFG->wwwroot)[0];		
		$msk = str_replace(".", "\.", $url);
		preg_match('/([^.]+)\.'.$msk.'/', $_SERVER['SERVER_NAME'], $matches);
		self::$SUB_DOMAIN = isset($matches[1])?$matches[1]:"";
		return self::$SUB_DOMAIN;
	}

/*
	CHECK IF REAL CAFE SUBDOMAIN
*/
	static public function check_cafe_subdomain(): void{	

		// $page = "Index";
		// $adminName = "admin222"; //$CFG->ADMIN_NAME;
		// $subdomain  = false;

	
	// $U = new Smart_collect("users","WHERE email='{$email}'");
	// if(!$U->found()) return false;
	// $USER = $U->get(0);
	// return $USER->id?$USER:false;
	}

}
		
?>