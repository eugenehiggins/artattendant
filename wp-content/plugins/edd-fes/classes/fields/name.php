<?php
class FES_Name_Field extends FES_Field {
	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'   => false,
			'submission'     => false,
			'vendor-contact' => true,
			'profile'        => false,
			'login'          => false,
		),
		'position'    => 'specific',
		'permissions' => array(
			'can_remove_from_formbuilder' => false,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'name',
		'title'       => 'Name',
		'phoenix'     => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two email fields. Stored in db. */
	public $characteristics = array(
		'name'            => 'name',
		'template'     => 'name',
		'public'          => false,
		'required'        => true,
		'label'          => '',
		'show_placeholder' => false,
		'default'        => false,
	);


	public function set_title() {
		$this->supports['title'] = apply_filters( 'fes_' . $this->name() . '_field_title', _x( 'Name', 'FES Field title translation', 'edd_fes' ) );
	}

	/** Returns the name to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}
		$user_id       = apply_filters( 'fes_render_name_user_id_frontend', $user_id, $this->id );
		$readonly      = apply_filters( 'fes_render_name_readonly_frontend', $readonly, $user_id, $this->id );
		$value         = '';
		$required      = $this->required( $readonly );
		$output        = '';
		$output       .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output       .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<input class="textfield<?php echo $this->required_class( $readonly ); ?>" id="<?php echo $this->name(); ?>" type="text" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
		return $output;
	}

	/** Returns the name to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		global $post;
		$removable    = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="name">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][public]", $this->characteristics['public'] ); ?>
			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
				<?php FES_Formbuilder_Templates::common_text( $index, $this->characteristics ); ?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

}