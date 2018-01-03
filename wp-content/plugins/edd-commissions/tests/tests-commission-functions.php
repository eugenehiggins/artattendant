<?php

class Tests_EDD_Commissions_Functions extends EDD_Commissions_Unitestcase {
	protected $object;

	protected static $_payment_id;
	protected static $_payment;
	protected static $_download_id;
	protected static $_user;
	protected static $_author;

	public static function wpSetUpBeforeClass() {

		edd_run_install();

		// Let's make some default users for later
		// `Author` can be used to make products that have commissions assigned to him
		$author = array(
			'user_login'  =>  'author',
			'roles'       =>  array( 'author' ),
			'user_pass'   => NULL,
		);

		wp_insert_user( $author ) ;

		// `Subscriber` can be used to check functions that should only work for commission recipients
		$subscriber = array(
			'user_login'  =>  'subscriber',
			'roles'       =>  array( 'subscriber' ),
			'user_pass'   => NULL,
		);

		wp_insert_user( $subscriber ) ;

		self::$_payment_id  = EDD_Helper_Payment::create_simple_payment();
		self::$_payment     = new EDD_Payment( self::$_payment_id );
		self::$_download_id = self::$_payment->downloads[ 0 ][ 'id' ];
		self::$_user        = get_user_by( 'login', 'subscriber' );
		self::$_author      = get_user_by( 'login', 'author' );

		// Set the product's rates
		$commissions_config = array(
			'type'    => 'percentage',
			'amount'  => '3,0',
			'user_id' => self::$_user->ID . ',' . self::$_author->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commisions_enabled', 'commissions_enabled' );
		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

	}

	public static function wpTearDownAfterClass() {
		EDD_Helper_Download::delete_download( self::$_download_id );
	}

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_eddc_calculate_payment_commissions(){

		$expected = array(
			array(
				'recipient'           => self::$_user->ID,
				'commission_amount'   => ( 3 / 100 ) * self::$_payment->cart_details[0]['price'],
				'rate'                => 3.0,
				'download_id'         => self::$_download_id,
				'payment_id'          => self::$_payment->ID,
				'currency'            => self::$_payment->currency,
				'has_variable_prices' => false,
				'price_id'            => NULL,
				'variation'           => NULL,
				'cart_item'           => self::$_payment->cart_details[0],
				'cart_index'          => 0,
			),
			array(
				'recipient'           => self::$_author->ID,
				'commission_amount'   => ( 0 / 100 ) * self::$_payment->cart_details[0]['price'],
				'rate'                => 0.0,
				'download_id'         => self::$_download_id,
				'payment_id'          => self::$_payment->ID,
				'currency'            => self::$_payment->currency,
				'has_variable_prices' => false,
				'price_id'            => NULL,
				'variation'           => NULL,
				'cart_item'           => self::$_payment->cart_details[0],
				'cart_index'          => 0,
			)
		);

		$actual = eddc_calculate_payment_commissions( self::$_payment->ID );

		$this->assertSame( $expected, $actual );
	}

	public function test_get_recipient_rate_item_level() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '3',
			'user_id' => self::$_user->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 3, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );

	}

	public function test_get_recipient_rate_product_level_zero() {
		global $edd_options;

		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		$edd_options['edd_commissions_allow_zero_value'] = 1;

		// Set a product level rate, zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '0',
			'user_id' => self::$_user->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 0, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
	}

	public function test_get_recipient_rate_user_level() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => self::$_user->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 2, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );

		// Set the user rate to 0
		update_user_meta( self::$_user->ID, 'eddc_user_rate', 0 );

		$this->assertEquals( 0, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );

	}

	public function test_get_recipient_rate_global_level() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', '' );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => self::$_user->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 1, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );

	}

	public function test_get_recipient_rate_item_level_multiuser() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', 2 );
		update_user_meta( self::$_author->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '3,0',
			'user_id' => self::$_user->ID . ',' . self::$_author->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 3, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
		$this->assertEquals( 0, eddc_get_recipient_rate( self::$_download_id, self::$_author->ID ) );

		// Swap the order, just to be sure
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '0,3',
			'user_id' => self::$_author->ID . ',' . self::$_user->ID,
		);

		$this->assertEquals( 3, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
		$this->assertEquals( 0, eddc_get_recipient_rate( self::$_download_id, self::$_author->ID ) );

	}

	public function test_get_recipient_rate_user_level_multiuser() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', 2 );
		update_user_meta( self::$_author->ID, 'eddc_user_rate', 2 );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => self::$_user->ID . ',' . self::$_author->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 2, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
		$this->assertEquals( 2, eddc_get_recipient_rate( self::$_download_id, self::$_author->ID ) );

		// Now rely on the user level for only 1 of the users
		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '1',
			'user_id' => self::$_user->ID . ',' . self::$_author->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 1, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
		$this->assertEquals( 2, eddc_get_recipient_rate( self::$_download_id, self::$_author->ID ) );
	}

	public function test_get_recipient_rate_global_level_multiuser() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', '' );
		update_user_meta( self::$_author->ID, 'eddc_user_rate', '' );

		// Set a product level rate, non-zero
		$commissions_config = array(
			'type'    => 'flat',
			'amount'  => '',
			'user_id' => self::$_user->ID . ',' . self::$_author->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		$this->assertEquals( 1, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
		$this->assertEquals( 1, eddc_get_recipient_rate( self::$_download_id, self::$_author->ID ) );

		// Now rely on a user having a rate, and the other being global
		update_user_meta( self::$_author->ID, 'eddc_user_rate', 2 );
		$this->assertEquals( 1, eddc_get_recipient_rate( self::$_download_id, self::$_user->ID ) );
		$this->assertEquals( 2, eddc_get_recipient_rate( self::$_download_id, self::$_author->ID ) );
	}

	public function test_get_recipient_rate_user_no_download() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', 2 );
		$this->assertEquals( 2, eddc_get_recipient_rate( 0, self::$_user->ID ) );

		update_user_meta( self::$_user->ID, 'eddc_user_rate', 0 );
		$this->assertEquals( 0, eddc_get_recipient_rate( 0, self::$_user->ID ) );
	}

	public function test_get_recipient_rate_global_no_download() {
		global $edd_options;
		// Set a global rate
		$edd_options['edd_commissions_default_rate'] = 1;

		// Set a user level rate
		update_user_meta( self::$_user->ID, 'eddc_user_rate', '' );
		$this->assertEquals( 1, eddc_get_recipient_rate( 0, self::$_user->ID ) );
	}

}
