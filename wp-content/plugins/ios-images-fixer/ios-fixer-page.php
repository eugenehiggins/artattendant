<?php
/**
 * iOS Fixer Page
 *
 * @package iOS Images Fixer
 * @since   1.2
 * @author  Bishoy A. <hi@bishoy.me>
 */
defined('ABSPATH') or die('No cookies for you.');
global $title; 

if ( ! empty( $_REQUEST['image'] ) && ! empty( $_REQUEST['action'] ) ) {
	$images = ( is_array( $_REQUEST['image'] ) ) ? $_REQUEST['image'] : array( $_REQUEST['image'] );

	if ( 'fix' === $_REQUEST['action'] ) {
		foreach ( $images as $image_id ) {
			$path = get_attached_file( $image_id );
			if ( ! empty( $path ) ) {
				BAImageFixer::fix_orientation( $path );
				$metadata = wp_generate_attachment_metadata( $image_id, $path );

				if ( is_wp_error( $metadata ) || empty( $metadata ) ) {
					echo '<div class="error"><p>Could not fix, unknown error happened.</p></div>';
					return false;
				}
				wp_update_attachment_metadata( $image_id, $metadata );
			} else {
				echo '<div class="error">Original uploaded image could not be found.</div>';
			}
		}
	}
}

$broken_images = self::get_broken_images(); 

if ( ! empty( $_POST['fixThemAll'] ) && ! empty( $broken_images ) ) {
	foreach ( $broken_images as $image ) {
		$path = get_attached_file( $image->ID );
		if ( ! empty( $path ) ) {
			self::fix_orientation( $path );
			$metadata = wp_generate_attachment_metadata( $image->ID, $path );

			if ( is_wp_error( $metadata ) || empty( $metadata ) ) {
				echo '<div class="error"><p>Could not fix, unknown error happened.</p></div>';
				return false;
			}
			wp_update_attachment_metadata( $image->ID, $metadata );
		}
	}
	$broken_images = self::get_broken_images();
} ?>
<div class="wrap">
<h2><?php echo $title; ?> <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=UGMBL9UDKFACG&lc=US&item_name=bishoy%2eme&item_number=ios%2dimage%2dfixer&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted" class="add-new-h2" id="iosif-donate-link" title="Much appreciated! :)" target="_blank">Donate</a></h2>
<?php 
	if ( empty( $broken_images ) ) {
		echo '<div class="updated"><p>Woohoo! You don\'t have any broken images!</p></div>';
		return false;
	}
	require_once 'list-media.php'; ?>
<p><?php _e( 'Click the "Fix All iOS-broken" button to correct <strong>all</strong> images previously uploaded and fix them. This could take sometime if you have many broken images', 'iosfixer' ); ?></p>
<form method="post">
	<input type="submit" class="button button-hero fix-button button-primary button-large" name="fixThemAll" value="Fix All iOS-broken" />
</form>
<h3>OR</h3>
<p><?php _e( 'Check and mark the images you\'d like to fix.', 'iosfixer' ); ?></p>
<?php 
$out_list = array();
foreach ( $broken_images as $image ) {
	$author = get_user_by( 'id', $image->post_author );
	if ( ! empty( $author ) ) {
		$img_author = $author->user_nicename;
	} else {
		$img_author = '';
	}
	$uploadedto = '<em>Unattached</em>';
	if ( ! empty( $image->post_parent ) ) {
		$parent_post = get_post( $image->post_parent );
		if ( ! empty( $parent_post ) ) {
			$uploadedto = '<a href="'.get_edit_post_link( $parent_post->ID ).'">' . $parent_post->post_title . '</a>';
		}
	}
	$image_date = strtotime( get_date_from_gmt( $image->post_date ) );
	$out_list[] = array( 
		'id'         => $image->ID, 
		'thumbnail'  => '<img src="' . wp_get_attachment_thumb_url( $image->ID ) . '" width="60" height="60" />', 
		'file'       => $image->post_title, 
		'author'     => $img_author, 
		'uploadedto' => $uploadedto,
		'date'       => date_i18n( get_option( 'date_format' ), $image_date ),
		'FilteredBy' => 'date' 
	);
}

$imf_list_table = new List_media( $out_list );
$imf_list_table->prepare_items(); ?>
<form method="post">
    <input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
<br />
<?php
$imf_list_table->display();
?>
</form>
</div>