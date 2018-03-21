<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Pro_To
  {
	private static $instance;
	private $first_message_id = 0;
	private $first_message_attachments;
	
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
			add_action( 'fep_form_field_validate_fep_pro_to', array($this, 'set_post_message_to_id' ), 5, 2 );
			add_action( 'fep_form_field_validate_fep_pro_to', array($this, 'form_field_validate_admin' ), 10, 2 );
			add_action( 'fep_form_field_validate_fep_pro_to', array($this, 'form_field_validate_users' ), 12, 2 );
			add_action( 'fep_action_validate_form', array($this, 'fep_action_validate_form' ), 10, 3 );
			add_filter( 'fep_directory_table_bulk_actions', array($this, 'fep_directory_table_bulk_actions' ) );
			add_action( 'fep_directory_posted_bulk_action_send_message_bulk', array($this, 'send_message_bulk' ) );
			
			if( 'separate-message' == fep_get_option('mr-message', 'same-message' ) && 'user' == $this->message_sending_to() ) {
				add_action( 'fep_posted_action_newmessage', array($this, 'fep_posted_action' ) );
				if ( '1' == fep_get_option('allow_attachment', 1)) {
					add_action ('fep_action_message_after_send', array($this, 'upload_attachment'), 10, 3 );
				}
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
				'value' => fep_get_option('oa-can-send-to-admin', 0 ),
				'cb_label' => __( 'Can users send message to admin.', 'front-end-pm' ),
				'label' => __( 'Can send to admin', 'front-end-pm' )
				);
			$fields['oa_admins'] =   array(
				'type'	=>	'oa_admins',
				'section'	=> 'oa_admins',
				'value' => fep_get_option('oa_admins', array()),
				'description' => __( 'Do not forget to save.', 'front-end-pm' ),
				'label' => __( 'Admins', 'front-end-pm' )
				);
			$fields['oa_admins_frontend'] =   array(
				'type'	=>	'select',
				'section'	=> 'oa_admins',
				'value' => fep_get_option('oa_admins_frontend', 'dropdown' ),
				'description' => __( 'Select how you want to see in frontend.', 'front-end-pm' ),
				'label' => __( 'Show in front end as', 'front-end-pm' ),
				'options'	=> array(
					'dropdown'	=> __( 'Dropdown', 'front-end-pm' ),
					'radio'	=> __( 'Radio Button', 'front-end-pm' )
					)
				);
			unset($fields['show_autosuggest']);
								
			return $fields;
			
		}
	function field_output( $field ){
		
		$count = 0;
		
		if( $field['value' ] && is_array($field['value' ]) ) {
		foreach( $field['value' ] as $k => $v ) { ?>
			<div>
				<span><input type="text" required name="oa_admins[oa_<?php echo $count; ?>][name]" value="<?php esc_attr_e( $v['name'] ); ?>"/></span>
				<span><input type="text" required name="oa_admins[oa_<?php echo $count; ?>][username]" value="<?php esc_attr_e( $v['username'] ); ?>"/></span>
				<span><input type="button" class="button button-small fep_oa_remove" value="<?php esc_attr_e( 'Remove', 'front-end-pm' ); ?>" /></span>
			</div>
		<?php
		$count++;
		 } } ?>
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
			
			$show_label = false;
			if( fep_current_user_can( 'mr_newmessage_to_users' ) ){
				$show_label = true;
			} elseif( fep_get_option('oa-can-send-to-admin', 0 ) && count(fep_get_option('oa_admins', array())) > 1 ){
				$show_label = true;
			} elseif( fep_get_option('can-send-to-group', 0 ) && count(fep_get_option('gm_groups', array())) > 1 ){
				$show_label = true;
			} elseif( fep_get_option('oa-can-send-to-admin', 0 ) && fep_get_option('can-send-to-group', 0 ) ){
				$show_label = true;
			}
			
				$fields['fep_pro_to'] = array(
						'label'     => ( $show_label ) ? __( 'To', 'front-end-pm' ) : '',
						//'required'  => true,
						'priority'  => 5,
						'type'	=> 'fep_pro_to'
						);

			return $fields;
			
		}

function form_field_output( $field, $errors )
	{

		if( isset( $_REQUEST['fep_to'] ) ) {
			$to = $_REQUEST['fep_to'];
		} else {
			$to = (isset($_REQUEST['to']))? $_REQUEST['to']:'';
		}
		
		if ( $errors->get_error_message( 'pro_to' ) ) : ?>
		<div class="fep-error">
		<?php echo $errors->get_error_message( 'pro_to' ); ?>
		<?php $errors->remove('pro_to'); ?>
		</div>
		<?php endif;
		
		$can_send_to_user = fep_current_user_can( 'mr_newmessage_to_users');
		$can_send_to_admin = fep_get_option('oa-can-send-to-admin', 0 );
		$can_send_to_group = fep_get_option('can-send-to-group', 0 );
		
		$count = 0;
		if( $can_send_to_user )
		$count++;
		if( $can_send_to_admin )
		$count++;
		if( $can_send_to_group )
		$count++;
		
		if( ! $count )
		return '';
							
		if( $can_send_to_user ) {
			
			if( isset( $_REQUEST['fep_mr_to'] ) ){
				$mr_to = esc_attr( $_REQUEST['fep_mr_to'] );
			} else {
				$support = array(
					'nicename' 	=> true,
					'id' 		=> true,
					'email' 	=> true,
					'login' 	=> true
					);
				
				$support = apply_filters( 'fep_message_to_support', $support );
					
				if ( !empty( $support['nicename'] ) && $mr_to = fep_get_userdata( $to ) ) {
				} elseif( !empty( $support['id'] ) && is_numeric( $to ) && $mr_to = fep_get_userdata( $to, 'ID', 'id' ) ) {
				} elseif ( !empty( $support['email'] ) && is_email( $to ) && $mr_to = fep_get_userdata( $to, 'ID', 'email' ) ) {
				} elseif ( !empty( $support['login'] ) && $mr_to = fep_get_userdata( $to, 'ID', 'login' ) ) {
				} else {
					$mr_to = '';
				}
			}

			$mr_to = explode( ',', $mr_to );
			$pre_populate = array();
			foreach( $mr_to as $id ) {
				
				if( $name = fep_user_name( $id ) ) {
					$pre_populate[] = array(
						'id' => $id,
						'name' => $name,
						//'readonly'	=> true
						);
					}
			}
			$pre_populate = apply_filters( 'fep_pro_filter_pre_populate', $pre_populate);

			wp_enqueue_script( 'fep-tokeninput-script');
			wp_enqueue_style( 'fep-tokeninput-style');				
							
		?><div class="fep_pro_to_div" id="fep_mr_to_div"><input id="fep_mr_to" type="text" name="fep_mr_to" value=""/></div><?php
}

if( $can_send_to_admin && $count >= 2 ){

?><div class="fep_send_to_admin_div fep_pro_to_div"><label><input type="checkbox" class="fep_pro_to_checkbox" id="fep_send_to_admin" name="fep_send_to_check" value="admin" <?php checked( isset($_REQUEST['fep_send_to_check']) ? $_REQUEST['fep_send_to_check']: 0, 'admin'); ?> /> <?php _e( 'Send Message to admin', 'front-end-pm'); ?></label>
<?php
	$admins = fep_get_option('oa_admins', array());
	if( ! $admins ){
		echo '<div class="fep_pro_to_field_div"><div class="fep-error">'.__("No admins found", "front-end-pm").'</div></div>';
	} elseif( count( $admins ) > 1 ){
	$fep_oa_to = isset($_REQUEST['fep_oa_to']) ? $_REQUEST['fep_oa_to'] : '';
 ?>
	<div id="fep_oa_admins_div" class="fep_pro_to_field_div"><?php
		if( 'radio' == fep_get_option('oa_admins_frontend', 'dropdown' ) ) {
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
	echo '</div>';
}

if( $can_send_to_group && $count >= 2 ){

?><div class="fep_send_to_group_div fep_pro_to_div"><label><input type="checkbox" class="fep_pro_to_checkbox" id="fep_send_to_group" name="fep_send_to_check" value="group" <?php checked( isset($_REQUEST['fep_send_to_check']) ? $_REQUEST['fep_send_to_check']: 0, 'group'); ?> /> <?php _e( 'Send Message to group', 'front-end-pm'); ?></label>
<?php
	$groups = Fep_Group_Message::init()->get_user_groups();
	if( ! $groups ){
		echo '<div class="fep_pro_to_field_div"><div class="fep-error">'.__("You are not member to any group", "front-end-pm").'</div></div>';
	} elseif( count( $groups ) > 1 ){
	$fep_gm_to = isset($_REQUEST['fep_gm_to']) ? $_REQUEST['fep_gm_to'] : '';

?>
	<div id="fep_gm_div" class="fep_pro_to_field_div"><?php
		if( 'radio' == fep_get_option('gm_frontend', 'dropdown' ) ) {
			foreach( $groups as $k => $v ) {
				?><label><input type="radio" name="fep_gm_to" value="<?php esc_attr_e( $k ); ?>" <?php checked( $fep_gm_to, $k ); ?> /> <?php esc_attr_e( $v ); ?></label>															<?php		}
		} else {
			?><select name="fep_gm_to"><?php
				foreach( $groups as $k => $v ) {
					?><option value="<?php esc_attr_e( $k ); ?>" <?php selected( $fep_gm_to, $k ); ?>><?php esc_attr_e( $v ); ?></option><?php
				}
			?></select><?php
		} ?>
	</div><?php
	}
	echo '</div>';
} ?>
<script type="text/javascript">
	jQuery(document).ready(function(){
	//comment previous line and uncomment next line if you have any issue with multiple receipant field ( eg. for CloudFlare rocketscript tech )
	//jQuery(window).load(function(){
	<?php if( $can_send_to_user ) { ?>
		jQuery("#fep_mr_to").tokenInput( "<?php echo admin_url( 'admin-ajax.php' ); ?>?action=fep_mr_ajax&token=<?php echo wp_create_nonce('fep-mr-script'); ?>", {
			method: "POST",
			theme: "facebook",
			excludeCurrent: true,
			tokenLimit: <?php echo absint(fep_get_option('mr-max-recipients', 5 )); ?>,
			hintText: "<?php _e("Type user name", 'front-end-pm'); ?>",
			noResultsText: "<?php _e("No matches found", 'front-end-pm'); ?>",
			searchingText: "<?php _e("Searching...", 'front-end-pm'); ?>",
			prePopulate: <?php echo wp_json_encode($pre_populate); ?>,
			width: '250px',
			preventDuplicates: true,
			zindex: 99999,
			resultsLimit: 5
		});
		<?php } ?>
		<?php if( $count >= 2 ) { 
			wp_enqueue_script( 'jquery'); ?>

			jQuery(".fep_pro_to_checkbox").change(function(){
				if(jQuery(this).prop("checked")) {
					jQuery('.fep_pro_to_checkbox').not(jQuery(this)).prop('checked',false);
					jQuery('.fep_pro_to_div').not(jQuery(this).parent().parent()).hide('slow');
					jQuery(this).parent().next('.fep_pro_to_field_div').show('slow');
				} else {
					jQuery('.fep_pro_to_div').show('slow');
					jQuery('.fep_pro_to_field_div').hide('slow');
				}
			});
			jQuery(".fep_pro_to_checkbox").each(function(){
				if(jQuery(this).prop("checked")) {
					jQuery(this).trigger("change");
					return false;
				} else {
					jQuery(this).trigger("change");
				}
			});
		<?php } ?>

	});
</script>
 <?php }


	function set_post_message_to_id( $field, $errors ){
		$_POST['message_to_id'] = array();
	}
	
	function message_sending_to(){
		if( $_SERVER['REQUEST_METHOD'] !== 'POST' )
		return false;
		
		if( ! empty( $_POST['fep_send_to_check'] ) )
		return $_POST['fep_send_to_check'];
		
		$can_send_to_user = fep_current_user_can( 'mr_newmessage_to_users');
		$can_send_to_admin = fep_get_option('oa-can-send-to-admin', 0 );
		$can_send_to_group = fep_get_option('can-send-to-group', 0 );

		if( $can_send_to_user )
		return 'user';
		if( $can_send_to_admin )
		return 'admin';
		if( $can_send_to_group )
		return 'group';
		
		return false;
	}
	
	function form_field_validate_admin( $field, $errors ){
		
		if( 'admin' != $this->message_sending_to() ) 
			return false;
		
		if( ! fep_get_option('oa-can-send-to-admin', 0 ) ) {
			$errors->add( 'pro_to' , __('You do not have permission send message to admin.', 'front-end-pm'));
			return;
		}
		
		$admins = fep_get_option('oa_admins', array());
			
		if( ! $admins ) {
			$errors->add( 'pro_to' , __('Please add admins in back-end settings page of Front End PM', 'front-end-pm'));
		} elseif( count($admins) == 1 ) {
			$admin = array_pop($admins);
			$id = fep_get_userdata( ! empty($admin['username']) ? $admin['username']: '', 'ID', 'login' );
			if( ! $id ) {
				$errors->add( 'pro_to' , __('Please add valid admins in back-end settings page of Front End PM', 'front-end-pm'));
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
			
		if( 'user' != $this->message_sending_to() ) 
			return false;
		
		
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
			if ( ! fep_current_user_can('send_new_message_to', $to ) ) {
				$errors->add( 'pro_to' , sprintf(__("%s does not want to receive messages!", 'front-end-pm'), fep_user_name( $to )));
				continue;
			}
			
			$_POST['message_to_id'][] = $to;
		}

	}
	
	function fep_action_validate_form( $where, $errors, $fields )
		{
			if( 'newmessage' != $where )
				return;
				
			$send_to = $this->message_sending_to();
			
			if ( 'group' != $send_to && empty($_POST['message_to_id'])) {
				$errors->add( 'pro_to' , __('You must enter valid recipient!', 'front-end-pm'));
				return;
			}
			
			if( 'user' != $send_to ) 
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
				
				$count = 0;
				foreach( $to_ids as $to_id ){
					$message['message_to_id'] = $to_id;
					$message_id = fep_send_message( $message );
					
					if( ! $count++ && $message_id ){
						$this->first_message_id = $message_id;
					}
					
				}
					
				if( 'publish' == fep_get_option('parent_post_status','publish') ) {
					fep_success()->add( 'publish', __("Message successfully sent.", 'front-end-pm') );
				} else {
					fep_success()->add( 'pending', __("Message successfully sent and waiting for admin moderation.", 'front-end-pm') );
				}
			}
		}
	function upload_attachment( $message_id, $message, $inserted_message ) {
	    if ( ! $this->first_message_id || ! $message_id )
	        return false;
		
		if( fep_get_attachments( $message_id, 'ids' ) )
			return false;
		
		if( ! isset( $this->first_message_attachments ) ){
			$this->first_message_attachments = fep_get_attachments( $this->first_message_id );
		}
		
		if( ! $this->first_message_attachments || ! is_array( $this->first_message_attachments ) )
			return false;
		
		foreach ( $this->first_message_attachments as $attachment ){
			$attachment = (array)$attachment;
			$file = get_attached_file( $attachment['ID'] );
			unset( $attachment['ID'], $attachment['guid'] );
			wp_insert_attachment( $attachment, $file, $message_id );
		}
		return true;
	}
		
	function fep_directory_table_bulk_actions( $actions  ){
		$actions['send_message_bulk'] = __('Send Message', 'front-end-pm');
		return $actions;
	}
	
	function send_message_bulk( $ids ){
		$url = add_query_arg( 'fep_mr_to', implode(',', $ids), fep_query_url( 'newmessage' ) );
		wp_safe_redirect( $url );
		exit;
	}
  } //END CLASS

add_action('init', array(Fep_Pro_To::init(), 'actions_filters'));

