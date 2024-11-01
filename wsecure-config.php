<?php 
/*
Version: 2.5
Author: Ajay Lulia
Author URI: http://www.joomlaserviceprovider.com/
*/

if(!defined('ABSPATH'))exit; // Exit if accessed directly

	$opt ="";

	if(wp_trim_words(sanitize_key($_REQUEST['opt'])) == '')
	{
		$_REQUEST['opt'] = 'adv';
			
		if((isset($_REQUEST['opt']) && sanitize_key(wp_trim_words($_REQUEST['opt'])) == true ) || (isset($_REQUEST['Save']) && sanitize_key(wp_trim_words($_REQUEST['Save'])) == "Save"))
		{
			$_REQUEST['opt'] = 'config'; 
		}
	
	}  
	$opt = sanitize_key(wp_trim_words($_REQUEST['opt']));
	$flag_saved = 0;
?>
<div class="wrap">
<table width="100%" style="margin: 0px 0px 20px 0px;" >
<tr>
<td width="80%">
<h2 class="wsecure_heading" >wSecure Lite</h2>
</td>

</table>  
  <?php 
 
  if(sanitize_key($_REQUEST['w_action']) == "save" && $file_permission=="0")
  {
  	 echo "<div id='message' class='updated fade'>Settings is not updated! Check file permission. </div>"; 
	 $flag_saved = 0;
  }
  else if((sanitize_key($_REQUEST['w_action']) == "save") || (sanitize_key($_REQUEST['Save']) == "save"))
  {
  	echo "<div id='message' class='wsecure_updated fade'>Settings Updated</div>";
	 $flag_saved = 0;
  }   
   ?>
  <ul class="nav-tab-wrapper wsecuremenu">
 
    <li><a class="nav-tab-wsecure<?php $class = ($opt == 'adv') 	? $class = " nav-tab-wsecure-active" : $class = "";  echo $class; ?>" href="?page=<?php echo sanitize_key($_GET['page']); ?>&opt=adv">Advanced Configuration</a></li>
	<li><a class="nav-tab-wsecure<?php $class = ($opt == 'config')  ? $class = " nav-tab-wsecure-active" : $class = "";  echo $class; ?>" href="?page=<?php echo sanitize_key($_GET['page']); ?>&opt=config">Basic Configuration</a></li>
	<li><a class="nav-tab-wsecure<?php $class = ($opt == 'admin_protect')  ? $class = " nav-tab-wsecure-active" : $class = "";  echo $class; ?>" href="?page=<?php echo sanitize_key($_GET['page']); ?>&opt=admin_protect">Admin Protection</a></li>
    <li><a class="nav-tab-wsecure<?php $class = ($opt == 'help') 	? $class = " nav-tab-wsecure-active" : $class = "";  echo $class; ?>" href="?page=<?php echo sanitize_key($_GET['page']); ?>&opt=help">Help</a></li>
    <li><a class="nav-tab-wsecure<?php $class = ($opt == 'extension') 	? $class = " nav-tab-wsecure-active" : $class = "";  echo $class; ?>" href="?page=<?php echo sanitize_key($_GET['page']); ?>&opt=ext">Extensions</a></li>

  </ul>  
  <?php   
  if(sanitize_key($_REQUEST['opt'])=='config')
  {
	global $wpdb;
	$tablename = $wpdb->prefix . "wsecure_params";
	$sql = $wpdb->prepare("SELECT * FROM ".$tablename,NULL);
	$checkparams = $wpdb->get_results($sql);
	$captchasitekey = $checkparams[0]->captcha_site_key;
	$captchasecretkey = $checkparams[0]->captcha_secret_key;
	$captchapublish = $checkparams[0]->captcha_publish;
   ?>  
  <div class="wsecure_container" >
    <form name="save" id="save" method="post" action="options-general.php?page=wsecure-configuration" autocomplete="off">
	<input type="hidden" name="opt" value="basic"/>
        <?php wp_nonce_field('wse_up','wsecure_nonce');
		 foreach($checkparams as $params){
			$publish =$params->publish;
			$passkeytype =$params->passkeytype;		  		 		 
			$checkpasskeytype =wp_check_password('url',$passkeytype);

			if($checkpasskeytype == true){
				$passkeytype = 'url';
			}else{
				$passkeytype= 'form';
			}
		 
			 $wsecure_key =$params->wsecure_key;
			 $wsecure_options =$params->wsecure_options;
			 $custom_path =$params->custom_path;		
		?> 
        
	<table class="form-table">
          
          	<tr valign="top">
            	<th class="wsecure_th" scope="row" ><label for="enable"><?php _e('Enable') ?></label></th>
                <td>
                    <select name="publish" id="enable" style="width:100px" class="wsecure_input" >
                        <option value="0" <?php echo ($publish == 0)?"selected":''; ?>><?php _e('No'); ?></option>
                        <option value="1" <?php echo ($publish == 1)?"selected":''; ?>><?php _e('Yes'); ?></option>
                    </select>
					<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_desc_publish' );" onmouseover="showTooltip('wsecure_desc_publish', 'Enable', 'For wSecure to be activated set this to yes and go to the plugin manager and Activate wSecure Lite plugin')" />
					<div class="setting-description" id="wsecure_desc_publish" ><?php _e('For wSecure to be activated set this to yes and go to the plugin manager and Activate wSecure Lite plugin'); ?></div>
                </td>		

			</tr>	
            
			 <tr valign="top">
        <th  class="wsecure_th"  scope="row"><label for="passkeytype">
          <?php _e('Pass Key') ?>
          </label></th>
        <td><select name="passkeytype" id="passkeytype" style="width:100px"  class="wsecure_input"  >
            <option value="url" <?php echo ($passkeytype == "url")?"selected":''; ?>>
            <?php _e('URL'); ?>
            </option>
            <option value="form" <?php echo ($passkeytype == "form")?"selected":''; ?>>
            <?php _e('FORM'); ?>
            </option>
          </select>
		  <img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_desc_pass_key' );" onmouseover="showTooltip('wsecure_desc_pass_key', 'Pass Key', 'Select the mode in which you want to enter the key for authentication in wSecure.<br/><b>FORM</b> mode gives a customized form to enter the authentication key.<br/><b>URL</b> mode allows to enter the authentication directly in the url in the format /wp-admin?secretkey')" />
		 <div class="setting-description" id="wsecure_desc_pass_key" >
          <?php _e('Select the mode in which you want to enter the key for authentication in wSecure.<br/><b>FORM</b> mode gives a customized form to enter the authentication key.<br/><b>URL</b> mode allows to enter the authentication directly in the url in the format /wp-admin?secretkey.'); ?>
          </div> </td>
      </tr>
			
            <tr valign="top">
              <th scope="row" class="wsecure_th" ><label for="wsecure_key"><?php _e('Key') ?></label></th>
              <td>
              		<input type="password" name="wsecure_key" value="" size="50" id="key" class="wsecure_input regular-text"/>
				    <img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_desc_secret_key' );" onmouseover="showTooltip('wsecure_desc_secret_key', 'Secret Key', 'Enter the new key here. For example, if your desired URL is /wp-admin/?secretkey then enter <b>secretkey</b> in this field. Please do not use any spaces or special characters.The key is case sensitive and can **ONLY** contain alphanumeric values. PLEASE dont use numeric values')" />
					<div class="setting-description" id="wsecure_desc_secret_key" ><?php _e('Enter the new key here. For example, if your desired URL is /wp-admin/?secretkey then enter "secretkey" in this field. Please do not use any spaces or special characters.The key is case sensitive and can **ONLY** contain alphanumeric values. PLEASE dont use numeric values'); ?></div>
              </td>
            </tr>
            
            <tr valign="top">
              <th scope="row" class="wsecure_th" ><label for="wsecure_options"><?php _e('Redirect Options') ?></label></th>
              <td>
              	<select name="wsecure_options" id="wsecure_options" style="width:160px" onchange="javascript: hideCustomPath(this);"  class="wsecure_input"  >
					<option value="0" <?php echo ($wsecure_options == 0)?"selected":''; ?>><?php _e('Redirect to index page'); ?></option>
					<option value="1" <?php echo ($wsecure_options == 1)?"selected":''; ?>><?php _e('Custom Path'); ?></option>
				</select>
					<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_desc_redirect' );" onmouseover="showTooltip('wsecure_desc_redirect', 'Redirect Options', 'This sets where the user will be sent if they try to access the default WordPress administrator URL (/wp-admin)')" />
					<div class="setting-description" id="wsecure_desc_redirect" ><?php _e('This sets where the user will be sent if they try to access the default WordPress administrator URL (/wp-admin)'); ?></div>
              </td>
            </tr>
            
            <tr valign="top" id="custom_path">
              <th scope="row" class="wsecure_th" ><label for="custompath"><?php _e('Custom Path') ?></label></th>
              <td>
              	<input name="custom_path" type="text" value="<?php echo $custom_path; ?>" size="50" class="regular-text" id="custompath"  class="wsecure_input" />
                <span class="setting-description"><?php _e('Set the path to the page that will be displayed if the user tries to access the normal admin URL (/wp-admin)'); ?></span>
              </td>
            </tr>
			
			<!--code for recaptcha-->
			<tr valign="top" id="captchapublish">
				<th scope="row" class="wsecure_th"><label for="captchapublish"><?php _e('Captcha Status') ?></label></th>
					<td>
					<select name="captchapublish" id="captchapublish" style="width:100px" class="wsecure_input">
						<option value="0" <?php echo ($captchapublish == 0)?"selected":''; ?>><?php _e('No'); ?></option>
						<option value="1" <?php echo ($captchapublish == 1)?"selected":''; ?>><?php _e('Yes'); ?></option>
					</select>
					<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_captchapublish' );" onmouseover="showTooltip('wsecure_captchapublish', 'Enable', 'Displays the Google Re-Captcha on Wordpress Admin login screen if enabled')" />
					<div class="setting-description" id="wsecure_captchapublish" ></div>
				</td>
			</tr>
			<tr valign="top" id="captchasecretkey">
				<th scope="row" class="wsecure_th"><label for="captchasecretkey"><?php _e('Re-Captcha Secret Key') ?></label></th>
				<td>
				<input type="text" name="captchasecretkey" value="<?php echo $captchasecretkey;?>" size="50" id="captchakey" class="regular-text" AUTOCOMPLETE="off"/>
				<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_captchasecretkey' );" onmouseover="showTooltip('wsecure_captchasecretkey', 'Secret Key', 'Enter the recaptcha Secret Key obtained from Google Re-Captcha')" />
					<div class="setting-description" id="wsecure_captchasecretkey" ></div>
				</td>
			</tr>
			<tr valign="top" id="captchasitekey">
			<th scope="row" class="wsecure_th"><label for="captchasitekey">
			<?php _e('Re-Captcha Site Key') ?>
			</label>
			</th>
			<td>
			<input type="text" name="captchasitekey" value="<?php echo $captchasitekey;?>" size="50" id="captchasitekey" class="regular-text" AUTOCOMPLETE="off"/>
			<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_captchasitekey' );" onmouseover="showTooltip('wsecure_captchasitekey', 'Site Key', 'Enter the recaptcha Site Key obtained from Google Re-Captcha which is used to display the captcha form on the website')" />
					<div class="setting-description" id="wsecure_captchasitekey" ></div>
			</td>
			</tr>
			<tr valign="top" id="publishforumcheck">
            <th scope="row"><label for="publishforumcheck">
              <?php _e('Useful Links') ?>
              </label></th>
		  <td>		
		<a title="Get the Google Re-Captcha keys" href="https://www.google.com/recaptcha/intro/index.html" target="_blank">Obtain Google Re-Captcha Keys</a>
		<span class="setting-description">
          <?php _e('Use the link to get your Google Re-Captcha keys'); ?>
          </span>
		  </td>
		</tr>
			
			<!--code for recaptcha-->
			
            <?php }?>
          </table>

		  <input type="submit" name="Save" class="button-primary" value="Save" style="padding: 0px 18px;margin: 13px 0px;" />

    </form>
    
	<script type="text/javascript">
		hideCustomPath(document.getElementById('wsecure_options'));
	</script>
  
  </div>
  <?php
  }
  ?>
  <?php 
  if(sanitize_key($_REQUEST['opt'])=='help')
  {
  ?>
  <div class="wsecure_container" >
  	<h3 style="color:#2EA2CC;margin: 12px 0px 0px 0px;" ><?php _e('Drawback:'); ?></h3>
  	<p><?php _e('WordPress has one drawback, any web user can easily know the site is created in WordPress! by typing the URL to access the administration area (i.e. www.site name.com/wp-admin). This makes hackers hack the site easily once they crack username and password for WordPress!.'); ?></p>
	
    <h3 style="color:#2EA2CC;" ><?php _e('Instructions:'); ?></h3>
  	<p><?php _e('wSecure Lite plugin prevents access to administration (back end) login page without appropriate access key.'); ?></p>
    
    <h3 style="color:#2EA2CC;" ><?php _e('Important! :'); ?></h3>
  	<p><?php _e('In order for wSecure to work the wSecure Lite plugin must be activated. Go to Plugins ->Plugin Manager and look for the "wSecure Lite plugin". Make sure this plugin is activated.'); ?></p>
    
    <h3 style="color:#2EA2CC;" ><?php _e('Basic Configuration:'); ?></h3>
  	<p>
		<?php _e('The basic configuration will hide your administrator URL from public access. This serves for the basic security threat for all WordPress websites.'); ?>
        <ul style="font-weight:bold;" >
        	<li><?php _e('1. Set "Enable" to "yes".'); ?></li>
			<li><?php _e('2. In the "Pass Key" field enter the option of URL or FORM.In the case of url the secret key will be added to url For example, if you enter "wSecure" into the key field, then the admin URL will be http://www.yourwebsite/wp-admin/?wSecure.<p>
If you choose the option form it will lead to the display of wSecure form where one can enter the secret key to gain admin access.</p>'); ?></li>
			<li><?php _e('3. In the "Key" field enter the key that will be part of your new administrator URL. For example, if you enter "wSecure" into the key field, then the administrator URL will be http://www.yourwebsite/wp-admin/?wSecure. Please note that you cannot have a key that is only numbers.
			<p>If you do not enter a key, but enable the wSecure component, then the URL to access the administrator area is /?wSecure (http://www.yourwebsite/wp-admin/?wSecure).</p>'); ?></li>
			<li><?php _e('4. Set the "Redirect Options" field. By default, if someone tries to access you /wp-admin URL without the correct key, they will be redirected to the home page of your WordPress site. You can also set up a "Custom Path" is you would like the user to be redirected somewhere else, such as a 404 error page.'); ?></li>
        </ul>
    </p>
     <p>
    	<?php _e('For More information <a href="http://joomlaserviceprovider.com" title="http://joomlaserviceprovider.com" target="_blank">http://joomlaserviceprovider.com</a><br/>'); ?>
    </p>
	</div>
  <?php 
  }
  ?>
  <?php 
  if(sanitize_key($_REQUEST['opt'])=='adv')
  {
  ?>
  <div class="wsecure_container" >
  <p style="font-weight: bold;font-size: 15px;" >
  Please upgrade to <a title="Get Premium Version" href="http://www.joomlaserviceprovider.com/extensions/wordpress/commercial/wsecure-authentication.html" target="_blank" style="text-decoration:none;" >Premium Version</a> to enjoy the following list of advanced features.
  </p>  
  	<hr/>
	<div class="wsecure_header_disp" >Current Features </div>	
	<hr/>
	<div class="wsecure_acc_parent" >
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Mail
			<div class="wsecure_acc_child_desc" >Provides you an option whether you want an email to be sent every time there is a failed login attempt into the WordPress administration area.<br/>You can set it to send the wSecure correct key or the incorrect key that was entered</div>
		</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >IP
			<div class="wsecure_acc_child_desc" > Provides an option to allow you to control which IPs have access to your admin URL.<br/><span style="min-width: 130px;width: 130px;display: inline-block;" >White Listed IPs:</span> If set to "White Listed IPs" you can make a white list for certain IPs. Only those specific IPS will be allowed to access your admin URL.<br/><span style="min-width: 130px;width: 130px;display: inline-block;" >Blocked IPs:</span> If set to "Blocked IPs" you can block certain IPs form accessing your admin URL.
			</div>
			</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Master Password
			<div class="wsecure_acc_child_desc" >You can block access to the wSecure component from other administrators. Setting to "Yes", allows you to create a password that will be required when any administrator tries to access the wSecure configuration settings in the WordPress administration area.</div>
		</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Master Mail
			<div class="wsecure_acc_child_desc" >Provides an option to allow you to have an email sent every time any of the wSecure configuration is changed, so that you have record  of the new configuration made.</div>
		</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Log
			<div class="wsecure_acc_child_desc" > This setting allows you to decide how long the wSecure logs should remain in the database. The longer this is set for, the more database space will be used.
			</div>
			</div>
		</div>
	</div>  
		<hr/>
	<div class="wsecure_header_disp" >Upcoming Features</div>	
	<hr/>
	<div class="wsecure_acc_parent" >
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >AutoBan Ip
			<div class="wsecure_acc_child_desc" >With this feature you automate the process to add vulnerable IP addresses to Blacklisted/ Blocked IP'S, by just selecting the time duration and number of invalid admin access attempts.</div>
			</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Master Password (upgrade)
			<div class="wsecure_acc_child_desc" >We  are upgrading the current feature of Master Password, to allow option to include/ exclude different sections of wSecure configurations in password protection of Master Password Protection.</div>
			</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Directory Listing
			<div class="wsecure_acc_child_desc" >Directory listing to show list of all files and folders with their permissions on the site.
			</div>
			</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Plugin Password Protection
				<div class="wsecure_acc_child_desc" >With this feature you can restrict access to different admin's of site for configuration and data of plugins that are installed.
You can set password for the admin side access of plugins that are installed and set option to "Enabled". This will restrict other administrators from accessing the protected plugins.</div>
			</div>
		</div>
		<div class="wsecure_acc_child" >
			<div class="wsecure_acc_child_title" >Log (upgrade)
			<div class="wsecure_acc_child_desc" > We  are upgrading the current feature of Log, we are going to add an option to directly add the IP's from Log to Blacklist or remove from blackList. So can analyze the Log and classify IP's directly.
			</div>
			</div>
		</div>
	</div>  
</div>  
  <?php 
  }
  ?>
  
</div>

<?php
if(sanitize_key($_REQUEST['opt'])=='ext')
  { 

   ?>
  <?php
  $extensions = array(
  'wSecure Authentication' => (object) array(
		'url'       => 'http://www.joomlaserviceprovider.com/extensions/wordpress/commercial/wsecure-authentication.html',
		'title'     => 'wSecure Authentication',
		/* translators: %1$s expands to Yoast SEO */
		'desc'      => sprintf( __('Protect you WordPress site! wSecure hides your WordPress admin page from public access making it invisible helping protect your website from hackers.')),
		
	),	
);
?>
<div class="tabwrapper">
		<div id="extensions" class = "wstab" >			
<?php
	
foreach($extensions as $extn){
	?>
	<div class="wswrapper" >
	<a href="<?php _e($extn->url);?>" target="_blank">
	</a>
	<h3 style="color:#2EA2CC; background-color:#fff;" ><?php _e($extn->title); ?></h3>
	 <div class="wscontetntwrap">
	 <p><?php _e($extn->desc); ?></p>
     <p class="buttons"><a href="<?php _e($extn->url);?>" target="_blank" class="button-primary">Get This Extension</a></p>
	 </div>
	</div>	
	<?php	
}
  ?> 
	<div class="clearfix"></div>
  </div>
  </div>
  <?php
  }
  
  if(sanitize_key($_REQUEST['opt'])=='admin_protect'){
  
	global $wpdb;
	$tablename = $wpdb->prefix . "wsecure_config";
	$sql = $wpdb->prepare("SELECT * FROM ".$tablename,NULL);
	$configdata = $wpdb->get_results($sql);
  ?>
  <div class="wsecure_container">
	<form name="admin_protect" method="POST" action="options-general.php?page=wsecure-configuration" autocomplete="off" onsubmit="return validateAdminProtect()">
	  <?php wp_nonce_field('wse_up','wsecure_nonce');
	   foreach($configdata as $params){
			$publish =$params->admin_protect_status;
	  
	  ?>
		<table>
			<tr valign="top">
				<th class="wsecure_th" scope="row" ><label for="admin_username"><?php _e('Enable Admin Password Protection') ?></label></th>
				<td>
				<select name="admin_protect_publish" id="enable" style="width:100px" class="wsecure_input" >
                        <option value="0" <?php echo ($publish == 0)?"selected":''; ?>><?php _e('No'); ?></option>
                        <option value="1" <?php echo ($publish == 1)?"selected":''; ?>><?php _e('Yes'); ?></option>
                </select>
				<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_admin_protect_publish' );" onmouseover="showTooltip('wsecure_admin_protect_publish', 'Enable', 'Enable/Disable .htaccess Admin Protection')" />
					<div class="setting-description" id="wsecure_admin_protect_publish" ></div>
				</td>
			</tr>
			<tr valign="top">
				<th class="wsecure_th" scope="row" ><label for="admin_username"><?php _e('Admin Username') ?></label></th>
				<td><input type="text" id="admin_username" name="admin_username" value="" size="30">
				<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_admin_protect_username' );" onmouseover="showTooltip('wsecure_admin_protect_username','Admin Protect Username','Enter your Admin Protection Username')" />
					<div class="setting-description" id="wsecure_admin_protect_username" ></div>
				</td>
				
			</tr>
			<tr valign="top">
				<th class="wsecure_th" scope="row" ><label for="admin_password"><?php _e('Admin Password') ?></label></th>
				<td><input type="password" name="admin_password" id="admin_password" value="" size="30">
				<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_admin_protect_password' );" onmouseover="showTooltip('wsecure_admin_protect_password','Admin Protect Password','Enter your Admin Protection Password')" />
					<div class="setting-description" id="wsecure_admin_protect_password" ></div>
				</td>	
			</tr>
			<tr valign="top">
				<th class="wsecure_th" scope="row" ><label for="verify_password"><?php _e('Verify Password') ?></label></th>
				<td><input type="password" name="verify_password" id="verify_password" value="" size="30">
				<img class="wsecure_info" src="<?php echo plugins_url('images/wsecure_info.png', __FILE__ );?>" onmouseout="hideTooltip('wsecure_admin_verify_password' );" onmouseover="showTooltip('wsecure_admin_verify_password','Verify Admin Protect Password','Confirm your Admin Protection Password')" />
					<div class="setting-description" id="wsecure_admin_verify_password" ></div>
				</td>	
			</tr>
			<tr>
				<td><input type="submit" class="button-primary" name="Save" value="Save"><td>
			</tr>
		</table>
		<?php }?>
		<input type="hidden" name="opt" value="admin_protect"/>
	</form>
  <div>
  
 <?php }
  ?>

