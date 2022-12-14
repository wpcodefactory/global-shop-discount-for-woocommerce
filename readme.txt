=== Global Shop Discount for WooCommerce ===
Contributors: wpcodefactory, algoritmika, anbinder
Tags: woocommerce, discount, global shop discount, woo commerce
Requires at least: 4.4
Tested up to: 6.0
Stable tag: 1.5.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Add global shop discount to all WooCommerce products. Beautifully.

== Description ==

**Global Shop Discount for WooCommerce** plugin lets you add global shop discount to all or a group of WooCommerce products.

### &#9989; Main Features ###

* Set discounts as **percent** or as **fixed** values.
* Optionally set **active date(s)** for the discount.
* Optionally choose **products scope** (sale or non-sale products).
* Optionally include/exclude **product categories**.
* Optionally include/exclude **product tags**.
* Optionally include/exclude **individual products**.
* Optionally include/exclude **custom product taxonomies**, e.g. **product brands**.
* Optionally include/exclude **users**.
* And more...

### &#128472; Feedback ###

* We are open to your suggestions and feedback. Thank you for using or trying out one of our plugins!
* [Visit plugin site](https://wpfactory.com/item/global-shop-discount-for-woocommerce/).

== Installation ==

1. Upload the entire plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Start by visiting plugin settings at "WooCommerce > Settings > Global Shop Discount".

== Screenshots ==

1. Discount settings.

== Changelog ==

= 1.5.2 - 27/10/2022 =
* WC tested up to: 7.0.
* Readme.txt updated.
* Deploy script added.

= 1.5.1 - 27/07/2022 =
* Dev - `[alg_wc_gsd_products]` shortcode added.
* WC tested up to: 6.7.
* Tested up to: 6.0.

= 1.5.0 - 07/04/2022 =
* Dev - General - "Use list instead of comma separated text in settings" option removed (now always `yes`).
* Dev - General - Taxonomies - "Taxonomies sorting in admin settings" option added.
* Dev - Discount Groups - "Users" options added.
* Dev - Discount Groups - Products - Using `wc-product-search` in admin settings now.
* Dev - Discount Groups - Taxonomies - Adding term parents list in admin settings now.
* Dev - Code refactoring.
* WC tested up to: 6.3.
* Tested up to: 5.9.

= 1.4.1 - 28/09/2021 =
* Dev - PHP v8.0.0 compatibility added.
* WC tested up to: 5.7.

= 1.4.0 - 21/09/2021 =
* Dev - General - "Taxonomies" option added (defaults to "Product categories" and "Product tags"). It's now possible to include/exclude various custom taxonomies in the discount groups, e.g. brands, product attributes, etc.
* Dev - Discount Groups - Admin settings restyled.
* Dev - Plugin is initialized on the `plugins_loaded` action now.
* Dev - Code refactoring.
* Tested up to: 5.8.
* WC tested up to: 5.6.

= 1.3.0 - 25/02/2021 =
* Dev - Discount Groups - "Date(s)" options added.
* Dev - Code refactoring.
* WC tested up to: 5.0.

= 1.2.1 - 27/01/2021 =
* Dev - Settings - Include/Exclude products/product categories/tags - Making sure current values are added to the list. This will ensure that selected values do not disappear when changing site language in backend (e.g. with WPML), or when product/term is deleted.

= 1.2.0 - 26/01/2021 =
* Dev - Use list instead of comma separated text for products in settings - Now applied to "Include/Exclude product categories/tags" options as well.
* Dev - Discount Groups - "Admin title" options added.
* Dev - Localization - `load_plugin_textdomain()` moved to the `init` action.
* Dev - Admin settings descriptions updated.
* Dev - Code refactoring.
* Tested up to: 5.6.
* WC tested up to: 4.9.

= 1.1.0 - 11/11/2019 =
* Dev - Admin settings restyled.
* Dev - Code refactoring.
* Plugin URI updated.
* WC tested up to: 3.8.
* Tested up to: 5.2.

= 1.0.0 - 30/05/2018 =
* Initial Release.

== Upgrade Notice ==

= 1.0.0 =
This is the first release of the plugin.
