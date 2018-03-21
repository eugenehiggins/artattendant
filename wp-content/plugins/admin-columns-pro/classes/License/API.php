<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Columns Pro website connection API
 *
 * @since 3.0
 */
class ACP_License_API {

	const URL = 'https://www.admincolumns.com';

	const PROXY = 'https://api.admincolumns.com';

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var bool
	 */
	protected $use_proxy = true;

	/**
	 * Request args
	 *
	 * @since 3.1.2
	 * @var $request_arg array
	 */
	protected $request_args;

	/**
	 * Constructor
	 *
	 * @since 3.1.2
	 */
	public function __construct() {
		$this->request_args = array(
			'timeout' => 15,
			'body'    => array(
				'wc-api' => 'software-licence-api',
			),
		);
	}

	/**
	 * Set request arg
	 *
	 * @since 3.1.2
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function set_request_arg( $key, $value ) {
		$this->request_args[ $key ] = $value;

		return $this;
	}

	/**
	 * Get request args
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get_request_args() {
		return $this->request_args;
	}

	/**
	 * @return string
	 */
	public function get_url() {
		return $this->url;
	}

	/**
	 * @param string $url
	 *
	 * @return $this
	 */
	public function set_url( $url ) {
		$this->url = $url;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function is_use_proxy() {
		return $this->use_proxy;
	}

	/**
	 * @param bool $use_proxy
	 *
	 * @return $this
	 */
	public function set_use_proxy( $use_proxy ) {
		$this->use_proxy = $use_proxy;

		return $this;
	}

	/**
	 * Get the URL to call
	 *
	 * @return string
	 */
	private function get_request_url() {
		if ( null !== $this->url ) {
			return $this->url;
		}

		if ( $this->use_proxy ) {
			return self::PROXY;
		}

		return self::URL;
	}

	/**
	 * API Request
	 *
	 * @since 1.1
	 *
	 * @param string $action
	 * @param array  $body
	 * @param string $format
	 *
	 * @return WP_Error|object API Response
	 */
	public function request( $action, array $body, $format = 'json' ) {
		$body['request'] = $action;

		foreach ( $body as $k => $v ) {
			$this->request_args['body'][ $k ] = $v;
		}

		$response = wp_remote_post( $this->get_request_url(), $this->request_args );
		$result = wp_remote_retrieve_body( $response );

		// retry with proxy disabled
		if ( empty( $result ) && $this->use_proxy ) {
			$this->use_proxy = false;

			return $this->request( $action, $body );
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// TODO: format is a strange thing, it should always be JSON
		if ( 'json' === $format ) {
			$result = json_decode( $result );

			if ( isset( $result->error ) ) {
				return new WP_Error( $result->code, $result->message );
			}
		}

		if ( empty( $result ) ) {
			return new WP_Error( 'empty_response', __( 'Empty response from API.', 'codepress-admin-columns' ) );
		}

		return $result;
	}

}
