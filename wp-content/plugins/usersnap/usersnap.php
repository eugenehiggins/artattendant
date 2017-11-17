<?php
/*
Plugin Name: Usersnap
Plugin URI: http://www.usersnap.com
Description: Usersnap helps website owners to get feedback in form of screenshots from their customers, readers or users.
Version: 4.5
Author: Usersnap
Author URI: http://usersnap.com
License: GPL v2
*/

define('USERSNAP_VERSION', '4.5');
define('USERSNAP_POINTER_VERSION', '0_1');
define('USERSNAP_PLUGIN_URL', plugin_dir_url( __FILE__ ));

if ( is_admin() ){ // admin actions
  add_action( 'admin_init', 'us_register_settings' );
  add_action( 'admin_menu', 'us_plugin_menu' );
  add_action( 'admin_head', 'us_add_js_admin');
} else {
	add_action('wp_head', 'us_add_js');
}

/**
* add js code to webpage
**/
function us_add_js() {
	$options = get_option('usersnap_options');
	//check if we should display usersnap
	$dispUS = false;
	if (isset($options['api-key']) && strlen($options['api-key'])>0) {
		if (!isset($options['visible-for'])) {
			$options['visible-for']="all";
		}
		if ($options['visible-for']=="users") {
			if (is_user_logged_in()) {
				$dispUS = true;
			}
		} else if ($options['visible-for']=="roles") {
			if ( is_user_logged_in() ) {
				$user = new WP_User(get_current_user_id());
				if (!empty($user->roles) && is_array($user->roles)) {
					foreach($user->roles as $role ) {
						if ($dispUS) {
							break;
						}
						foreach($options['visible-for-roles'] as $chrole) {
							if ($chrole == $role) {
								$dispUS = true;
							}
						}
					}
				}
			}
		} else {
			$dispUS = true;
		}
	}

	if ($dispUS) {
		?>
		<script type="text/javascript" data-cfasync="false">
		<?php
			if ( is_user_logged_in() ) {
				$userObj = get_userdata(get_current_user_id());
				?>
				window['_usersnapconfig'] = {emailBoxValue: '<?php echo $userObj->user_email; ?>'};
				<?php
			}
		?>
			(function() {
			    var s = document.createElement('script');
			    s.type = 'text/javascript';
			    s.async = true;
			    s.src = '//api.usersnap.com/load/<?php echo $options['api-key']; ?>.js';
			    var x = document.getElementsByTagName('head')[0];
			    x.appendChild(s);
			})();
		</script>
		<?php
	}
}

/**
* add js code to admin page
**/
function us_add_js_admin() {
	$options = get_option('usersnap_options');
	//check if we should display usersnap
	if (isset($options['api-key']) &&
	(strlen($options['api-key'])>0) &&
	isset($options['visible-for-backend']) &&
	($options['visible-for-backend']=='backend')) {
		?>
		<script type="text/javascript" data-cfasync="false">
		<?php
			if ( is_user_logged_in() ) {
				$userObj = get_userdata(get_current_user_id());
				?>
				window['_usersnapconfig'] = {emailBoxValue: '<?php echo $userObj->user_email; ?>'};
				<?php
			}
		?>
			(function() {
			    var s = document.createElement('script');
			    s.type = 'text/javascript';
			    s.async = true;
			    s.src = '//api.usersnap.com/load/<?php echo $options['api-key']; ?>.js';
			    var x = document.getElementsByTagName('head')[0];
			    x.appendChild(s);
			})();
		</script>
		<?php
	}
}

/**
* build settings menu
**/

function us_plugin_menu() {
	$page = add_submenu_page('options-general.php', 'Usersnap Settings', 'Usersnap', 'administrator', __FILE__, 'us_option_page');

	add_action('admin_print_styles-'. $page, 'us_add_admin_styles');
}

function us_add_admin_styles() {
	wp_enqueue_style('usersnapAdminStyle');
}

function us_register_settings() {
	register_setting( 'usersnap_options', 'usersnap_options', 'usersnap_options_validate');
	add_settings_section('usersnap_main', '', 'usersnap_section_text', 'usersnap');
	add_settings_field('us-api-key', 'Enter your Usersnap API key', 'usersnap_input_text', 'usersnap', 'usersnap_main');

	//page usersnap_pg_new
	add_settings_section('usersnap_new', 'Create your Usersnap account', 'usersnap_section_new', 'usersnap_pg_new');
	add_settings_field('us-user-name', 'Your name', 'usersnap_input_user_name', 'usersnap_pg_new', 'usersnap_new');
	add_settings_field('us-user-email', 'Your email', 'usersnap_input_user_email', 'usersnap_pg_new', 'usersnap_new');
	add_settings_field('us-user-url', 'Blog URL', 'usersnap_input_user_url', 'usersnap_pg_new', 'usersnap_new');
	add_settings_field('us-user-pwd', 'Choose a password', 'usersnap_input_user_pwd', 'usersnap_pg_new', 'usersnap_new');
	add_settings_field('us-user-pwd2', 'Retype your password', 'usersnap_input_user_pwd2', 'usersnap_pg_new', 'usersnap_new');


	//add css
	wp_register_style('usersnapAdminStyle', plugins_url('style.css', __FILE__));
}

//user - section
function usersnap_input_user_name() {
	$options = get_option('usersnap_options');
	if (!isset($options['user-name'])) {
		$options['user-name'] = "";
	}
	?><input id="us-user-name" style="width:250px;" name="usersnap_options[user-name]" size="40" type="text" value="<?php echo $options['user-name']; ?>" /><?php
}
function usersnap_input_user_email() {
	$options = get_option('usersnap_options');
	if (!isset($options['user-email']) || $options['user-email']=="") {
		$options['user-email'] = get_bloginfo("admin_email");
	}
	?><input id="us-user-email" style="width:250px;" name="usersnap_options[user-email]" size="40" type="email" value="<?php echo $options['user-email']; ?>" /><?php
}
function usersnap_input_user_pwd() {
	$options = get_option('usersnap_options');
	?><input id="us-user-pwd" style="width:250px;" name="usersnap_options[user-pwd]" size="40" type="password" value="" /><?php
}

function usersnap_input_user_pwd2() {
	$options = get_option('usersnap_options');
	?><input id="us-user-pwd2" style="width:250px;" name="usersnap_options[user-pwd2]" size="40" type="password" value="" /><?php
}

function usersnap_input_user_url() {
	$options = get_option('usersnap_options');
	if (!isset($options['user-url']) || $options['user-url']=="") {
		$options['user-url'] = get_bloginfo("url");
	}
	?><input id="us-user-url" style="width:250px;" name="usersnap_options[user-url]" size="40" type="text" value="<?php echo $options['user-url']; ?>" /><?php
}

//end of user section

function usersnap_section_text() {
	?>
    <div class="us-box">Manage and configure the button theme and settings on your <a href="https://usersnap.com/a/" target="_blank">Usersnap site configuration</a>.</div>
	<?php
}

function usersnap_section_new() {
	?>
    <div class="us-box">Screenshots of your WordPress site will help you improve your site and communicate with your readers. Promised.<br/><a href="https://usersnap.com/wordpress?gat=wpplugin" target="_blank">Learn more about Usersnap here</a></div>
	<?php
}

function usersnap_input_text() {
	$options = get_option('usersnap_options');
	$key = "";
	if (isset($options['api-key'])) {
		$key = $options['api-key'];
	}
	?>
	<input id="us-api-key" style="width:300px;" name="usersnap_options[api-key]" size="40" type="text" value="<?php echo $key; ?>" /><?php
	if (strlen($key) > 0) {
		?>&nbsp;<a href="https://usersnap.com/configurator?key=<?php echo $key; ?>" target="_blank" class="button">Configure Widget</a>
		<p><i>If you got the error message "Referer not valid for this API-key". Please visit your<br/>
			<a href="https://usersnap.com/a/#/company/p/<?php echo $key; ?>/edit" target="_blank">Account</a> and add the blog URL to your project settings.</i></p><?php
	}
}



function usersnap_options_validate($input) {
	if (!isset($input["usersnap-api-requ"])) {
		$input["usersnap-api-requ"] = false;
	}

	if (!isset($input["message"])) {
		$input["message"] = "";
	}

	if (!isset($input["error"])) {
		$input["error"] = false;
	}

	if (!isset($input['visible-for-backend'])) {
		$input['visible-for-backend']="no";
	}

	if (isset($_POST['us_setup']) && ($input["usersnap-api-requ"] !== true)) {
		$input["usersnap-api-requ"] = true;
		//setup
		$email = $input["user-email"];
		$pwd = $input["user-pwd"];
		$url = $input["user-url"];
		$name = $input["user-name"];
		$data = http_build_query(
			array('email' => $email,
            'url' => $url,
            'password' => $pwd,
            'password2' => $pwd,
            'gat' => 'wpplugin',
		      	'tos' => 'true',
            'securetoken' => 'userhash',
			      'name' => $name,
            'package' => 'Company',
            'payment' => 'oneyear')
		);

		$opts = array(
		    'http' => array(
		        'Content-Type: text/html; charset=utf-8',
		        'method' => "POST",
		        'header' => "Accept-language: en\r\n" .
		        'Content-length: '. strlen($data) . "\r\n",
		        'content' => $data
		     )
		);

		$context = stream_context_create($opts);
		$error = false;
		$msg = "";

		$fp = @fopen('https://d.usersnap.com/angular/signup', 'r', false, $context);
		if (!$fp) {
			$msg = "HTTP Error";
			$error = true;
		} else {
			$resp = fread($fp, 1000);
			$resp_obj = json_decode($resp);
			$errorMsg = $resp_obj->{'error'};
			if($errorMsg == null) {
				$apikey = $resp_obj->{'apikey'};
				if($apikey != "") {
					//echo "Congratulations: Your API KEY is ".$apikey;
				} else {
					$error = true;
					$msg = "Could not create an API key! (".$errorMsg.")";
					//var_dump($resp_obj);
				}
			} else {
				$error = true;
				$msg = "Could not create an API key! (".$errorMsg.")";
				//var_dump($resp_obj);
			}
			fclose($fp);
		}

		//var_dump($errorMsg);

		if (!$error) {
			//no error valid api key
			$input["api-key"] = $apikey;
			$input["message"] = "";
			$input["error"] = false;
		} else {
			$input["message"] .= $msg."<br/>";
			$input["error"] = true;
		}

	} else {
		$input["usersnap-api-requ"] = false;
	}
	return $input;
}

function us_option_tab_menu($current = "newusersnap", $tabs) {
	?>
	<div id="icon-usersnap" class="icon32"><br></div>
	<h2 class="nav-tab-wrapper">
	<?php
	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		?>
		<a class='nav-tab<?php echo $class; ?>' href='?page=usersnap/usersnap.php&tab=<?php echo $tab; ?>'><?php echo $name; ?></a>
		<?php
	}
	?>
   	</h2>
   	<?php
}

function us_create_visibility_form() {
	$options = get_option('usersnap_options');
	if (!isset($options['visible-for'])) {
		$options['visible-for']="all";
	}
	if (!isset($options['visible-for-roles'])) {
		$options['visible-for-roles']=array();
	}
	if (!isset($options['visible-for-backend'])) {
		$options['visible-for-backend']="backend";
	}
	?>
	<table class="form-table">
		<tr>
		<th scope="row">
               Enable Usersnap for:
		</th>
		<td>
		  <fieldset>
			  <label for="us-visible-for-all">
			  	<input type="radio" <?php echo ($options['visible-for']=="all"?"checked":"")?> name="usersnap_options[visible-for]" value="all" id="us-visible-for-all"/> <span>All Visitors</span>
			  </label>
			  <br>
			  <label for="us-visible-for-users">
			  	<input type="radio" <?php echo ($options['visible-for']=="users"?"checked":"")?> name="usersnap_options[visible-for]" value="users" id="us-visible-for-users"/> <span>Only users who are signed in</span>
			  </label>
			  <br>
			  <label for="us-visible-for-roles">
			  	<input type="radio" <?php echo ($options['visible-for']=="roles"?"checked":"")?> name="usersnap_options[visible-for]" value="roles" id="us-visible-for-roles"/> <span>Only users with a specific role</span>
			  </label>
		  </fieldset>

		  <div class="form-table" id="us-visible-roles">
			<?php
			$wp_roles = new WP_Roles();
			$roles = $wp_roles->get_names();
			$ctn = 0;
			$check = false;

			foreach ($roles as $role_value => $role_name) {
				$check = false;
				foreach($options['visible-for-roles'] as $lurole) {
					if ($lurole === $role_value) {
						$check = true;
						break;
					}
				}
				?>
				<p>
				  <input type="checkbox" <?php echo ($check?"checked":"")?> name="usersnap_options[visible-for-roles][]" value="<?php echo $role_value; ?>" id="us-visible-for-role-<?php echo $ctn;?>"/>
				  <label for="us-visible-for-role-<?php echo $ctn;?>"><?php echo $role_name; ?></label>
				</p>
				<?php
				$ctn++;
		  	}
			?>
			</div>

		</td>
		<tr>
			<th scope="row">
	               Visibility Settings:
			</th>
			<td>
				<!--<p>
					<input type="checkbox" <?php echo ($options['visible-for-frontend']=="frontend"?"checked":"")?> name="usersnap_options[visible-for-frontend]" value="frontend" id="us-visible-for-frontend"/>
					<label for="us-visible-for-frontend">Visible for Frontend</label>
				</p>-->

				<p>
					<input type="checkbox" <?php echo ($options['visible-for-backend']=="backend"?"checked":"")?> name="usersnap_options[visible-for-backend]" value="backend" id="us-visible-for-backend"/>
					<label for="us-visible-for-backend">Visible in Administration Backend</label>
				</p>
			</td>
		</tr>
	</table>
	<script type="text/javascript">
	jQuery(function() {
		jQuery('#us-settings-form input[type=radio]').change(function() {
			var radio = jQuery('#us-visible-for-roles');
			if (radio.is(':checked')) {
				jQuery('#us-visible-roles').show();
			} else {
				jQuery('#us-visible-roles').hide();
			}
		});
		var radio = jQuery('#us-visible-for-roles');
		if (radio.is(':checked')) {
			jQuery('#us-visible-roles').show();
		}
	});
	</script>
	<?php
}


function us_option_page() {
	if (!current_user_can('administrator'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	$options = get_option('usersnap_options');
	$tabs = array();
	if (isset($options['api-key']) && strlen($options['api-key'])>0) {
		$tabs = array(
			'configure' => 'Configure'
		);
		$currenttab = "configure";
		if (isset($_GET['tab']) && $_GET['tab'] == "newusersnap") {
			$_GET['tab'] = $currenttab;
		}
	} else {
		$tabs = array(
			'newusersnap' => 'Setup Usersnap',
			'configure' => 'Configure'
		);
		$currenttab = "newusersnap";
	}
	?>
	<div class="wrap">

	<h2 class="us-headline"><?php _e( 'Settings' ); ?> › Usersnap</h2>

	<?php
	if (isset($_GET['tab'])) {
		$currenttab = $_GET['tab'];
	}

	if(count($tabs) > 1) us_option_tab_menu($currenttab, $tabs);

	?>
	<?php
	if ($options["error"] == true) {
		?><div class="error below-h2"><p><?php echo $options["message"]; ?></p></div><?php
	} ?>
	<form method="post" action="options.php" id="us-settings-form">
	<?php settings_fields( 'usersnap_options' ); ?>
	<?php
	switch($currenttab) {
		case 'newusersnap':
			?>
			<h3>Already have a Usersnap account?</h3>
			<p>Click the configure tab above.</p>
			<?php
			do_settings_sections('usersnap_pg_new');
			?>

			<p class="submit">
				<input type="hidden" name="us_setup" value="true"/>
				<input type="submit" id="us-btn-setup" name="us_btn_setup" class="button-primary" style="margin-bottom:.5em" value="<?php _e('Create Usersnap account') ?>" /><br>
				<i>By clicking "Create Usersnap account" you agree to the <a href="https://usersnap.com/terms-of-service">Terms of Service</a> and <a href="https://usersnap.com/privacy-policy">Privacy Policy</a>.</i>
			</p>
			<script type="text/javascript">
			jQuery('#us-settings-form').submit(function(form) {
				if ((jQuery('#us-user-name').val()==='')) {
					jQuery('.wrap h2:last').after('<div class="error below-h2" style="margin-top:1em"><p><?php _e('Please specify a name!') ?></p></div>');
					jQuery('#us-user-name').focus();
					return false;
				}
				var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				if ((jQuery('#us-user-email').val()==='') || !re.test(jQuery('#us-user-email').val())) {
					jQuery('.wrap h2:last').after('<div class="error below-h2" style="margin-top:1em"><p><?php _e('Please enter a valid email address!') ?></p></div>');
					jQuery('#us-user-email').focus();
					return false;
				}
				if ((jQuery('#us-user-pwd').val()==='') || (jQuery('#us-user-pwd').val() !== jQuery('#us-user-pwd2').val())) {
					jQuery('.wrap h2:last').after('<div class="error below-h2" style="margin-top:1em"><p><?php _e('Your passwords are empty or not equal!') ?></p></div>');
					jQuery('#us-user-pwd').focus();
					return false;
				}
				jQuery('#us-btn-setup').attr("disabled", true).val("<?php _e('Please wait...') ?>");
			});


			jQuery.post( ajaxurl, {
              pointer: '<?php echo $pointer; ?>',
              action: 'dismiss-wp-pointer'
           	} );
			</script>
			<?php
			break;
		case 'configure':
			do_settings_sections('usersnap');
			us_create_visibility_form();
			?>
			<p class="submit">
				<input type="submit" id="us-btn-save" name="us_btn_save" class="button-primary" value="<?php _e('Save Changes') ?>" />
				<input type="button" class="button" id="us-reset-settings" value="<?php _e('Reset Settings') ?>" />
			</p>
			<script type="text/javascript">
			jQuery(function() {
				jQuery('#us-settings-form').submit(function() {
					if (jQuery('#us-api-key').val()!=='') {
						var s = /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i
						if (!s.test(jQuery('#us-api-key').val())) {
							jQuery('#us-api-key').focus();
							jQuery('.wrap h2:last').after('<div class="error below-h2" style="margin-top:1em"><p><?php _e('Your API key is not valid, please check again!') ?></p></div>');
							return false;
						}
					}
				});

				jQuery('#us-reset-settings').click(function() {
					jQuery('#us-api-key').val('');
					jQuery('#us-visible-for-all').attr('checked',true);
					jQuery('#us-visible-roles').hide();
					jQuery('#us-btn-save').click();
				});
			});

			jQuery.post( ajaxurl, {
                  pointer: '<?php echo $pointer; ?>',
                  action: 'dismiss-wp-pointer'
            } );

			</script>
			<?php
			break;
	}
	?>
	</form>
	</div>
	<?php
}



//Show Setup bubble and Indo Bubble

add_action( 'admin_enqueue_scripts', 'usersnap_admin_pointer_header' );

function usersnap_admin_pointer_header() {
   if ( usersnap_admin_pointer_check() ) {
      add_action( 'admin_print_footer_scripts', 'usersnap_admin_pointer_footer' );

      wp_enqueue_script( 'wp-pointer' );
      wp_enqueue_style( 'wp-pointer' );
   }
}

function usersnap_admin_pointer_check() {
	$pointer = 'usersnap_admin_pointer' . USERSNAP_POINTER_VERSION . '_new_items';
	$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

	//don't show the pointe if we are in the usersnap settings page
	if(!is_admin()) return false;
	if(get_current_screen()->base == 'settings_page_usersnap/usersnap') {
		$options = get_option('usersnap_options');

		//remove the pointer if usersnap has been set up
		if (isset($options['api-key']) && strlen($options['api-key']) > 0 ) {
			$pointer = 'usersnap_admin_pointer' . USERSNAP_POINTER_VERSION . '_new_items';
			$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
			if(! in_array( $pointer, $dismissed ) ) {
				array_push($dismissed, $pointer);
				$dismissed = implode( ',', $dismissed );
				$users = get_users();
				update_user_meta( get_current_user_id(), 'dismissed_wp_pointers', $dismissed );
			}

		}

		return false;
	}


   $admin_pointers = usersnap_admin_pointer();
   foreach ( $admin_pointers as $pointer => $array ) {
      if ( $array['active'] )
         return true;
   }
}

function usersnap_admin_pointer_footer() {
   $admin_pointers = usersnap_admin_pointer();
   ?>
<script type="text/javascript">
/* <![CDATA[ */
( function($) {
   <?php
   foreach ( $admin_pointers as $pointer => $array ) {
      if ( $array['active'] ) {
         ?>
         $( '<?php echo $array['anchor_id']; ?>' ).pointer( {
            content: '<?php echo $array['content']; ?>',
            position: {
            edge: '<?php echo $array['edge']; ?>',
            align: '<?php echo $array['align']; ?>'
         },
            close: function() {
               $.post( ajaxurl, {
                  pointer: '<?php echo $pointer; ?>',
                  action: 'dismiss-wp-pointer'
               } );
            }
         } ).pointer( 'open' );
         <?php
      }
   }
   ?>
} )(jQuery);
/* ]]> */
</script>
   <?php
}

function usersnap_admin_pointer() {
	$options = get_option('usersnap_options');
   $dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
   $version = USERSNAP_POINTER_VERSION; // version of this pointer - change to view again after update
   $prefix = 'usersnap_admin_pointer' . $version . '_';

   if (isset($options['api-key']) && strlen($options['api-key'])>0) {
   		$new_pointer_content = '<h3>' . __( 'Usersnap Settings have moved.' ) . '</h3>';
   		$new_pointer_content .= '<p>' . __( 'You can now find your Usersnap settings in the settings menu.' ) . '</p>';
   } else {
	   $new_pointer_content = '<h3>' . __( 'Set up Usersnap!' ) . '</h3>';
	   $new_pointer_content .= '<p>' . __( 'Set up your Account and API-Key to get Usersnap up and running!' ) . '</p>';
   }

   return array(
      $prefix . 'new_items' => array(
         'content' => $new_pointer_content,
         'anchor_id' => '#menu-settings',
         'edge' => 'left',
         'align' => 'left',
         'active' => ( ! in_array( $prefix . 'new_items', $dismissed ) )
      ),
   );
}

function usersnap_admin_pointer_hide($pointer_id) {

}
