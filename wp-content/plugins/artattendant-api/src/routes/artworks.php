<?php
/**
 * Endpoints for artworks.
 *
 * @package   artattendant\artattendant_api\
 * @author    Josh Pollock <Josh@JoshPress.net>
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 Josh Pollock
 */

namespace artattendant\artattendant_api\routes;


class artworks extends endpoints {


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
	 * Get multiple artworks
	 *
	 * @since 0.0.1
	 *
	 * @param \WP_REST_Request $request Full details about the request
	 *
	 * @return \WP_HTTP_Response
	 */
	public function get_items( $request ) {
		$params = $request->get_params();

		$args[ 'post_status' ] = 'draft';

		if ( $params[ 'slug' ] ) {
			$args[ 'name' ] = $params[ 'slug' ];
			$args[ 'post_type' ] = $this->post_type;
		}elseif( $params[ 'soon' ] ) {
			$args[ 'meta_key' ] = 'edd_coming_soon';
			$args[ 'meta_value' ] = true;
		}else{
			$args = $this->query_args( $params );
		}


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

		$data['total'] = count($data);

		$data['rows'][] = array(
			'id' 		   => $post->ID,
			'name'         => $post->post_title,
			'link'         => get_the_permalink( $post->ID ),
			'image' => get_the_post_thumbnail( $post->ID, 'thumbnail' ),
			'image_src'    => $image,
			'status' 	   => EDD_FES()->dashboard->product_list_status($post->ID),
			'prices'       => edd_get_variable_prices( $post->ID ),
			'purchases' 	   => 'hi',
			'actions' 	   => '',
			'date' 	   => '',
									/*
			'excerpt'      => $post->post_excerpt,
			'tagline'      => get_post_meta( $post->ID, 'product_tagline', true ),

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
