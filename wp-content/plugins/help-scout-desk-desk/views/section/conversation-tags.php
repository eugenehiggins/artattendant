<?php foreach ( $conversation_tags as $key => $tag ) : ?>
	<span class="label label-default label-<?php echo esc_attr( $tag ) ?>"><?php echo esc_html( $tag ) ?></span> 
<?php endforeach ?>
