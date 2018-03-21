<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ACF Field for Advanced Custom Fields
 *
 * @since 1.1
 * @abstract
 */
abstract class ACA_ACF_Column extends AC_Column_Meta
	implements ACP_Column_EditingInterface, ACP_Column_FilteringInterface, ACP_Column_SortingInterface, ACP_Export_Column {

	public function __construct() {
		$this
			->set_type( 'column-acf_field' )
			->set_label( __( 'Advanced Custom Fields', 'codepress-admin-columns' ) )
			->set_group( 'acf' );
	}

	// Meta

	public function get_meta_key() {
		return $this->get_field()->get( 'name' );
	}

	// Display

	public function get_value( $id ) {
		$value = $this->get_field()->get_value( $id );

		if ( $value instanceof AC_Collection ) {
			$value = $value->filter()->implode( $this->get_separator() );
		}

		// Wrap in ACF Append Prepend
		if ( $value ) {
			$prepend = $this->get_field()->get( 'prepend' );
			$append = $this->get_field()->get( 'append' );

			// remove &nbsp; characters
			$prepend = str_replace( chr( 194 ) . chr( 160 ), ' ', $prepend );
			$append = str_replace( chr( 194 ) . chr( 160 ), ' ', $append );

			$value = $prepend . $value . $append;
		}

		return $value;
	}

	public function get_raw_value( $id ) {
		return $this->get_field()->get_raw_value( $id );
	}

	// Settings

	/**
	 * @param string $type Comment, Post, Taxonomy, User or Media
	 */
	protected function register_settings_by_type( $type ) {
		$setting = 'Setting_Field_' . $type;

		// Default version
		$class = ac_addon_acf()->get_prefix() . $setting;

		// Free version specific
		if ( ACA_ACF_API::is_free() ) {
			$free_class = ac_addon_acf()->get_prefix() . 'Free_' . $setting;

			if ( class_exists( $free_class ) ) {
				$class = $free_class;
			}
		}

		if ( class_exists( $class ) ) {

			/* @var ACA_ACF_Setting_Field $setting */
			$setting = new $class( $this );

			$this->add_setting( $setting );
		}
	}

	// Pro

	public function editing() {
		return $this->get_field()->editing();
	}

	public function filtering() {
		return $this->get_field()->filtering();
	}

	public function sorting() {
		return $this->get_field()->sorting();
	}

	public function export() {
		return $this->get_field()->export();
	}

	// Field

	/**
	 * @return array|false ACF Field settings
	 */
	public function get_acf_field() {
		return ACA_ACF_API::get_field( $this->get_field_hash() );
	}

	/**
	 * @param string $property
	 *
	 * @return string|array|false
	 */
	public function get_acf_field_option( $property ) {
		$field = $this->get_acf_field();

		return $field && isset( $field[ $property ] ) ? $field[ $property ] : false;
	}

	/**
	 * @return ACA_ACF_Field
	 */
	public function get_field() {
		return $this->get_field_by_type( $this->get_acf_field_option( 'type' ) );
	}

	/**
	 * Returns Field. By default it will return a Pro version Field, but when available this returns a Free version Field.
	 *
	 * @param string $type ACF field type
	 *
	 * @return ACA_ACF_Field|false
	 */
	public function get_field_by_type( $field_type ) {
		$class = ac_addon_acf()->get_prefix() . 'Field';

		// Specific field types
		$type = $class . '_' . AC_Autoloader::string_to_classname( $field_type );

		if ( class_exists( $type ) ) {
			$class = $type;
		}

		// Free version specific
		if ( ACA_ACF_API::is_free() ) {
			$type = ac_addon_acf()->get_prefix() . 'Free_Field_' . AC_Autoloader::string_to_classname( $field_type );

			if ( class_exists( $type ) ) {
				$class = $type;
			}
		}

		return new $class( $this );
	}

	/**
	 * Get Field hash
	 *
	 * @since 1.1
	 *
	 * @return string ACF field Hash (key)
	 */
	public function get_field_hash() {
		if ( ! $this->get_setting( 'field' ) ) {
			return false;
		}

		return $this->get_setting( 'field' )->get_value();
	}

	/**
	 * Get formatted ID for ACF
	 *
	 * @since 1.2.2
	 */
	public abstract function get_formatted_id( $id );

}
