=== WC Products Quick View ===
Contributors: shamimmoeen
Tags: WooCommerce Products Quick View, Products Quick View, WooCommerce Quick View, Quick View, WooCommerce
Requires at least: 3.0.1
Tested up to: 4.2.3
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin to get preview of woocommerce products from product list.

== Description ==

WC Products Quick View plugin comes for giving your shop an exclusive feature. This plugin adds a "Quick View" button to the products loop. Your customers can see the products in a lightbox without leaving the page.

Features:

* Add simple and variable product to cart with an ajax request
* Navigate to the next and previous products
* Fully responsive and compatible with most of the themes

= Developer =

Are you a developer? See the F.A.Q section.

== Installation ==

Once you have installed the plugin, you just need to activate the plugin in order to enable it.

== Frequently Asked Questions ==

= Can I change the "Quick View" button position? =

Yes, you can. Paste these codes in your theme's functions.php.
`remove_action('woocommerce_after_shop_loop_item', 'wpqv_button', 15);
add_action('woocommerce_after_shop_loop_item', 'wpqv_button', 25);`

= Can I customize the templates? =

Yes, you can. You have to copy the wpqv-button.php and wpqv-product.php templates from the templates folder of the plugin and paste it into woocommerce folder of your theme and don't forget to update the image source to load the images properly.

== Screenshots ==

1. Quick View button on products loop
2. Product Details in lightbox

== Changelog ==

= 1.0 =

* Initial release