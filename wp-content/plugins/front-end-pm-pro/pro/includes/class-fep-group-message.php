<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Group_Message
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
			add_action('fep_admin_settings_field_output_gm_groups', array($this, 'field_output' ) );
			add_filter('fep_settings_field_sanitize_filter_gm_groups', array($this, 'settings_field_sanitize_filter' ), 10, 2 );
			add_action('fep_action_after_admin_options_save', array($this, 'recalculate_user_message_count') );
			
			if( 'group' == Fep_Pro_To::init()->message_sending_to() ){
				add_action( 'fep_form_field_validate_fep_pro_to', array($this, 'form_field_validate_group' ), 15, 2 );
				remove_action( 'fep_action_message_after_send', 'fep_add_message_participants', 5, 3 );
				add_action( 'fep_action_message_after_send', array($this, 'add_message_group' ), 5, 3 );
			}
			add_filter( 'fep_filter_send_email_participants', array($this, 'send_email_participants' ), 10, 2 );
			
			add_filter( 'fep_message_count_query_args', array($this, 'query_args' ), 10, 2 );
			add_filter( 'fep_message_query_args', array($this, 'query_args' ), 10, 2 );
			add_filter( 'fep_current_user_can', array($this, 'fep_current_user_can' ), 10, 3 );
			add_filter( 'fep_filter_display_participants', array($this, 'display_participants' ), 10, 3 );
			
			if( fep_get_option('can-add-to-group', 0 ) ){
				add_action( 'show_user_profile', array($this, 'user_profile_fields' ) );
				add_action( 'edit_user_profile', array($this, 'user_profile_fields' ) );
				add_action( 'personal_options_update', array($this, 'save_user_profile_fields' ) );
				add_action( 'edit_user_profile_update', array($this, 'save_user_profile_fields' ) );
				
				add_filter( 'fep_form_fields', array($this, 'user_settings_fields' ) );
				add_action ('fep_action_form_validated', array( $this, 'user_settings_save' ), 10, 2);
				add_filter( 'fep_filter_user_settings_before_save', array($this, 'user_settings_save_remove_groups' ) );
			}
			//add_action( 'fep_admin_settings_field_output_rtr_block', array($this, 'field_output' ) );
			//add_filter( 'fep_settings_field_sanitize_filter_rtr_block', array($this, 'settings_field_sanitize_filter' ), 10, 2 );
			
    	}
		
	function admin_settings_tabs( $tabs ) {
				
		$tabs['gm_groups'] =  array(
				'section_title'			=> __('Groups', 'front-end-pm'),
				'section_page'		=> 'fep_settings_recipient',
				'priority'			=> 20,
				'tab_output'		=> false
				);
				
		return $tabs;
	}
	
	function settings_fields( $fields )
		{
			$fields['can-send-to-group'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'gm_groups',
				'value' => fep_get_option('can-send-to-group', 0 ),
				'cb_label' => __( 'Can users send message to group.', 'front-end-pm' ),
				'label' => __( 'Can send to group', 'front-end-pm' )
				);
			$fields['can-add-to-group'] =   array(
				'type'	=>	'checkbox',
				'class'	=> '',
				'section'	=> 'gm_groups',
				'value' => fep_get_option('can-add-to-group', 0 ),
				'cb_label' => __( 'Can users add themself to group.', 'front-end-pm' ),
				'label' => __( 'Can add to group', 'front-end-pm' )
				);
			$fields['gm_groups'] =   array(
				'type'	=>	'gm_groups',
				'section'	=> 'gm_groups',
				'value' => fep_get_option('gm_groups', array()),
				'description' => __( 'Do not forget to save.', 'front-end-pm' ),
				'label' => __( 'Groups', 'front-end-pm' )
				);
			$fields['gm_frontend'] =   array(
				'type'	=>	'select',
				'section'	=> 'gm_groups',
				'value' => fep_get_option('gm_frontend', 'dropdown' ),
				'description' => __( 'Select how you want to see in frontend.', 'front-end-pm' ),
				'label' => __( 'Show in front end as', 'front-end-pm' ),
				'options'	=> array(
					'dropdown'	=> __( 'Dropdown', 'front-end-pm' ),
					'radio'	=> __( 'Radio Button', 'front-end-pm' )
					)
				);
								
			return $fields;
			
		}
	function field_output( $field ){
		
		wp_enqueue_script( 'fep-tokeninput-script');
		wp_enqueue_style( 'fep-tokeninput-style');
		
		$pre_populate_users = array();
		
		if( $field['value' ] && is_array($field['value' ]) ) {
		foreach( $field['value' ] as $k => $v ) { ?>
			<div style="border: 2px solid #c7c7c7;margin-bottom: 10px;padding: 10px;">
				<div><label><?php _e('Group Name', 'front-end-pm'); ?></label><br />
					<input type="text" class="regular-text" required name="gm_groups[<?php esc_attr_e( $k ); ?>][name]" value="<?php esc_attr_e( $v['name'] ); ?>"/></div>
				<div><label><?php _e('Group Slug', 'front-end-pm'); ?></label><br />
					<input type="text" class="regular-text" required name="gm_groups[<?php esc_attr_e( $k ); ?>][slug]" value="<?php esc_attr_e( $v['slug'] ); ?>"/></div>
				<span class="description"><?php _e('Changing slug may have unexpected result.', 'front-end-pm'); ?></span>
				<div><label><?php _e('Group Members', 'front-end-pm'); ?></label><br />
					<textarea class="fep_group_textarea" data-gm_group="<?php esc_attr_e( $k ); ?>" name="gm_groups[<?php echo $k; ?>][members]"><?php esc_attr_e( $v['members'] ); ?></textarea></div>
					
				<div><input type="button" class="button button-small fep_group_remove" value="<?php esc_attr_e( 'Remove this group', 'front-end-pm' ); ?>" /></div>
			</div>
		<?php
		
		if( !empty( $v['members'] ) ){
			$members = explode(',', $v['members'] );
			$pre_populate = array();
			foreach( $members as $member_id ) {
				$name = fep_user_name( $member_id );
				
				if( $name ) {
					$pre_populate[] = array(
						'id' => $member_id,
						'name' => $name,
						//'readonly'	=> true
					);
				}
			}
			$pre_populate_users[ $k ] = $pre_populate;
		}
		 } } ?>
		<div id="fep_group_add_more_here"></div>
		<div><input type="button" class="button fep_group_add" value="<?php esc_attr_e( 'Add More', 'front-end-pm' ); ?>" /></div>
		
		<script type="text/javascript">
		jQuery(document).ready(function(){
			var pre_populate_users = <?php echo wp_json_encode( $pre_populate_users ); ?>;
			
			jQuery(".fep_group_textarea").each( function() {
				fep_instance_tokeninput( this, pre_populate_users[ jQuery(this).data("gm_group") ] );
		    });
			jQuery(document).on('click', '.fep_group_remove', function(){
				jQuery(this).parent().parent().remove();
			});
			count = 1;
			
			jQuery('.fep_group_add').on('click',function(){
				
				jQuery('#fep_group_add_more_here').append('<div style="border: 2px solid #c7c7c7;margin: 10px;padding: 10px;"><div><label><?php _e('Group Name', 'front-end-pm'); ?></label><br /><input type="text" class="regular-text" required name="gm_groups['+count+'][name]" value=""/></div><div><label><?php _e('Group Slug', 'front-end-pm'); ?></label><br /><input type="text" class="regular-text" required name="gm_groups['+count+'][slug]" value=""/></div><div><label><?php _e('Group Members', 'front-end-pm'); ?></label><br /><textarea id="fep_group_'+count+'" name="gm_groups['+count+'][members]"></textarea></div><div><input type="button" class="button button-small fep_group_remove" value="<?php esc_attr_e( 'Remove this group', 'front-end-pm' ); ?>" /></div></div>' );
				
				fep_instance_tokeninput( '#fep_group_'+count );
				count++;
        		return false;			
			});
			
			function fep_instance_tokeninput( id, pre = null ){
				jQuery(id).tokenInput( "<?php echo admin_url( 'admin-ajax.php' ); ?>?action=fep_group_members&token=<?php echo wp_create_nonce('fep_group_members'); ?>", {
					method: "POST",
					theme: "facebook",
					excludeCurrent: true,
					hintText: "<?php _e("Type user name", 'front-end-pm'); ?>",
					noResultsText: "<?php _e("No matches found", 'front-end-pm'); ?>",
					searchingText: "<?php _e("Searching...", 'front-end-pm'); ?>",
					prePopulate: pre,
					width: '250px',
					preventDuplicates: true,
					allowFreeTagging: true,
					zindex: 99999,
					resultsLimit: 5
				});
			}
		});
		</script>
		
		<?php
		
		}

	function settings_field_sanitize_filter( $value, $field )
		{
			if( !$value || !is_array($value ) ) {
				return array();
			}
			
			foreach( $value as $k => $v ) {
					if( empty($v['name']) || empty( $v['slug'] ) ){
						add_settings_error( 'fep-settings', $field['id'], __( 'Group name or Slug cannot be empty.', 'front-end-pm' ) );
						return $field['value'];
					}
					$v['name'] = sanitize_text_field( $v['name'] );
					$v['slug'] = sanitize_title_with_dashes( $v['slug'] );
					
					$members = explode( ',', $v['members'] );
					$roles = array();
					foreach ($members as $x => $member ) {
						if( is_numeric( $member ) ){
							//This is User ID
						} elseif( false !== strpos( $member, '{role}-') ){
							$roles[] = str_replace( '{role}-', '', $member );
							unset( $members[ $x ] );
						} elseif( fep_get_userdata( $member ) ){
							//nicename
							$members[ $x ] = fep_get_userdata( $member );
						} else {
							unset( $members[ $x ] );
						}
					}
					if( $roles ){
						$users = get_users( array( 'fields' => 'ids', 'role__in' => $roles ) );
						$members = array_merge( $members, $users);
					}
					$v['members'] = implode( ',', array_filter( array_unique( $members ) ) );
					
					unset( $value[ $k ] );
					$value[ $v['slug'] ] = $v;
			}
			return fep_array_trim( $value );
		}

	function recalculate_user_message_count( $old_value ){
		global $wpdb;
		
		if( ! isset( $old_value['gm_groups'] ) )
		$old_value['gm_groups'] = '';
		
		if( fep_get_option('gm_groups') != $old_value['gm_groups']) {
			delete_metadata( 'user', 0, $wpdb->get_blog_prefix() . '_fep_user_message_count', '', true );
		}
	}
	
	function form_field_validate_group( $field, $errors ){
		
		if( ! fep_get_option('can-send-to-group', 0 ) ) {
			$errors->add( 'pro_to' , __('You do not have permission send message to group.', 'front-end-pm'));
			return false;
		}
		$_POST['message_to_group'] = '';
		
		$groups = $this->get_user_groups();
			
		if( ! $groups ) {
			$errors->add( 'pro_to' , __('You are not member to any group', 'front-end-pm'));
		} elseif( count($groups) === 1 ) {
			
			$slug = key( $groups );
			if( ! $slug ) {
				$errors->add( 'pro_to' , __('Please add valid groups in back-end settings page of Front End PM PRO', 'front-end-pm'));
			} else {
				$_POST['message_to_group'] = $slug;
			}
		} else {
			$to = isset($_POST['fep_gm_to']) ? $_POST['fep_gm_to'] : '';
			if( ! $to || empty($groups[ $to ]) ) {
				$errors->add( 'pro_to' , __('You must select group.', 'front-end-pm'));
			} else {
				$_POST['message_to_group'] = $to;
			}
		}
	}
	
	function add_message_group( $message_id, $message, $inserted_message ){
		$members = array();
		
		if( ! empty( $message['message_to_group'] ) && ! $inserted_message->post_parent ){
			add_post_meta( $message_id, '_fep_group', $message['message_to_group'], true );
			$members = $this->get_group_members( $message['message_to_group'] );
		}
		if( $inserted_message->post_parent && 'threaded' != fep_get_message_view() ) {
			$group = get_post_meta( $inserted_message->post_parent, '_fep_group', true );
			if( $group ){
				add_post_meta( $message_id, '_fep_group', $group, true );
				$members = $this->get_group_members( $group );
			}
		}
		
		if( $members && is_array( $members ) ){
			foreach ($members as $member ) {
				delete_user_option( $member, '_fep_user_message_count' );
			}
		}
		fep_make_read( true, $message_id, $inserted_message->post_author );
	}
	
	function send_email_participants( $participants, $postid ){
		
		if( ! $participants && $group = get_post_meta( $postid, '_fep_group', true ) ){
			$participants = $this->get_group_members( $group );
		}
		return $participants;
	}
	
	function get_user_groups( $user_id = 0 ){
		if( ! $user_id )
		$user_id = get_current_user_id();
		
		$user_groups = array();
		$groups = fep_get_option('gm_groups', array());
		if( $groups && is_array( $groups) ){
			foreach ( $groups as $group ) {
				$members = explode(',', $group['members'] );
				if( in_array( $user_id, $members ) ){
					$user_groups[ $group['slug'] ] = $group['name'];
				}
			}
		}
		return $user_groups;
	}
	function get_group_members( $group ){
		
		$members = '';
		$groups = fep_get_option('gm_groups', array());
		if( $groups && is_array( $groups) ){
			if( isset( $groups[ $group ] ) && isset( $groups[ $group ]['members'] ) ){
				$members = $groups[ $group ]['members'];
			}
		}
		return array_filter( explode( ',', $members ) );
	}
	
	function query_args( $args, $user_id ){
		if( empty( $args['meta_query'] ) || ! is_array( $args['meta_query'] ) )
		return $args;
		$groups = $this->get_user_groups( $user_id );
		
		if( ! $groups || ! is_array( $groups ) )
		return $args;
		
		foreach( $args['meta_query'] as $k => $v ){
			if( isset( $v['key'] ) && '_fep_participants' == $v['key'] ){
				//unset( $args['meta_query'][ $k ] );
				
				$args['meta_query'][ $k ] = array(
					'relation' => 'OR',
					array(
						'key'     => '_fep_participants',
						'value'   => $user_id,
						'compare' => '='
					),
					array(
						'key'     => '_fep_group',
						'value'   => array_keys( $groups ),
						'compare' => 'IN'
					)
				);
				break;
			}
		}
		return $args;
	}
	
	function fep_current_user_can( $can, $cap, $id ){
		
		if( ! is_user_logged_in() || $can || ! in_array( $cap, array( 'send_reply', 'view_message', 'delete_message' ) ) )
		return $can;
		
		$group = get_post_meta( $id, '_fep_group', true );
		if( $group ){
			$members = $this->get_group_members( $group );
			if( in_array( get_current_user_id(), $members ) )
			return true;
		}
		return $can;
	}
	
	function display_participants( $out, $par, $participants ){
		if( ! $participants && $group = get_post_meta( get_the_ID(), '_fep_group', true ) ){
			$groups = fep_get_option('gm_groups', array());
			if( $groups && is_array( $groups ) ){
				if( isset( $groups[ $group ] ) && isset( $groups[ $group ]['name'] ) ){
					$out = __('Group', 'front-end-pm'). ': ' . esc_html($groups[ $group ]['name']);
				}
			}
		}
		return $out;
	}
	
	function user_profile_fields( $user ){
		$groups = fep_get_option('gm_groups', array());
		
		if( ! $groups || ! is_array( $groups) )
		return false;
		?>
		<h3><?php _e("FEP Group", "front-end-pm"); ?></h3>

    <table class="form-table">
		<tbody>
		    <tr>
		        <th><label for="fep_groups"><?php _e( 'Groups', 'front-end-pm' ); ?></label></th>
		        <td>
					<?php 
						$user_groups = $this->get_user_groups( $user->ID );
						foreach ( $groups as $group ) { ?>
							<label><input type="checkbox" name="fep_groups[]" value="<?php esc_attr_e($group['slug']) ?>" <?php if( !empty( $user_groups [ $group['slug'] ] ) ) echo 'checked="checked"'; ?> /> <?php esc_attr_e($group['name']) ?></label><br />
						<?php } ?>
						<span class="description"><?php _e('Please select groups which you want to join.', 'front-end-pm'); ?></span>
		        </td>
		    </tr>
		</tbody>
	</table>
	<?php }
	
	function save_user_profile_fields( $user_id ) {
	    if ( !current_user_can( 'edit_user', $user_id ) ) { 
	        return false;
	    }
		$groups = fep_get_option('gm_groups', array());
		
		if( ! $groups || ! is_array( $groups) )
		return false;
		
		$selected_groups = isset( $_POST["fep_groups"] ) ? $_POST["fep_groups"] : array();
		if( ! is_array( $selected_groups ) )
		$selected_groups = array();
		
		foreach ( $groups as &$group ) {
			$members = explode(',', $group['members'] );

			if( in_array( $group['slug'], $selected_groups ) ){
				$members[] = $user_id;
			} else {
				if (($key = array_search($user_id, $members)) !== false) {
				    unset($members[$key]);
				}
			}
			$members = array_filter( array_unique( $members ) );
			
			$group['members'] = implode(',', $members );
		}
		unset( $group );
		fep_update_option('gm_groups', $groups);
		delete_user_option( $user_id, '_fep_user_message_count' );
	}
	
	function user_settings_fields( $fields ){
		$groups = fep_get_option('gm_groups', array());
		if( ! $groups || ! is_array( $groups ) )
		return $fields;
		
		$group_array = array();
		foreach ( $groups as $group ) {
			$group_array[ $group['slug'] ] = $group['name'];
		}
		
		$fields['fep_groups'] = array(
			'label'       => __( 'Groups', 'front-end-pm' ),
			'type'        =>  'checkbox',
			'multiple'	=> true,
			'value'     => array_keys( $this->get_user_groups() ),
			'priority'    => 45,
			'where'    => 'settings',
			'description'	=> __('Please select groups which you want to join.', 'front-end-pm'),
			'options'	=> $group_array,
		);
		return $fields;
	}
	
	function user_settings_save( $where, $fields ){
		
		if( 'settings' != $where )
			return;
		
		if( !$fields || !is_array($fields) || !isset($fields['fep_groups']) || !is_array($fields['fep_groups']) )
			return;
		
		$selected_groups = is_array( $fields['fep_groups']['posted-value'] ) ? $fields['fep_groups']['posted-value'] : array();
		
		$groups = fep_get_option('gm_groups', array());
		
		if( ! $groups || ! is_array( $groups) )
		return false;
		
		foreach ( $groups as &$group ) {
			$members = explode(',', $group['members'] );

			if( in_array( $group['slug'], $selected_groups ) ){
				$members[] = get_current_user_id();
			} else {
				if (($key = array_search(get_current_user_id(), $members)) !== false) {
				    unset($members[$key]);
				}
			}
			$members = array_filter( array_unique( $members ) );
			
			$group['members'] = implode(',', $members );
		}
		unset( $group );
		fep_update_option('gm_groups', $groups);
		delete_user_option( get_current_user_id(), '_fep_user_message_count' );	
		}
	
	function user_settings_save_remove_groups( $settings ){
		
		unset( $settings['fep_groups'] );
		return $settings;
	}

  } //END CLASS

add_action('init', array(Fep_Group_Message::init(), 'actions_filters'));

