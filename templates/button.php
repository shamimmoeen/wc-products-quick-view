<?php
/**
 * Quick view button template.
 *
 * Override this template by copying it to yourtheme/wc-products-quick-view/button.php
 *
 * @author  Mainul Hassan
 * @package WC_Products_Quick_View/Templates
 * @version 2.0.0
 */

global $post;
?>
<div class="wcpqv-button">
	<a href="#" class="<?php echo esc_attr( $button_class ); ?> button" data-product_id="<?php echo esc_attr( $post->ID ); ?>">
		<?php esc_html_e( 'Quick View', 'wc-products-quick-view' ); ?>
		<span class="<?php echo esc_attr( $spinner_class ); ?>"></span>
	</a>
</div>
