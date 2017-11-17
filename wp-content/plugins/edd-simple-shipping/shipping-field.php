<?php
class FES_Simple_Shipping_Field extends FES_Field {

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => false,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'     => false,
			'submission'       => true,
			'vendor-contact'   => false,
			'profile'          => false,
			'login'            => false,
		),
		'position'    => 'extension',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => false,
			'can_add_to_formbuilder'      => true,
		),
		'template'	  => 'edd_simple_shipping',
		'title'       => 'Shipping', // l10n on output
		'phoenix'	   => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two text fields. Stored in db. */
	public $characteristics = array(
		'name'        => 'edd_simple_shipping',
		'template'	  => 'edd_simple_shipping',
		'public'      => false,
		'required'    => true,
		'label'       => 'Shipping',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
	);

	public function extending_constructor( ){
		// exclude from submission form in admin
		add_filter( 'fes_templates_to_exclude_render_submission_form_admin', array( $this, 'exclude_from_admin' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_validate_submission_form_admin', array( $this, 'exclude_from_admin' ), 10, 1  );
		add_filter( 'fes_templates_to_exclude_save_submission_form_admin', array( $this, 'exclude_from_admin' ), 10, 1  );
	}

	public function set_title() {
		$title = _x( 'Shipping', 'FES Field title translation', 'edd-simple-shipping' );
		$title = apply_filters( 'fes_' . $this->name() . '_field_title', $title );
		$this->supports['title'] = $title;
	}

	public function exclude_from_admin( $fields ){
		array_push( $fields, 'edd_simple_shipping' );
		return $fields;
	}

	/** Don't register in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		return '';
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_edd_simple_shipping_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_edd_simple_shipping_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required  = $this->required( $readonly );

        $el_name       = $this->name();
        $class_name    = $this->css();

        $output        = '';
        $output        .= sprintf( '<fieldset class="fes-el %s%s">', $el_name, $class_name );
        $output    	   .= $this->label( $readonly );
		$enabled       = get_post_meta( $this->save_id, '_edd_enable_shipping', true );

		$domestic 	   = get_post_meta( $this->save_id, '_edd_shipping_domestic', true );
		$domestic      = edd_format_amount( esc_attr( $domestic ) );

		$international = get_post_meta( $this->save_id, '_edd_shipping_international', true );
		$international = edd_format_amount( esc_attr( $international ) );
        ob_start(); ?>
		<style>
		div.fes-form fieldset .fes-fields.edd_simple_shipping label { width: 100%; display:block; }
		div.fes-form fieldset .fes-fields.edd_simple_shipping .edd-fes-shipping-fields label { width: 45%; display:inline-block; }
		div.fes-form fieldset .fes-fields .edd-shipping-field { width: 45%; display:inline-block; }
		</style>
		<div class="fes-fields <?php echo sanitize_key( $this->name()); ?>">
			<?php if ( ! $this->required() ) { ?>
			<label for="edd_simple_shipping[enabled]">
				<input type="checkbox" name="edd_simple_shipping[enabled]" id="edd_simple_shipping[enabled]" value="1"<?php checked( '1', $enabled ); ?>/>
				<?php _e( 'Enable Shipping', 'edd-simple-shipping' ); ?>
			</label>
			<?php } ?>
			<div class="edd-fes-shipping-fields">
				<label for="edd_simple_shipping[domestic]"><?php _e( 'Domestic', 'edd-simple-shipping' ); ?></label>
				<label for="edd_simple_shipping[international]"><?php _e( 'International', 'edd-simple-shipping' ); ?></label>
				<input class="edd-shipping-field textfield<?php echo esc_attr( $required ); ?>" id="edd_simple_shipping[domestic]" type="text" data-required="<?php echo $required ?>" data-type="text" name="<?php echo esc_attr( $this->name() ); ?>[domestic]" placeholder="<?php echo __( 'Enter the domestic shipping charge amount', 'edd-simple-shipping' ); ?>" value="<?php echo esc_attr( $domestic ) ?>" size="10" <?php $this->required_html5( $readonly ); ?> <?php echo $readonly ? 'disabled' : ''; ?> />
				<input class="edd-shipping-field textfield<?php echo esc_attr( $required ); ?>" id="edd_simple_shipping[international]" type="text" data-required="<?php echo $required ?>" data-type="text" name="<?php echo esc_attr( $this->name() ); ?>[international]" placeholder="<?php echo __( 'Enter the international shipping charge amount', 'edd-simple-shipping' ); ?>" value="<?php echo esc_attr( $international ) ?>" size="10" <?php $this->required_html5( $readonly ); ?> <?php echo $readonly ? 'disabled' : ''; ?> />
			</div>
		</div> <!-- .fes-fields -->
        <?php
		$output .= ob_get_clean();
		$output .= '</fieldset>';
		return $output;
	}

	public function display_field( $user_id = -2, $single = false ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$user_id   = apply_filters( 'fes_display_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		ob_start(); ?>

			<?php if ( $single ) { ?>
			<table class="fes-display-field-table">
			<?php } ?>

				<tr class="fes-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
					<td class="fes-display-field-label"><?php echo $this->get_label(); ?></td>
					<td class="fes-display-field-values">
						<?php
						echo '';
						?>
					</td>
				</tr>
			<?php if ( $single ) { ?>
			</table>
			<?php } ?>
		<?php
		return ob_get_clean();
	}

	public function formatted_data( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id   = apply_filters( 'fes_fomatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$values     = $this->get_field_value_frontend( $this->save_id, $user_id );
		$output    = '';
		return $output;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
        <li class="edd_simple_shipping">
			<style>
				div.fes-form fieldset .fes-fields.edd_simple_shipping label { width: 100%; display:block; }
				div.fes-form fieldset .fes-fields.edd_simple_shipping .edd-fes-shipping-fields label { width: 45%; display:inline-block; }
				div.fes-form fieldset .fes-fields .edd-shipping-field { width: 45%; display:inline-block; }
			</style>
            <?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
            <?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
                <?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
            </div>
        </li>
        <?php
		return ob_get_clean();
	}

	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
        $name = $this->name();
		if ( $this->required() ){
			$values[ $name ]['enabled'] = '1';
			if ( ! isset( $values[ $name ]['domestic'] ) ) {
				return __( 'Please enter your domestic shipping price.', 'edd-simple-shipping' );
			}
			if ( ! isset( $values[ $name ]['international'] ) ) {
				return __( 'Please enter your international shipping price.', 'edd-simple-shipping' );
			}
		} else {
			if ( !empty( $values[ $name ]['enabled'] ) ) {
				if ( ! isset( $values[ $name ]['domestic'] ) ) {
					return __( 'Please enter your domestic shipping price.', 'edd-simple-shipping' );
				}
				if ( ! isset( $values[ $name ]['international'] ) ) {
					return __( 'Please enter your international shipping price.', 'edd-simple-shipping' );
				}
			}

		}
        return apply_filters( 'fes_validate_' . $this->template() . '_field', false, $values, $name, $save_id, $user_id );
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ){
        $name = $this->name();
		if ( !empty( $values[ $name ] ) ){
			if ( $this->required() ) {
				$values[ $name ]['enabled'] = '1';
			}

			if ( ! empty( $values[ $name ]['domestic'] ) ) {
				$values[ $name ]['domestic'] = edd_sanitize_amount( trim( $values[ $name ]['domestic'] ) );
			} else {
				$values[ $name ]['domestic'] = '0';
			}

			if ( ! empty( $values[ $name ]['international'] ) ) {
				$values[ $name ]['international'] = edd_sanitize_amount( trim( $values[ $name ]['international'] ) );
			} else {
				$values[ $name ]['international'] = '0';
			}
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}

	public function save_field_frontend( $save_id = -2, $value = array(), $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $save_id == -2 || $save_id < 1 ) {
			$save_id = $this->save_id;
		}

		if ( isset( $value['enabled'] ) ) {
			update_post_meta( $save_id, '_edd_enable_shipping', $value['enabled'] );
			update_post_meta( $save_id, '_edd_shipping_domestic', $value['domestic'] );
			update_post_meta( $save_id, '_edd_shipping_international', $value['international']  );

			$prices = edd_get_variable_prices( $save_id );
			if( ! empty( $prices ) ) {
				foreach( $prices as $price_id => $price ) {
					$prices[ $price_id ]['shipping'] = '1';
				}
				update_post_meta( $save_id, 'edd_variable_prices', $prices );
			}
		} else {
			delete_post_meta( $save_id, '_edd_enable_shipping' );
		}

	}
}
