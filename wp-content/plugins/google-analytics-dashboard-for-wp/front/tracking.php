<?php
/**
 * Author: Alin Marcu
 * Author URI: https://deconf.com
 * Copyright 2017 Alin Marcu
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit();

if ( ! class_exists( 'GADWP_Tracking' ) ) {

	class GADWP_Tracking {

		private $gadwp;

		public $analytics;

		public $analytics_amp;

		public $tagmanager;

		public function __construct() {
			$this->gadwp = GADWP();

			$this->init();
		}

		public function tracking_code() { // Removed since 5.0
			GADWP_Tools::doing_it_wrong( __METHOD__, __( "This method is deprecated, read the documentation!", 'google-analytics-dashboard-for-wp' ), '5.0' );
		}

		public function init() {
			// excluded roles
			if ( GADWP_Tools::check_roles( $this->gadwp->config->options['track_exclude'], true ) || ( $this->gadwp->config->options['superadmin_tracking'] && current_user_can( 'manage_network' ) ) ) {
				return;
			}

			if ( 'universal' == $this->gadwp->config->options['tracking_type'] && $this->gadwp->config->options['tableid_jail'] ) {

				// Analytics
				require_once 'tracking-analytics.php';

				if ( 1 == $this->gadwp->config->options['ga_with_gtag'] ) {
					$this->analytics = new GADWP_Tracking_GlobalSiteTag();
				} else {
					$this->analytics = new GADWP_Tracking_Analytics();
				}

				if ( $this->gadwp->config->options['amp_tracking_analytics'] ) {
					$this->analytics_amp = new GADWP_Tracking_Analytics_AMP();
				}
			}

			if ( 'tagmanager' == $this->gadwp->config->options['tracking_type'] && $this->gadwp->config->options['web_containerid'] ) {

				// Tag Manager
				require_once 'tracking-tagmanager.php';
				$this->tagmanager = new GADWP_Tracking_TagManager();
			}
		}
	}
}
