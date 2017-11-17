<?php
$task       = ! empty( $_GET['task'] ) ? $_GET['task'] : 'dashboard';
if(! empty( $_GET['fepaction'] ) ){  $task = 'messages';  };
$icon_css   = apply_filters( "fes_vendor_dashboard_menu_icon_css", "icon-white" ); //else icon-black/dark
$menu_items = EDD_FES()->dashboard->get_vendor_dashboard_menu();

?>
 <div id="sidebar-wrapper">
            <div class="sidebar-nav">


<!--
      <div class="navbar navbar-default" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".sidebar-navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <span class="visible-xs navbar-brand">Sidebar menu</span>
        </div>

        <div class="navbar-collapse collapse sidebar-navbar-collapse">
        -->
			<nav class="fes-vendor-menu nav">
				<ul class="nav">
					<?php foreach ( $menu_items as $item => $values ) : $values["task"] = isset( $values["task"] ) ? $values["task"] : '';
						$page_link  = get_permalink(74);

						if($values["task"] == 'messages')$page_link  = get_permalink(1085); ?>
						<li class="fes-vendor-menu-tab <?php echo 'fes-vendor-' . esc_attr( $values["task"] ) . '-tab'; if ( ($task === $values["task"] )  ) { echo ' active'; } ?>">
							<a href='<?php echo add_query_arg( 'task', $values["task"], $page_link ); ?>'>

								<i class="icon icon-<?php echo esc_attr( $values["icon"] ); ?> <?php echo esc_attr( $icon_css ); ?>"></i> <span class="hidden-phone hidden-tablet"><?php echo isset( $values["name"] ) ? $values["name"] : $item; ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

  <!--      </div>
     </div>
-->
    </div>

  </div>
   <a href="#menu-toggle" class="" id="menu-toggle"><i class="fa fa-bars"  aria-hidden="true"></i></a>