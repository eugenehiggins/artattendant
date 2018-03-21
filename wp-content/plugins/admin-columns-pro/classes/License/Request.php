<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class ACP_License_Request {

	/**
	 * @var array
	 */
	protected $args;

	public function __construct() {
		$this->args = array(
			'timeout' => 15,
			'body'    => array(
				'wc-api' => 'software-licence-api',
			),
		);
	}

	public function set_body_param( $key, $value ) {
		$this->args['body'][ $key ] = $value;

		return $this;
	}

	public function set_body( array $body ) {
		$this->args['body'] = array();

		foreach ( $body as $key => $value ) {
			$this->set_body_param( $key, $body );
		}

		return $this;
	}

	public function set_request( $request ) {
		$this->set_body_param( 'request', $request );

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return $this
	 */
	public function set_licence_key( $key ) {
		$this->set_body_param( 'license_key', $key );

		return $this;
	}

	/**
	 * @return $this
	 */
	public function set_site_url() {
		$this->set_body_param( 'site_url', site_url() );

		return $this;
	}

}
