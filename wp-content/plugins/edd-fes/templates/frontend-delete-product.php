<?php
/**
 * Template to delete a product.
 *
 * @package FES/Templates
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$post_id = absint( $_REQUEST['post_id'] );

// Ensure the vendor is the author of this download and has permission the delete.
if ( ! EDD_FES()->vendors->vendor_can_delete_product( $post_id ) ) :
	_e( 'Access Denied: You may only delete your own products','edd_fes' );
else : ?>
	<p><?php printf( _x( 'Are you sure you want to delete this %s? This action is irreversible.', 'FES lowercase singular setting for vendor', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( false, false ) ); ?></p>
	<form id="fes-delete-form" action="" method="post">
		<fieldset class="fes-form-fieldset fes-form-fieldset-delete">
			<legend class="fes-form-legend" id="fes-delete-product-page-title">
				<?php printf( _x( 'Delete %s: #%d', 'FES uppercase singular setting for download', 'edd_fes' ), EDD_FES()->helper->get_product_constant_name( false, true ), $post_id ); ?>
			</legend>
			<input type="hidden" name="pid" value="<?php echo esc_attr( $post_id ); ?>">
			<?php wp_nonce_field( 'fes_delete_nonce', 'fes_nonce' ); ?>
			<button class="fes-delete button" type="submit"><?php _e( 'Delete', 'edd_fes' ); ?></button>
		</fieldset>
	</form>
<?php endif; ?>