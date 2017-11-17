<?php
/**
 * Endpoints for products.
 *
 * @package   artattendant\artattendant_api\
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 Josh Pollock
 */

namespace artattendant\artattendant_api\routes;


class products extends endpoints {


	/**
	 * Get a single product
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_item( $request) {
		$params = $request->get_params();
		$id = $params[ 'id' ];
		if ( 1 < $id ) {
			$post = get_post( $id );
		}else{
			$post = null;
		}


		if ( $post ) {
			$data = $this->make_data( $post, array() );

			$response = rest_ensure_response( $data );
			$response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );
		}else{
			$response = new \WP_REST_Response( 0, 404, array() );
		}

		$response->set_matched_route( $request->get_route() );

		return $response;

	}

	/**
	 * Get multiple products
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();


		if ( $params[ 'slug' ] ) {
			$args[ 'name' ] = $params[ 'slug' ];
			$args[ 'post_type' ] = $this->post_type;
			$args[ 'post_status' ] = 'draft';
		}elseif( $params[ 'soon' ] ) {
			$args[ 'meta_key' ] = 'edd_coming_soon';
			$args[ 'meta_value' ] = true;
		}else{

			$args = $this->query_args( $params );
		}
		$userargs = $this->query_args( $params );


		$args["posts_per_page"] = $userargs["posts_per_page"];
		$args[ 'author' ] = $userargs["author"];
		//$args[' name '][ 'params' ] ='TEST';

			//var_dump($userargs);




		return $this->do_query( $request, $args );

	}

	/**
	 * Get Caldera Forms add-ons
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_cf_addons( $request ) {
		$params = $request->get_params();
		$args = $this->query_args( $params );
        $category = $request[ 'category' ];
        switch( $category ){
            case 'tool' :
                $category = 'developer-tool';
                break;
            case 'free' :
                $category = 'free-caldera-forms-add-on';
            break;
            case 'payment' :
                $category = 'payment-processers';
            break;
            case 'bundles' :
            case 'bundle' :
                $category = 'caldera-forms-bundles';
            break;
        }
        if( ! in_array( $category, [ 'developer-tool', 'email', 'content-managment','free-caldera-forms-add-on', 'payment-processers', 'caldera-forms-bundles' ] ) ){
            $category = 'all-cf-addons';
        }
		$args[ 'tax_query' ] = array(
			array(
				'taxonomy' => 'download_category',
				'field'    => 'slug',
				'terms'    => $category,
			),
		);


		return $this->do_query( $request, $args );

	}

	/**
	 * Get plugins in caldera search bundle
	 *
	 * @since 0.2.0
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_caldera_search( \WP_REST_Request $request  ) {
		$args[ 'post__in' ] = array( 333, 3688, 1427, 4172 );

		return $this->do_query( $request, $args );

	}

	/**
	 * Get Caldera Forms bundle
	 *
	 * @since 0.2.0
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_cf_bundles( \WP_REST_Request $request ){
		$bundles   = [
			20520,
			20518,
			20515,
			20521
		];
		$args[ 'post__in' ] = $bundles;

		return $this->do_query( $request, $args );


	}

	/**
	 * Get featured plugins
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_featured( $request ) {
		$params = $request->get_params();
		$args = $this->query_args( $params );
		$args[ 'meta_key' ] = 'show_on_front_page';
		$args[ 'meta_value' ] = true;

		return $this->do_query( $request, $args );

	}

	/**
	 * Add current post to response data for this route.
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_Post $post Current post object.
	 * @param array $data Current collection of data
	 *
	 * @return array
	 */
	protected function make_data( $post, $data ) {
		$image = get_post_thumbnail_id( $post->ID );
		if ( $image ) {
			$_image = wp_get_attachment_image_src( $image, 'large' );
			if ( is_array( $_image ) ) {
				$image = $_image[0];
			}

		}

		/* Use the below to allow for search site
			A URL to request data from remote site.
			http://bootstrap-table.wenzhixin.net.cn/documentation/#table-options
Note that the required server response format is different depending on whether the 'sidePagination' option is specified. See the following examples:
Without server-side pagination
With server-side pagination

		*/
	//$data['total'] = count($data['rows']);
	//$data['rows'][] = array(
		$data[] = array(
			'id' 		   => $post->ID,
			'title'        => $post->post_title,
			'artist'       => get_custom_taxonomy('artist', ' ', 'name' , $post->ID),
			'link'         => get_the_permalink( $post->ID ),
			'image' 	   => '<a href="https://artattendant.com/collection/?task=preview&post_id='.$post->ID.'">'.get_the_post_thumbnail( $post->ID, array(220,220) ).'</a>',
			'image_src'    => $image,
			'status' 	   => get_post_status ($post->ID ),
			'public_status'  => anagram_artwork_status( $post->ID ),
			'date_created' => get_post_meta( $post->ID, 'date_created',true),
			'edd_price'    => str_replace(',', '', str_replace('$', '', get_post_meta( $post->ID , 'edd_price', true ) ) ),
			'cost'         => str_replace(',', '', str_replace('$', '', get_post_meta( $post->ID , 'cost', true ) ) ),
			'inventory'    => get_post_meta( $post->ID, 'inventory', true ),
			'location'     => get_post_meta( $post->ID, 'location', true ),//get_custom_taxonomy('location', ' ', 'name' , $post->ID),
			'details'    => anagram_get_artwork_info_for_cardview( $post->ID ),
			'purchased_from'     => get_post_meta( $post->ID, 'purchased_from', true ),
			'consigned_sold_to'     => get_post_meta( $post->ID, 'consigned_sold_to', true ),
			//'actions' 	   => EDD_FES()->dashboard->product_list_actions($post->ID),
			//'date' 	       => EDD_FES()->dashboard->product_list_date($post->ID),
/*
			'excerpt'      => $post->post_excerpt,
			'tagline'      => get_post_meta( $post->ID, 'product_tagline', true ),
			'prices'       => edd_get_variable_prices( $post->ID ),
			'slug'         => $post->post_name,
			'cf'            => get_post_meta( $post->ID, 'cf_add_on', true ),
*/
		);



/*
		for ( $i = 1; $i <= 3; $i++ ) {
			foreach( array(
				'title',
				'text',
				'image'
			) as $field ) {
				if ( 'image' != $field ) {
					$field                       = "benefit_{$i}_{$field}";
					$data[ $post->ID ][ $field ] = get_post_meta( $post->ID, $field, true );
				}else{
					$field                       = "benefit_{$i}_{$field}";
					$_field = get_post_meta( $post->ID, $field, true );
					$url = false;

					if ( is_array( $_field ) && isset( $_field[ 'ID' ] )) {
						$img = $_field[ 'ID' ];
						$img = wp_get_attachment_image_src( $img, 'large' );

						if ( is_array( $img ) ) {

							$url = $img[0];
						}

					}
					$_field[ 'image_src' ] = $url;
					$data[ $post->ID ][ $field ] = $_field;
				}

			}

		}
*/

		return $data;

	}



}
