<?php
/**
 * Plugin Name: Sitewide Discount for WooCommerce: Apply Discount to All Products
 * Plugin URI: https://wpfactory.com/item/global-shop-discount-for-woocommerce/
 * Description: Add global shop discount to all WooCommerce products. Beautifully.
 * Version: 2.3.0
 * Author: WPFactory
 * Author URI: https://wpfactory.com
 * Requires at least: 4.8
 * Text Domain: global-shop-discount-for-woocommerce
 * Domain Path: /langs
 * WC tested up to: 11.1
 * Requires Plugins: woocommerce
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WPFactory\WC_Global_Shop_Discount
 */

defined( 'ABSPATH' ) || exit;

if ( 'global-shop-discount-for-woocommerce.php' === basename( __FILE__ ) ) {
	if ( ! function_exists( 'alg_wc_global_shop_discount_is_pro_activated' ) ) {
		/**
		 * Check if Pro plugin version is activated.
		 *
		 * @version 2.3.0
		 * @since   1.4.0
		 */
		function alg_wc_global_shop_discount_is_pro_activated() {
			$plugin = 'global-shop-discount-for-woocommerce-pro/global-shop-discount-for-woocommerce-pro.php';
			return (
				in_array( $plugin, (array) get_option( 'active_plugins', array() ), true ) ||
				(
					is_multisite() &&
					array_key_exists( $plugin, (array) get_site_option( 'active_sitewide_plugins', array() ) )
				)
			);
		}
	}

	if ( alg_wc_global_shop_discount_is_pro_activated() ) {
		defined( 'ALG_WC_GLOBAL_SHOP_DISCOUNT_FILE_FREE' ) || define( 'ALG_WC_GLOBAL_SHOP_DISCOUNT_FILE_FREE', __FILE__ );
		return;
	}
}

/**
 * Plugin version.
 *
 * @version 1.0.0
 * @since   1.0.0
 */
defined( 'ALG_WC_GLOBAL_SHOP_DISCOUNT_VERSION' ) || define( 'ALG_WC_GLOBAL_SHOP_DISCOUNT_VERSION', '2.3.0' );

/**
 * Plugin file.
 *
 * @version 1.0.0
 * @since   1.0.0
 */
defined( 'ALG_WC_GLOBAL_SHOP_DISCOUNT_FILE' ) || define( 'ALG_WC_GLOBAL_SHOP_DISCOUNT_FILE', __FILE__ );

/**
 * Main class file.
 *
 * @version 1.0.0
 * @since   1.0.0
 */
require_once plugin_dir_path( __FILE__ ) . 'includes/class-alg-wc-global-shop-discount.php';

if ( ! function_exists( 'alg_wc_global_shop_discount' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Global_Shop_Discount to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function alg_wc_global_shop_discount() {
		return Alg_WC_Global_Shop_Discount::instance();
	}
}

/**
 * Initialization.
 *
 * @version 1.0.0
 * @since   1.0.0
 */
add_action( 'plugins_loaded', 'alg_wc_global_shop_discount' );
