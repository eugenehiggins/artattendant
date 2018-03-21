<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_TableScreen_User extends ACP_Filtering_TableScreen {

	public function __construct( array $models ) {
		parent::__construct( $models );

		add_action( 'restrict_manage_users', array( $this, 'render_markup' ) );
	}

	/**
	 * Run once for Users
	 */
	public function render_markup() {
		remove_action( 'restrict_manage_users', array( $this, 'render_markup' ) );

		parent::render_markup();

		$this->render_button();
	}

	/**
	 * @since 3.5
	 */
	public function render_button() {
		?>

		<input type="submit" name="acp_filter_action" class="button" value="<?php echo esc_attr( __( 'Filter', 'codepress-admin-columns' ) ); ?>">

		<?php
	}

}
