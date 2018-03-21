<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @property ACA_ACF_Column $column
 */
abstract class ACA_ACF_Setting_Field extends AC_Settings_Column {

	/**
	 * @var string
	 */
	private $field;

	/**
	 * @return array
	 */
	abstract public function get_grouped_field_options();

	protected function define_options() {
		return array( 'field' );
	}

	public function create_view() {
		$setting = $this->create_element( 'select' );

		$setting
			->set_no_result( sprintf( __( 'No %s fields available.', 'codepress-admin-columns' ), 'ACF' ) )
			->set_attribute( 'data-refresh', 'column' )
			->set_attribute( 'data-label', 'update' )
			->set_options( $this->get_cached_field_options() );

		$this->check_errors( $setting );

		$view = new AC_View();
		$view->set( 'label', __( 'Field', 'codepress-admin-columns' ) )
		     ->set( 'setting', $setting );

		return $view;
	}

	/**
	 * @return array
	 */
	private function get_cached_field_options() {
		$options = wp_cache_get( $this->column->get_list_screen()->get_storage_key(), 'ac-field-groups' );

		if ( ! $options ) {
			$options = $this->get_grouped_field_options();

			wp_cache_add( $this->column->get_list_screen()->get_storage_key(), $options, 'ac-field-groups', 15 );
		}

		return $options;
	}

	/**
	 * Checks if selected value still exists in the options. If not, it will show an error message.
	 *
	 * @param AC_Settings_Form_Element_Select $setting
	 */
	private function check_errors( $setting ) {
		$value = $setting->get_value();

		if ( ! $value ) {
			return;
		}

		$options = array();

		// Flatten options
		foreach ( $setting->get_options() as $values ) {
			if ( isset( $values['options'] ) ) {
				$options = array_merge( $options, $values['options'] );
			}
		}

		if ( ! array_key_exists( $value, $options ) ) {
			$setting->set_description( '<span class="ac-setting-error">' . sprintf( __( 'The previous selected option %s no longer exists.', 'codepress-admin-columns' ), "<strong>" . $value . "</strong>" ) . "</span>" );
		}
	}

	public function get_dependent_settings() {
		return $this->column->get_field()->get_dependent_settings();
	}

	/**
	 * @return string
	 */
	public function get_field() {
		if ( null === $this->field ) {
			$this->field = $this->get_first_field_hash();
		}

		return $this->field;
	}

	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function set_field( $field ) {
		$this->field = $field;

		return $this;
	}

	/**
	 * @return bool|mixed
	 */
	public function get_first_field_hash() {
		$fields = $this->get_grouped_field_options();
		$field = reset( $fields );

		if ( empty( $field['options'] ) ) {
			return false;
		}

		return key( $field['options'] );
	}

	/**
	 * @param array $groups ACF (version 5) field groups
	 *
	 * @return array Group list
	 */
	protected function get_option_groups( $groups ) {
		$option_groups = array();

		foreach ( $groups as $group_id => $group ) {
			$options = array();

			$fields = acf_get_fields( $group );

			foreach ( $fields as $field ) {
				if ( in_array( $field['type'], array( 'tab', 'message' ) ) ) {
					continue;
				}

				// Clone is not supported
				if ( isset( $field['_clone' ] ) ) {
					continue;
				}

				$label = $field['label'];

				if ( ! $label ) {
					$label = __( 'empty label', 'codepress-admin-columns' );
				}

				$options[ $field['key'] ] = $label;
			}

			if ( ! empty( $options ) ) {

				// when using ACF lite with the php export, all group ID's are zero.
				if ( ! empty( $group['ID'] ) ) {
					$group_id = $group['ID'];
				}

				natcasesort( $options );

				$option_groups[ $group_id ] = array(
					'title'   => $group['title'],
					'options' => $options,
				);
			}
		}

		return $option_groups;
	}

}
