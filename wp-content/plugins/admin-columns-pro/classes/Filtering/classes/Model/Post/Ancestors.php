<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 4.2
 */
class ACP_Filtering_Model_Post_Ancestors extends ACP_Filtering_Model {

	public function get_filtering_vars( $vars ) {
		switch ( $this->get_filter_value() ) {
			case 'cpac_empty':
				$vars['post_parent'] = 0;

				break;
			case 'cpac_nonempty':
				$vars['post_parent__not_in'] = array( 0 );

				break;
		}

		return $vars;
	}

	public function get_filtering_data() {
		return array(
			'empty_option' => $this->get_empty_labels(),
		);
	}

}
