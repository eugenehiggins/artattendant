<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACP_Filtering_Cache_Model extends ACP_Filtering_Cache {

	/**
	 * @var ACP_Filtering_Model
	 */
	protected $model;

	public function __construct( ACP_Filtering_Model $model ) {
		$this->model = $model;

		parent::__construct( $this->get_key() );
	}

	protected function get_key() {
		$column = $this->model->get_column();

		return $column->get_list_screen()->get_storage_key() . $column->get_name();
	}

	public function put_if_expired() {
		if ( $this->is_expired() ) {
			$seconds = apply_filters( 'acp/filtering/cache/seconds', 10, $this->model );

			$this->put( $this->model->get_filtering_data(), $seconds );
		}
	}

}
