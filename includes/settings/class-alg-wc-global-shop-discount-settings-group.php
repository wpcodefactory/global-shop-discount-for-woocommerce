<?php
/**
 * Global Shop Discount for WooCommerce - Group Section Settings
 *
 * @version 1.5.0
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Global_Shop_Discount_Settings_Group' ) ) :

class Alg_WC_Global_Shop_Discount_Settings_Group extends Alg_WC_Global_Shop_Discount_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function __construct( $id ) {
		$admin_titles   = get_option( 'alg_wc_global_shop_discount_admin_title', array() );
		$this->id       = 'group_' . $id;
		$this->desc     = ( isset( $admin_titles[ $id ] ) && '' !== $admin_titles[ $id ] ? $admin_titles[ $id ] : sprintf( __( 'Discount Group #%d', 'global-shop-discount-for-woocommerce' ), $id ) );
		$this->group_nr = $id;
		parent::__construct();
	}

	/**
	 * maybe_convert_and_update_option_value.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @todo    [p1] (dev) do this only once, e.g. in `version_updated()`?
	 */
	function maybe_convert_and_update_option_value( $options ) {
		foreach ( $options as $option ) {
			$value = get_option( $option, array() );
			foreach ( $value as $k => &$v ) {
				if ( is_string( $v ) ) {
					$v = ( '' === $v ? array() : array_map( 'trim', explode( ',', $v ) ) );
				}
			}
			update_option( $option, $value );
		}
	}

	/**
	 * get_terms.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @see     https://developer.wordpress.org/reference/functions/get_terms/
	 * @see     https://developer.wordpress.org/reference/classes/wp_term_query/__construct/
	 *
	 * @todo    [p1] (dev) add `' #' . $term->term_id`?
	 */
	function get_terms( $taxonomy ) {
		$args = array(
			'taxonomy'   => $taxonomy,
			'orderby'    => get_option( 'alg_wc_global_shop_discount_taxonomies_orderby', 'name' ),
			'hide_empty' => false,
		);
		global $wp_version;
		if ( version_compare( $wp_version, '4.5.0', '>=' ) ) {
			$terms = get_terms( $args );
		} else {
			unset( $args['taxonomy'] );
			$terms = get_terms( $taxonomy, $args );
		}
		$terms_options = array();
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			foreach ( $terms as $term ) {
				$term_parents_list = ( empty( $term->parent ) ? '' :
					' (' . trim( get_term_parents_list( $term->term_id, $taxonomy, array( 'link' => false, 'inclusive' => false, 'separator' => ' > ' ) ), ' > ' ) . ')' );
				$terms_options[ $term->term_id ] = $term->name . $term_parents_list;
			}
		}
		return $terms_options;
	}

	/**
	 * maybe_add_current_values.
	 *
	 * @version 1.2.1
	 * @since   1.2.1
	 *
	 * @todo    [p3] (dev) better title, e.g. `get_the_title()` etc.?
	 */
	function maybe_add_current_values( $values, $option_id, $title ) {
		if ( is_array( $values ) ) {
			$current_values = get_option( $option_id, array() );
			if ( ! empty( $current_values[ $this->group_nr ] ) && is_array( $current_values[ $this->group_nr ] ) ) {
				$_current_values = array();
				foreach ( $current_values[ $this->group_nr ] as $value ) {
					$_current_values[ $value ] = $title . ' #' . $value;
				}
				$values = array_replace( $_current_values, $values );
			}
		}
		return $values;
	}

	/**
	 * get_product_options.
	 *
	 * @version 1.5.0
	 * @since   1.5.0
	 */
	function get_product_options( $option, $key = false ) {
		$product_options  = array();
		$current_products = get_option( $option, array() );
		if ( false !== $key ) {
			$current_products = ( isset( $current_products[ $key ] ) ? $current_products[ $key ] : array() );
		}
		foreach ( $current_products as $product_id ) {
			$product = wc_get_product( $product_id );
			$product_options[ esc_attr( $product_id ) ] = ( is_object( $product ) ?
				esc_html( wp_strip_all_tags( $product->get_formatted_name() ) ) : sprintf( esc_html__( 'Product #%d', 'global-shop-discount-for-woocommerce' ), $product_id ) );
		}
		return $product_options;
	}

	/**
	 * get_settings.
	 *
	 * @version 1.5.0
	 * @since   1.0.0
	 *
	 * @todo    [p1] (dev) AJAX for terms and users selectors
	 * @todo    [p1] (dev) Users: add `' #' . $user_id`?
	 * @todo    [p1] (dev) Users: do we need `maybe_add_current_values()`?
	 * @todo    [p1] (dev) `$i = $this->group_nr;`?
	 * @todo    [p3] (desc) Admin title: better desc?
	 * @todo    [p3] (desc) `alg_wc_global_shop_discount_dates_incl`: better desc and notes?
	 * @todo    [p3] (feature) add `alg_wc_global_shop_discount_dates_excl`?
	 */
	function get_settings() {

		$i = $this->group_nr;

		// Get users
		$users = wp_list_pluck( get_users( array( 'fields' => array( 'ID', 'user_nicename' ) ) ), 'user_nicename', 'ID' );

		// Get taxonomies
		$all_taxonomies = array_combine( get_object_taxonomies( 'product', 'names' ), wp_list_pluck( get_object_taxonomies( 'product', 'objects' ), 'label' ) );
		$taxonomies     = get_option( 'alg_wc_global_shop_discount_taxonomies', array( 'product_cat', 'product_tag' ) );

		// Maybe convert and update option value
		$this->maybe_convert_and_update_option_value( array( 'alg_wc_global_shop_discount_products_incl', 'alg_wc_global_shop_discount_products_excl' ) );
		$this->maybe_convert_and_update_option_value( array( 'alg_wc_global_shop_discount_users_incl', 'alg_wc_global_shop_discount_users_excl' ) );
		foreach ( $taxonomies as $taxonomy ) {
			$id = alg_wc_global_shop_discount()->core->get_taxonomy_option_id( $taxonomy );
			$this->maybe_convert_and_update_option_value( array( "alg_wc_global_shop_discount_{$id}_incl", "alg_wc_global_shop_discount_{$id}_excl" ) );
		}

		// General
		$settings = array(
			array(
				'title'    => $this->desc,
				'type'     => 'title',
				'id'       => "alg_wc_global_shop_discount_general_options_{$i}",
			),
			array(
				'title'    => __( 'Enabled', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => sprintf( __( 'Enabled/disables discount group #%d.', 'global-shop-discount-for-woocommerce' ), $i ),
				'desc'     => '<strong>' . __( 'Enable', 'global-shop-discount-for-woocommerce' ) . '</strong>',
				'id'       => "alg_wc_global_shop_discount_enabled[{$i}]",
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Admin title (optional)', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Visible only to admin.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_admin_title[{$i}]",
				'default'  => sprintf( __( 'Discount Group #%d', 'global-shop-discount-for-woocommerce' ), $i ),
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Type', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Can be fixed or percent.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_coefficient_type[{$i}]",
				'default'  => 'percent',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'percent' => __( 'Percent', 'global-shop-discount-for-woocommerce' ),
					'fixed'   => __( 'Fixed', 'global-shop-discount-for-woocommerce' ),
				),
			),
			array(
				'title'    => __( 'Value', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Must be negative number.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_coefficient[{$i}]",
				'default'  => 0,
				'type'     => 'number',
				'custom_attributes' => array( 'max' => 0, 'step' => 0.0001 ),
			),
			array(
				'title'    => __( 'Date(s)', 'global-shop-discount-for-woocommerce' ),
				'desc'     => '<a href="https://wpfactory.com/item/global-shop-discount-for-woocommerce/#section-date-format-and-examples" target="_blank">' .
						__( 'Accepted date format and examples', 'global-shop-discount-for-woocommerce' ) . '</a>.' . ' ' .
					sprintf( __( 'Current date: %s', 'global-shop-discount-for-woocommerce' ),
						'<code>' . date( 'd.m.Y H:i:s', current_time( 'timestamp' ) ) . '</code>' ),
				'desc_tip' => __( 'Set active date(s) for the current discount group.', 'global-shop-discount-for-woocommerce' ) . ' ' .
					__( 'Ignored if empty.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_dates_incl[{$i}]",
				'default'  => '',
				'type'     => 'text',
			),
			array(
				'title'    => __( 'Product scope', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Possible values: all products, only products that are already on sale, only products that are not on sale.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_product_scope[{$i}]",
				'default'  => 'all',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'all'              => __( 'All products', 'global-shop-discount-for-woocommerce' ),
					'only_on_sale'     => __( 'Only products that are already on sale', 'global-shop-discount-for-woocommerce' ),
					'only_not_on_sale' => __( 'Only products that are not on sale', 'global-shop-discount-for-woocommerce' ),
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => "alg_wc_global_shop_discount_general_options_{$i}",
			),
		);

		// Products
		$settings = array_merge( $settings, array(
			array(
				'title'    => __( 'Products', 'global-shop-discount-for-woocommerce' ),
				'type'     => 'title',
				'id'       => "alg_wc_global_shop_discount_products_options_{$i}",
			),
			array(
				'title'    => __( 'Include', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Set this field to apply discount to selected products only. Leave blank to apply to all products.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_products_incl[{$i}]",
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-product-search',
				'options'  => $this->get_product_options( 'alg_wc_global_shop_discount_products_incl', $i ),
				'custom_attributes' => array(
					'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
					'data-action'      => 'woocommerce_json_search_products_and_variations',
				),
			),
			array(
				'title'    => __( 'Exclude', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Set this field to NOT apply discount to selected products. Leave blank to apply to all products.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_products_excl[{$i}]",
				'default'  => array(),
				'type'     => 'multiselect',
				'class'    => 'wc-product-search',
				'options'  => $this->get_product_options( 'alg_wc_global_shop_discount_products_excl', $i ),
				'custom_attributes' => array(
					'data-placeholder' => esc_attr__( 'Search for a product&hellip;', 'woocommerce' ),
					'data-action'      => 'woocommerce_json_search_products_and_variations',
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => "alg_wc_global_shop_discount_products_options_{$i}",
			),
		) );

		// Taxonomies
		foreach ( $taxonomies as $taxonomy ) {
			$terms = $this->get_terms( $taxonomy );
			$id    = alg_wc_global_shop_discount()->core->get_taxonomy_option_id( $taxonomy );
			$settings = array_merge( $settings, array(
				array(
					'title'    => ( isset( $all_taxonomies[ $taxonomy ] ) ? $all_taxonomies[ $taxonomy ] : $taxonomy ),
					'type'     => 'title',
					'id'       => "alg_wc_global_shop_discount_{$taxonomy}_options_{$i}",
				),
				array(
					'title'    => __( 'Include', 'global-shop-discount-for-woocommerce' ),
					'desc_tip' => __( 'Set this field to apply discount to selected products only. Leave blank to apply to all products.', 'global-shop-discount-for-woocommerce' ),
					'id'       => "alg_wc_global_shop_discount_{$id}_incl[{$i}]",
					'type'     => 'multiselect',
					'default'  => array(),
					'class'    => 'chosen_select',
					'options'  => $this->maybe_add_current_values( $terms, "alg_wc_global_shop_discount_{$id}_incl", __( 'Term', 'global-shop-discount-for-woocommerce' ) ),
				),
				array(
					'title'    => __( 'Exclude', 'global-shop-discount-for-woocommerce' ),
					'desc_tip' => __( 'Set this field to NOT apply discount to selected products. Leave blank to apply to all products.', 'global-shop-discount-for-woocommerce' ),
					'id'       => "alg_wc_global_shop_discount_{$id}_excl[{$i}]",
					'type'     => 'multiselect',
					'default'  => array(),
					'class'    => 'chosen_select',
					'options'  => $this->maybe_add_current_values( $terms, "alg_wc_global_shop_discount_{$id}_excl", __( 'Term', 'global-shop-discount-for-woocommerce' ) ),
				),
				array(
					'type'     => 'sectionend',
					'id'       => "alg_wc_global_shop_discount_{$taxonomy}_options_{$i}",
				),
			) );
		}

		// Users
		$settings = array_merge( $settings, array(
			array(
				'title'    => __( 'Users', 'global-shop-discount-for-woocommerce' ),
				'type'     => 'title',
				'id'       => "alg_wc_global_shop_discount_users_options_{$i}",
			),
			array(
				'title'    => __( 'Include', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Set this field to apply discount to selected users only. Leave blank to apply to all users.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_users_incl[{$i}]",
				'type'     => 'multiselect',
				'default'  => array(),
				'class'    => 'chosen_select',
				'options'  => $this->maybe_add_current_values( $users, 'alg_wc_global_shop_discount_users_incl', __( 'User', 'global-shop-discount-for-woocommerce' ) ),
			),
			array(
				'title'    => __( 'Exclude', 'global-shop-discount-for-woocommerce' ),
				'desc_tip' => __( 'Set this field to NOT apply discount to selected users. Leave blank to apply to all users.', 'global-shop-discount-for-woocommerce' ),
				'id'       => "alg_wc_global_shop_discount_users_excl[{$i}]",
				'type'     => 'multiselect',
				'default'  => array(),
				'class'    => 'chosen_select',
				'options'  => $this->maybe_add_current_values( $users, 'alg_wc_global_shop_discount_users_excl', __( 'User', 'global-shop-discount-for-woocommerce' ) ),
			),
			array(
				'type'     => 'sectionend',
				'id'       => "alg_wc_global_shop_discount_users_options_{$i}",
			),
		) );

		return $settings;
	}

}

endif;
