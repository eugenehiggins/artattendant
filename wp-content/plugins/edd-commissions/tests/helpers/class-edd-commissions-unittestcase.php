<?php

class EDD_Commissions_Unitestcase extends WP_UnitTestCase {

	public static function tearDownAfterClass() {
		self::_delete_all_data();

		return parent::tearDownAfterClass();
	}

	protected static function _delete_all_data() {
		global $wpdb;

		$commission_db       = new EDDC_DB();
		$commissions_meta_db = new EDDC_Meta_DB();

		foreach ( array(
			$commission_db->table_name,
			$commissions_meta_db->table_name,
		) as $table ) {
			$wpdb->query( "TRUNCATE TABLE {$table}" );
		}

	}
}
