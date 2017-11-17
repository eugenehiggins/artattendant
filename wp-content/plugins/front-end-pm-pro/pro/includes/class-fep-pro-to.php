<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Pro_To
  {
	private static $instance;
	
	public static function init()
        {
            if(!self::$instance instanceof self) {
                self::$instance = new self;
            }
            return self::$instance;
        }
	
    function actions_filters()
    	{
			add_filter( 'fep_admin_settings_tabs', array($this, 'admin_settings_tabs' ) );
			add_filter( 'fep_settings_fields', array($this, 'settings_fields' ) );
			add_filter( 'fep_form_fields', array($this, 'fep_form_fields' ) );
			add_action('fep_admin_settings_field_output_oa_admins', array($this, 'field_output' ) );
			add_filter('fep_settings_field_sanitize_filter_oa_admins', array($this, 'settings_field_sanitize_filter' ), 10, 2 );
			add_action( 'fep_form_field_output_fep_pro_to', array($this, 'form_field_output' ), 10, 2 );
			add_action( 'fep_form_field_validate_fep_pro_to', array($this, 'form_field_validate_admin' ), 10, 2 );
			add_action( 'fep_form_field_validate_fep_pro_to', array($this, 'form_field_validate_users' ), 12, 2 );
			add_action( 'fep_action_validate_form', array($this, 'fep_action_validate_form' ), 10, 3 );
			
			if( 'separate-message' == fep_get_option('mr-message', 'same-message' ) && empty( $_POST['fep_send_to_admin'] ) ) {
				add_action( 'fep_posted_action_newmessage', array($this, 'fep_posted_action' ) );
			}
    	}

	function admin_settings_tabs( $tabs ) {
				
		$tabs['mr_multiple_recipients'] =  array(
				'section_title'			=> __('Multiple Recipients', 'front-end-pm'),
				'section_page'		=> 'fep_settings_recipient',
				'priority'			=> 5,
				'tab_output'		=> false
				);
		$tabs['oa_admins'] =  array(
				'section_title'			=> __('FEP Admins', 'front-end-pm'),
				'section_page'		=> 'fep_settings_recipient',
				'priority'			=> 10,
				'tab_output'		=> false
				);
				
		return $tabs;
	}
	
	function settings_fields( $fields)
		{
			$fields['mr-can-send-to-users'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'mr_multiple_recipients',
				'value' => fep_get_option('mr-can-send-to-users', 1 ),
				//'description' => __( 'Can users send message to other users.', 'front-end-pm' ),
				'label' => __( 'Can send to users', 'front-end-pm' ),
				'cb_label' => __( 'Can users send message to other users.', 'front-end-pm' )
				);
			$fields['mr-can-admin-send-to-users'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'mr_multiple_recipients',
				'value' => fep_get_option('mr-can-admin-send-to-users', 1 ),
				//'description' => __( 'Can users send message to other users.', 'front-end-pm' ),
				'label' => __( 'Can admin send to users', 'front-end-pm' ),
				'cb_label' => __( 'Can admin send new message to other users.', 'front-end-pm' )
				);
			$fields['mr-max-recipients'] =   array(
				'type'	=>	'number',
				'section'	=> 'mr_multiple_recipients',
				'value' => fep_get_option('mr-max-recipients', 5 ),
				'description' => __( 'Maximum recipients per message.', 'front-end-pm' ),
				'label' => __( 'Max recipients', 'front-end-pm' )
				);
			$fields['mr-message'] =   array(
				'type'	=>	'select',
				'section'	=> 'mr_multiple_recipients',
				'value' => fep_get_option('mr-message', 'same-message' ),
				'description' => __( 'How message will be sent to recipients', 'front-end-pm' ),
				'label' => __( 'Message type', 'front-end-pm' ),
				'options' => array(
					'same-message' => __( 'Same Message', 'front-end-pm' ),
					'separate-message' => __( 'Separate Message', 'front-end-pm' )
					)
				);
			$fields['oa-can-send-to-admin'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'oa_admins',
				'value' => fep_get_option('oa-can-send-to-admin', 1 ),
				'description' => __( 'Can users send message to admin.', 'front-end-pm' ),
				'label' => __( 'Can send to admin', 'front-end-pm' )
				);
			$fields['oa_admins'] =   array(
				'type'	=>	'oa_admins',
				'section'	=> 'oa_admins',
				'value' => fep_get_option('oa_admins', array()),
				'description' => __( 'Do not forget to save.', 'front-end-pm' ),
				'label' => 'Admins'
				);
			$fields['oa_admins_frontend'] =   array(
				'type'	=>	'select',
				'section'	=> 'oa_admins',
				'value' => fep_get_option('oa_admins_frontend', 'select' ),
				'description' => __( 'Select how you want to see in frontend.', 'front-end-pm' ),
				'label' => __( 'Show in front end as', 'front-end-pm' ),
				'options'	=> array(
					'select'	=> __( 'Select', 'front-end-pm' ),
					'radio'	=> __( 'Radio', 'front-end-pm' )
					)
				);
			unset($fields['hide_autosuggest']);
								
			return $fields;
			
		}
	function field_output( $field ){
		
		$count = 0;
		
		if( $field['value' ] && is_array($field['value' ]) ) {
		foreach( $field['value' ] as $k => $v ) { ?>
			<div>
				<span><input type="text" required name="oa_admins[oa_<?php echo $count; ?>][name]" value="<?php esc_attr_e( $v['name'] ); ?>"/></span>
				<span><input type="text" required name="oa_admins[oa_<?php echo $count; ?>][username]" value="<?php esc_attr_e( $v['username'] ); ?>"/></span>
				<span><input type="button" class="button button-small fep_oa_remove" value="<?php esc_attr_e( 'Remove' ); ?>" /></span>
			</div>
		<?php
		$count++;
		 } } else { ?>
		
			<div>
				<span><input type="text"  placeholder="<?php esc_attr_e( 'Display as', 'front-end-pm' ); ?>" required name="oa_admins[oa_<?php echo $count; ?>][name]" value=""/></span>
				<span><input type="text"  placeholder="<?php esc_attr_e( 'Username', 'front-end-pm' ); ?>" required name="oa_admins[oa_<?php echo $count; ?>][username]" value=""/></span>
				<span><input type="button" class="button button-small fep_oa_remove" value="<?php esc_attr_e( 'Remove', 'front-end-pm' ); ?>" /></span>
			</div>
		
		<?php 
		$count++;
		} ?>
		<div id="fep_oa_add_more_here"></div>
		<div><input type="button" class="button fep_oa_add" value="<?php esc_attr_e( 'Add More', 'front-end-pm' ); ?>" /></div>
		
		<?php 
		wp_localize_script( 'fep-oa-script', 'fep_oa_script', 
					array( 
						'count' => $count,
						'remove' => esc_js(__('Remove', 'front-end-pm')),
						'name' => esc_js(__('Display as', 'front-end-pm')),
						'username' => esc_js(__('Username', 'front-end-pm'))	
					) 
				);
		wp_enqueue_script( 'fep-oa-script');
		
		}

	function settings_field_sanitize_filter( $value, $field )
		{
			if( !$value || !is_array($value ) ) {
				return array();
			}
			
			foreach( $value as $k => $v ) {
					if( empty($v['username']) || ! username_exists( $v['username'] ) ){
						add_settings_error( 'fep-settings', $field['id'], sprintf(__( 'Invalid username %s', 'front-end-pm' ), $v['username'] ));
						return $field['value'];
					}
					if( empty( $v['name'] ) ){
						add_settings_error( 'fep-settings', $field['id'], __( 'Name can not be empty.', 'front-end-pm' ));
						return $field['value'];
					}
			}
			return fep_array_trim( $value );
		}
	
	function fep_form_fields( $fields )
		{
				
			unset($fields['message_to']);
			$can_send_to_user = fep_get_option('mr-can-send-to-users', 1 ) || ( fep_get_option('mr-can-admin-send-to-users', 1 ) && fep_is_user_admin() );
			$admins = fep_get_option('oa_admins', array());
			
				$fields['fep_pro_to'] = array(
						'label'     => ( !$can_send_to_user && count($admins) <= 1 ) ? '' : __( 'To', 'front-end-pm' ),
						//'required'  => true,
						'priority'  => 5,
						'type'	=> 'fep_pro_to'
						);

			return $fields;
			
		}

	function form_field_output( $field, $errors )
		{
			
			$to = (isset($_REQUEST['to']))? $_REQUEST['to']:'';
			
			$can_send_to_user = fep_get_option('mr-can-send-to-users', 1 ) || ( fep_get_option('mr-can-admin-send-to-users', 1 ) && fep_is_user_admin() );
			$can_send_to_admin = fep_get_option('oa-can-send-to-admin', 1 );
			$admins = fep_get_option('oa_admins', array());
			
			if ( $errors->get_error_message( 'pro_to' ) ) : ?>
			<div class="fep-error">
			<?php echo $errors->get_error_message( 'pro_to' ); ?>
			<?php $errors->remove('pro_to'); ?>
			</div>
			<?php endif;
		
		
		if( !$can_send_to_user && count($admins) <= 1 )
			return; 
								
			if( $can_send_to_user ) {
			
					$support = array(
						'nicename' 	=> true,
						'id' 		=> true,
						'email' 	=> true,
						'login' 	=> true
						);
					
					$support = apply_filters( 'fep_message_to_support', $support );
						
					if ( !empty( $support['nicename'] ) && $id = fep_get_userdata( $to ) ) {
					} elseif( is_numeric( $to ) && !empty( $support['id'] ) && $id = fep_get_userdata( $to, 'ID', 'id' ) ) {
					} elseif ( is_email( $to ) && !empty( $support['email'] ) && $id = fep_get_userdata( $to, 'ID', 'email' ) ) {
					} elseif ( !empty( $support['login'] ) && $id = fep_get_userdata( $to, 'ID', 'login' ) ) {
					} else {
						$id = '';
					}
					
					$mr_to = ( isset( $_POST['fep_mr_to'] ) ) ? esc_attr( $_POST['fep_mr_to'] ): $id;


					$mr_to = explode( ',', $mr_to );
					$ret = array();
				foreach( $mr_to as $v) {
					$display_name = fep_get_userdata( $v, 'display_name', 'id' );
					
					if( $display_name ) {
						$ret[] = array(
							'id' => $v,
							'name' => $display_name
							);
						}
				}

				wp_enqueue_script( 'fep-mr-script');
								
								
			?><div id="fep_mr_to_div"><input id="fep_mr_to" type="text" name="fep_mr_to" value=""/></div>
<script type="text/javascript">
	jQuery(document).ready(function(){	
		jQuery("#fep_mr_to").tokenInput( "<?php echo admin_url( 'admin-ajax.php' ); ?>?action=fep_mr_ajax&token=<?php echo wp_create_nonce('fep-mr-script'); ?>", {
						method: "POST",
						theme: "facebook",
						excludeCurrent: true,
						tokenLimit: <?php echo absint(fep_get_option('mr-max-recipients', 5 )); ?>,
						hintText: "<?php _e("Type user name", 'front-end-pm'); ?>",
						noResultsText: "<?php _e("No matches found", 'front-end-pm'); ?>",
						searchingText: "<?php _e("Searching...", 'front-end-pm'); ?>",
						prePopulate: <?php echo wp_json_encode($ret); ?>,
						width: '250px',
						preventDuplicates: true,
						resultsLimit: 5
		});
	
	});
</script><?php
}

if( $can_send_to_user && $can_send_to_admin ){

wp_enqueue_script( 'jquery');

?><div class="fep_send_to_admin_div"><label><input type="checkbox" id="fep_send_to_admin" name="fep_send_to_admin" value="1" <?php checked( isset($_POST['fep_send_to_admin']) ? $_POST['fep_send_to_admin']: 0, '1'); ?> /> <?php _e( 'Send Message to admin', 'front-end-pm'); ?></label></div>
<script type="text/javascript">
jQuery(document).ready(function(){

	if(jQuery("#fep_send_to_admin").prop("checked")) {
		jQuery("#fep_mr_to_div").hide('slow');
		jQuery("#fep_oa_admins_div").show('slow');
	} else {
		jQuery("#fep_mr_to_div").show('slow');
		jQuery("#fep_oa_admins_div").hide('slow');
	}
		
	jQuery("#fep_send_to_admin").change(function(){
	
		if(jQuery(this).prop("checked")) {
			jQuery("#fep_mr_to_div").hide('slow');
			jQuery("#fep_oa_admins_div").show('slow');
		} else {
			jQuery("#fep_mr_to_div").show('slow');
			jQuery("#fep_oa_admins_div").hide('slow');
		}
	});
});
</script>
<?php }

if( $can_send_to_admin ){
	$fep_oa_to = isset($_POST['fep_oa_to']) ? $_POST['fep_oa_to'] : '';
	
	if( count($admins) > 1 ) { ?>
	<div id="fep_oa_admins_div"><?php
		if( 'radio' == fep_get_option('oa_admins_frontend', 'select' ) ) {
			foreach( $admins as $k => $v ) {
				?><label><input type="radio" name="fep_oa_to" value="<?php esc_attr_e( $k ); ?>" <?php checked( $fep_oa_to, $k ); ?> /> <?php esc_attr_e( $v['name'] ); ?></label>															<?php		}
		} else {
			?><select name="fep_oa_to"><?php
				foreach( $admins as $k => $v ) {
					?><option value="<?php esc_attr_e( $k ); ?>" <?php selected( $fep_oa_to, $k ); ?>><?php esc_attr_e( $v['name'] ); ?></option><?php
				}
			?></select><?php
		} ?>
	</div><?php
	}
}		
}
	
	function form_field_validate_admin( $field, $errors ){
		
		$_POST['message_to_id'] = array();
		
		$can_send_to_user = fep_get_option('mr-can-send-to-users', 1 ) || ( fep_get_option('mr-can-admin-send-to-users', 1 ) && fep_is_user_admin() );
			
		if( $can_send_to_user && empty( $_POST['fep_send_to_admin'] ) ) 
			return;
		
		if( ! fep_get_option('oa-can-send-to-admin', 1 ) ) {
			$errors->add( 'pro_to' , __('You do not have permission send message to admin.', 'front-end-pm'));
			return;
		}
		
		$admins = fep_get_option('oa_admins', array());
			
		if( ! $admins ) {
			$errors->add( 'pro_to' , __('Please add admins in back-end settings page of Front End PM (Pro tab)', 'front-end-pm'));
		} elseif( count($admins) == 1 ) {
			$admin = array_pop($admins);
			$id = fep_get_userdata( ! empty($admin['username']) ? $admin['username']: '', 'ID', 'login' );
			if( ! $id ) {
				$errors->add( 'pro_to' , __('Please add valid admins in back-end settings page of Front End PM (Pro tab)', 'front-end-pm'));
			} else {
				$_POST['message_to_id'] = $id;
			}
		} else {
			$to = isset($_POST['fep_oa_to']) ? $_POST['fep_oa_to'] : '';
			if( ! $to ) {
				$errors->add( 'pro_to' , __('You must select admin.', 'front-end-pm'));
				return;
			}
			
			$username = !empty($admins[$to]['username']) ? $admins[$to]['username'] : '';
			$id = fep_get_userdata( $username, 'ID', 'login' );
				
			if( ! $id ) {
				$errors->add( 'pro_to' , __('You must select admin.', 'front-end-pm'));
			} else {
				$_POST['message_to_id'] = $id;
			}
		}
	}

	function form_field_validate_users( $field, $errors ){
			
		if( ! empty( $_POST['fep_send_to_admin'] ) ) 
			return;
		
		$can_send_to_user = fep_get_option('mr-can-send-to-users', 1 ) || ( fep_get_option('mr-can-admin-send-to-users', 1 ) && fep_is_user_admin() );
		
		if( ! $can_send_to_user ) {
			return;
		}
		
		
		$preTo = !empty( $_POST['fep_mr_to'] ) ? explode( ',', $_POST['fep_mr_to'] ): array();
		
		if( ! $preTo || ! is_array( $preTo ) )
			return;
					  
		foreach ( $preTo as $pre ) {
			$to = fep_get_userdata( $pre, 'ID', 'id' );
			
			if( ! $to ) {
				$errors->add( 'pro_to' , sprintf(__('Invalid receiver "%s".', "front-end-pm"), $pre ) );
				continue;
			}
			if( get_current_user_id() == $to) {
				$errors->add( 'pro_to' , __('You can not message yourself.', "front-end-pm") );
				continue;
			}
			if ( fep_get_user_option( 'allow_messages', 1, $to ) != '1') {
				$errors->add( 'pro_to' , sprintf(__("%s does not want to receive messages!", 'front-end-pm'), fep_get_userdata( $to, 'display_name', 'id')));
				continue;
			}
			
			$_POST['message_to_id'][] = $to;
		}

	}
	
	function fep_action_validate_form( $where, $errors, $fields )
		{
			if( 'newmessage' != $where )
				return;
			
			if ( empty($_POST['message_to_id'])) {
				$errors->add( 'pro_to' , __('You must enter valid recipient!', 'front-end-pm'));
				return;
			}
			
			if( ! empty( $_POST['fep_send_to_admin'] ) ) 
				return;
			
			$count = count($_POST['message_to_id']);
			$max_repipients = absint( fep_get_option('mr-max-recipients', 5 ) );
			
			if( $count > $max_repipients ) {
				$errors->add( 'pro_to' , sprintf(__('Maximum %s allowed', 'front-end-pm' ),sprintf(_n('%s recipient', '%s recipients', $max_repipients, 'front-end-pm'), number_format_i18n($max_repipients) )));
			}
			if( 'separate-message' == fep_get_option('mr-message', 'same-message' ) ) {
				if( ! $max = fep_get_current_user_max_message_number() )
					return;
					
				if( fep_get_user_message_count( 'total' ) > ( $max - $count ) ) {
					$errors->add('MgsBoxFull', __( "Your message box is full. Please delete some messages.", 'front-end-pm' ));
				}
			}
		}
	
	function fep_posted_action(){
		
		if ( ! fep_current_user_can( 'send_new_message') )
			return;
					
			Fep_Form::init()->validate_form_field();
			if( count( fep_errors()->get_error_messages()) == 0 ){
				$message = $_POST;
				$to_ids = $message['message_to_id'];
				
				foreach( $to_ids as $to_id ){
					$message['message_to_id'] = $to_id;
					fep_send_message( $message );
				}
					
				if( 'publish' == fep_get_option('parent_post_status','publish') ) {
					fep_success()->add( 'publish', __("Message successfully sent.", 'front-end-pm') );
				} else {
					fep_success()->add( 'pending', __("Message successfully sent and waiting for admin moderation.", 'front-end-pm') );
				}
			}
		}
  } //END CLASS

add_action('init', array(Fep_Pro_To::init(), 'actions_filters'));

