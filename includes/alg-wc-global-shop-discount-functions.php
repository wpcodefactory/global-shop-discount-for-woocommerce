<?php
/**
 * Global Shop Discount for WooCommerce - Functions
 *
 * @version 1.9.2
 * @since   1.6.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Global_Shop_Discount\Functions
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_gsd_get_product_discount_groups' ) ) {
	/**
	 * Get product discount groups.
	 *
	 * @version 1.9.2
	 * @since   1.9.2
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array|false The product discount groups or false if not available.
	 */
	function alg_wc_gsd_get_product_discount_groups( $product ) {
		return (
			function_exists( 'alg_wc_global_shop_discount' ) ?
			alg_wc_global_shop_discount()->core->get_product_discount_groups( $product ) :
			false
		);
	}
}

if ( ! function_exists( 'alg_wc_gsd_is_discount_product' ) ) {
	/**
	 * Is discount product.
	 *
	 * @version 1.9.2
	 * @since   1.9.2
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return bool True if the product has a plugin discount, false otherwise.
	 */
	function alg_wc_gsd_is_discount_product( $product ) {
		return (
			function_exists( 'alg_wc_global_shop_discount' ) ?
			alg_wc_global_shop_discount()->core->is_gsd_product( $product ) :
			false
		);
	}
}

if ( ! function_exists( 'alg_wc_gsd_get_product_ids' ) ) {
	/**
	 * Get product IDs.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 *
	 * @param array $product_query_args The product query arguments.
	 * @param bool  $incl_on_sale       Whether to include on-sale products.
	 * @param bool  $use_transient      Whether to use transient for caching.
	 *
	 * @return array|false The product IDs or false if not available.
	 */
	function alg_wc_gsd_get_product_ids( $product_query_args = array( 'limit' => -1 ), $incl_on_sale = true, $use_transient = false ) {
		return (
			function_exists( 'alg_wc_global_shop_discount' ) ?
			alg_wc_global_shop_discount()->core->get_gsd_product_ids( $product_query_args, $incl_on_sale, $use_transient ) :
			false
		);
	}
}
