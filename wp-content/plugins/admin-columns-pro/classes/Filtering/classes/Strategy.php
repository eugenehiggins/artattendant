<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_Filtering_Strategy extends ACP_Strategy {

	/**
	 * @var ACP_Filtering_Model
	 */
	protected $model;

	/**
	 * @param ACP_Filtering_Model $model
	 */
	public function __construct( ACP_Filtering_Model $model ) {
		$this->model = $model;
	}

	/**
	 * @return ACP_Filtering_Model
	 */
	public function get_model() {
		return $this->model;
	}

	/**
	 * @param string $field
	 *
	 * @return array|false
	 */
	abstract public function get_values_by_db_field( $field );

	/**
	 * Used to add a callback for handling filter request
	 *
	 * @return void
	 */
	abstract public function handle_request();

}
