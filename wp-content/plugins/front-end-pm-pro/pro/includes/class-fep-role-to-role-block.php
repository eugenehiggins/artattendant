<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


class Fep_Role_To_Role_Block
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
			add_action( 'fep_admin_settings_field_output_rtr_block', array($this, 'field_output' ) );
			add_filter( 'fep_settings_field_sanitize_filter_rtr_block', array($this, 'settings_field_sanitize_filter' ), 10, 2 );
			add_action( 'fep_action_validate_form', array($this, 'fep_action_validate_form' ), 10, 3 );
			
			add_action( 'fep_pro_plugin_update', array($this, 'fep_pro_plugin_update' ) );
			
			add_filter( 'fep_autosuggestion_arguments', array($this, 'hide_users' ) );
			add_filter( 'fep_directory_arguments', array($this, 'hide_users' ) );
			
			add_filter( 'fep_current_user_can', array($this, 'hide_directory' ), 10, 3 );
			add_filter( 'fep_current_user_can_mr_newmessage_to_users', array($this, 'mr_newmessage_to_users' ), 10, 3 );
			
    	}

	function admin_settings_tabs( $tabs ) {
				
		$tabs['rtr_block'] =  array(
				'section_title'			=> __('Role to Role Block', 'front-end-pm'),
				'section_page'		=> 'fep_settings_security',
				'priority'			=> 35,
				'tab_output'		=> false
				);
				
		return $tabs;
	}
	
	function settings_fields( $fields)
		{

			$fields['rtr_block'] =   array(
				'type'	=>	'rtr_block',
				'section'	=> 'rtr_block',
				'value' => fep_get_option('rtr_block', array()),
				'description' => __( 'Do not forget to save.', 'front-end-pm' ),
				//'label' => __( 'Blocks', 'front-end-pm' ),
				);
								
			return $fields;
			
		}
	
	function field_output( $field ){
		
		$count = 0;
		$for_array = array(
			'newmessage'	=> __( 'New Message', 'front-end-pm' ),
			'shortcode-newmessage'	=> __( 'Shortcode New Message', 'front-end-pm' ),
			'reply'	=> __( 'Reply', 'front-end-pm' ),
			);
		
		$roles = array( 'fep_all' => __( 'All Roles', 'front-end-pm' ) );
		foreach ( get_editable_roles() as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}
		
			
		$for_in_option_tag = '<option value="">'.__( 'Select For', 'front-end-pm' ) .'</option>';
		foreach ( $for_array as $k => $v ) {
			$for_in_option_tag .= '<option value="' . $k . '">' . $v . '</option>';
		}
		
		$user_role_in_option_tag = '<option value="">'.__( 'Select Role', 'front-end-pm' ) .'</option>';
		foreach ( $roles as $key => $role ) {
			$user_role_in_option_tag .= '<option value="' . $key . '">' . $role . '</option>';
		}
		?><table>
				<th><?php _e( 'From Role', 'front-end-pm' );?></th>
				<th><?php _e( 'To Role', 'front-end-pm' );?></th>
				<th><?php _e( 'Block For', 'front-end-pm' );?></th>
				<th><?php _e( 'Remove', 'front-end-pm' );?></th>
			</table><?php
		
		if( $field['value' ] && is_array($field['value' ]) ) {
		foreach( $field['value' ] as $v ) { ?>
			<div>
				<span><select name="rtr_block[<?php echo $count; ?>][from_role]">
					<?php foreach ( $roles as $key => $role ) {
						echo '<option value="' . $key . '" ' . selected( $key, $v['from_role'], false ). '>' . $role . '</option>';
					} ?>
				</select></span>
				<span><select name="rtr_block[<?php echo $count; ?>][to_role]">
					<?php foreach ( $roles as $key => $role ) {
						echo '<option value="' . $key . '" ' . selected( $key, $v['to_role'], false ). '>' . $role . '</option>';
					} ?>
					</select></span>
				<span><select name="rtr_block[<?php echo $count; ?>][for]">
					<?php foreach ( $for_array as $x => $y ) {
						echo '<option value="' . $x . '" ' . selected( $x, $v['for'], false ). '>' . $y . '</option>';
					} ?>
					</select></span>
				<span><input type="button" class="button button-small fep_rtr_remove" value="<?php esc_attr_e( 'Remove' ); ?>" /></span>
			</div>
		<?php
		$count++;
		 } } ?>
		<div id="fep_rtr_add_more_here"></div>
		<div><input type="button" class="button fep_rtr_add" value="<?php esc_attr_e( 'Add More', 'front-end-pm' ); ?>" /></div>
		<script type="text/javascript">
		jQuery(document).ready(function(){
				jQuery(document).on('click', '.fep_rtr_remove', function(){
					jQuery(this).parent().parent().remove();
				});
				var count = <?php echo $count; ?>;
				
				jQuery('.fep_rtr_add').on('click',function(){
					
					jQuery('#fep_rtr_add_more_here').append('<div><span><select name="rtr_block['+count+'][from_role]"><?php echo $user_role_in_option_tag; ?></select></span><span><select name="rtr_block['+count+'][to_role]"><?php echo $user_role_in_option_tag; ?></select></span><span><select name="rtr_block['+count+'][for]"><?php echo $for_in_option_tag; ?></select></span><span><input type="button" class="button button-small fep_rtr_remove" value="<?php _e( 'Remove', 'front-end-pm' ); ?>" /></span></div>' );
					count++;
            		return false;			
				});      
			});
		</script>
		<?php
		
		}

	function settings_field_sanitize_filter( $value, $field )
		{
			if( !$value || !is_array($value ) ) {
				return array();
			}
			
			foreach( $value as $v ) {
					if( empty($v['from_role']) || ( 'fep_all' != $v['from_role'] && ! wp_roles()->is_role( $v['from_role'] ) ) ){
						add_settings_error( 'fep-settings', $field['id'], sprintf(__( 'Invalid role %s', 'front-end-pm' ), $v['from_role'] ));
						return $field['value'];
					}
					if( empty($v['to_role']) || ( 'fep_all' != $v['to_role'] && ! wp_roles()->is_role( $v['to_role'] ) ) ){
						add_settings_error( 'fep-settings', $field['id'], sprintf(__( 'Invalid role %s', 'front-end-pm' ), $v['to_role'] ));
						return $field['value'];
					}
					if( empty($v['for']) ){
						add_settings_error( 'fep-settings', $field['id'], __( 'Invalid Block For', 'front-end-pm' ) );
						return $field['value'];
					}
			}
			return fep_array_trim( $value );
		}
	

	function fep_action_validate_form( $where, $errors, $fields )
		{
			$block =  fep_get_option('rtr_block', array());
			
			if( empty( $block ) || ! is_array( $block ) )
			return;
			
			if( 'newmessage' == $where
				&& fep_get_option('oa-can-send-to-admin', 1 )
				&& ( ! empty( $_POST['fep_send_to_admin'] ) || ! fep_current_user_can( 'mr_newmessage_to_users') ) )
				return;
			
			if( 'reply' == $where && ! empty( $_POST['fep_parent_id']) ){
				$to = fep_get_participants( $_POST['fep_parent_id'] );
			} else {
				$to = ! empty( $_POST['message_to_id'] ) ? $_POST['message_to_id'] : 0;
			}
			
			if( empty( $to ) )
			return;

			$roles = wp_get_current_user()->roles;
			
			foreach( $block as $b ){
				if( $b['for'] != $where )
				continue;
				
				if( 'fep_all' != $b['from_role'] && ! in_array( $b['from_role'], $roles ) )
				continue;
				
				if( is_array( $to ) ){
					foreach( $to as $t ){
						if( get_current_user_id() != $t && ( 'fep_all' == $b['to_role'] ||  in_array( $b['to_role'], get_userdata( $t )->roles ) ) ){
							$errors->add( 'pro_to' , sprintf(__('You cannot message to %s', 'front-end-pm'), fep_get_userdata( $t, 'display_name', 'id' )));
							//return;
						}
					}
				} else {
					if( get_current_user_id() != $to && ( 'fep_all' == $b['to_role'] || in_array( $b['to_role'], get_userdata( $to )->roles ) ) ){
						$errors->add( 'pro_to' , sprintf(__('You cannot message to %s', 'front-end-pm'), fep_get_userdata( $to, 'display_name', 'id' )));
						//return;
					}
				}
				
			}
			
		}
	
	function fep_pro_plugin_update( $prev_ver ){
		if( version_compare( $prev_ver, '5.2', '<' ) ){
			if( ! fep_get_option('mr-can-send-to-users', 1 ) && ! fep_get_option('mr-can-admin-send-to-users', 1 ) ){
				$block[] = array(
					'from_role' => 'fep_all',
					'to_role' => 'fep_all',
					'for' => 'newmessage'
					);
				
				fep_update_option( 'rtr_block', $block );
			} elseif( ! fep_get_option('mr-can-send-to-users', 1 ) || ! fep_get_option('mr-can-admin-send-to-users', 1 ) ) {
				$block = array();
				$admin_cap = apply_filters( 'fep_admin_cap', 'manage_options' );
				
				foreach ( wp_roles()->roles as $k => $role ){
					$cap = $role['capabilities'];
					
					if( empty( $cap[ $admin_cap ] ) ){
						if( ! fep_get_option('mr-can-send-to-users', 1 ) ){
							$block[] = array(
								'from_role' => $k,
								'to_role' => 'fep_all',
								'for' => 'newmessage'
								);
						}
					} else {
						if( ! fep_get_option('mr-can-admin-send-to-users', 1 ) ){
							$block[] = array(
								'from_role' => $k,
								'to_role' => 'fep_all',
								'for' => 'newmessage'
								);
						}
					}
					
				}
				fep_update_option( 'rtr_block', $block );
			}
			
		}
	}
		
	function hide_users( $args )
		{
			$block =  fep_get_option('rtr_block', array());
			
			if( empty( $block ) || ! is_array( $block ) )
			return $args;

			$roles = wp_get_current_user()->roles;
			
			foreach( $block as $b ){
				if( $b['for'] != 'newmessage' )
				continue;
				
				if( 'fep_all' != $b['from_role'] && ! in_array( $b['from_role'], $roles ) )
				continue;
				
				if( 'fep_all' == $b['to_role'] ){
					$args['include'] = array ( 0 );
					return $args;
				} else {
					$args['role__not_in'][] = $b['to_role'];
				}
				
			}
			return $args;
			
		}
		
	function hide_directory( $can, $cap, $id ){
		
		if( 'access_directory' != $cap )
		return $can;
		
		if( ! $can )
		return $can;
		
		$block =  fep_get_option('rtr_block', array());
			
		if( empty( $block ) || ! is_array( $block ) )
		return $can;

		$roles = wp_get_current_user()->roles;
		
		foreach( $block as $b ){
			if( $b['for'] != 'newmessage' )
			continue;
			
			if( 'fep_all' != $b['from_role'] && ! in_array( $b['from_role'], $roles ) )
			continue;
			
			if( 'fep_all' == $b['to_role'] ){
				return false;
			}
			
		}
		return $can;
	}
	
	function mr_newmessage_to_users( $can, $cap, $id ){
		
		if( ! fep_get_option('mr-max-recipients', 5 ) )
		return false;
		
		$block =  fep_get_option('rtr_block', array());
			
		if( empty( $block ) || ! is_array( $block ) )
		return true;

		$roles = wp_get_current_user()->roles;
		
		foreach( $block as $b ){
			if( $b['for'] != 'newmessage' )
			continue;
			
			if( 'fep_all' != $b['from_role'] && ! in_array( $b['from_role'], $roles ) )
			continue;
			
			if( 'fep_all' == $b['to_role'] ){
				return false;
			}
			
		}
		return true;
	}
	
  } //END CLASS

add_action('init', array(Fep_Role_To_Role_Block::init(), 'actions_filters'), 5 ); //mr_newmessage_to_users filters need to be first

