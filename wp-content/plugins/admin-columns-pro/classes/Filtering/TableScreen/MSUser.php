<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_TableScreen_MSUser extends ACP_Filtering_TableScreen_User {

	public function __construct( array $models ) {
		parent::__construct( $models );

		add_action( 'in_admin_footer', array( $this, 'render_markup' ) );
	}

}
