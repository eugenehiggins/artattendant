<div class="form-group">
	<label for="hs_thread_type" class="hidden"><?php _e( 'Select Topic', 'help-scout-desk' ) ?></label>
	<select name="hs_thread_type" class="hsd_select select_input">
		<option><?php _e( 'Select Topic', 'help-scout-desk' ) ?></option>
		<?php foreach ( $tags as $key => $tag ) : ?>
			<option value="<?php echo esc_attr( $tag->id ) ?>"><?php echo esc_html( $tag->tag ) ?></option>
		<?php endforeach ?>
	</select>
</div>
