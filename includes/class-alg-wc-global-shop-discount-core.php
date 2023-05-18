<?php
/**
 * Global Shop Discount for WooCommerce - Core Class
 *
 * @version 1.6.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Global_Shop_Discount_Core' ) ) :

class Alg_WC_Global_Shop_Discount_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 *
	 * @todo    (feature) fee instead of discount
	 * @todo    (feature) regular price coefficient (`$this->product_get_regular_price_filter, 'woocommerce_variation_prices_regular_price', 'woocommerce_product_variation_get_regular_price'`)
	 * @todo    (feature) "direct price"
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_global_shop_discount_plugin_enabled', 'yes' ) ) {
			if ( $this->is_frontend() ) {
				$this->init();
				$this->price_hooks( PHP_INT_MAX, false );
				add_shortcode( 'alg_wc_gsd_products', array( $this, 'products_shortcode' ) );
			}
		}
	}

	/**
	 * get_gsd_product_ids.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 *
	 * @todo    (dev) modify `wc_product_meta_lookup` instead?
	 * @todo    (dev) args: `$transient_expiration = DAY_IN_SECONDS`
	 */
	function get_gsd_product_ids( $product_query_args = array( 'limit' => -1 ), $incl_on_sale = true, $use_transient = false ) {

		$md5 = md5( serialize( array_merge( $product_query_args, array( 'alg_wc_gsd_incl_on_sale' => $incl_on_sale ) ) ) );

		if ( ! isset( $this->gsd_products[ $md5 ] ) ) {

			$transient = false;

			if ( $use_transient ) {
				$transient = get_transient( 'alg_wc_gsd_products_' . $md5 );
			}

			if ( false === $transient ) {

				$this->gsd_products[ $md5 ] = array();

				if ( ! isset( $this->groups ) ) {
					$this->init();
				}

				$all_products = wc_get_products( $product_query_args );

				foreach ( $all_products as $product ) {

					if (
						( $incl_on_sale && $product->is_on_sale() ) ||
						( $price = $product->get_price() ) != $this->add_global_shop_discount( $price, $product, 'price' )
					) {
						$this->gsd_products[ $md5 ][] = $product->get_id();
					}

				}

				if ( $use_transient ) {
					set_transient( 'alg_wc_gsd_products_' . $md5, $this->gsd_products[ $md5 ], DAY_IN_SECONDS );
				}

			} else {

				$this->gsd_products[ $md5 ] = $transient;

			}

		}

		return $this->gsd_products[ $md5 ];

	}

	/**
	 * `[alg_wc_gsd_products]` shortcode.
	 *
	 * @version 1.6.0
	 * @since   1.5.1
	 *
	 * @todo    (dev) use `get_gsd_product_ids()`
	 * @todo    (dev) `$atts`: `block_size`?
	 * @todo    (dev) `$atts`: `transient_expiration`?
	 * @todo    (dev) use `wc_get_products()`
	 */
	function products_shortcode( $atts ) {

		$product_ids_on_sale = false;
		$do_use_transient    = ( isset( $atts['use_transient'] ) && filter_var( $atts['use_transient'], FILTER_VALIDATE_BOOLEAN ) );

		// Try cache
		if ( $do_use_transient ) {
			$product_ids_on_sale = get_transient( 'alg_wc_gsd_products_onsale' );
		}

		// Get on-sale products
		if ( false === $product_ids_on_sale ) {

			$product_ids_on_sale = array();
			$offset              = 0;
			$block_size          = 1024;
			while ( true ) {
				$query_args = array(
					'post_type'      => 'product',
					'fields'         => 'ids',
					'offset'         => $offset,
					'posts_per_page' => $block_size,
				);
				$query = new WP_Query( $query_args );
				if ( ! $query->have_posts() ) {
					break;
				}
				foreach ( $query->posts as $product_id ) {
					if ( ( $product = wc_get_product( $product_id ) ) && $product->is_on_sale() ) {
						$product_ids_on_sale[] = $product_id;
					}
				}
				$offset += $block_size;
			}

			// Save cache
			if ( $do_use_transient ) {
				set_transient( 'alg_wc_gsd_products_onsale', $product_ids_on_sale, DAY_IN_SECONDS );
			}

		}

		// Pass additional atts
		$_atts = '';
		if  ( ! empty( $atts ) ) {
			$_atts = ' ' . implode( ' ', array_map(
				function ( $v, $k ) {
					return sprintf( '%s="%s"', $k, $v );
				},
				$atts,
				array_keys( $atts )
			) );
		}

		// Run [products] shortcode
		return do_shortcode( '[products ids="' . implode( ',', $product_ids_on_sale ) . '"' . $_atts .  ']' );

	}

	/**
	 * init.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function init() {

		// WC version and price filters
		$this->is_wc_version_below_3_0_0         = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
		$this->product_get_price_filter          = ( $this->is_wc_version_below_3_0_0 ? 'woocommerce_get_price'         : 'woocommerce_product_get_price' );
		$this->product_get_sale_price_filter     = ( $this->is_wc_version_below_3_0_0 ? 'woocommerce_get_sale_price'    : 'woocommerce_product_get_sale_price' );
		$this->product_get_regular_price_filter  = ( $this->is_wc_version_below_3_0_0 ? 'woocommerce_get_regular_price' : 'woocommerce_product_get_regular_price' );

		// Groups
		$total_groups  = apply_filters( 'alg_wc_global_shop_discount_total_groups', 1 );
		$group_options = array(
			'enabled'          => 'yes',
			'coefficient_type' => 'percent',
			'coefficient'      => 0,
			'dates_incl'       => '',
			'product_scope'    => 'all',
			'products_incl'    => array(),
			'products_excl'    => array(),
			'users_incl'       => array(),
			'users_excl'       => array(),
		);
		$taxonomies = get_option( 'alg_wc_global_shop_discount_taxonomies', array( 'product_cat', 'product_tag' ) );
		foreach ( $taxonomies as $taxonomy ) {
			$id = $this->get_taxonomy_option_id( $taxonomy );
			$group_options[ "{$id}_incl" ] = array();
			$group_options[ "{$id}_excl" ] = array();
		}
		foreach ( $group_options as $option => $default ) {
			$this->groups[ $option ] = array_slice( get_option( 'alg_wc_global_shop_discount_' . $option, array() ), 0, $total_groups, true );
			for ( $i = 1; $i <= $total_groups; $i++ ) {
				$this->groups[ $option ][ $i ] = ( isset( $this->groups[ $option ][ $i ] ) ? $this->groups[ $option ][ $i ] : $default );
			}
		}

		// Options
		$this->do_stop_on_first_discount_group = ( 'yes' === get_option( 'alg_wc_global_shop_discount_stop_on_first_discount_group', 'yes' ) );

	}

	/**
	 * is_frontend.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function is_frontend() {
		return ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) );
	}

	/**
	 * price_hooks.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (feature) global shipping discount
	 */
	function price_hooks( $priority, $include_shipping = true, $action_func = 'add_filter' ) {

		// Prices
		$action_func( $this->product_get_price_filter,                       array( $this, 'change_price' ),              $priority, 2 );
		$action_func( $this->product_get_sale_price_filter,                  array( $this, 'change_price' ),              $priority, 2 );
		$action_func( $this->product_get_regular_price_filter,               array( $this, 'change_price' ),              $priority, 2 );

		// Variations
		$action_func( 'woocommerce_variation_prices_price',                  array( $this, 'change_price' ),              $priority, 2 );
		$action_func( 'woocommerce_variation_prices_regular_price',          array( $this, 'change_price' ),              $priority, 2 );
		$action_func( 'woocommerce_variation_prices_sale_price',             array( $this, 'change_price' ),              $priority, 2 );
		$action_func( 'woocommerce_get_variation_prices_hash',               array( $this, 'get_variation_prices_hash' ), $priority, 3 );
		if ( ! $this->is_wc_version_below_3_0_0 ) {
			$action_func( 'woocommerce_product_variation_get_price',         array( $this, 'change_price' ),              $priority, 2 );
			$action_func( 'woocommerce_product_variation_get_regular_price', array( $this, 'change_price' ),              $priority, 2 );
			$action_func( 'woocommerce_product_variation_get_sale_price',    array( $this, 'change_price' ),              $priority, 2 );
		}

		// Shipping
		if ( $include_shipping ) {
			$action_func( 'woocommerce_package_rates',                       array( $this, 'change_price_shipping' ),     $priority, 2 );
		}

		// Grouped products
		$action_func( 'woocommerce_get_price_including_tax',                 array( $this, 'change_price_grouped' ),      $priority, 3 );
		$action_func( 'woocommerce_get_price_excluding_tax',                 array( $this, 'change_price_grouped' ),      $priority, 3 );

	}

	/**
	 * get_product_id_or_variation_parent_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_product_id_or_variation_parent_id( $_product ) {
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( $this->is_wc_version_below_3_0_0 ) {
			return $_product->id;
		} else {
			return ( $_product->is_type( 'variation' ) ) ? $_product->get_parent_id() : $_product->get_id();
		}
	}

	/**
	 * get_product_display_price.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_product_display_price( $_product, $price = '', $qty = 1 ) {
		$minus_sign = '';
		if ( $price < 0 ) {
			$minus_sign = '-';
			$price *= -1;
		}
		$display_price = ( $this->is_wc_version_below_3_0_0 ?
			$_product->get_display_price( $price, $qty ) : wc_get_price_to_display( $_product, array( 'price' => $price, 'qty' => $qty ) ) );
		return $minus_sign . $display_price;
	}

	/**
	 * change_price.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function change_price( $price, $_product ) {
		$_current_filter = current_filter();
		if ( in_array( $_current_filter, array( $this->product_get_price_filter, 'woocommerce_variation_prices_price', 'woocommerce_product_variation_get_price' ) ) ) {
			return $this->add_global_shop_discount( $price, $_product, 'price' );
		} elseif ( in_array( $_current_filter, array( $this->product_get_sale_price_filter, 'woocommerce_variation_prices_sale_price', 'woocommerce_product_variation_get_sale_price' ) ) ) {
			return $this->add_global_shop_discount( $price, $_product, 'sale_price' );
		} else {
			return $price;
		}
	}

	/**
	 * change_price_grouped.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function change_price_grouped( $price, $qty, $_product ) {
		if ( $_product->is_type( 'grouped' ) ) {
			foreach ( $_product->get_children() as $child_id ) {
				$the_price   = get_post_meta( $child_id, '_price', true );
				$the_product = wc_get_product( $child_id );
				$the_price   = $this->get_product_display_price( $the_product, $the_price, 1 );
				if ( $the_price == $price ) {
					return $this->add_global_shop_discount( $price, $the_product, 'price' );
				}
			}
		}
		return $price;
	}

	/**
	 * calculate_price.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function calculate_price( $price, $coefficient, $group ) {
		$price        = ( float ) $price;
		$return_price = ( 'percent' === $this->groups['coefficient_type'][ $group ] ? ( $price + $price * ( $coefficient / 100 ) ) : ( $price + $coefficient ) );
		return ( $return_price >= 0 ? $return_price : 0 );
	}

	/**
	 * check_if_applicable.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @return  bool
	 */
	function check_if_applicable( $group, $product, $price, $price_type ) {
		return (
			$this->is_enabled_for_product_group( $product, $group ) &&
			$this->check_if_applicable_by_date( $group ) &&
			$this->check_if_applicable_by_product_scope( $product, $price, $price_type, $this->groups['product_scope'][ $group ] ) &&
			$this->check_if_applicable_by_user( $group )
		);
	}

	/**
	 * is_enabled_for_product_group.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function is_enabled_for_product_group( $product, $group ) {
		$product_id = $this->get_product_id_or_variation_parent_id( $product );
		$args = array(
			'include_products' => $this->groups['products_incl'][ $group ],
			'exclude_products' => $this->groups['products_excl'][ $group ],
		);
		$taxonomies = get_option( 'alg_wc_global_shop_discount_taxonomies', array( 'product_cat', 'product_tag' ) );
		foreach ( $taxonomies as $taxonomy ) {
			$id = $this->get_taxonomy_option_id( $taxonomy );
			$args[ "include_{$id}" ] = $this->groups[ "{$id}_incl" ][ $group ];
			$args[ "exclude_{$id}" ] = $this->groups[ "{$id}_excl" ][ $group ];
		}
		return $this->is_enabled_for_product( $product_id, $args );
	}

	/**
	 * get_taxonomy_option_id.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_taxonomy_option_id( $taxonomy ) {
		return ( 'product_cat' === $taxonomy ? 'categories' : ( 'product_tag' === $taxonomy ? 'tags' : $taxonomy ) );
	}

	/**
	 * is_product_term.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 *
	 * @todo    (dev) `has_term()`?
	 */
	function is_product_term( $product_id, $term_ids, $taxonomy ) {
		if ( empty( $term_ids ) ) {
			return false;
		}
		$product_terms = get_the_terms( $product_id, $taxonomy );
		if ( empty( $product_terms ) ) {
			return false;
		}
		foreach( $product_terms as $product_term ) {
			if ( in_array( $product_term->term_id, $term_ids ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * maybe_convert_to_array.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 *
	 * @todo    (dev) pre-calculate this?
	 */
	function maybe_convert_to_array( $value ) {
		return ( ! is_array( $value ) ? array_map( 'trim', explode( ',', $value ) ) : $value );
	}

	/**
	 * is_enabled_for_product.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 *
	 * @todo    (feature) by product meta, e.g. `total_sales`
	 */
	function is_enabled_for_product( $product_id, $args ) {
		// Products
		if (
			( ! empty( $args['include_products'] ) && ! in_array( $product_id, $this->maybe_convert_to_array( $args['include_products'] ) ) ) ||
			( ! empty( $args['exclude_products'] ) &&   in_array( $product_id, $this->maybe_convert_to_array( $args['exclude_products'] ) ) )
		) {
			return false;
		}
		// Taxonomies
		$taxonomies = get_option( 'alg_wc_global_shop_discount_taxonomies', array( 'product_cat', 'product_tag' ) );
		foreach ( $taxonomies as $taxonomy ) {
			$id = $this->get_taxonomy_option_id( $taxonomy );
			if (
				( ! empty( $args[ "include_{$id}" ] ) && ! $this->is_product_term( $product_id, $this->maybe_convert_to_array( $args[ "include_{$id}" ] ), $taxonomy ) ) ||
				( ! empty( $args[ "exclude_{$id}" ] ) &&   $this->is_product_term( $product_id, $this->maybe_convert_to_array( $args[ "exclude_{$id}" ] ), $taxonomy ) )
			) {
				return false;
			}
		}
		// All passed
		return true;
	}

	/**
	 * check_if_applicable_by_user.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function check_if_applicable_by_user( $group ) {
		if ( ! empty( $this->groups['users_incl'][ $group ] ) || ! empty( $this->groups['users_excl'][ $group ] ) ) {
			$current_user_id = get_current_user_id();
			if ( ! empty( $this->groups['users_incl'][ $group ] ) ) {
				return (   in_array( $current_user_id, $this->maybe_convert_to_array( $this->groups['users_incl'][ $group ] ) ) );
			} elseif ( ! empty( $this->groups['users_excl'][ $group ] ) ) {
				return ( ! in_array( $current_user_id, $this->maybe_convert_to_array( $this->groups['users_excl'][ $group ] ) ) );
			}
		}
		return true;
	}

	/**
	 * check_if_applicable_by_date.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function check_if_applicable_by_date( $group ) {
		if ( '' !== $this->groups['dates_incl'][ $group ] ) {
			if ( ! isset( $this->current_time ) ) {
				$this->current_time = current_time( 'timestamp' );
			}
			$value = array_map( 'trim', explode( ';', $this->groups['dates_incl'][ $group ] ) );
			foreach ( $value as $_value ) {
				$_value = array_map( 'trim', explode( '-', $_value ) );
				if ( 2 == count( $_value ) ) {
					$start_time  = strtotime( $_value[0], $this->current_time );
					$end_time    = strtotime( $_value[1], $this->current_time );
					$is_in_range = ( $this->current_time >= $start_time && $this->current_time <= $end_time );
					if ( $is_in_range ) {
						return true;
					}
				}
			}
			return false;
		}
		return true;
	}

	/**
	 * check_if_applicable_by_product_scope.
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 */
	function check_if_applicable_by_product_scope( $_product, $price, $price_type, $scope ) {
		$return = true;
		if ( 'sale_price' === $price_type ) {
			if ( empty( $price ) ) {
				// The product is currently not on sale
				if ( 'only_on_sale' === $scope ) {
					$return = false;
				}
			} else {
				// The product is currently on sale
				if ( 'only_not_on_sale' === $scope ) {
					$return = false;
				}
			}
		} else { // if ( 'price' === $price_type )
			$this->price_hooks( PHP_INT_MAX, false, 'remove_filter' );
			$sale_price = $_product->get_sale_price();
			if ( 'only_on_sale' === $scope && empty( $sale_price ) ) {
				$return = false;
			} elseif ( 'only_not_on_sale' === $scope && ! empty( $sale_price ) ) {
				$return = false;
			}
			$this->price_hooks( PHP_INT_MAX, false );
		}
		return $return;
	}

	/**
	 * add_global_shop_discount.
	 *
	 * @version 1.4.1
	 * @since   1.0.0
	 */
	function add_global_shop_discount( $price, $product, $price_type ) {
		if ( 'price' === $price_type && '' === $price ) {
			return $price; // no changes
		}
		$total_number = apply_filters( 'alg_wc_global_shop_discount_total_groups', 1 );
		for ( $i = 1; $i <= $total_number; $i++ ) {
			if ( 'yes' === $this->groups['enabled'][ $i ] && ( $coef = $this->groups['coefficient'][ $i ] ) && ! empty( $coef ) && $this->check_if_applicable( $i, $product, $price, $price_type ) ) {
				$price = $this->calculate_price( ( 'sale_price' === $price_type && empty( $price ) ? $product->get_regular_price() : $price ), $coef, $i ); // discount applied
				if ( $this->do_stop_on_first_discount_group ) {
					return $price;
				}
			}
		}
		return $price;
	}

	/**
	 * get_variation_prices_hash.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_variation_prices_hash( $price_hash, $_product, $display ) {
		$price_hash['alg_wc_global_shop_discount_price_hash']['groups']                       = $this->groups;
		$price_hash['alg_wc_global_shop_discount_price_hash']['stop_on_first_discount_group'] = $this->do_stop_on_first_discount_group;
		return $price_hash;
	}

}

endif;

return new Alg_WC_Global_Shop_Discount_Core();
