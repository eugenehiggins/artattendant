<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_TableScreen_Comment extends ACP_Filtering_TableScreen {

	public function __construct( array $models ) {
		parent::__construct( $models );

		add_action( 'restrict_manage_comments', array( $this, 'render_markup' ) );
	}

}
