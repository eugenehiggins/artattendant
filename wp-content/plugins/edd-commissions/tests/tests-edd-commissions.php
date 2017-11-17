<?php

class Tests_EDD_Commissions extends EDD_Commissions_Unitestcase {
	protected $object;

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @covers Easy_Digital_Downloads::setup_constants
	 */
	public function test_constants() {
		// Plugin File
		$path = str_replace( 'tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( EDDC_PLUGIN_FILE, $path .'edd-commissions.php' );

		// Plugin Folder Path
		$path = str_replace( 'tests/', '', plugin_dir_path( __FILE__ ) );
		$path = substr( $path, 0, -1 );
 		$dir  = substr( EDDC_PLUGIN_DIR, 0, -1 );
 		$this->assertSame( $dir, $path );

		// Plugin Folder URL
		$url = str_replace( 'tests/', '', plugin_dir_url( EDDC_PLUGIN_FILE ) );
		$this->assertSame( EDDC_PLUGIN_URL, $url );
	}
}
