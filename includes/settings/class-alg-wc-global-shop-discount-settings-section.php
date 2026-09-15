<?php
/**
 * Global Shop Discount for WooCommerce - Section Settings
 *
 * @version 2.0.0
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Global_Shop_Discount\Settings
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Global_Shop_Discount_Settings_Section' ) ) :

	/**
	 * Alg_WC_Global_Shop_Discount_Settings_Section class.
	 *
	 * @version 2.0.0
	 * @since   1.0.0
	 */
	class Alg_WC_Global_Shop_Discount_Settings_Section {

		/**
		 * Section ID.
		 *
		 * @version 2.0.0
		 * @since   2.0.0
		 *
		 * @var string
		 */
		public $id;

		/**
		 * Section description.
		 *
		 * @version 2.0.0
		 * @since   2.0.0
		 *
		 * @var string
		 */
		public $desc;

		/**
		 * Constructor.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 */
		public function __construct() {
			add_filter(
				'woocommerce_get_sections_alg_wc_global_shop_discount',
				array( $this, 'settings_section' )
			);
			add_filter(
				'woocommerce_get_settings_alg_wc_global_shop_discount_' . $this->id,
				array( $this, 'get_settings' ),
				PHP_INT_MAX
			);
		}

		/**
		 * Settings section callback.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 *
		 * @param array $sections Array of existing sections.
		 *
		 * @return array Modified array of sections.
		 */
		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}
	}

endif;
