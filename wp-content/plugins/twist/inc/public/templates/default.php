<?php
/**
 * Single Product Image
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/product-image.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 3.5.1
 * @package WooCommerce\Templates
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 */

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( !function_exists( 'wc_get_gallery_image_html' ) ) {
    return;
}
/**
 * Plugin Options
 */
$thumbnails_active = ( cix_wpgs::option( 'thumbnails' ) == 1 ) ? 'true' : 'false';

global $product;
$post_thumbnail_id = $product->get_image_id();
$attachment_ids    = $product->get_gallery_image_ids();

$slider_rtl = ( is_rtl() ) ? 'true' : 'false';

do_action( 'wpgs_before_image_gallery' );

?>
<div class="woocommerce-product-gallery images wpgs-wrapper <?php echo esc_attr( apply_filters( 'wpgs_wrapper_add_classes', '', $attachment_ids ) ); ?>" style="opacity:0">

	<div class="wpgs-image" <?php echo esc_attr( $slider_rtl == 'true' ? 'dir=rtl' : '' ); ?> >

	<?php
    if ( $product->get_image_id() ) {
        $html = wpgs_get_image_gallery_html( $post_thumbnail_id, true );
    } else {
        $html = '<div class="woocommerce-product-gallery__image--placeholder">';
        $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'wpgs-td' ) );
        $html .= '</div>';
    }
    if ( apply_filters( 'wpgs_show_featured_image_in_gallery', true ) ) {
        echo apply_filters( 'woocommerce_single_product_image_large_html', $html, $post_thumbnail_id );
    }

    if ( apply_filters( 'wpgs_carousel_mode', true ) ) {
        foreach ( $attachment_ids as $attachment_id ) {
            $html = wpgs_get_image_gallery_html( $attachment_id );

            echo apply_filters( 'woocommerce_single_product_image_large_html', $html, $attachment_id ); // phpcs:disable
        }

        do_action( 'wpgs_end_of_gallery_items', $product );
    }

    ?>

	</div>
	<?php if ( $thumbnails_active == 'true' ) {?>

	<div class="wpgs-thumb" <?php echo esc_attr( $slider_rtl == 'true' ? 'dir=rtl' : '' ); ?>>
    <?php
    if ( $product->get_image_id() ) {
        $html = wpgs_get_image_gallery_thumb_html( $post_thumbnail_id, true );
    } else {
        $html = '<div class="woocommerce-product-gallery__image--placeholder">';
        $html .= sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'wpgs-td' ) );
        $html .= '</div>';
    }

    if ( apply_filters( 'wpgs_show_featured_image_in_gallery', true ) && apply_filters( 'wpgs_carousel_mode', true ) ) {
        echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $post_thumbnail_id );
    }

    foreach ( $attachment_ids as $attachment_id ) {
        $html = wpgs_get_image_gallery_thumb_html( $attachment_id );

        echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $attachment_id ); // phpcs:disable
    }

    do_action( 'wpgs_end_of_gallery_thumbnail_items', $product );

    ?>
	</div>
	<?php } // End $thumbnails_active?>
</div>
<?php do_action( 'wpgs_after_image_gallery' );?>