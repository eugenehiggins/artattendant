<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACA_ACF_Editing_User extends ACA_ACF_Editing {

	public function get_edit_value( $id ) {
		$user_ids = parent::get_edit_value( $id );

		if ( empty( $user_ids ) ) {
			return false;
		}

		$values = array();

		foreach ( (array) $user_ids as $k => $user_id ) {
			$values[ $user_id ] = ac_helper()->user->get_display_name( $user_id );
		}

		return $values;
	}

	public function get_view_settings() {
		$data = array(
			'type'          => 'select2_dropdown',
			'ajax_populate' => true,
		);

		$field = $this->column->get_field();

		if ( $field->get( 'multiple' ) ) {
			$data['multiple'] = true;
		} else if ( $field->get( 'allow_null' ) ) {
			$data['clear_button'] = true;
		}

		return $data;
	}

	public function get_ajax_options( $request ) {

		// ACF Free
		if ( ACA_ACF_API::is_free() ) {
			return acp_editing_helper()->get_users_list( array(
				'search' => $request['search'],
				'paged'  => $request['paged'],
			) );
		}

		// ACF Pro
		$acf_field = new acf_field_user();

		return $this->format_choices( $acf_field->get_ajax_query( array(
			's'         => $request['search'],
			'paged'     => $request['paged'],
			'field_key' => $this->column->get_field_hash(),
		) ) );
	}

}
