<?php
/**
 * Global Shop Discount for WooCommerce - Functions
 *
 * @version 1.6.0
 * @since   1.6.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'alg_wc_gsd_get_product_ids' ) ) {
	/**
	 * alg_wc_gsd_get_product_ids.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function alg_wc_gsd_get_product_ids( $product_query_args = array( 'limit' => -1 ), $incl_on_sale = true, $use_transient = false ) {
		return ( function_exists( 'alg_wc_global_shop_discount' ) ? alg_wc_global_shop_discount()->core->get_gsd_product_ids( $product_query_args, $incl_on_sale, $use_transient ) : false );
	}
}
