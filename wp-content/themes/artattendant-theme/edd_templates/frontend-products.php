<?php global $products; ?>
<!-- <h1 class="fes-headers" id="fes-products-page-title"><?php echo EDD_FES()->helper->get_product_constant_name( $plural = true, $uppercase = true ) ?></h1> -->
<?php //echo EDD_FES()->dashboard->product_list_status_bar(); ?>

<?php if ( member_has_artworks() ) {

	//mapi_var_dump(get_member_location( ) );
		?>



<!--
<div id="toolbar"  class="btn-group">
 <select class="form-control">
                <option value="">Export Basic</option>
                <option value="all">Export All</option>
                <option value="selected">Export Selected</option>
            </select>
    <a href="<?php echo get_site_url(); ?>/collection/?task=new-product" class="btn btn-default">
        <i class="fa fa-plus"></i>
    </a>
    <button type="button" class="btn btn-default">
        <i class="fa fa-heart"></i>
    </button>
    <button type="button" class="btn btn-default">
        <i class="fa fa-trash"></i>
    </button>




</div>
-->

<table class="table table-no-bordered-off"  id="fes-product-list" >
	<thead>
	</thead>
	<tbody>

	</tbody>
</table>


		<?php }else{ ?>

		<div id="fes-vendor-announcements text-center">
			<div class="upper"> Welcome to your dashboard, next step is to add some artwork</div>
			<a href="<?php echo get_site_url(); ?>/collection/?task=new-product" class="btn btn-default">Add Artworks</a>
		</div>


<?php	} ?>

<?php //EDD_FES()->dashboard->product_list_pagination();