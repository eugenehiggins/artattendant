<?php
/**
 * List Media Class
 * @package iOS Images Fixer
 * @author  Bishoy A. <hi@bishoy.me>
 * @since   1.2
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class List_media extends WP_List_Table {
	/**
	 * Table Data
	 * @var $out_list array
	 */
	public $out_list;

	/**
	 * The constructor
	 * @param array $out_list Table Data
	 */
	public function __construct( $out_list ){
		global $page;
		$this->out_list = $out_list;
		parent::__construct(
			array(
			'singular'  => __( 'image', 'iosfixer' ),	//singular name of the listed records
			'plural'	=> __( 'iamges', 'iosfixer' ),	//plural name of the listed records
			'ajax'		=> false,						//does this table support ajax?
			)
		);
	}

	/**
	 * Message to show when no items are found
	 * @return string echos a string
	 */
	public function no_items() {
		_e( 'Woohoo! No broken images found.' );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
			case 'thumbnail':
			case 'file':
			case 'author':
			case 'uploadedto':
			case 'date':
				return $item[ $column_name ];
			default:
				return true;
		}
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'id'	     => array( 'id', false ),
			'author'     => array( 'author', false ),
			'uploadedto' => array( 'uploadedto', false ),
			'date'	     => array( 'date', false )
		);
		return $sortable_columns;
	}

	public function get_columns(){
		$columns = array(
			'cb'		 => '<input type="checkbox" />',
			'thumbnail'	 => __( 'Thumbnail', 'iosfixer' ),
			'id'	     => __( 'File', 'iosfixer' ),
			'author'	 => __( 'Author', 'iosfixer' ),
			'uploadedto' => __( 'Uploaded To', 'iosfixer' ),
			'date'		 => __( 'Date', 'iosfixer' ),
		);
		return $columns;
	}

	public function usort_reorder( $a, $b ) {
		// If no sort, default to title
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'date';
		// If no order, default to asc
		$order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
		// Determine sort order
		$result = strcmp( $a[$orderby], $b[$orderby] );
		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : -$result;
	}

	public function column_id( $item ) {

		$actions = array(
			'fix'	 => sprintf( '<a href="'.admin_url('upload.php?page=ios-images-fixer').'&amp;action=fix&amp;image=%s" class="fix-link">Fix</a>', $item['id'] ),
			'edit'	 => sprintf( '<a href="post.php?post=%s&amp;action=edit">Edit</a>', $item['id'] ),
			'delete' => sprintf( '<a class="submitdelete" href="post.php?action=delete&amp;post=%s&amp;_wpnonce=%s" onclick="return showNotice.warn();">Delete Permanently</a>', $item['id'], wp_create_nonce( 'delete-post_'.$item['id'] ) ),
			'view'   => sprintf( '<a href="'.home_url().'/?attachment_id=%s">View</a>', $item['id'] ),
		);

		return sprintf( '%1$s %2$s', sprintf( '<strong><a href="post.php?post=%s&amp;action=edit" class="row-title">%s</a></strong>', $item['id'], $item['file'] ), $this->row_actions( $actions ) );
	}

	public function get_bulk_actions() {
		$actions = array(
			'fix' => 'Fix iOS-Broken'
		);
		return $actions;
	}

	public function column_cb( $item ) {
		$checked = '';
		if ( ! empty( $_POST['image'] ) ) {
			$images = ( is_array( $_REQUEST['image'] ) ) ? $_REQUEST['image'] : array( $_REQUEST['image'] );
			if ( in_array( $item['id'], $images ) ) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
		}
		return sprintf(
			'<input type="checkbox" name="image[]" value="%d" %s />', @$item['id'], $checked
		);
	}

	//Push Table list public function with all properties
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		usort( $this->out_list, array( &$this, 'usort_reorder' ) );

		$per_page     = 15;
		$current_page = $this->get_pagenum();
		$total_items  = count( $this->out_list );

		// only ncessary because we have sample data
		$this->found_data = array_slice( $this->out_list,( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args(
			array(
			'total_items' => $total_items,				  //WE have to calculate the total number of items
			'per_page'	  => $per_page,					 //WE have to determine how many items to show on a page
			) 
		);
		$this->items = $this->found_data;
	}
}