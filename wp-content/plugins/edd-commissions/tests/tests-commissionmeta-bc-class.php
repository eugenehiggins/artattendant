<?php

class Tests_EDD_Commissions_Meta_Backwards_Compatibility extends EDD_Commissions_Unitestcase {
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
		edd_commissions()->commission_meta_db->add_meta( self::$_commission->id, '_edd_commission_legacy_id', 8675309 );

	}

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_post_meta_commission_info() {
		$expected = array(
			'user_id'  => self::$_commission->user_id,
			'rate'     => self::$_commission->rate,
			'amount'   => self::$_commission->amount,
			'currency' => self::$_commission->currency,
			'type'     => self::$_commission->type,
		);

		$actual = get_post_meta( self::$_commission->id, '_edd_commission_info', true );

		$this->assertSame( $expected, $actual );
	}

	public function test_get_post_meta_commission_info_legacy() {
		$expected = array(
			'user_id'  => self::$_commission->user_id,
			'rate'     => self::$_commission->rate,
			'amount'   => self::$_commission->amount,
			'currency' => self::$_commission->currency,
			'type'     => self::$_commission->type,
		);

		$actual = get_post_meta( 8675309, '_edd_commission_info', true );

		$this->assertSame( $expected, $actual );
	}

	public function test_get_post_meta_commission_info_null() {
		$actual = get_post_meta( 8675310, '_edd_commission_info', true );
		$this->assertEmpty( $actual );
	}

	public function test_update_post_meta_commission_payment() {
		update_post_meta( self::$_commission->id, '_edd_commission_payment_id', '100' );
		$commission = new EDD_Commission( self::$_commission->id );
		$this->assertSame( $commission->payment_id, '100' );
	}

	public function test_add_post_meta_id() {
		$added = add_post_meta( self::$_commission->id, '_edd_all_access_info', 'test' );
		$this->assertNotEmpty( $added );
	}

	public function test_add_post_meta_value() {
		add_post_meta( self::$_commission->id, '_edd_all_access_info', 'test' );
		$this->assertSame( 'test', self::$_commission->get_meta( '_edd_all_access_info', true ) );
	}

	public function test_delete_post_meta_false() {
		$this->assertFalse( delete_post_meta( self::$_commission->id, '_edd_all_access_info', 'test', true ) );
	}

	public function test_delete_post_meta_falsetrue() {
		add_post_meta( self::$_commission->id, '_edd_all_access_info', 'test' );
		$this->assertTrue( delete_post_meta( self::$_commission->id, '_edd_all_access_info', 'test', true ) );
	}

	public function test_delete_commission_info() {
		$this->assertFalse( delete_post_meta( self::$_commission->id, '_edd_commission_info' ) );
	}

	public function test_add_commission_info() {
		$this->assertFalse( delete_post_meta( self::$_commission->id, '_edd_commission_info' ) );
	}

}
