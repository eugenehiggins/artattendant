<?php
	$per_page = apply_filters( 'hsd_pagination_per_page', 10 );
	$offset = ( $current_page - 1 ) * $per_page;
	$total_pages = count( $conversations ) / $per_page;
	$current_conversations = array_splice( $conversations, $offset, $per_page );
		?>

<table id="hsd_support_table" class="table table-hover">
	<thead>
		<tr>
			<th><?php _e( 'Status', 'help-scout-desk' ) ?></th>
			<th><span class="cloak"><?php _e( 'Thread', 'help-scout-desk' ) ?></span></th>
			<th><?php _e( 'Subject', 'help-scout-desk' ) ?></th>
			<?php /*/ hidden, since previews can include notes and drafts ?><th><?php _e( 'Preview', 'help-scout-desk' ) ?></th><?php /**/ ?>
			<th><?php _e( 'Date', 'help-scout-desk' ) ?></th>
		</tr>
	</thead>
	<tbody>
		<?php if ( ! empty( $current_conversations ) ) : ?>

			<?php foreach ( $current_conversations as $key => $data ) : ?>
				<tr class="status-<?php echo sanitize_html_class( hsd_get_status_class( $data['status'] ) ) ?>">
					<td>
						<span class="label label-<?php echo sanitize_html_class( hsd_get_status_class( $data['status'] ) ) ?>"><?php echo esc_html( hsd_get_status_label( $data['status'] ) ) ?></span>
					</td>
					<td>
						<span class="badge"><?php echo esc_html( $data['threadCount'] ) ?></span>
					</td>
					<td>
						<a href="<?php echo esc_url( add_query_arg( array( 'conversation_id' => esc_attr( $data['id'] ) ), get_permalink( $post_id ) ) ) ?>"><?php echo esc_html( substr( $data['subject'], 0, 60 ) ) ?></a>

						<?php
							$tags = HSD_Tags::get_converstation_tags( $data );
						?>
						<?php if ( ! empty( $tags ) ) :  ?>
							<?php foreach ( $tags as $tag ) :  ?>
								&nbsp;<span class="badge"><?php echo esc_html( $tag ) ?></span>
							<?php endforeach ?>
						<?php endif ?>
					</td>
					<?php /*/ hidden, since previews can include notes and drafts ?>
					<td>
						<span class="conversation_preview"><?php echo esc_html( $data['preview'] ) ?></span>
					</td>
					<?php /**/ ?>
					<td>
						<time datetime="<?php echo esc_attr( $data['createdAt'] ) ?>"><?php echo date_i18n( get_option( 'date_format' ), strtotime( esc_attr( $data['createdAt'] ) ) ) ?></time>
					</td>
				</tr>
			<?php endforeach ?>

		<?php else : ?>
			<tr><td colspan="5" rowspan="3"><?php _e( 'No support requests found.', 'help-scout-desk' ) ?></td></tr>
		<?php endif ?>

	</tbody>
</table>

<?php if ( $total_pages > 1 ) : ?>
	<div class="pages clearfix">
		<ul class="pagination">
			<?php for ( $i = 1; $i < $total_pages + 1; $i++ ) : ?>
				<li class="paginated_link<?php if ( $current_page === $i  ) { echo ' active'; } ?>"><a href="<?php echo esc_url( add_query_arg( array( 'page' => (int) $i ), get_permalink( $post_id ) ) ) ?>"><?php echo (int) $i ?></a></li>
			<?php endfor; ?>
		</ul>
	</div>
<?php endif ?>
