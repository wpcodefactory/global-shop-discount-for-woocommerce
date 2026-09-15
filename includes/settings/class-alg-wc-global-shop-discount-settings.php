<?php
/**
 * Global Shop Discount for WooCommerce - Settings
 *
 * @version 2.3.0
 * @since   1.0.0
 *
 * @author WPFactory
 *
 * @package WPFactory\WC_Global_Shop_Discount\Settings
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Alg_WC_Global_Shop_Discount_Settings' ) ) :

	/**
	 * Alg_WC_Global_Shop_Discount_Settings class.
	 *
	 * @version 2.3.0
	 * @since   1.0.0
	 */
	class Alg_WC_Global_Shop_Discount_Settings extends WC_Settings_Page {

		/**
		 * Constructor.
		 *
		 * @version 2.3.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id    = 'alg_wc_global_shop_discount';
			$this->label = __( 'Global Shop Discount', 'global-shop-discount-for-woocommerce' );

			parent::__construct();

			// Sections.
			require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-global-shop-discount-settings-section.php';
			require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-global-shop-discount-settings-general.php';
			require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-global-shop-discount-settings-group.php';
			$total_groups = get_option( 'alg_wc_global_shop_discount_total_groups', 1 );
			for ( $i = 1; $i <= $total_groups; $i++ ) {
				new Alg_WC_Global_Shop_Discount_Settings_Group( $i );
			}
			require_once plugin_dir_path( __FILE__ ) . 'class-alg-wc-global-shop-discount-settings-tools.php';

			// Style.
			add_action( 'admin_enqueue_scripts', array( $this, 'style' ) );
		}

		/**
		 * Style.
		 *
		 * @version 2.3.0
		 * @since   1.9.4
		 *
		 * @param string $hook The current admin page.
		 */
		public function style( $hook ) {
			if (
				'woocommerce_page_wc-settings' !== $hook ||
				! isset( $_GET['tab'] ) || // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'alg_wc_global_shop_discount' !== sanitize_text_field( wp_unslash( $_GET['tab'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			) {
				return;
			}

			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			wp_enqueue_style(
				'alg-wc-global-shop-discount-admin',
				alg_wc_global_shop_discount()->plugin_url() . '/assets/css/admin' . $min . '.css',
				array(),
				alg_wc_global_shop_discount()->version
			);
		}

		/**
		 * Get settings.
		 *
		 * @version 1.9.0
		 * @since   1.0.0
		 */
		public function get_settings() {
			global $current_section;
			return array_merge(
				apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				(
					'tools' === $current_section ?
					array() :
					array(
						array(
							'title' => __( 'Reset Settings', 'global-shop-discount-for-woocommerce' ),
							'type'  => 'title',
							'id'    => $this->id . '_' . $current_section . '_reset_options',
						),
						array(
							'title'    => __( 'Reset section settings', 'global-shop-discount-for-woocommerce' ),
							'desc'     => '<strong>' . __( 'Reset', 'global-shop-discount-for-woocommerce' ) . '</strong>',
							'desc_tip' => __( 'Check the box and save changes to reset.', 'global-shop-discount-for-woocommerce' ),
							'id'       => $this->id . '_' . $current_section . '_reset',
							'default'  => 'no',
							'type'     => 'checkbox',
						),
						array(
							'type' => 'sectionend',
							'id'   => $this->id . '_' . $current_section . '_reset_options',
						),
					)
				)
			);
		}

		/**
		 * Maybe reset settings.
		 *
		 * @version 1.1.0
		 * @since   1.0.0
		 *
		 * @todo (dev) Add notice.
		 */
		public function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$id   = explode( '[', $value['id'] );
						$id_a = $id[0];
						if ( $id_a === $value['id'] ) {
							delete_option( $value['id'] );
						} else {
							$id_i                = explode( ']', $id[1] );
							$id_i                = $id_i[0];
							$prev_value          = get_option( $id_a, array() );
							$prev_value[ $id_i ] = $value['default'];
							update_option( $id_a, $prev_value );
						}
					}
				}
			}
		}

		/**
		 * Save settings.
		 *
		 * @version 1.9.0
		 * @since   1.0.0
		 */
		public function save() {
			parent::save();

			$this->maybe_reset_settings();

			do_action( 'alg_wc_global_shop_discount_settings_saved' );

			global $current_section;
			if ( '' === $current_section ) {
				// For the "Total groups" option.
				wp_safe_redirect( add_query_arg( '', '' ) );
				exit;
			}
		}
	}

endif;

return new Alg_WC_Global_Shop_Discount_Settings();
