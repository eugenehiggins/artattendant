<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Setting_Oembed extends AC_Settings_Column
	implements AC_Settings_FormatValueInterface {

	/**
	 * @var string
	 */
	private $oembed;

	protected function define_options() {
		return array( 'oembed' );
	}

	public function format( $value, $original_value ) {
		if ( 'video' == $this->get_oembed() ) {
			$value = wp_oembed_get( $value, array(
				'width'  => 200,
				'height' => 200,
			) );
		}

		return $value;
	}

	public function create_view() {
		$select = $this->create_element( 'select' );
		$select->set_options( array(
			''      => __( 'Url' ), // default
			'video' => __( 'Video' ),
		) );

		$view = new AC_View( array(
			'label'   => __( 'Display format', 'codepress-admin-columns' ),
			'setting' => $select,
		) );

		return $view;
	}

	/**
	 * @return string
	 */
	public function get_oembed() {
		return $this->oembed;
	}

	/**
	 * @param string $display
	 *
	 * @return $this
	 */
	public function set_oembed( $display ) {
		$this->oembed = $display;

		return $this;
	}

}
