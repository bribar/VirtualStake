<?php
/**
 * gismo functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package gismo
 */
 
/**
 * Load Gismo.
 */
require get_template_directory() . '/classes/init.php';

if(is_admin()){

	require get_template_directory() . '/classes/admin.php';
	$gismo_admin = new Gismo_Admin();

}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';

/**
 * Woocommerce compatibility.
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

add_action('woocommerce_before_main_content', 'gs_woo_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'gs_woo_wrapper_end', 10);

function gs_woo_wrapper_start() {
  echo '<section id="main">';
}

function gs_woo_wrapper_end() {
  echo '</section>';
}

add_action( 'after_setup_theme', 'gs_woocommerce_support' );
function gs_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}