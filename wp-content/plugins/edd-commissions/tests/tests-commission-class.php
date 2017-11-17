<?php

class Tests_EDD_Commissions_Class extends EDD_Commissions_Unitestcase {
	protected $object;

	public static $_payment_id;
	public static $_payment;
	public static $_download_id;
	public static $_download;
	public static $_user;
	public static $_author;
	public static $_commission;

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
		self::$_download    = new EDD_Download( self::$_download_id );
		self::$_user        = get_user_by( 'login', 'subscriber' );
		self::$_author      = get_user_by( 'login', 'author' );

		// Set the product's rates
		$commissions_config = array(
			'type'    => 'percentage',
			'amount'  => '10',
			'user_id' => self::$_author->ID,
		);

		update_post_meta( self::$_download_id, '_edd_commisions_enabled', 'commissions_enabled' );
		update_post_meta( self::$_download_id, '_edd_commission_settings', $commissions_config );

		self::$_payment->status = 'publish';
		self::$_payment->save();

		$commissions = eddc_get_commissions( array( 'payment_id' => self::$_payment->ID ) );
		self::$_commission = eddc_get_commission( $commissions[0] );

	}

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_commission_ID() {
		$this->assertTrue( ! empty( self::$_commission->ID ) );
	}

	public function test_user_ID() {
		$this->assertEquals( self::$_author->ID, self::$_commission->user_ID );
	}

	public function test_description() {
		$this->assertEquals( 'admin@example.org - ' . self::$_download->post_title, self::$_commission->description );
	}

	public function test_rate() {
		$this->assertEquals( 10, self::$_commission->rate );
	}

	public function test_type() {
		$this->assertEquals( 'percentage', self::$_commission->type );
	}

	public function test_amount() {
		$expected_amount = self::$_download->price * .10;
		$this->assertEquals( $expected_amount, self::$_commission->amount );
	}

	public function test_currency() {
		$this->assertEquals( 'USD', self::$_commission->currency );
	}

	public function test_download_ID() {
		$this->assertEquals( self::$_download_id, self::$_commission->download_ID );
	}

	public function test_payment_ID() {
		$this->assertEquals( self::$_payment_id, self::$_commission->payment_ID );
	}

	public function test_status() {
		$this->assertEquals( 'unpaid', self::$_commission->status );
	}

	public function test_is_renewal() {
		$this->assertFalse( self::$_commission->is_renewal );
	}

	public function test_download_variation() {
		$this->assertEmpty( self::$_commission->download_variation );
	}

	public function test_price_id() {
		$this->assertEmpty( self::$_commission->price_id );
	}

	public function test_cart_index() {
		$this->assertEquals( 0, self::$_commission->cart_index );
	}

	public function test_update_status_success() {
		$this->assertTrue( self::$_commission->update_status( 'paid' ) );
	}

	public function test_update_status_false_empty() {
		$this->assertFalse( self::$_commission->update_status( '' ) );
	}

}
