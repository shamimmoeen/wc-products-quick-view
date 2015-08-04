<?php

if ( !function_exists( 'wpqv_get_template_part' ) ) {
	function wpqv_get_template_part( $template_name, $params = array() ) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

		/*template name*/
		$template = $template_name . '.php';

		/*check if a custom template exists in the theme folder, if not, load the plugin template file*/
		if ( $theme_file = locate_template( 'woocommerce/' . $template ) ) {
			$file = $theme_file;
		} else {
			$file = TEMPLATE_PATH . $template;
		}

		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}
		extract($params, EXTR_SKIP);

		// load the template
		require( $file );
	}
}

if ( !function_exists( 'wpqv_button' ) ) {
	function wpqv_button() {
		global $post;
		$params = array(
			'button_class' => 'wc-quick-view',
			'image_class'  => 'wc-loading-button-open'
		);
		wpqv_get_template_part( 'wpqv-button', $params );
	}
}

/*add quick view button*/
add_action('woocommerce_after_shop_loop_item', 'wpqv_button', 15);


/*Give a new location for quick view button*/
// Paste these two lines in your theme's functions.php and uncomment
// remove_action('woocommerce_after_shop_loop_item', 'wpqv_button', 15);
// add_action('woocommerce_after_shop_loop_item', 'wpqv_button', 25);