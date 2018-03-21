<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.0
 */
abstract class ACP_Filtering_TableScreen {

	/**
	 * @var ACP_Filtering_Model[]
	 */
	protected $models;

	public function __construct( array $models ) {
		$this->models = $models;

		add_action( 'ac/table_scripts', array( $this, 'scripts' ) );
		add_action( 'ac/admin_head', array( $this, 'add_indicator' ), 10, 0 );
		add_action( 'ac/admin_head', array( $this, 'hide_default_dropdowns' ), 10, 0 );
	}

	public function scripts() {
		wp_enqueue_style( 'acp-filtering-table', acp_filtering()->get_plugin_url() . 'assets/css/table.css', array(), acp_filtering()->get_version() );
		wp_enqueue_script( 'acp-filtering-table', acp_filtering()->get_plugin_url() . 'assets/js/table.js', array( 'jquery', 'jquery-ui-datepicker' ), acp_filtering()->get_version() );
	}

	/**
	 * Colors the column label orange on the listing screen when it is being filtered
	 */
	public function add_indicator() {
		$classes = array();

		foreach ( $this->models as $model ) {
			if ( ! $model->is_active() || ! $model->get_filter_value() ) {
				continue;
			}

			$column_class = 'thead tr th.column-' . $model->get_column()->get_name();

			$classes[] = $column_class;
			$classes[] = $column_class . ' > a span:first-child';
		}

		if ( ! $classes ) {
			return;
		}

		?>

		<style>
			<?php echo implode( ', ', $classes ) .  '{ font-weight: bold; position: relative; }'; ?>
		</style>

		<?php
	}

	/**
	 * @since 3.8
	 */
	public function hide_default_dropdowns() {
		$disabled = array();

		foreach ( $this->models as $model ) {
			if ( $model instanceof ACP_Filtering_Model_Delegated && ! $model->is_active() ) {
				$disabled[] = '#' . $model->get_dropdown_attr_id();
			}
		}

		if ( ! $disabled ) {
			return;
		}

		?>

		<style>
			<?php echo implode( ', ', $disabled ) . '{ display: none; }'; ?>
		</style>

		<?php
	}

	protected function get_data_from_cache( ACP_Filtering_Model $model ) {
		$cache = new ACP_Filtering_Cache_Model( $model );
		$data = $cache->get();

		if ( ! $data ) {
			$data = array(
				'options' => array(
					ACP_Filtering_Markup_Dropdown::get_disabled_prefix() . 'loading' => __( 'Loading values', 'codepress-admin-columns' ) . ' ...',
				),
			);
		}

		return $data;
	}

	/**
	 * @since 3.6
	 *
	 * @return string
	 */
	public function update_dropdown_cache() {
		ob_start();

		foreach ( $this->models as $model ) {
			if ( $model->is_ranged() ) {
				continue;
			}

			$cache = new ACP_Filtering_Cache_Model( $model );
			$cache->put_if_expired();

			$this->render_model( $model );
		}

		return ob_get_clean();
	}

	public function render_markup() {
		foreach ( $this->models as $model ) {
			$this->render_model( $model );
		}
	}

	/**
	 * Display dropdown markup
	 *
	 * @param array $data
	 */
	protected function render_model( ACP_Filtering_Model $model ) {
		if ( $model instanceof ACP_Filtering_Model_Delegated || ! $model->is_active() ) {
			return;
		}

		$column = $model->get_column();

		// Check filter
		$filter_setting = $column->get_setting( 'filter' );

		if ( ! $filter_setting instanceof ACP_Filtering_Settings ) {
			return;
		}

		// Get label
		$label = $filter_setting->get_filter_label();

		if ( ! $label ) {
			$label = $filter_setting->get_filter_label_default();
		}

		// Get name
		$name = $column->get_name();

		// Range inputs or select dropdown
		if ( $model->is_ranged() ) {
			$min = $model->get_request_var( 'min' );
			$max = $model->get_request_var( 'max' );

			switch ( $model->get_data_type() ) {
				case 'date':
					$markup = new ACP_Filtering_Markup_Ranged_Date( $name, $label, $min, $max );

					break;
				case 'numeric':
					$markup = new ACP_Filtering_Markup_Ranged_Number( $name, $label, $min, $max );

					break;
				default:
					return;
			}
		} else {
			$enable_cache = apply_filters( 'acp/filtering/cache/enable', true, $column );

			$data = $enable_cache ?
				$this->get_data_from_cache( $model ) :
				$model->get_filtering_data();

			$defaults = array(
				'order'        => true,
				'options'      => array(),
				'empty_option' => false,
				'label'        => $label, // backcompat
				'limit'        => 5000,
			);

			$data = array_merge( $defaults, $data );

			$data = apply_filters( 'acp/filtering/dropdown_args', $data, $model->get_column() );

			$markup = new ACP_Filtering_Markup_Dropdown( $name );
			$markup->set_value( $model->get_request_var() )
			       ->set_label( $label )
			       ->set_order( $data['order'] );

			// backwards compatible for the acp/filtering/dropdown_args filter
			if ( is_array( $data['options'] ) ) {
				$limit = absint( $data['limit'] );

				if ( count( $data['options'] ) >= $limit ) {
					$data['options'] = array_slice( $data['options'], 0, $limit, true );
					$data['options'][ $markup::get_disabled_prefix() . 'limit' ] = '───── ' . sprintf( __( 'Limited to %s items' ), $limit ) . ' ─────';
				}

				$markup->set_options( $data['options'] );
			}

			// backwards compatible for the default options, this should be done using an array as well
			if ( true === $data['empty_option'] ) {
				$markup->set_empty()
				       ->set_nonempty();
			} elseif ( is_array( $data['empty_option'] ) ) {
				$markup->set_empty( $data['empty_option'][0] )
				       ->set_nonempty( $data['empty_option'][1] );
			}
		}

		echo $markup->render();
	}

}
