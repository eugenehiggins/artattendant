<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Filtering_Markup_Ranged {

	/**
	 * @var AC_Form_Element_Input
	 */
	private $min;

	/**
	 * @var AC_Form_Element_Input
	 */
	private $max;

	/**
	 * @param string $name
	 * @param string $label
	 * @param mixed  $min The requested minimal value
	 * @param mixed  $max The requested maximal value
	 */
	public function __construct( $name, $label, $min, $max ) {
		$this->init_input( 'min', $name, $label, $min );
		$this->init_input( 'max', $name, ' - ', $max );
	}

	private function init_input( $element, $name, $label, $value ) {
		$input = new AC_Form_Element_Input( sprintf( 'acp_filter-%s[%s]', $element, $name ) );
		$input->set_value( $value )
		      ->set_id( sprintf( 'acp-filter-%s-%s', $element, $name ) )
		      ->set_label( $label )
		      ->set_class( $element );

		if ( $value ) {
			$input->add_class( 'active' );
		}

		$this->$element = $input;
	}

	/**
	 * Set type (should be similar for both inputs )
	 *
	 * @param string $type
	 */
	protected function set_input_type( $type ) {
		$this->min->set_type( $type );
		$this->max->set_type( $type );
	}

	protected function render_input( AC_Form_Element_Input $input ) {
		if ( $input->get_label() ) {
			printf( '<label for="%s">%s</label>',
				$input->get_id(),
				$input->get_label()
			);
		}

		echo $input;
	}

	/**
	 * Get the class type based on it's simple name
	 *
	 * @return string
	 */
	private function get_type() {
		$class_name = get_class( $this );

		return strtolower( substr( $class_name, strrpos( $class_name, '_' ) + 1 ) );
	}

	public function render() {
		$class = 'acp-range ' . $this->get_type();

		if ( $this->min->get_value() || $this->max->get_value() ) {
			$class .= ' active';
		}

		?>

		<div class="<?= esc_attr( $class ); ?>">
			<div class="input_group">
				<?php

				$this->render_input( $this->min );
				$this->render_input( $this->max );

				?>
			</div>
		</div>

		<?php
	}

	/**
	 * @return AC_Form_Element_Input
	 */
	protected function get_min() {
		return $this->min;
	}

	/**
	 * @return AC_Form_Element_Input
	 */
	protected function get_max() {
		return $this->max;
	}

}
