<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

//Announcement CLASS
class Fep_Announcement
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
			add_action( 'publish_fep_announcement', array($this, 'recalculate_user_stats') );
			add_action( 'trash_fep_announcement', array($this, 'recalculate_user_stats') );
			//add_action( 'after_delete_post', array($this, 'recalculate_user_stats') );
    	}
	

function recalculate_user_stats(){
	
	delete_metadata( 'user', 0, '_fep_user_announcement_count', '', true );
}

function get_announcement( $id )
{
	$args = array(
		'post_type' => 'fep_announcement',
		'post_status' => 'publish',
		'post__in' => array( $id ),
	 );
	return new WP_Query( $args );
}

function get_user_announcements()
{

	$user_id = get_current_user_id();
	
	$filter = ! empty( $_GET['fep-filter'] ) ? $_GET['fep-filter'] : '';
	
		$args = array(
			'post_type' => 'fep_announcement',
			'post_status' => 'publish',
			'post_parent' => 0,
			'posts_per_page' => fep_get_option('announcements_page',15),
			'paged'	=> !empty($_GET['feppage']) ? absint($_GET['feppage']): 1,
			'meta_query' => array(
				array(
					'key' => '_participant_roles',
					'value' => wp_get_current_user()->roles,
					'compare' => 'IN'
				),
				array(
					'key' => '_fep_delete_by_'. $user_id,
					//'value' => $id,
					'compare' => 'NOT EXISTS'
				)
				
			)
		 );
		 
		 if( ! $user_id )
			$args['post__in'] = array(0);
		 
		 switch( $filter ) {
		 	case 'after-i-registered' :
				$args['date_query'] = array( 'after' => fep_get_userdata( $user_id, 'user_registered', 'id' ) );
			break;
			case 'read' :
				$args['meta_query'][] = array(
					'key' => '_fep_read_by',
					'value' => serialize($user_id),
					'compare' => 'LIKE'
				);
			break;
			case 'unread' :
				$args['meta_query'][] = array(
					'relation' => 'OR',
						array(
							'key' => '_fep_read_by',
							//'value' => serialize($user_id),
							'compare' => 'NOT EXISTS'
						),
						array(
							'key' => '_fep_read_by',
							'value' => serialize($user_id),
							'compare' => 'NOT LIKE'
						),
				);
			break;
			default:
				$args = apply_filters( 'fep_announcement_query_args_'. $filter, $args);
			break;
		 }
		 $args = apply_filters( 'fep_announcement_query_args', $args);
		 
	return new WP_Query( $args );

}

function get_user_announcement_count( $value = 'all', $force = false, $user_id = false )
{
	if( ! $user_id ) {
		$user_id = get_current_user_id();
	}
	
	if( ! $user_id ) {
		if( 'all' == $value ) {
			return array();
		} else {
			return 0;
		}
	}
	
	$user_meta = get_user_meta( $user_id, '_fep_user_announcement_count', true );
	
	if( false === $user_meta || $force || !isset( $user_meta['total'] ) || !isset( $user_meta['read'] )|| !isset( $user_meta['unread'] ) ) {
	
		$args = array(
			'post_type' => 'fep_announcement',
			'post_status' => 'publish',
			'post_parent' => 0,
			'posts_per_page' => -1,
			'meta_query' => array(
				array(
					'key' => '_participant_roles',
					'value' => get_userdata( $user_id )->roles,
					'compare' => 'IN'
				),
				array(
					'key' => '_fep_delete_by_'. $user_id,
					//'value' => $id,
					'compare' => 'NOT EXISTS'
				)
				
			)
		 );
		 $announcements = get_posts( $args );
		 
		 $total_count 		= 0;
		 $read_count 		= 0;
		 $unread_count 		= 0;
		 $after_i_registered_count = 0;
		 
		 if( $announcements && !is_wp_error($announcements) ) {
			 foreach( $announcements as $announcement ) {
		
			 	$total_count++;
				
			 	$read_by = get_post_meta( $announcement->ID, '_fep_read_by', true );
			
				if( is_array( $read_by ) && in_array( $user_id, $read_by ) ) {
					$read_count++;
				} else {
					$unread_count++;
				}
				$user_registered = strtotime(fep_get_userdata( $user_id, 'user_registered', 'id' ));
					
				if( $user_registered < strtotime( $announcement->post_date ) ) {
					$after_i_registered_count++;
				}
				
			 }
			}

		 
		 $user_meta = array(
			'total' => $total_count,
			'read' => $read_count,
			'unread' => $unread_count,
			'after-i-registered' => $after_i_registered_count
		);
		update_user_meta( $user_id, '_fep_user_announcement_count', $user_meta );
	}
	if( isset($user_meta[$value]) ) {
		return $user_meta[$value];
	}
	if( 'all' == $value ) {
		return $user_meta;
	} else {
		return 0;
	}

}

function bulk_action( $action, $ids = null ) {

	if( null === $ids ) {
		$ids = !empty($_POST['fep-message-cb'])? $_POST['fep-message-cb'] : array();
	}
	if( !$action || !$ids || !is_array($ids) ) {
		return '';
	}
					
	$count = 0;
	foreach( $ids as $id ) {
		if( $this->bulk_individual_action( $action, absint($id) ) ) {
			$count++;
		}
	}
	$message = '';
	
	if( $count ) {
		delete_user_meta( get_current_user_id(), '_fep_user_announcement_count' );
		
		if( 'delete' == $action ){
			$message = sprintf(_n('%s announcement', '%s announcements', $count, 'front-end-pm'), number_format_i18n($count) );
			$message .= ' ';
			$message .= __('successfully deleted.', 'front-end-pm');
		} 
		//$message = '<div class="fep-success">'.$message.'</div>';
	}
	return apply_filters( 'fep_bulk_action_message', $message, $count);
}

function bulk_individual_action( $action, $id ) {
	$return = false;
	
	switch( $action ) {
		case 'delete':
			if( fep_current_user_can( 'view_announcement', $id ) ) {
				$return = add_post_meta( $id, '_fep_delete_by_'. get_current_user_id(), time(), true );
			}

		break;
		default:
			$return = apply_filters( 'fep_announcement_bulk_individual_action', false, $action, $id );
		break;
	}
	return $return;
}

function get_table_bulk_actions()
{
	
	$actions = array(
			'delete' => __('Delete', 'front-end-pm')
			);

	
	return apply_filters('fep_announcement_table_bulk_actions', $actions );
}

function get_table_filters()
{
	$filters = array(
			'read' => __('Read', 'front-end-pm'),
			'unread' => __('Unread', 'front-end-pm'),
			'after-i-registered' => __('After i registered', 'front-end-pm')
			);
	return apply_filters('fep_announcementbox_table_filters', $filters );
}

function get_table_columns()
{
	$columns = array(
			'fep-cb' => __('Checkbox', 'front-end-pm'),
			'date' => __('Date', 'front-end-pm'),
			'title' => __('Title', 'front-end-pm')
			);
	return apply_filters('fep_announcement_table_columns', $columns );
}

function get_column_content($column)
{
	switch( $column ) {
		
		case has_action("fep_get_announcement_column_content_{$column}"):

			do_action("fep_get_announcement_column_content_{$column}");

		break;
		case 'fep-cb' :
			?><input type="checkbox" name="fep-message-cb[]" value="<?php echo get_the_ID(); ?>" /><?php
		break;
		case 'date' :
			?><span class="fep-message-date"><?php the_time(); ?></span><?php
		break;
		case 'title' :
			if( ! fep_is_read() ) {
					$span = '<span class="fep-unread-classp"><span class="fep-unread-class">' .__("Unread", "front-end-pm"). '</span></span>';
					$class = ' fep-strong';
				} else {
					$span = '';
					$class = '';
				} 
			?><span class="<?php echo $class; ?>"><a href="<?php echo fep_query_url('view_announcement', array('id'=> get_the_ID())); ?>"><?php the_title(); ?></a></span><?php echo $span; ?><div class="fep-message-excerpt"><?php echo fep_get_the_excerpt(100); ?></div><?php
		break;
		default:
			do_action( 'fep_get_announcement_column_content', $column );
		break;
	}
}

	function announcement_box()
	{		
		  $g_filter = ! empty( $_GET['fep-filter'] ) ? $_GET['fep-filter'] : '';
		  
		  $total_announcements = $this->get_user_announcement_count('total');
		  
		  $announcements = $this->get_user_announcements();
		  
		  $template = fep_locate_template( 'announcement_box.php');
		  
		  ob_start();
		  include( $template );
		  return ob_get_clean();
	}

function view_announcement()
    {
      global $post;

      $id = !empty($_GET['id']) ? absint($_GET['id']) : 0;
	  
	  if ( ! $id || ! fep_current_user_can( 'view_announcement', $id ) ) {
	  	return "<div class='fep-error'>".__("You do not have permission to view this announcement!", 'front-end-pm')."</div>";
	  }

      $announcement = $this->get_announcement( $id );

	  $template = fep_locate_template( 'view_announcement.php');
		  
		ob_start();
		include( $template );
		return ob_get_clean();
    }

	
	
  } //END CLASS

add_action('wp_loaded', array(Fep_Announcement::init(), 'actions_filters'));

