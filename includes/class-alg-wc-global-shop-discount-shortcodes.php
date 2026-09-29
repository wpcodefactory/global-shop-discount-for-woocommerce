<?php
/**
 * Global Shop Discount for WooCommerce - Shortcodes Class
 *
 * @version 2.3.2
 * @since   1.7.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Global_Shop_Discount
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Global_Shop_Discount_Shortcodes' ) ) :

	/**
	 * Alg_WC_Global_Shop_Discount_Shortcodes class.
	 *
	 * @version 2.3.2
	 * @since   1.7.0
	 */
	class Alg_WC_Global_Shop_Discount_Shortcodes {

		/**
		 * Constructor.
		 *
		 * @version 1.7.0
		 * @since   1.7.0
		 */
		public function __construct() {
			add_shortcode( 'alg_wc_gsd_products', array( $this, 'products_shortcode' ) );
		}

		/**
		 * `[alg_wc_gsd_products]` shortcode.
		 *
		 * @version 2.3.2
		 * @since   1.5.1
		 *
		 * @param array $atts Shortcode attributes.
		 *
		 * @todo (dev) Use `get_gsd_product_ids()`.
		 * @todo (dev) `$atts`: `block_size`?
		 * @todo (dev) `$atts`: `transient_expiration`?
		 * @todo (dev) Use `wc_get_products()`.
		 */
		public function products_shortcode( $atts ) {
			$product_ids_on_sale = false;

			$do_use_transient = (
				isset( $atts['use_transient'] ) &&
				filter_var( $atts['use_transient'], FILTER_VALIDATE_BOOLEAN )
			);

			// Try cache.
			if ( $do_use_transient ) {
				$product_ids_on_sale = get_transient( 'alg_wc_gsd_products_onsale' );
			}

			// Get on-sale products.
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
						$product = wc_get_product( $product_id );
						if (
							$product &&
							$product->is_on_sale()
						) {
							$product_ids_on_sale[] = $product_id;
						}
					}

					$offset += $block_size;
				}

				// Filter.
				$product_ids_on_sale = apply_filters(
					'alg_wc_global_shop_discount_shortcode_product_ids',
					$product_ids_on_sale,
					$atts
				);

				// Save cache.
				if ( $do_use_transient ) {
					set_transient(
						'alg_wc_gsd_products_onsale',
						$product_ids_on_sale,
						DAY_IN_SECONDS
					);
				}
			}

			// Pass additional atts.
			$_atts = '';
			if ( ! empty( $atts ) ) {
				$_atts = ' ' . implode(
					' ',
					array_map(
						function ( $v, $k ) {
							return sprintf(
								'%s="%s"',
								sanitize_key( $k ),
								esc_attr( $v )
							);
						},
						$atts,
						array_keys( $atts )
					)
				);
			}

			// Result.
			$result = (
				! empty( $product_ids_on_sale ) ?
				do_shortcode( '[products' . $_atts . ' ids="' . implode( ',', array_map( 'absint', $product_ids_on_sale ) ) . '"]' ) :
				( $atts['on_empty'] ? $atts['on_empty'] : '' )
			);
			return wp_kses(
				$result,
				$this->get_allowed_html()
			);
		}

		/**
		 * Get allowed HTML for `wp_kses()`.
		 *
		 * @version 2.3.1
		 * @since   2.3.1
		 *
		 * @return array
		 */
		public function get_allowed_html() {
			$allowed_html = wp_kses_allowed_html( 'post' );

			$allowed_html['bdi'] = array();

			$allowed_html['span']['translate'] = true;

			$allowed_html['img']['srcset'] = true;
			$allowed_html['img']['sizes']  = true;

			return $allowed_html;
		}
	}

endif;

return new Alg_WC_Global_Shop_Discount_Shortcodes();
