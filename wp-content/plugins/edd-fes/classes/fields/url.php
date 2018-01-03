<?php
/**
 * URL Field Object
 *
 * @package    FES
 * @subpackage Classes/Fields
 * @copyright  Copyright (c) 2017, Easy Digital Downloads, LLC
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * FES_Url_Field Class.
 *
 * @see FES_Field
 */
class FES_Url_Field extends FES_Field {
	/**
	 * Field version.
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * For 3rd parameter of get_post/user_meta.
	 * @var boolean
	 */
	public $single = true;

	/**
	 * Supports are things that are the same for all fields of a field type. Like whether or not a field type supports jQuery Phoenix. Stored in obj, not db.
	 * @var array
	 */
	public $supports = array(
		'multiple' => true,
		'is_meta'  => true,  // in object as public (bool) $meta;
		'forms'    => array(
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
		'template' => 'url',
		'title'    => 'URL',
		'phoenix'  => true,
	);

	/**
	 * Characteristics are things that can change from field to field of the same field type. Like the placeholder between two url fields. Stored in db.
	 * @var array
	 */
	public $characteristics = array(
		'name'        => '',
		'template'    => 'url',
		'public'      => true,
		'required'    => false,
		'label'       => '',
		'css'         => '',
		'default'     => '',
		'size'        => '',
		'help'        => '',
		'placeholder' => '',
	);

	/**
	 * Sets the title of the field. Called in FES_Field::__construct().
	 *
	 * @access public
	 */
	public function set_title() {
		$this->supports['title'] = apply_filters( 'fes_' . $this->name() . '_field_title', _x( 'URL', 'FES Field title translation', 'edd_fes' ) );
	}

	/**
	 * Returns the HTML to render a field in admin
	 *
	 * @access public
	 *
	 * @param int $user_id  User ID. (Default: -2)
	 * @param int $readonly Whether the field is read-only. (Default: -2)
	 * @return string $output Rendered output.
	 */
	public function render_field_admin( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id  = apply_filters( 'fes_render_url_field_user_id_admin', $user_id, $this->id );
		$readonly = apply_filters( 'fes_render_url_field_readonly_admin', $readonly, $user_id, $this->id );
		$value    = $this->get_field_value_admin( $this->save_id, $user_id, $readonly );

		$output = '';
		$output .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<input id="fes-<?php echo $this->name(); ?>" type="url" class="url" data-required="false" data-type="text"< name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
		return $output;
	}

	/**
	 * Returns the HTML to render a field in frontend
	 *
	 * @access public
	 *
	 * @param int $user_id  User ID. (Default: -2)
	 * @param int $readonly Whether the field is read-only. (Default: -2)
	 * @return string $output Rendered output.
	 */
	public function render_field_frontend( $user_id = -2, $readonly = -2 ) {
		if ( $user_id === -2 ) {
			$user_id = get_current_user_id();
		}

		if ( $readonly === -2 ) {
			$readonly = $this->readonly;
		}

		$user_id  = apply_filters( 'fes_render_url_field_user_id_frontend', $user_id, $this->id );
		$readonly = apply_filters( 'fes_render_url_field_readonly_frontend', $readonly, $user_id, $this->id );
		$value    = $this->get_field_value_frontend( $this->save_id, $user_id, $readonly );
		$required = $this->required( $readonly );

		$output = '';
		$output .= sprintf( '<div class="fes-el %1s %2s %3s">', $this->template(), $this->name(), $this->css() );
		$output .= $this->label( $readonly );
		ob_start(); ?>
		<div class="fes-fields">
			<input id="fes-<?php echo $this->name(); ?>" type="url" class="url" data-required="<?php echo $required; ?>" data-type="text"<?php $this->required_html5( $readonly ); ?> name="<?php echo esc_attr( $this->name() ); ?>" placeholder="<?php echo esc_attr( $this->placeholder() ); ?>" value="<?php echo esc_attr( $value ) ?>" size="<?php echo esc_attr( $this->size() ) ?>" />
		</div>
		<?php
		$output .= ob_get_clean();
		$output .= '</div>';
		return $output;
	}

	/**
	 * Returns the HTML to render a field for the formbuilder.
	 *
	 * @access public
	 *
	 * @param  int     $index  Field index.
	 * @param  boolean $insert Whether the field is being inserted.
	 * @return $output Rendered output.
	 */
	public function render_formbuilder_field( $index = -2, $insert = false ) {
		$removable = $this->can_remove_from_formbuilder();
		ob_start(); ?>
		<li class="custom-field url">
			<?php
			$this->legend( $this->title(), $this->get_label(), $removable );
			FES_Formbuilder_Templates::hidden_field( "[$index][template]", $this->template() );

			FES_Formbuilder_Templates::field_div( $index, $this->name(), $this->characteristics, $insert );
				FES_Formbuilder_Templates::public_radio( $index, $this->characteristics, $this->form_name );
				FES_Formbuilder_Templates::standard( $index, $this );
				FES_Formbuilder_Templates::common_text( $index, $this->characteristics );
			?>
			</div>
		</li>
		<?php
		return ob_get_clean();
	}

	/**
	 * Validates the field.
	 *
	 * @access public
	 *
	 * @param  array   $values  Field values.
	 * @param  integer $save_id Save ID. (Default: -2)
	 * @param  integer $user_id User ID. (Default: -2)
	 * @return string Validated output.
	 */
	public function validate( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		$return_value = false;
		if ( ! empty( $values[ $name ] ) ) {
			if ( filter_var( $values[ $name ], FILTER_VALIDATE_URL ) === false ) {
				$return_value = __( 'Please enter a valid URL', 'edd_fes' );
			}
		} else {
			if ( $this->required() ) {
				$return_value = __( 'Please fill out this field.', 'edd_fes' );
			}
		}
		return apply_filters( 'fes_validate_' . $this->template() . '_field', $return_value, $values, $name, $save_id, $user_id );
	}

	/**
	 * Sanitize the field output.
	 *
	 * @access public
	 *
	 * @param  array   $values  Field values.
	 * @param  integer $save_id Save ID. (Default: -2)
	 * @param  integer $user_id User ID. (Default: -2)
	 * @return string Sanitized output.
	 */
	public function sanitize( $values = array(), $save_id = -2, $user_id = -2 ) {
		$name = $this->name();
		if ( ! empty( $values[ $name ] ) ) {
			$values[ $name ] = filter_var( trim( $values[ $name ] ), FILTER_SANITIZE_URL );
		}
		return apply_filters( 'fes_sanitize_' . $this->template() . '_field', $values, $name, $save_id, $user_id );
	}
}