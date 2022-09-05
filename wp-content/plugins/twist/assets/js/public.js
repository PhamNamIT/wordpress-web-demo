(function ($) {
  'use strict';

  $(document).ready(function () {

    // Check if have Fancybox
    if (typeof $.fn.fancybox == 'function') {
      // Customize icons
      var data_autostat = false;
      if (wpgs_js_data.thumb_autoStart == '1') {
        data_autostat = true;
      }
      $.fancybox.defaults = $.extend(true, {}, $.fancybox.defaults, {
        btnTpl: {

          // Arrows
          arrowLeft: '<button data-fancybox-prev class="fancybox-button fancybox-button--arrow fancybox-button--arrow_left" title="{{PREV}}">' +
            '<span class="arrow-prev"></span>' +
            "</button>",

          arrowRight: '<button data-fancybox-next class="fancybox-button fancybox-button--arrow fancybox-button--arrow_right" title="{{NEXT}}">' +
            '<span class="arrow-next"></span>' +
            "</button>",


        },
        thumbs: {
          autoStart: data_autostat,
          hideOnClose: true,
          parentEl: ".fancybox-container",
          axis: wpgs_js_data.thumb_axis
        },
        mobile: {
          clickContent: "close",
          clickSlide: "close",
          thumbs: {
            autoStart: false,
            axis: "y"
          }
        }
      });


      var selector = '.wpgs-wrapper .slick-slide:not(.slick-cloned) a';

      // Skip cloned elements
      $().fancybox({
        selector: selector,
        backFocus: false
      });

      // Attach custom click event on cloned elements, 
      // trigger click event on corresponding link
      $(document).on('click', '.wpgs-wrapper .slick-cloned a', function (e) {
        $(selector)
          .eq(($(e.currentTarget).attr("data-slick-index") || 0) % $(selector).length)
          .trigger("click.fb-start", {
            $trigger: $(this)
          });

        return false;
      });

    }

    // Variation Data

    var get_thumb_first = $(document).find('.gallery_thumbnail_first');
    var get_main_first = $(document).find('.woocommerce-product-gallery__image');
    get_main_first.find('img').removeAttr('srcset');
    jQuery('.variations_form').each(function () {

      jQuery(this).on('show_variation', function (event, variation) {

        if (wpgs_js_data.variation_mode == 'default') {
          var thumb_src = variation.image.gallery_thumbnail_src,
            variable_image_caption,
            data_caption_source = wpgs_js_data.slider_caption,
            first_thumb_src = get_main_first.find('img').attr("src");
          get_thumb_first.find('img').attr('src', thumb_src);
          get_main_first.find('img').attr('src', variation.image.src);
          get_main_first.find('img').removeAttr('srcset');
          if (data_caption_source == 'title') {
            variable_image_caption = variation.image.title;
          } else {
            variable_image_caption = variation.image.caption;
          }
          get_main_first.find('.wpgs-gallery-caption').empty().append(variable_image_caption);
          get_main_first.find('.woocommerce-product-gallery__lightbox').data('caption', variable_image_caption);

          // Reset Slider location to '0' when variation change
          $('.woocommerce-product-gallery__image .wp-post-image').on('load', function () {
            if (wpgs_js_data.thumbnails_lightbox != 1) {
              $('.wpgs-image').slick('slickGoTo', 0);
            }


            $('.woocommerce-product-gallery__image').find('.zoomImg').attr('src', variation.image.url);

            if (get_main_first.find('.wp-post-image').data("o_img") == get_main_first.find('.wp-post-image').attr("src")) {
              get_main_first.find('.wpgs-gallery-caption').empty().append(get_main_first.find('.wp-post-image').data('caption'));
              get_main_first.find('.woocommerce-product-gallery__lightbox').data('caption', get_main_first.find('.wp-post-image').data('caption'));
              get_thumb_first.find('img').attr('src', get_thumb_first.find('img').data("thumb"));
              $('.woocommerce-product-gallery__image').find('.zoomImg').attr('src', get_main_first.find('.wp-post-image').data("large_image"));
            }


          });


        } else {
          var gallery_slide_index = $('.wpgs-image').find('[data-attachment-id="' + variation.image_id + '"]').data('slick-index');

          if (gallery_slide_index !== undefined) {
            $('.wpgs-image').slick('slickGoTo', gallery_slide_index);
          }
        }

      });
    });
    // Check if ezPlus enable
    // if (typeof $.fn.ezPlus == 'function') {
    //   $(".wpgs_image img").ezPlus({

    //     zoomType: 'inner',
    //     scrollZoom: true,
    //     loadingIcon: 'http://www.elevateweb.co.uk/spinner.gif',
    //     cursor: 'crosshair',
    //     borderColour: '#888',
    //   });

    // }

    $('.thumbnail_image').each(function (index) {
      $(this).on('click', function () {
        $('.thumbnail_image').removeClass('slick-current');
        $(this).addClass('slick-current');
        $('.woocommerce-product-gallery__lightbox').css({ "display": "none" });
        setTimeout(function () {
          $('.slick-current .woocommerce-product-gallery__lightbox').css({ "display": "block", "opacity": "1" });
          $('.woocommerce-product-gallery__image .woocommerce-product-gallery__lightbox').css({ "display": "block", "opacity": "1" });
        }, 400);

      });
    });


    $('.wpgs_image img').each(function () {
      $(this).wrap("<div class='zoomtoo-container' data-zoom-image=" + $(this).data("large_image") + "></div>");
    });

    // var imgUrl = $(this).data("zoom-image");
    if (typeof $.fn.zoom == 'function') {
      $('.zoomtoo-container').zoom({

        // Set zoom level from 1 to 5.
        magnify: wpgs_js_data.zoom_level,
        // Set what triggers the zoom. You can choose mouseover, click, grab, toggle.
        on: wpgs_js_data.zoom_action,
      });
    }

    if (wpgs_js_data.lightbox_icon == 'none' && wpgs_js_data.zoom_action == 'mouseover') {
      $('.zoomtoo-container').on('click', function () {
        $(this).next().trigger("click");
      });

    }



    // Remove SRCSET for Thumbanils
    $('.wpgs-thumb img').each(function () {
      $(this).removeAttr('srcset', 'data-thumb_image');
      $(this).removeAttr('data-thumb_image');
      $(this).removeAttr('sizes');
      $(this).removeAttr('data-large_image');
    });

    function ZoomIconApperce() {
      setTimeout(function () {
        $('.woocommerce-product-gallery__lightbox').css({ "display": "block", "opacity": "1" });

      }, 500);

    }

    // On swipe event
    $('.wpgs-image').on('swipe', function (event, slick, direction) {
      $('.woocommerce-product-gallery__lightbox').css({ "display": "none" });
      ZoomIconApperce();
    });
    // On edge hit
    $('.wpgs-image').on('afterChange', function (event, slick, direction) {
      ZoomIconApperce();
    });
    $('.wpgs-image').on('click', '.slick-arrow ,.slick-dots', function () {
      $('.woocommerce-product-gallery__lightbox').css({ "display": "none" });
      ZoomIconApperce();
    });
    $('.wpgs-image').on('init', function (event, slick) {
      ZoomIconApperce();
    });


  });
  // if found prettyphoto rel then unbind click
  $(window).on('load', function () {
    $("a.woocommerce-product-gallery__lightbox").attr('rel', ''); // remove prettyphoto
    $("a.woocommerce-product-gallery__lightbox").removeAttr('data-rel'); // remove prettyphoto ("id")	
    $('a.woocommerce-product-gallery__lightbox').unbind('click.prettyphoto');

  });


})(jQuery);

// Other code using $ as an alias to the other library