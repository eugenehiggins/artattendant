<?php
class FES_Toggle_Field extends FES_Field {

	/** @var string Version of field */
	public $version = '1.0.0';

	/** @var bool For 3rd parameter of get_post/user_meta */
	public $single = true;

	/** @var array Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db. */
	public $supports = array(
		'multiple'    => true,
		'is_meta'     => true,  // in object as public (bool) $meta;
		'forms'       => array(
			'registration'   => true,
			'submission'     => true,
			'vendor-contact' => false,
			'profile'        => true,
			'login'          => false,
		),
		'position'    => 'custom',
		'permissions' => array(
			'can_remove_from_formbuilder' => true,
			'can_change_meta_key'         => true,
			'can_add_to_formbuilder'      => true,
		),
		'template'    => 'toggle',
		'title'       => 'Toggle',
		'phoenix'     => true,
	);

	/** @var array Characteristics are things that can change from field to field of the same field type. Like the placeholder between two checkbox fields. Stored in db. */
	public $characteristics = array(
		'name'           => '',
		'template'       => 'toggle',
		'public'         => true,
		'required'       => false,
		'label'          => '',
		'css'            => '',
		'selected'       => 0,
		'checkbox_label' => '',
	);

	public function set_title() {
		$this->supports['title'] = apply_filters( 'fes_' . $this->name() . '_field_title', _x( 'Toggle', 'FES Field title translation', 'edd_fes' ) );
	}

	/** Returns the HTML to render a field in admin */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_toggle_field_user_id_admin', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_toggle_field_readonly_admin', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );
		$selected  = empty( $this->characteristics['selected'] ) ? 0 : 1;
		$required  = $this->required( $readonly );
		$output    = '';
		$output   .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output   .= $this->label( $readonly );

		if ( $this->save_id > 0 && ( $this->type !== 'post' || ( $this->type === 'post' && get_post_status( $this->save_id ) !== 'auto-draft' ) ) ) {
			$selected = $this->get_meta( $this->save_id, $this->name(), $this->type );
		}
		ob_start(); ?>
		<div class="fes-fields">
			<label for="<?php echo $this->name(); ?>">
				<input type="checkbox" id="<?php echo $this->name(); ?>" name="<?php echo $this->name(); ?>"  <?php echo $selected ? ' checked="checked"' : ''; ?> /> <?php echo $this->characteristics['checkbox_label']; ?>
			</label>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
		return $output;
	}

	/** Returns the HTML to render a field in frontend */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id   = apply_filters( 'fes_render_toggle_field_user_id_frontend', $user_id, $this->id );
		$readonly  = apply_filters( 'fes_render_toggle_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$selected  = empty( $this->characteristics['selected'] ) ? 0 : 1;
		$required  = $this->required( $readonly );
		$output    = '';
		$output   .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output   .= $this->label( $readonly );

		if ( $this->save_id > 0 && ( $this->type !== 'post' || ( $this->type === 'post' && get_post_status( $this->save_id ) !== 'auto-draft' ) ) ) {
			$selected = $this->get_meta( $this->save_id, $this->name(), $this->type );
		}
		ob_start(); ?>
		<div class="fes-fields">
			<label for="<?php echo $this->name(); ?>">
				<input type="checkbox" id="<?php echo $this->name(); ?>" name="<?php echo $this->name(); ?>"  <?php echo $selected ? ' checked="checked"' : ''; ?> <?php echo ( $required ? 'required="required"' : '' );?> /> <?php echo $this->characteristics['checkbox_label']; ?>
			</label>
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
		return $output;
	}

	public function display_field( $user_id = -2, $single = false ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}
		$user_id   = apply_filters( 'fes_display_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		if ( $this->is_public() ) {
			ob_start(); ?>

			<?php if ( $single ) { ?>
			<table class="fes-display-field-table">
			<?php } ?>

			<tr class="fes-display-field-row <?php echo $this->template(); ?>" id="<?php echo $this->name(); ?>">
				<td class="fes-display-field-label"><?php echo $this->get_label(); ?></td>
				<td class="fes-display-field-values">
					<?php
					echo (int) $value; ?>
				</td>
			</tr>

			<?php if ( $single ) { ?>
			</table>
			<?php } ?>
		<?php
		}
		return ob_get_clean();
	}

	public function formatted_data( $user_id = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		$user_id   = apply_filters( 'fes_formatted_' . $this->template() . '_field_user_id', $user_id, $this->id );
		$value     = $this->get_field_value_frontend( $this->save_id, $user_id );
		return (int) $value;
	}

	/** Returns the HTML to render a field for the formbuilder */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		$checkbox_label_name  = sprintf( '%s[%d][checkbox_label]', 'fes_input', $index );
		$selected_name        = sprintf( '%s[%d][selected]', 'fes_input', $index );
		$checkbox_value       = esc_attr( $this->characteristics['checkbox_label'] );
		ob_start(); ?>
		<li class="custom-field toggle_field">
			<?php $this->legend( $this->title(), $this->get_label(), $removable ); ?>
			<?php FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() ); ?>

			<?php FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert ); ?>
				<?php FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name ); ?>
				<?php FES_Formbuilder_Templates::standard( $index, $this ); ?>
				<div class="fes-form-rows">
					<label><?php _e( 'Toggle Label', 'edd_fes' ); ?></label>
					<input type="text" name="<?php echo $checkbox_label_name; ?>" value="<?php echo esc_attr( $checkbox_value ); ?>" />
				</div>
				<div class="fes-form-rows">
					<label><?php _e( 'Selected By Default', 'edd_fes' ); ?></label>
					<input type="checkbox" name="<?php echo $selected_name; ?>"<?php checked( true, ! empty( $this->characteristics['selected'] ) ); ?> />
				</div>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( !empty( $values[ $name ] ) ) {
			$values[ $name ] = 1;
		} else {
			$values[ $name ] = 0;
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}
