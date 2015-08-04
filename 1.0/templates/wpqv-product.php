<?php
/**
* The template for displaying product
*
* Override this template by copying it to yourtheme/wc-quick-view/product.php
* Don't forget to update the image source to show the close, next and previous images properly
*
* @author 	Shamim Al Mamun
* @package 	WC Products Quick View/Templates
* @version  1.0
*/

global $post, $woocommerce, $product;
?>
<!-- close modal markup -->
<div class="modal-close">
	<a href="#" class="quick-view-close">
		<img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/images/close.png'; ?>" alt="close">
	</a>
</div>
<!-- close modal markup -->

<div class="nav-wrapper">
	<div class="nav-wrapper-inner">
		<?php if ( !empty( $prev_id ) ): ?>
		<div class="left-nav">
			<a href="#" class="<?php echo $prev_class; ?> prev-button"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/images/chevron-left.svg'; ?>" alt="Previous Product"></a>
		</div>
		<?php endif; ?>
		<?php if ( !empty( $next_id ) ): ?>
		<div class="right-nav">
			<a href="#" class="<?php echo $next_class; ?> next-button"><img src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/images/chevron-right.svg'; ?>" alt="Next Product"></a>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- product wrapper -->
<div <?php post_class('product product-wrapper'); ?>>

	<?php woocommerce_show_product_images(); ?>
	<div class="product-brief">
		<?php
		woocommerce_show_product_loop_sale_flash();
		echo '<h3 class="title">', the_title(), '</h3>';
		woocommerce_template_loop_rating();
		woocommerce_template_loop_price();
		woocommerce_template_single_add_to_cart();
		woocommerce_template_single_excerpt();
		woocommerce_template_single_meta();
		?>
	</div>
</div>
<!-- product wrapper -->

<div class="clear quick-view-nav-wrapper">
	<?php if ( !empty( $prev_id ) ): ?>
		<a href="#" class="button <?php echo $prev_class; ?>">Prev</a>
	<?php endif; ?>
	<?php if ( !empty( $next_id ) ): ?>
		<a href="#" class="button <?php echo $next_class; ?>">Next</a>
	<?php endif; ?>
</div>