<div id="hsd_support_conversation" data-item-status="<?php echo esc_attr( $item['status'] ) ?>">
	<header id="conversation_header" class="entry-header clearfix">
		
		<?php do_action( 'hsd_single_conversation_pre_title', $item ) ?>

		<h1 class="entry-title title"><?php echo esc_attr( $item['subject'] ) ?></h1>

		<?php do_action( 'hsd_single_conversation_post_title', $item ) ?>

		<div class="author">
			
			<?php do_action( 'hsd_single_conversation_pre_posted_on', $item ) ?>

			<span class="posted-on">

				<span class="label label-<?php hsd_status_class( $item['status'] ) ?>"><?php hsd_status_label( $item['status'] ) ?></span>

				<?php do_action( 'hsd_single_conversation_status', $item ) ?>

				<?php
					$name = esc_attr( $item['customer']['firstName'] ) . ' ' . esc_attr( $item['customer']['lastName'] );
					$time = '<time datetime="'.esc_attr( $item['createdAt'] ).'">'.date_i18n( get_option( 'date_format' ), strtotime( esc_attr( $item['createdAt'] ) ) ).'</time>';

					printf( esc_html__( 'By %s on %s', 'help-scout-desk' ), $name, $time ); ?>
			</span>
		
			<?php do_action( 'hsd_single_conversation_posted_on', $item ) ?>
		
		</div>
		
		<?php do_action( 'hsd_single_conversation_post_header', $item ) ?>

	</header><!-- /conversation_header -->

	<a href="<?php echo get_permalink( $post_id ) ?>" class="button hsd_goback"><?php esc_attr_e( 'Go back', 'help-scout-desk' ) ?></a>

	<?php do_action( 'hsd_single_conversation_go_back', $item ) ?>

	<section id="hsd_conversation_thread"  class="clearfix">
		
		<?php do_action( 'hsd_single_conversation_thread_start', $item ) ?>

		<?php
		$thread = $item['threadCount'];
		foreach ( $threads as $key => $data ) : ?>
			<?php if ( $data['type'] !== 'lineitem' ) : ?>
				<div class="panel panel-default">
					<div class="panel-heading clearfix">
						<span class="avatar pull-left"><?php echo get_avatar( $data['createdBy']['email'], 36 ) ?></span>
						<h3 class="panel-title pull-right">
							<?php
								$name = esc_attr( $data['createdBy']['firstName'] ) . ' ' . esc_attr( $data['createdBy']['lastName'] );
								$time = '<time datetime="'.esc_attr( $data['createdAt'] ).'">'.date_i18n( get_option( 'date_format' ), strtotime( esc_attr( $data['createdAt'] ) ) ).'</time>';

								printf( esc_html__( '%s on %s', 'help-scout-desk' ), $name, $time ); ?>
						</h3>
					</div>
					<div class="panel-body">
						<div class="conversation_body clearfix">
							<div class="message">
								<?php echo wpautop( self::linkify( $data['body'] ) ); ?>
							</div>
							<!-- Image Attachments will be imgs -->
							<?php if ( isset( $data['attachments'] ) && ! empty( $data['attachments'] ) ) : ?>
								<div class="img_attachments_wrap clearfix">
									<ul class="attachments img_attachments clearfix">
									<?php foreach ( $data['attachments'] as $key => $att_data ) : ?>
										<?php if ( in_array( $att_data['mimeType'], array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' ) ) ) : ?>
											<li class="image_att">
												<a target="_blank" href="<?php echo esc_url( $att_data['url'] ) ?>" class="file fancyimg" title="View Attachment"><img src="<?php echo esc_url( $att_data['url'] ) ?>" alt="<?php echo esc_attr( $att_data['fileName'] ) ?>"></a>
											</li>
										<?php endif ?>
									<?php endforeach ?>
									</ul>
								</div>
								<!-- All Attachments will be linked -->
								<div class="attachments_wrap clearfix">
									<h5><?php esc_html_e( 'Attachments', 'help-scout-desk' ) ?></h5>
									<?php if ( isset( $data['attachments'] ) && ! empty( $data['attachments'] ) ) : ?>
										<ul class="attachments file_attachments">
										<?php foreach ( $data['attachments'] as $key => $att_data ) : ?>
											<li class="file_att">
												<a target="_blank" href="<?php echo esc_url( $att_data['url'] ) ?>" class="file fancyimg" title="View Attachment"><?php echo esc_attr( $att_data['fileName'] ) ?></a>
											</li>
										<?php endforeach ?>
										</ul>
									<?php endif ?>
								</div>
							<?php endif ?>
						</div>
					</div>
					<div class="panel-footer">
						<?php
							printf( esc_html__( '<b>%s</b> of %s', 'help-scout-desk' ), $thread, esc_attr( $item['threadCount'] ) ); ?>
					</div>
				</div>
				<?php $thread--; // update thread count ?>
			<?php else : ?>
				<div class="line_item">
					<?php
						$name = esc_attr( $data['createdBy']['firstName'] ) . ' ' . esc_attr( $data['createdBy']['lastName'] );
						$time = '<time datetime="'.esc_attr( $data['createdAt'] ).'">'.date_i18n( get_option( 'date_format' ), strtotime( esc_attr( $data['createdAt'] ) ) ).'</time>';
						$status = sprintf( '<span class="label label-%s">%s</span>', hsd_get_status_class( $data['status'] ), hsd_get_status_label( $data['status'] ) );
						printf( esc_html__( '%s by %s on %s', 'help-scout-desk' ), $status, $name, $time ); ?>
				</div>
			<?php endif ?>
		<?php
		endforeach; ?>
		<?php do_action( 'hsd_single_conversation_thread_end', $item ) ?>
	</section><!-- #hsd_conversation_thread -->

</div><!-- #hsd_support_conversation -->
