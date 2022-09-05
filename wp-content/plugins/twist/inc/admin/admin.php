<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    twist
 * @subpackage twist/inc
 *
 * @link       http://codeixer.com
 * @since      1.0.0
 */

// Check if the free version is enabled, and if so, disable it
if ( in_array( 'woo-product-gallery-slider/woo-product-gallery-slider.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	deactivate_plugins( 'woo-product-gallery-slider/woo-product-gallery-slider.php' );
}

require WPGS_INC . 'admin/options.php';

add_filter( 'attachment_fields_to_edit', 'wpgs_add_video_url', 10, 2 );
add_filter( 'attachment_fields_to_save', 'wpgs_add_video_url_save', 10, 2 );

if ( !function_exists( 'wpgs_add_video_url' ) ) {
	/**
	 * Add Product Video URL fields to media uploader
	 *
	 * @param $form_fields array,  fields to include in attachment form
	 * @param $post        object, attachment record in database
	 */

	function wpgs_add_video_url( $form_fields, $post ) {
		$form_fields['twist-video-url'] = array(
			'label' => 'Video URL',
			'input' => 'text',
			'value' => get_post_meta( $post->ID, 'twist_video_url', true ),
			'helps' => 'Woocommerce Product Video Link',
		);

		return $form_fields;
	}
}

if ( !function_exists( 'wpgs_add_video_url_save' ) ) {
	/**
	 * Save values of Product Video URL fields to media uploader
	 *
	 * @param $post       array, the post data for database
	 * @param $attachment array, attachment fields from $_POST form
	 */

	function wpgs_add_video_url_save( $post, $attachment ) {
		if ( isset( $attachment['twist-video-url'] ) ) {
			update_post_meta( $post['ID'], 'twist_video_url', $attachment['twist-video-url'] );
		}

		return $post;
	}
}

//value from Plugin settings
$main_image_size = cix_wpgs::option( 'slider_image_size' );

if ( $main_image_size == 'pgs_custom_image' ) {
	$check_img = cix_wpgs::option( 'adv_single_image' );

	$opts = cix_wpgs::option( 'adv_single_image' );

	add_image_size( 'wpgs_custom_main', $opts['main_image_width'], $opts['main_image_height'], $opts['main_image_crop'] );
	add_filter( 'wpgs_new_main_img_size', function () {

		$size = 'wpgs_custom_main';
		return $size;

	} );

}

$thumb_image_size = cix_wpgs::option( 'slider_image_thumb_size' );
if ( $thumb_image_size == 'pgs_custom_image' ) {

	$opts = cix_wpgs::option( 'adv_thumbs_image' );

	add_image_size( 'wpgs_custom_thumb', $opts['i_width'], $opts['i_height'], $opts['i_crop'] );
	add_filter( 'wpgs_new_thumb_img_size', function () {

		$size = 'wpgs_custom_thumb';
		return $size;

	} );

}

function wpgs_single_image_width() {
	$old_single_size = get_option( 'shop_single_image_size', array() );

	return ( !empty( $old_single_size ) ) ? $old_single_size['width'] : '600';
}

add_filter( 'wc_get_template', 'wpgs_get_template', 10, 5 );

if ( !function_exists( 'wpgs_get_template' ) ) {
	/**
	 * @param $located
	 * @param $template_name
	 * @param $args
	 * @param $template_path
	 * @param $default_path
	 */
	function wpgs_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'single-product/product-image.php' == $template_name ) {
			$located = WPGS_INC . 'public/templates/default.php';
		}

		return $located;
	}
}

/**
 * @return mixed
 */
function twist_shortcod_render() {
	ob_start();
	if ( is_product() ) {
		cix_wpgs::wpgs_templates();
	}

	$output = ob_get_clean();
	return $output;
}
/**
 * we just keep it for older version support
 * @deprecated shortocde
 * @since version 1.0
 */

add_shortcode( 'twist_vc', 'twist_shortcod_render' );

/**
 * Shortcdoe for display the gallery section
 * @since verion 3.0
 */
add_shortcode( 'product_gallery_slider', 'twist_shortcod_render' );

add_action( 'vc_before_init', 'twist_vc_map' );

function twist_vc_map() {
	vc_map( array(
		"name"        => __( "Twist Product Gallery", "wpgs-td" ),
		"base"        => "twist_vc",
		"description" => __( "Product Gallery Slider", "wpgs-td" ),
		"category"    => __( "WooCommerce", "wpgs-td" ),

	) );
}

add_filter( 'plugin_row_meta', 'wpgs_plugin_meta_links', 10, 2 );
/**
 * Add links to plugin's description in plugins table
 *
 * @param array  $links Initial list of links.
 * @param string $file  Basename of current plugin.
 */
function wpgs_plugin_meta_links( $links, string $file ) {
	if ( $file !== WPGS_PLUGIN_BASE ) {
		return $links;
	}
	$doc_faq   = '<a target="_blank" href="https://www.codeixer.com/docs-category/product-gallery-slider/"> Docs & FAQs </a>';
	$support_link = '<a target="_blank" href="https://codeixer.com/s" title="' . __( 'Get help', 'wpgs-td' ) . '">' . __( 'Premium Support', 'wpgs-td' ) . '</a>';
	$rate_twist   = '<a target="_blank" href="https://wordpress.org/support/plugin/woo-product-gallery-slider/reviews/?filter=5"> Rate this plugin » </a>';

	$links[] = $doc_faq;
	$links[] = $support_link;
	$links[] = $rate_twist;

	return $links;
} // plugin_meta_links

/**
 * @param $links
 */
function wpgs_plugin_settings_link( $links ) {
	$settings_link = '<a href="' . get_admin_url( null, 'admin.php?page=cix-gallery-settings' ) . '">' . __( 'Settings', 'wpgs-td' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

$plugin = WPGS_PLUGIN_BASE;
add_filter( "plugin_action_links_$plugin", 'wpgs_plugin_settings_link' );

add_filter( 'wpgs_carousel_mode', 'wpgs_carousel_mode_return', 20 );

/**
 * @param $boolen
 */
function wpgs_carousel_mode_return( $boolen ) {
	$thumb_lightbox = ( cix_wpgs::option( 'thumbnails_lightbox' ) == 1 ) ? 'true' : 'false';
	if ( $thumb_lightbox == 'true' ) {
		return false;
	} else {
		return true;
	}
}

// add class into main wrapper
add_filter( 'wpgs_wrapper_add_classes', 'wpgs_no_gallery_class', 20, 2 );

/**
 * @param $class
 * @param $attachment_ids
 */
function wpgs_no_gallery_class( $class, $attachment_ids ) {
	return ( empty( $attachment_ids ) ) ? ' wpgs-no-gallery-images' : ' wpgs-has-gallery-images';
}

add_action( 'csf_options_after', 'twist_bayna_is_here' );
// notice for option page
function twist_bayna_is_here() {
	echo '<a class="cit-admin-pro-notice" target="_blank" href="https://www.codeixer.com/bfd-twist"><p><strong>2000+ </strong>WooCommerce powered shop uses our plugin to enable customers to pay for products using a deposit payment.</p></p> <span>Read More</span></a>';
}