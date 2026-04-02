=== WPQV – Quick View for WooCommerce ===
Contributors: shamimmoeen
Tags: quick view, woocommerce, products, modal, ajax
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.0.0
WC requires at least: 7.0
WC tested up to: 10.6.2
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a Quick View button to the WooCommerce products loop so customers can preview product details in a modal without leaving the page.

== Description ==

WPQV – Quick View for WooCommerce adds a Quick View button to your shop product listings. Customers can click it to open a modal with the product details — including images, price, rating, and add-to-cart — without navigating away from the shop page.

Features:

* Quick view modal for simple and variable products
* Add to cart directly from the modal with AJAX
* Navigate to next and previous products within the modal
* Template overrides supported — copy templates to your theme
* Fully responsive

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory, or install it directly through the WordPress plugins screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. No configuration needed — the Quick View button appears automatically on your shop and archive pages.

== Frequently Asked Questions ==

= Can I change the Quick View button position? =

Yes. Add these lines to your theme's `functions.php`:

`remove_action( 'woocommerce_after_shop_loop_item', 'wpqv_button', 15 );
add_action( 'woocommerce_after_shop_loop_item', 'wpqv_button', 25 );`

= Can I customise the templates? =

Yes. Copy `button.php` or `product.php` from the plugin's `templates/` folder into `yourtheme/wc-products-quick-view/` and edit them there.

== Screenshots ==

1. Quick View button on the products loop
2. Product details in the modal

== Changelog ==

= 1.0 =
* Initial release
