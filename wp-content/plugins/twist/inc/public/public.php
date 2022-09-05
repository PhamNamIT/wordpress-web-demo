<?php

class WpgsPublicCode {
	/**
	 * @var mixed
	 */
	public static $gallery_inline_script;

	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'wpgs_enqueue_files' ), 10 );
		add_action( 'elementor_twist_preview_scripts', array( $this, 'wpgs_enqueue_files' ) );

		if ( cix_wpgs::option( 'slider_lazy_laod' ) != 'disable' && apply_filters( 'wpgs_carousel_mode', true ) ) {
			// Remove SRC FROM GALLERY IMAGES because we have data-lazy attr for render
			add_filter( 'wpgs_lazyload_src', 'wpgs_remove_src' );
			function wpgs_remove_src( $args ) {
				return esc_url( wc_placeholder_img_src( 'woocommerce_single' ) );
			}
		}
	}

	/**
	 * @return null
	 */
	public function wpgs_enqueue_files() {
		if ( !is_product() ) {
			return;
		}
		$product_id     = get_the_ID();
		$twist_product  = new WC_Product( $product_id );
		$attachment_ids = $twist_product->get_gallery_image_ids();

		/* Plugin Options */
		$lightbox           = ( cix_wpgs::option( 'lightbox_picker' ) == 1 ) ? 'true' : 'false';
		$lightbox_bg        = cix_wpgs::option( 'lightbox_bg' );
		$lightbox_txt_color = cix_wpgs::option( 'lightbox_txt_color' );
		$icon_bg_color      = cix_wpgs::option( 'lightbox_icon_bg_color' );
		$icon_link_color    = cix_wpgs::option( 'lightbox_icon_color' );

		$slider_rtl                     = ( is_rtl() ) ? 'true' : 'false';
		$slider_dragging                = ( cix_wpgs::option( 'slider_dragging' ) == 1 ) ? 'true' : 'false';
		$slider_infinity                = ( cix_wpgs::option( 'slider_infinity' ) == 1 ) ? 'true' : 'false';
		$slider_adaptiveHeight          = ( cix_wpgs::option( 'slider_adaptiveHeight' ) == 1 ) ? 'true' : 'false';
		$slider_nav                     = ( cix_wpgs::option( 'slider_nav' ) == 1 ) ? 'true' : 'false';
		$slider_nav_animation           = ( cix_wpgs::option( 'slider_nav_animation' ) == 1 ) ? 'true' : 'false';
		$slider_nav_bg                  = cix_wpgs::option( 'slider_nav_bg' );
		$slider_nav_icon                = cix_wpgs::option( 'slider_nav_color' );
		$slider_icon                    = cix_wpgs::option( 'slider_icon' );
		$slider_animation               = ( cix_wpgs::option( 'slider_animation' ) );
		$slider_lazyload                = cix_wpgs::option( 'slider_lazy_laod' );
		$slider_autoplay                = ( cix_wpgs::option( 'slider_autoplay' ) == 1 ) ? 'true' : 'false';
		$slider_autoplay_time           = cix_wpgs::option( 'autoplay_timeout', '4000' );
		$slider_autoplay_pause_on_hover = ( cix_wpgs::option( 'slider_autoplay_pause' ) == 1 ) ? 'true' : 'false';

		$zoom = ( cix_wpgs::option( 'image_zoom' ) == 1 ) ? 'true' : 'false';

		$thumbnails_active = ( cix_wpgs::option( 'thumbnails' ) == 1 ) ? 'true' : 'false';
		$thumbnails_id     = ( 'true' == $thumbnails_active ? '\'.wpgs-thumb\'' : 'false' );

		$thumbnails_nav = ( cix_wpgs::option( 'thumb_nav' ) == 1 ) ? 'true' : 'false';

		$thumb_to_show   = cix_wpgs::option( 'thumb_to_show' );
		$thumb_scroll_by = cix_wpgs::option( 'thumb_scroll_by' );

		$thumbnails_mobile_thumb_to_show   = cix_wpgs::option( 'thumbnails_mobile_thumb_to_show' );
		$thumbnails_mobile_thumb_scroll_by = cix_wpgs::option( 'thumbnails_mobile_thumb_scroll_by' );

		$thumbnails_tabs_thumb_to_show   = cix_wpgs::option( 'thumbnails_tabs_thumb_to_show' );
		$thumbnails_tabs_thumb_scroll_by = cix_wpgs::option( 'thumbnails_tabs_thumb_scroll_by' );

		$thumb_position        = cix_wpgs::option( 'thumb_position' );
		$thumb_position_mobile = cix_wpgs::option( 'thumbnails_mobile_thumb_position' );
		$thumb_position_tablet = cix_wpgs::option( 'thumbnails_tabs_thumb_position' );

		$thumb_v        = ( $thumb_position != 'bottom' ) ? 'vertical: true,verticalSwiping: true,rtl:false,' : 'rtl: ' . $slider_rtl . ',';
		$thumb_v_mobile = ( $thumb_position_mobile != 'bottom' ) ? 'vertical: true,verticalSwiping: true,variableWidth: false,rtl:false,' : 'vertical: false,verticalSwiping: false,rtl: ' . $slider_rtl . ',';
		$thumb_v_tablet = ( $thumb_position_tablet != 'bottom' ) ? 'vertical: true,verticalSwiping: true,variableWidth: false,rtl:false,' : 'vertical: false,verticalSwiping: false,rtl: ' . $slider_rtl . ',';

		$thumbnails_style = cix_wpgs::option( 'thumbnails_layout' );

		$slider_dots      = ( cix_wpgs::option( 'dots' ) == 1 ) ? 'true' : 'false';
		$wpgs_setting_css = ''; // CSS

		$wpgs_thumb_js_mode = ( apply_filters( 'wpgs_carousel_mode', true ) == true ) ? 'focusOnSelect: true,asNavFor: \'.wpgs-image\',' : 'focusOnSelect: false,';

		if ( apply_filters( 'wpgs_enqueue_slick_js', true ) && is_product() ) {
			wp_enqueue_script( 'slick', WPGS_ROOT_URL . 'assets/js/slick.min.js', array( 'jquery' ), null );
		}

		$slider_main_js = ( apply_filters( 'wpgs_carousel_mode', true ) == true ) ? "
		$('.wpgs-image').slick({

			fade: $slider_animation,
			asNavFor: $thumbnails_id,
			lazyLoad:'$slider_lazyload',
			adaptiveHeight: $slider_adaptiveHeight,
			dots: $slider_dots,
			dotsClass:'slick-dots wpgs-dots',
			focusOnSelect:false,
			rtl: $slider_rtl,
			infinite: $slider_infinity,
			draggable: $slider_dragging,
			arrows: $slider_nav,
			prevArrow:'<span class=\"slick-prev slick-arrow\" aria-label=\"prev\"  ></span>',
			nextArrow:'<span class=\"slick-next slick-arrow\" aria-label=\"Next\" ></span>',
			speed: 500,
			autoplay: $slider_autoplay,
			pauseOnHover: $slider_autoplay_pause_on_hover,
			pauseOnDotsHover: $slider_autoplay_pause_on_hover,
			autoplaySpeed: $slider_autoplay_time,
		});
		" : "";
		// Check if Slider Lazyload is enable
		$slider_lazyLoad_js = '';
		if ( $slider_lazyload != 'disable' ) {
			// Remove SRCSET for Main SLider
			$slider_lazyLoad_js = "	$('.wpgs-image .wpgs_image img').each(function () {
				$(this).removeAttr('srcset');
				$(this).removeAttr('sizes');

			});";
		}
		// Slider Thumbnails JS
		$slider_thumbnails_js = '';
		if ( $thumbnails_active == 'true' ) {
			$variableWidth = '';
			if ( is_product() ) {
				if ( count( $attachment_ids ) + 1 > 2 && count( $attachment_ids ) + 1 < $thumb_to_show - 1 && 'bottom' == $thumb_position ) {
					$variableWidth = 'variableWidth: true,';
				}
			}

			$slider_thumbnails_js = "
			$('.wpgs-thumb').slick({
				slidesToShow: $thumb_to_show,
				slidesToScroll: $thumb_scroll_by,
				$thumb_v
				$variableWidth
				arrows: $thumbnails_nav,
				prevArrow:'<span class=\"slick-prev slick-arrow\" aria-label=\"prev\"  ></span>',
				nextArrow:'<span class=\"slick-next slick-arrow\" aria-label=\"Next\" ></span>',
				speed:600,
				infinite: $slider_infinity,
				centerMode: false,
				$wpgs_thumb_js_mode

				responsive: [

					{
					breakpoint: 1025,
					settings: {
						variableWidth: false,
						$thumb_v_tablet
						slidesToShow: $thumbnails_tabs_thumb_to_show,
						slidesToScroll: $thumbnails_tabs_thumb_scroll_by,
						swipeToSlide :true,

					}
					},

					{
					breakpoint: 767,
					settings: {
						variableWidth: false,
						$thumb_v_mobile
						slidesToShow: $thumbnails_mobile_thumb_to_show,
						slidesToScroll: $thumbnails_mobile_thumb_scroll_by,
						swipeToSlide :true,
					}
					}

				]

			});";
		}
		// Check if lightbox is enable

		if ( $lightbox == 'true' && is_product() ) {
			$fp_deps = null;
			if ( defined( 'PORTO_VERSION' ) ) {
				$fp_deps = array( 'porto-plugins' );
			} // Fix fancybox conflict with porto theme

			wp_enqueue_script( 'fancybox', WPGS_ROOT_URL . 'assets/js/jquery.fancybox.min.js', array( 'jquery' ), WPGS_VERSION, false );
			wp_enqueue_style( 'fancybox', WPGS_ROOT_URL . 'assets/css/jquery.fancybox.min.css', $fp_deps, WPGS_VERSION );
		}

		// Check if zoom is enable

		$mobile_zoom = ( cix_wpgs::option( 'image_zoom_mobile' ) == 1 ) ? 'true' : 'false';
		if ( $zoom == 'true' && is_product() && cix_wpgs::option( 'image_zoom_mode' ) != 'inner' ) {
			//TODO: add more options later
			//	wp_enqueue_script( 'ez-plus', WPGS_ROOT_URL . 'assets/js/jquery.ez-plus.js', array( 'jquery' ), WPGS_VERSION, false );
		} elseif ( $zoom == 'true' && is_product() && cix_wpgs::option( 'image_zoom_mode' ) == 'inner' ) {
			wp_enqueue_script( 'twist-imageZoom', WPGS_ROOT_URL . 'assets/js/imageZoom.js', array( 'jquery' ), WPGS_VERSION, false );

		}

		self::$gallery_inline_script = "
			$slider_main_js
			$slider_thumbnails_js


			$slider_lazyLoad_js

			$('.wpgs-wrapper').hide();
			$('.wpgs-wrapper').css(\"opacity\", \"1\");
			$('.wpgs-wrapper').fadeIn();



		";

		if ( is_product() ) {

			wp_enqueue_script( 'wpgs-public', WPGS_ROOT_URL . 'assets/js/public.js', array( 'jquery' ), WPGS_VERSION, true );

			// Localize the script with new data
			$wpgs_js_data = array(
				'thumb_axis'          => cix_wpgs::option( 'lightbox_thumb_axis', 'y' ),
				'thumb_autoStart'     => cix_wpgs::option( 'lightbox_thumb_autoStart', '' ),
				'variation_mode'      => cix_wpgs::option( 'variation_slide', '' ),
				'zoom_action'         => cix_wpgs::option( 'image_zoom_action', 'mouseover' ),
				'zoom_level'          => cix_wpgs::option( 'image_zoom_level', '1' ),
				'lightbox_icon'       => cix_wpgs::option( 'lightbox_icon' ),
				'thumbnails_lightbox' => cix_wpgs::option( 'thumbnails_lightbox' ),
				'slider_caption'      => cix_wpgs::option( 'slider_caption' ),
			);
			wp_localize_script( 'wpgs-public', 'wpgs_js_data', $wpgs_js_data );

			wp_add_inline_script( 'wpgs-public', "(function( $ ) {
  		'use strict';
		$(document).ready(function(){

		" . self::wcavi() . "
		});

		})(jQuery);" );

			// Inline Script for Zoom Feature End;

			wp_enqueue_style( 'slick-theme', WPGS_ROOT_URL . 'assets/css/slick-theme.css' );
			wp_enqueue_style( 'slick', WPGS_ROOT_URL . 'assets/css/slick.css' );

			wp_enqueue_style( 'wpgs', WPGS_ROOT_URL . 'assets/css/wpgs-style.css', array(), WPGS_VERSION );

			//deregister scripts
			wp_dequeue_script( 'photoswipe' );
			wp_dequeue_script( 'photoswipe-ui-default' );
		}

		// Inline CSS for WPGS Start

		if ( $slider_dots == 'true' ) {
			$wpgs_setting_css .= "
			
			.wpgs-dots li button{
				background: ".cix_wpgs::option('dots_color')['color'].";
			}
			.wpgs-dots li button:hover{
				background: ".cix_wpgs::option('dots_color')['hover'].";
			}
			.wpgs-dots li.slick-active button {
				background: ".cix_wpgs::option('dots_color')['active'].";
			}
			";
			if(cix_wpgs::option('dots_placement') == 'inside'){
				$wpgs_setting_css .= "
				.wpgs-dots{
					bottom: ".cix_wpgs::option('dots_placement_inside_margin')."px;
				}
				";
			}
			if(cix_wpgs::option('dots_shape') == 'circle'){
				$wpgs_setting_css .= "
				.wpgs-dots li button{
					border-radius:50px;
				}
				";
			}elseif(cix_wpgs::option('dots_shape') == 'line'){
				$wpgs_setting_css .= "
				.wpgs-dots li button {
				border-radius:0px;
				width: 16px;
				height: 6px;
				}
				.wpgs-image.slick-dotted {
					margin-bottom: 30px !important;
				}
				.wpgs-dots li {
				width: 16px;
				height: 6px;
				overflow:hidden;
				}
				";
			}
			
		}
		if ( cix_wpgs::option( 'lightbox_icon' ) == 'none' ) {
			$wpgs_setting_css .= "
			a.woocommerce-product-gallery__lightbox {

				opacity: 0 !important;
			}
			";
		}
		if ( $mobile_zoom == 'false' && wp_is_mobile() ) {
			$wpgs_setting_css .= "
			a.woocommerce-product-gallery__lightbox {
				display:block !important;
			}
			";
		}

		if ( cix_wpgs::option( 'lightbox_thumb_axis' ) == 'x' ) {
			$wpgs_setting_css .= " @media all and (min-width: 768px) {
			.fancybox-thumbs {
				top: auto;
				width: auto;
				bottom: 0;
				left: 0;
				right : 0;
				height: 95px;
				padding: 10px 10px 5px 10px;
				box-sizing: border-box;
				background: rgba(0, 0, 0, 0.3);

			}

			.fancybox-show-thumbs .fancybox-inner {
				right: 0;
				bottom: 95px;
			}
			.fancybox-thumbs-x .fancybox-thumbs__list{
			margin:0 auto;
			}

			}";
		} else {
			$wpgs_setting_css .= ".fancybox-thumbs{
				width:115px;
			}
			.fancybox-thumbs__list a{
				 max-width: calc(100% - 4px);
				 margin:3px;
			} ";
		}

		// Thumbnails CSS
		if ( $thumb_position == 'left' || $thumb_position == 'right' && $thumbnails_active == 'true' ) {
			$wpgs_setting_css .= "
			.images.wpgs-wrapper .wpgs-image{
				margin-bottom:0px ;
			}
			@media (min-width: 1025px) {


			.wpgs-image {
				width: 79%;
				float: right;

    			margin-left: 1%;
			}
			.wpgs-thumb {
				width: 20%;
			}
			.thumbnail_image {
				margin: 3px 0px;
			}

			}";
		}
		if ( $thumb_position == 'right' && $thumbnails_active == 'true' ) {
			$wpgs_setting_css .= "
			@media (min-width: 1025px) {

			.wpgs-image {
				float: left;
				margin-left: 0%;
    			margin-right: 1%;
			}
			.wpgs-thumb {
				width: 20%;
				float:right
			}
			}";
		} elseif ( $thumb_position == 'left' && $thumbnails_active == 'true' ) {
			$wpgs_setting_css .= "
			@media (min-width: 1025px) {
			.wpgs-thumb {
				width: 20%;
				float: left;
			}
			}";
		}

		if ( $thumbnails_active == 'true' && $thumbnails_style == 'opacity' ) {
			$wpgs_setting_css .= "

			.thumbnail_image:before{
				background: " . cix_wpgs::option( 'thumb_non_active_color' ) . ";
			}

			";
		} elseif ( $thumbnails_active == 'true' && $thumbnails_style == 'border' ) {
			# code...
			$wpgs_setting_css .= "

			.thumbnail_image{
				border: 1px solid " . cix_wpgs::option( 'thumb_border_non_active_color' ) . " !important;
			}
			.thumbnail_image.slick-current{
				border: 1px solid " . cix_wpgs::option( 'thumb_border_active_color' ) . "!important;
				box-shadow: 0px 0px 3px 0px " . cix_wpgs::option( 'thumb_border_active_color' ) . ";
			}

			";
		} else {
		}

		// Slider Navigation css
		if ( $slider_nav_animation == 'false' ) {
			$wpgs_setting_css .= "
			.wpgs-image .slick-prev{
				opacity:1;
				left:0;
			}
			.wpgs-image .slick-next{
				opacity:1;
				right:0;
			}
			";
		}
		$wpgs_setting_css .= "
                 .wpgs-wrapper .slick-prev:before, .wpgs-wrapper .slick-next:before,.wpgs-image button:not(.toggle){

				color: {$slider_nav_icon};
				}
                .wpgs-wrapper .slick-prev,.wpgs-wrapper .slick-next{
				background: {$slider_nav_bg} !important;

				}

				.woocommerce-product-gallery__lightbox {
					 background: {$icon_bg_color};
					 color: {$icon_link_color};
				}

				.fancybox-bg,.fancybox-button{
					background: {$lightbox_bg};
				}
				.fancybox-caption__body,.fancybox-infobar{
					 color: {$lightbox_txt_color};
				}

				.thumbnail_image{
					margin: " . cix_wpgs::option( 'thumb_padding' ) . "px;
				}
				";

		switch ( $slider_icon ) {
		case "icon-right-bold":
			$wpgs_setting_css .= "
				[dir='rtl'] .slick-next:before {
					content: '\\e807';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e806';
				}
				.arrow-next:before,
				.slick-next:before{
				content: '\\e806';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e807';
				}
				";

			break;
		case "icon-right-dir":
			$wpgs_setting_css .= "
				.arrow-next:before,
				.slick-next:before{
				content: '\\e801';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e802';
				}
				[dir='rtl'] .slick-next:before {
					content: '\\e802';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e801';
				}
				";

			break;
		case "icon-right-open-big":
			$wpgs_setting_css .= "
				.arrow-next:before,
				.slick-next:before{
				content: '\\e804';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e805';
				}
				[dir='rtl'] .slick-next:before {
					content: '\\e805';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e804';
				}
				";
			break;

		default:
			$wpgs_setting_css .= "
				.arrow-next:before,
				.slick-next:before{
				content: '\\e80a';
				}
				.arrow-prev:before,
				.slick-prev:before{
				content: '\\e80b';
				}
				[dir='rtl'] .slick-next:before {
					content: '\\e80b';
				}
				[dir='rtl'] .slick-prev:before {
					content: '\\e80a';
				}
				";
		}

		// Thumbnails CSS for min-width: 767px to 1024px
		if ( $thumb_position_tablet == 'left' || $thumb_position_tablet == 'right' ) {
			$wpgs_setting_css .= "

			@media (min-width: 768px) and (max-width: 1024px)  {

			.wpgs-image {
				width: 79%;
				float: right;

    			margin-left: 1%;
			}
			.wpgs-thumb {
				width: 20%;
			}
			.thumbnail_image {
				margin: 3px 0px;
			}

			}";
		}
		if ( $thumb_position_tablet == 'right' ) {
			$wpgs_setting_css .= "
			@media (min-width: 768px) and (max-width: 1024px)  {

			.wpgs-image {
				float: left;
				margin-left: 0%;
    			margin-right: 1%;
			}
			.wpgs-thumb {
				width: 20%;
				float:right
			}
			}";
		} elseif ( $thumb_position_tablet == 'left' ) {
			$wpgs_setting_css .= "
			@media (min-width: 768px) and (max-width: 1024px) {
			.wpgs-thumb {
				width: 20%;
				float: left;
			}
			}";
		}

		// Thumbnails CSS for max-width: 767px
		if ( $thumb_position_mobile == 'left' || $thumb_position_mobile == 'right' ) {
			$wpgs_setting_css .= "
			@media only screen and (max-width: 767px)  {


			.wpgs-image {
				width: 79%;
				float: right;

    			margin-left: 1%;
			}
			.wpgs-thumb {
				width: 20%;
			}
			.thumbnail_image {
				margin: 3px 0px;
			}

			}";
		}
		if ( cix_wpgs::option( 'lightbox_icon' ) == 'none' ) {
			$wpgs_setting_css .= "
			@media only screen and (max-width: 767px)  {

			a.woocommerce-product-gallery__lightbox {
    		opacity: 1 !important;
			}
			}";

		}

		if ( $thumb_position_mobile == 'right' ) {
			$wpgs_setting_css .= "
			@media only screen and (max-width: 767px)  {

			.wpgs-image {
				float: left;
				margin-left: 0%;
    			margin-right: 1%;
			}
			.wpgs-thumb {
				width: 20%;
				float:right
			}
			}";
		} elseif ( $thumb_position_mobile == 'left' ) {
			$wpgs_setting_css .= "
			@media only screen and (max-width: 767px)  {
			.wpgs-thumb {
				width: 20%;
				float: left;
			}
			}";
		}

		if ( is_product() ) {
			if ( count( $attachment_ids ) + 1 <= $thumb_to_show ) {
				$wpgs_setting_css .= "
				@media only screen and (min-width: 767px) {
					.wpgs-thumb .slick-track {
						transform: inherit !important;
					}
				}
				";
			}
			
			if(empty($attachment_ids)){
				$wpgs_setting_css .= "
					.wpgs-dots {
						display:none;
					}
				";
				
			}
		}
		// Plugin custom css option
		$wpgs_setting_css .= cix_wpgs::option( 'custom_css' );
		wp_add_inline_style( 'wpgs', $wpgs_setting_css );
		// Inline CSS for WPGS END
	}

	public static function wcavi() {
		return self::$gallery_inline_script;
	}
}

new WpgsPublicCode;

if ( !function_exists( 'wpgs_get_image_gallery_html' ) ) {

	// Custom HTML layout
	/**
	 * @param $attachment_id
	 * @param $main_image
	 */
	function wpgs_get_image_gallery_html( $attachment_id, $main_image = false ) {
		$size = apply_filters( 'wpgs_new_main_img_size', cix_wpgs::option( 'slider_image_size' ) );
		/* Plugin Options */
		$lightbox = ( cix_wpgs::option( 'lightbox_picker' ) == 1 ) ? 'true' : 'false';
		//Zoom Icon
		$zoom_icon_class                                            = cix_wpgs::option( 'lightbox_icon' );
		$lightbox_img_alt                                           = ( cix_wpgs::option( 'lightbox_alt_text' ) == 1 ) ? 'true' : 'false';
		$img_caption                                                = ( cix_wpgs::option( 'slider_caption' ) == 'caption' ) ? wp_get_attachment_caption( $attachment_id ) : get_the_title( $attachment_id );
		( $lightbox_img_alt == 'true' ) ? $img_caption : $img_caption = '';
		// Check if Gallery have Video URL

		$lightbox_animation        = cix_wpgs::option( 'lightbox_oc_effect' );
		$lightbox_slides_animation = cix_wpgs::option( 'lightbox_slide_effect' );
		$lightbox_img_count        = ( cix_wpgs::option( 'lightbox_img_count' ) == 1 ) ? 'true' : 'false';

		$img_has_video = get_post_meta( $attachment_id, 'twist_video_url', true );
		// $gallery_first_item_class = ( cix_wpgs::option( 'variation_slide' ) == 'default' ) ? 'woocommerce-product-gallery__image' : 'wpgs1';
		$video_class    = $img_has_video ? 'wpgs-video' : '';
		$gallery__image = ( $main_image ) ? 'class="woocommerce-product-gallery__image wpgs_image"' : 'class="wpgs_image"';

		$img_lightbox_url    = $img_has_video ? $img_has_video : wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_lightbox_image_size', 'full' ) );
		$img_lightbox_srcset = wp_get_attachment_image_srcset( $attachment_id );
		$caption_html        = ( cix_wpgs::option( 'slider_alt_text' ) == 1 ) ? '<span class="wpgs-gallery-caption">' . $img_caption . '</span>' : '';
		$image               = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(
				// 'title'            => _wp_specialchars( get_post_field( 'post_title', $attachment_id ), ENT_QUOTES, 'UTF-8', true ),
				'alt'              => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				'class'            => esc_attr( $main_image ? 'wp-post-image img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) : 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
				'src'              => apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ),
				'data-lazy'        => wp_get_attachment_image_url( $attachment_id, $size ),
				'data-o_img'       => wp_get_attachment_image_url( $attachment_id, $size ),
				'data-large_image' => wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', 'large' ) ),
				'data-zoom-image'  => wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', 'large' ) ),
				'data-caption'     => $img_caption,

			),
			$attachment_id,
			$main_image
		);

		if ( $lightbox == 'true' ) {
			$markup = '<div ' . $gallery__image . ' data-attachment-id=' . $attachment_id . ' >' . $image . '<a class=" woocommerce-product-gallery__lightbox ' . $video_class . '"
			href = "' . $img_lightbox_url . '"
			data-elementor-open-lightbox="no"
			data-caption="' . $img_caption . '"
			data-thumb="' . wp_get_attachment_image_url( $attachment_id, apply_filters( 'wpgs_new_thumb_img_size', 'woocommerce_gallery_thumbnail' ) ) . '"
			data-fancybox="wpgs"
			data-zoom-image=' . wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', 'large' ) ) . '
			data-animation-effect="' . $lightbox_animation . '"
			data-transition-effect="' . $lightbox_slides_animation . '"
			data-infobar="' . $lightbox_img_count . '"
			data-loop="true"
			data-hash="false"
			data-click-slide="close"
			data-options=\'{"buttons": ["zoom","slideShow","fullScreen","thumbs","close"] }\'

			>
			<i class="' . $zoom_icon_class . '"></i>
			</a>' . $caption_html . '</div>';
			return $markup;
		} elseif ( $lightbox == 'false' ) {
			$markup = '<div ' . $gallery__image . ' data-attachment-id=' . $attachment_id . '>' . $image . $caption_html . '</div>';

			return $markup;
		}
	}
}

if ( !function_exists( 'wpgs_get_image_gallery_thumb_html' ) ) {

	// Custom HTML layout
	/**
	 * @param $attachment_id
	 * @param $main_image
	 */
	function wpgs_get_image_gallery_thumb_html( $attachment_id, $main_image = false ) {

		$size = apply_filters( 'wpgs_new_thumb_img_size', cix_wpgs::option( 'slider_image_thumb_size' ) );

		/* Plugin Options */

		$lightbox_img_alt                                           = ( cix_wpgs::option( 'lightbox_alt_text' ) == 1 ) ? 'true' : 'false';
		$img_caption                                                = ( empty( wp_get_attachment_caption( $attachment_id ) ) ) ? get_the_title( $attachment_id ) : wp_get_attachment_caption( $attachment_id );
		( $lightbox_img_alt == 'true' ) ? $img_caption : $img_caption = '';
		// Check if Gallery have Video URL

		$lightbox_animation        = cix_wpgs::option( 'lightbox_oc_effect' );
		$lightbox_slides_animation = cix_wpgs::option( 'lightbox_slide_effect' );
		$lightbox_img_count        = ( cix_wpgs::option( 'lightbox_img_count' ) == 1 ) ? 'true' : 'false';

		$img_has_video = get_post_meta( $attachment_id, 'twist_video_url', true );
		$video_class   = $img_has_video ? 'wpgs-video' : '';

		$gallery_thumb_image = $main_image ? 'class="gallery_thumbnail_first thumbnail_image ' . $video_class . ' "' : 'class="thumbnail_image ' . $video_class . '"';

		$img_lightbox_url = $img_has_video ? $img_has_video : wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_lightbox_image_size', 'full' ) );

		$image = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(

				'alt'        => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				'class'      => esc_attr( $main_image ? 'wp-post-image img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) : 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
				'src'        => apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ),
				'data-lazy'  => wp_get_attachment_image_url( $attachment_id, $size ),
				'data-thumb' => wp_get_attachment_image_url( $attachment_id, $size ),

			),
			$attachment_id,
			$main_image
		);

		if ( apply_filters( 'wpgs_carousel_mode', true ) == false ) {
			$markup = '<a ' . $gallery_thumb_image . '
			href = "' . $img_lightbox_url . '"
			data-elementor-open-lightbox="no"
			data-caption="' . $img_caption . '"
			data-thumb="' . wp_get_attachment_image_url( $attachment_id, $size ) . '"
			data-fancybox="wpgs" data-animation-effect="' . $lightbox_animation . '" data-transition-effect="' . $lightbox_slides_animation . '"
			data-infobar="' . $lightbox_img_count . '"
			data-loop="true"
			data-hash="false"
			data-click-slide="close"
			data-options=\'{"buttons": ["zoom","slideShow","fullScreen","thumbs","close"] }\'

			>
			' . $image . '
			</a>';
			return $markup;
		} else {
			//  the thumbnail markup
			return '<div ' . $gallery_thumb_image . '>' . $image . '</div>';
		}
	}
}
