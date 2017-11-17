<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACA_ACF_Free_Setting_Field extends ACA_ACF_Setting_Field {

	/**
	 * @param int[] $group_ids ACF (version 4) field group ID's
	 *
	 * @return array Group list
	 */
	protected function get_option_groups( $group_ids ) {
		$option_groups = array();

		foreach ( $group_ids as $group_id ) {
			$options = array();

			$fields = apply_filters( 'acf/field_group/get_fields', array(), $group_id );

			$group = $this->get_acf_group_by_id( $group_id );

			foreach ( $fields as $field ) {
				if ( in_array( $field['type'], array( 'tab' ) ) ) {
					continue;
				}

				$options[ $field['key'] ] = $field['label'] ? $field['label'] : __( 'empty label', 'codepress-admin-columns' );
			}

			if ( ! empty( $options ) ) {

				natcasesort( $options );

				$option_groups[ $group_id ] = array(
					'title'   => $group['title'],
					'options' => $options,
				);
			}
		}

		return $option_groups;
	}

	/**
	 * @param int $id Group ID
	 *
	 * @return array|false Field group
	 */
	private function get_acf_group_by_id( $id ) {
		$groups = apply_filters( 'acf/get_field_groups', array() );

		foreach ( $groups as $group ) {
			if ( $id == $group['id'] ) {
				return $group;
			}
		}

		return false;
	}

}
