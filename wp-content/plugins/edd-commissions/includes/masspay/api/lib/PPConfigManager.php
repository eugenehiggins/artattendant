<?php
/**
 * PPConfigManager loads the SDK configuration file and
 * hands out appropriate config params to other classes
 */
require_once 'exceptions/PPConfigurationException.php';

class PPConfigManager
{

	private $config;
	/**
	 * @var PPConfigManager
	 */
	private static $instance;

	private function __construct()
	{
		$configFile = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . ".."
			. DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "sdk_config.ini";
		$this->load( $configFile );
	}

	// create singleton object for PPConfigManager
	public static function getInstance()
	{
		if ( !isset( self::$instance ) ) {
			self::$instance = new PPConfigManager();
		}

		return self::$instance;
	}

	//used to load the file
	private function load( $fileName )
	{
		if ( class_exists( 'Easy_Digital_Downloads' ) ) {
			global $edd_options;
			if ( edd_is_test_mode() ){
				$mode = 'yes';
			}
			else{
				$mode = 'no';
			}

			$this->config = array(
				'acct1.UserName'         => $mode == 'yes' ? $edd_options[ 'epap_test_api_username' ] : $edd_options[ 'epap_live_api_username' ],
				'acct1.Password'         => $mode == 'yes' ? $edd_options[ 'epap_test_api_password' ] : $edd_options[ 'epap_live_api_password' ],
				'acct1.Signature'        => $mode == 'yes' ? $edd_options[ 'epap_test_api_signature' ] : $edd_options[ 'epap_live_api_signature' ],
				'acct1.AppId'            => $mode == 'yes' ? $edd_options[ 'epap_test_app_id' ] : $edd_options[ 'epap_live_app_id' ],

				'service.Binding'        => 'SOAP',
				'service.EndPoint'       => $mode == 'yes' ? 'https://api-3t.sandbox.paypal.com/2.0/' : 'https://api-3t.paypal.com/2.0/',
				'service.RedirectURL'    => $mode == 'yes' ? 'https://sandbox.paypal.com/webscr&cmd=' : 'https://paypal.com/webscr&cmd=',
				'service.DevCentralURL'  => 'https://developer.paypal.com',
				'http.ConnectionTimeOut' => '10',
				'http.Retry'             => '5',
				'log.FileName'           => 'PayPal.log',
				'log.LogLevel'           => 'INFO',
				'log.LogEnabled'         => 'true',
			);
		} else {
			$this->config = @parse_ini_file( $fileName );
		}

		if ( $this->config == null || count( $this->config ) == 0 ) {
			throw new PPConfigurationException( "Config file $fileName not found", "303" );
		}
	}

	/**
	 * simple getter for configuration params
	 * If an exact match for key is not found,
	 * does a "contains" search on the key
	 */
	public function get( $searchKey )
	{

		if ( array_key_exists( $searchKey, $this->config ) ) {
			return $this->config[ $searchKey ];
		} else {
			$arr = array();
			foreach ( $this->config as $k => $v ) {
				if ( strstr( $k, $searchKey ) ) {
					$arr[ $k ] = $v;
				}
			}

			return $arr;
		}

	}

	/**
	 * Utility method for handling account configuration
	 * return config key corresponding to the API userId passed in
	 *
	 * If $userId is null, returns config keys corresponding to
	 * all configured accounts
	 */
	public function getIniPrefix( $userId = null )
	{

		if ( $userId == null ) {
			$arr = array();
			foreach ( $this->config as $key => $value ) {
				$pos = strpos( $key, '.' );
				if ( strstr( $key, "acct" ) ) {
					$arr[ ] = substr( $key, 0, $pos );
				}
			}

			return array_unique( $arr );
		} else {
			$iniPrefix = array_search( $userId, $this->config );
			$pos       = strpos( $iniPrefix, '.' );
			$acct      = substr( $iniPrefix, 0, $pos );

			return $acct;
		}
	}
}

?>