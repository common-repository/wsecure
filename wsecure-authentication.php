<?php
/*
Plugin Name: wSecure Lite
Plugin URI: http://www.joomlaserviceprovider.com/
Description: WordPress! has one security problem, any web user can easily know if the site is created in WordPress! by typing the URL to access the administration area (i.e. www.sitename.com/wp-admin). This allows hackers to hack the site easily once they crack the id and password for WordPress!. The wSecure Lite plugin prevents access to the administration (back end) login page if the user does not use the appropriate access key.
Version: 2.5
Author: Ajay Lulia
Author URI: http://www.joomlaserviceprovider.com/
*/

if(!defined('ABSPATH'))exit; // Exit if accessed directly
	$wsecurelite = new wSecurelite();

class wSecurelite
{
	public function __construct()
	{
		register_activation_hook(__FILE__,array(get_called_class(),'wsecure_installer'));
		register_deactivation_hook(__FILE__,array(get_called_class(),'wsecure_unistaller'));
		add_action( 'login_enqueue_scripts', array(get_called_class(),'wsecure_recaptcha_style') );
		add_action('init', array(get_called_class(),'register_session'));
		add_action('admin_enqueue_scripts',array(get_called_class(),'wsecure_addScript'));
		add_action('wp_logout',array(get_called_class(),'ws_logout'));
		add_action('init',array(get_called_class(),'ws_checkUrlKey'));
		add_action('admin_menu',array(get_called_class(),'my_custom_url_handler'));
		add_action('admin_menu',array(get_called_class(),'wsecure_admin_actions'));
		add_action('login_form',array(get_called_class(),'wsecure_recaptcha_login_form'));
		add_action( 'wp_authenticate_user',array(get_called_class(),'wsecure_recaptcha_login_check'),10,2 );
		
	}	

	public static function wsecure_installer() 
	{
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$table_name = $wpdb->prefix . 'wsecure_params';
		$config_table = $wpdb->prefix . 'wsecure_config';

		$sql = "CREATE TABLE " . $table_name . " (
		`id` int(11) NOT NULL,
		`publish` int(11) NOT NULL,
		`passkeytype` varchar(45) NOT NULL,
		`wsecure_key` varchar(45) NOT NULL,
		`wsecure_options` int(11) NOT NULL,
		`custom_path` varchar(300) NOT NULL,
		`captcha_publish` int(11) NOT NULL,
		`captcha_site_key` varchar(50) NOT NULL,
		`captcha_secret_key` varchar(50) NOT NULL,
		PRIMARY KEY(`id`)
		);";	
		dbDelta($sql);    
		$wpdb->insert($table_name,array('id'=>1,'publish'=>0,'passkeytype'=>'$P$BP/KuP93J.ajmiQOiYwyA/RSaSqEs8.','wsecure_key'=>'$P$BPREHAFr3h/NSTXEiJGJhFXEhSEw6a/','wsecure_options'=>0,'custom_path'=>'','captcha_publish'=>0,'captcha_site_key'=>'','captcha_secret_key'=>''));

		$query = "CREATE TABLE ".$config_table. "(
		`id` int(10) NOT NULL,
		`admin_protect_status` int(10) NOT NULL,
		PRIMARY KEY (`id`)
		);";
		dbDelta($query);
		$wpdb->insert($config_table,array('id'=>1,'admin_protect_status'=>0));
	}

    public static function wsecure_unistaller()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'wsecure_params';
		$config_table = $wpdb->prefix . 'wsecure_config';
		$sql = "DROP TABLE IF EXISTS $table_name;";
		$wpdb->query($sql);
		$config_query = "DROP TABLE IF EXISTS $config_table;";
		$wpdb->query($config_query);
		$htaccess = ABSPATH.'wp-admin/.htaccess';
		$htpasswd = ABSPATH.'wp-admin/.htpasswd';
		if (file_exists($htaccess) && file_exists($htpasswd)) {
			 wp_delete_file( $htaccess );
			 wp_delete_file( $htpasswd );
		}
    }
	
	public static function wsecure_recaptcha_style()
	{ ?>
		<style type="text/css">	
		.g-recaptcha{
		transform:scale(0.90);
		-webkit-transform:scale(0.90);
		transform-origin:0 0;
		-webkit-transform-origin:0 0;
		}
       </style>
	
	<?php
	}

	public static function wsecure_menu()
	{
		global $wpdb;
		include 'wsecure-config.php';
	}
	
	public static function my_custom_url_handler()
	{		
		$url = sanitize_text_field($_REQUEST['page']);
		if($url == 'wsecure-configuration' && sanitize_key($_REQUEST['Save'])) {
			self::wse_up();
		}else{
			return;
		}
	}
	 
	public static function register_session()
	{
	if(!session_id())
		session_start();
	}
	
	//After logout redirect to index page
	public static function ws_logout()
	{	
		global $wpdb;
	   	$tablename = $wpdb->prefix . "wsecure_params";
		$sql = $wpdb->prepare("SELECT * FROM ".$tablename,NULL);
		$checkparams = $wpdb->get_results($sql);
		
		 foreach($checkparams as $params){	
			$wsecure_options =$params->wsecure_options;
			$custom_path =$params->custom_path;				
			$publish = $params->publish;	
		}	
		
		if($publish == 1){		
			$custom_path = ($custom_path == '')? 1 : 0;		
			if($custom_path){
				$custom_path = plugins_url('/wsecure/404.html');		
			}else{
				$custom_path = $checkparams[0]->custom_path;
			}			
			$home = get_bloginfo('home');
			$redirect_option = ($wsecure_options == "0") ? $home : $custom_path;
			$_SESSION['wSecureAuthentication'] = null;
			
			if(!is_admin()){
				$_SESSION['wSecureAuthentication'] = null;
				unset($_SESSION['wSecureAuthentication']);
				wp_redirect($redirect_option);
				exit;
			}
		}
	}
	 
	public static function wsecure_admin_actions()
	{
		add_options_page("wSecure","wSecure Lite",1,"wsecure-configuration",array(get_called_class(),'wsecure_menu'));
	}
	 
	public static function wsecure_addScript()
	{
		wp_register_style('wsecurecss',plugins_url('/css/wsecure.css', __FILE__ ));
		wp_enqueue_style('wsecurecss');
		wp_register_style('tabscss',plugins_url('/css/tabs.css', __FILE__ ));
		wp_enqueue_style('tabscss');
		wp_register_script('basicjs',plugin_dir_url(__FILE__).'/js/basic.js');
		wp_enqueue_script('basicjs');
		wp_register_script('tabbedjs',plugin_dir_url(__FILE__).'/js/tabbed.js');
		wp_enqueue_script('tabbedjs');	
	}
	
	public static function wsecure_recaptcha_login_form()
	{
		global $wpdb;
		$table = $wpdb->prefix . "wsecure_params";
		$query = $wpdb->prepare("SELECT publish,captcha_site_key,captcha_publish from " .$table. " where id=1",NULL);
		$result = $wpdb->get_results($query);
		$captchasitekey = $result[0]->captcha_site_key;
		$captcha_publish = $result[0]->captcha_publish;
		$basic_publish = $result[0]->publish;
		
		if($captcha_publish && $basic_publish){ ?>
		<script src='https://www.google.com/recaptcha/api.js'></script>
		<div class="g-recaptcha" data-sitekey="<?php echo $captchasitekey; ?>"></div>
		<?php
		}
	}
	
	public static function wsecure_recaptcha_login_check($user,$password)
	{
		global $wpdb;
		$table = $wpdb->prefix . "wsecure_params";
		$query = $wpdb->prepare("SELECT publish,captcha_secret_key,captcha_publish from " .$table. " where id=1",NULL);
		$result = $wpdb->get_results($query);
		$recaptcha_secret = $result[0]->captcha_secret_key;
		$captcha_publish = $result[0]->captcha_publish;
		$basic_publish = $result[0]->publish;
		
		if($captcha_publish && $basic_publish){
			if (sanitize_text_field($_POST['g-recaptcha-response'])) {
				$response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=". $recaptcha_secret ."&response=". sanitize_text_field($_POST['g-recaptcha-response']));
				$response = json_decode($response["body"], true);
				if (true == $response["success"]) {
					return $user;
				} else {
					return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: You are a bot"));
				} 
			}else {
			return new WP_Error("Captcha Invalid", __("<strong>ERROR</strong>: You are a bot. If not then enable JavaScript"));
			} 
		}else{
			return $user;
		}
		
	}

	public static function wsecure_pwdprotect($username,$password)
	{	
		global $wp_filesystem;
		$cryptpw= $password;

		$htpasswd = $username.':'.$cryptpw."\n";
		clearstatcache();

		if (empty($wp_filesystem)) {
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}

		$status = $wp_filesystem->put_contents(ABSPATH.'wp-admin/.htpasswd',$htpasswd);

		if(!$status){
			$url = admin_url('/options-general.php?page=wsecure-configuration&opt=admin_protect');		
			wp_redirect($url);		
		}
		$path = ABSPATH.'wp-admin/';

$htaccess = <<<ENDHTACCESS
AuthUserFile "$path.htpasswd"
AuthName "Restricted Area"
AuthType Basic
require valid-user
ENDHTACCESS;
			  $status = $wp_filesystem->put_contents(ABSPATH.'wp-admin/.htaccess', $htaccess);


	}
	
	//Checking for authenticate key value.

	public static function ws_checkUrlKey()
	{	
		global $wpdb;   	
		
		if(!isset($_SESSION['wSecureAuthentication']))
		$_SESSION['wSecureAuthentication'] = "";
			
		if(strpos($_SERVER['PHP_SELF'],'wp-login.php') !== false && $_SESSION['wSecureAuthentication']=='')
		{ 	
			$tablename = $wpdb->prefix . "wsecure_params";
			$sql = $wpdb->prepare("SELECT * FROM ".$tablename,NULL);
			$checkparams = $wpdb->get_results($sql);			
		
			foreach($checkparams as $params){
				 $publish =$params->publish;
				 $passkeytype =$params->passkeytype;				 
				 $checkpasskeytype =wp_check_password('url',$passkeytype);

				 if($checkpasskeytype == true){
					$passkeytype = 'url';
				 }else{
					$passkeytype= 'form';
				 }
				
				 $value =$params->wsecure_key;
				 $wsecure_options =$params->wsecure_options;
				 $custom_path =$params->custom_path;				
				 $custom_path = ($custom_path == '')? 1 : 0;
				
				 if($custom_path){
					$custom_path = plugins_url('/wsecure/404.html');		
				 }else{
					$custom_path = $checkparams[0]->custom_path;
				 }	
			
				 $home = get_bloginfo('home');
				 $reditect_option = ($wsecure_options=="0") ? $home : $custom_path ;
			}		
			
			if(intval($publish) != 1)
			{ 
				return;
			}			
							
			if($passkeytype == "url")
			{			
				$check_url = urldecode($_SERVER['QUERY_STRING']);		
				$get_key=explode("?",$check_url);
					
				if(strpos($get_key['1'],'&reauth')!== false){
					$reauth=explode("&",$get_key['1']);
					$check_key = sanitize_text_field($reauth['0']);
				}else{
					$check_key = sanitize_text_field($get_key['1']);
				}			
			}else{ 
				if(strtolower(sanitize_key($_POST['submit'])) != 'submit' )
				{
					self::displayForm();
					exit;
				}
			
				$check_key = sanitize_text_field($_POST['passkey']);				
			}
			
			$check =wp_check_password($check_key,$value);
		
			if((!$check) && $publish == 1) 		
			{
				unset($_SESSION['wSecureAuthentication']);
				wp_redirect($reditect_option); 
			}else{			
				$_SESSION['wSecureAuthentication'] = 1;
			}	
		}else{
			if($_SESSION['wSecureAuthentication'] !=1 || empty($_SESSION['wSecureAuthentication']) || $_SESSION['wSecureAuthentication'] == ''):
			$siteurl = get_bloginfo('siteurl');
			$home = get_bloginfo('home');
			unset($_SESSION['wSecureAuthentication']);
			wp_redirect( $reditect_option ); 
			endif;
		}
	}

	public static function wse_up()
	{
		if(!isset($_REQUEST['wsecure_nonce']) || !wp_verify_nonce($_REQUEST['wsecure_nonce'],'wse_up'))
			wp_die('Are you sure you want to do this?');
		
		if(sanitize_text_field($_POST['opt'])=="basic" && sanitize_text_field($_POST['Save'])=="Save" ){
			/* Code to Save wSecure Config */		
			global $wpdb;
			
			$publish =sanitize_text_field($_POST['publish']);			
			$passkeytype =sanitize_text_field($_POST['passkeytype']);			
			$passkeytype = wp_hash_password($passkeytype);			
			$wsecure_key =sanitize_key($_POST['wsecure_key']);
			$wsecure_options =sanitize_text_field($_POST['wsecure_options']);
			$captchapublish =sanitize_text_field($_POST['captchapublish']);
			$captchasitekey =sanitize_text_field($_POST['captchasitekey']);
			$captchakey =sanitize_text_field($_POST['captchasecretkey']);
								
			if($wsecure_key == ''){
				$tablename = $wpdb->prefix . "wsecure_params";
				$sql = $wpdb->prepare("SELECT wsecure_key FROM ".$tablename,NULL);
				$checkparamskey = $wpdb->get_results($sql);			
				$wsecure_key = $checkparamskey[0]->wsecure_key;
			}
			
			$custom_path =sanitize_text_field($_POST['custom_path']);
			
	        $newkey = sanitize_user($_POST["wsecure_key"],$strict=true)=="" ? $wsecure_key : wp_hash_password((sanitize_user($_POST["wsecure_key"],$strict=true)));
			$tablename = $wpdb->prefix . "wsecure_params";
            $sql = $wpdb->prepare("SELECT * FROM ".$tablename,NULL);
            $checkparams = $wpdb->get_results($sql);	
		
			if(empty($checkparams)){
				$wpdb->insert($tablename,array('id'=>1,'publish'=>$publish,'passkeytype'=>$passkeytype,'wsecure_key'=>$newkey,'wsecure_options'=>$wsecure_options,'custom_path'=>$custom_path,'captcha_publish'=>$captchapublish,'captcha_site_key'=>$captchasitekey,'captcha_secret_key'=>$captchakey));
			}else{
				$wpdb->update($tablename,array('id'=>1,'publish'=>$publish,'passkeytype'=>$passkeytype,'wsecure_key'=>$newkey,'wsecure_options'=>$wsecure_options,'custom_path'=>$custom_path,'captcha_publish'=>$captchapublish,'captcha_site_key'=>$captchasitekey,'captcha_secret_key'=>$captchakey),array('id' => 1));
			}
			
			$url = admin_url('/options-general.php?page=wsecure-configuration&w_action=save&opt=config');
			wp_redirect($url);			
		}
		
		if(sanitize_text_field($_POST['opt']) == "admin_protect" && sanitize_text_field($_POST['Save']) == "Save"){
		
			$admin_username = sanitize_text_field($_POST['admin_username']);
		    $admin_password = sanitize_text_field($_POST['admin_password']);
			$admin_protect_publish = sanitize_text_field($_POST['admin_protect_publish']);
			
			global $wpdb;
			$tablename = $wpdb->prefix . "wsecure_config";
			$paramstable = $wpdb->prefix . "wsecure_params";
			
			$result = $wpdb->update($tablename,array('admin_protect_status'=>$admin_protect_publish),array('id'=>1));
			
			$sql = $wpdb->prepare("SELECT admin_protect_status FROM ".$tablename." where id=1",NULL);
			$data = $wpdb->get_results($sql);
			
			$params_query = $wpdb->prepare("SELECT publish FROM ".$paramstable." where id=1",NULL);
			$params_data = $wpdb->get_results($params_query);
			$basic_publish = $params_data[0]->publish;
			
		
			if(($data[0]->admin_protect_status == 1) && $admin_username != '' && $admin_password !='' && $basic_publish == 1 ){
				self::wsecure_pwdprotect($admin_username,$admin_password);
			}else{
				$htaccess = ABSPATH.'wp-admin/.htaccess';
				$htpasswd = ABSPATH.'wp-admin/.htpasswd';
				if (file_exists($htaccess) && file_exists($htpasswd)) {
					 wp_delete_file( $htaccess );
					 wp_delete_file( $htpasswd );
				}
				
			}
		}
	}
	
	public static function displayForm(){
		$image= plugins_url('/wsecure/images/');		
	?>
			<div style="background: rgb(25, 119, 163);margin: 0px !important;padding: 0px !important;position: absolute;width: 100%;top: 0px;bottom: 0px;right: 0px;left: 0px;overflow:hidden;">

			<form name="key" action="" method="post" autocomplete="off">
				<div style="border: 2px solid #E3E7E9;margin: 9% 38%;padding: 0% 1%;background: #F1F1F1;" >
					 <div class="wsecure_key" style="background-image:url(<?php echo $image;?>wsecure_key.jpg);width: 149px;height: 140px;margin: 10px auto 0;border-radius: 40px;-moz-border-radius: 40px;-webkit-border-radius: 40px;margin-top: 35px;margin-bottom: 11px" ></div> 
					
						<div style="margin-bottom: 30px !important;" >
						<p style="font-weight: normal;font-size: 22px;text-align: center;color: #2EA2CC;
			padding-top: 8px !important;margin: 0px;font-family: arial;text-transform: uppercase;" >Admin Key</p> 
						<p style="margin: 15px 0px;padding: 0px;text-align: center;" >
						<p style="padding: 0px 5px;text-align: center;margin:0px !important;"  >
						<input type="password" name="passkey" id="passkey_id" value="" style="width: 78%;line-height: 32px;font-size: 17px;padding: 0px 6px;" placeholder="Enter security key" /></p>
					
						<p style="text-align:center;margin:5px 0px !important;" ><input type="submit" name="submit" value="Submit" style="background: #2EA2CC;padding: 7px 18px;color: #FFF;border: 0px;cursor: pointer;cursor: hand;width: 76%;line-height: 22px;font-size: 16px;" /></p>
						</p>			
					</div>
					
				</div>
			</form>
			</div>
<?php 
	}
}
?>