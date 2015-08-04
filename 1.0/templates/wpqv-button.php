<?php
/**
* The template for displaying quick view button
*
* Override this template by copying it to yourtheme/wc-quick-view/button.php
* Don't forget to update the image source to show the loading image properly
*
* @author 	Shamim Al Mamun
* @package 	WC Products Quick View/Templates
* @version  1.0
*/

global $post;
?>
<div class="quick-view-button">
	<a href="#" class="<?php echo $button_class; ?> button" data-product_id="<?php echo $post->ID; ?>">
		<?php _e('Quick View', 'wpqv'); ?>
		<!-- loader-image -->
		<img class="<?php echo $image_class; ?>" src="<?php echo plugin_dir_url( __FILE__ ) . '../assets/images/loading-small.gif'; ?>" alt="loading..">
	</a>
</div>