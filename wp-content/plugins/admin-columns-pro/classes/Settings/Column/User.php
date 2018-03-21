<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Settings_Column_User extends AC_Settings_Column_User {

	protected function get_display_options() {
		$options = parent::get_display_options();

		$options['gravatar'] = __( 'Gravatar', 'codepress-admin-columns' );

		natcasesort( $options );

		// Grouped options
		return array(
			array(
				'title'   => __( 'User', 'codepress-admin-columns' ),
				'options' => $options,
			),
			array(
				'title'   => __( 'Custom Field', 'codepress-admin-columns' ),
				'options' => array( 'custom_field' => __( 'Custom Field', 'codepress-admin-columns' ) ),
			),
		);
	}

	public function get_dependent_settings() {

		switch ( $this->get_display_author_as() ) {
			case 'custom_field' :

				return array( new ACP_Settings_Column_UserCustomField( $this->column ) );
			case 'gravatar' :

				return array( new ACP_Settings_Column_Gravatar( $this->column ) );
			default:

				return parent::get_dependent_settings();
		}
	}

	public function format( $user_id, $original_value ) {

		switch ( $this->get_display_author_as() ) {
			case 'custom_field' :
				/** @var ACP_Settings_Column_UserCustomField $setting */
				$setting = $this->column->get_setting( 'custom_field' );

				return get_user_meta( $user_id, $setting->get_field(), true );
			case 'gravatar':

				return get_avatar_url( $user_id );
			default:

				return parent::format( $user_id, $original_value );
		}
	}

}
