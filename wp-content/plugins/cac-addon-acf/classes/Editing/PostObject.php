<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_PostObject extends ACA_ACF_Editing {

	public function get_edit_value( $post_id ) {
		$values = array();
		$ids = $this->column->get_raw_value( $post_id );

		if ( ! $ids ) {
			return $values;
		}

		// ACF Free
		if ( ACA_ACF_API::is_free() ) {
			foreach ( $ids as $id ) {
				$values[ $id ] = html_entity_decode( get_the_title( $id ) );
			}

			return $values;
		}

		// ACF Pro
		$acf_field = new acf_field_post_object();

		foreach ( $ids as $id ) {
			$values[ $id ] = html_entity_decode( $acf_field->get_post_title( $id, $this->column->get_acf_field(), $post_id ) );
		}

		return $values;
	}

	public function get_view_settings() {
		$data = parent::get_view_settings();

		$data['type'] = 'select2_dropdown';
		$data['ajax_populate'] = true;
		$data['store_values'] = false;

		if ( $this->column->get_field()->get( 'multiple' ) ) {
			$data['multiple'] = true;
		} else if ( $this->column->get_field()->get( 'allow_null' ) ) {
			$data['clear_button'] = true;
		}

		return $data;
	}

	public function get_ajax_options( $request ) {

		// ACF Free
		if ( ACA_ACF_API::is_free() ) {
			return acp_editing_helper()->get_posts_list( array(
				's'         => $request['search'],
				'post_type' => $this->get_post_type(),
				'paged'     => $request['paged'],
			) );
		}

		// ACF Pro
		$acf_field = new acf_field_post_object();

		$args = array(
			's'         => $request['search'],
			'field_key' => $this->column->get_field_hash(),
			'post_id'   => $request['object_id'],
			'paged'     => $request['paged'],
			'post_type' => $this->get_post_type(),
		);

		return $this->format_choices( $acf_field->get_ajax_query( $args ) );
	}

	// Helper

	/**
	 * @return array
	 */
	protected function get_post_type() {
		$post_types = $this->column->get_field()->get( 'post_type' );

		if ( ! $post_types || in_array( 'all', $post_types ) || in_array( 'any', $post_types ) ) {
			$post_types = array( 'any' );
		}

		return $post_types;
	}

}
