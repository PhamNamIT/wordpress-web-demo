'use strict';
jQuery(document).ready(function () {
    jQuery('.vi-ui.accordion').accordion('refresh');
    jQuery('.vi-ui.dropdown').unbind().dropdown();
    jQuery('.vi-ui.checkbox').unbind().checkbox();
    jQuery('.vi_wcc_template').dropdown({
        onChange: function (val) {
            jQuery('.vi_wcc_template_style').addClass('vi_wcc_hidden');
            jQuery('.vi_wcc_template_style.vi_wcc_template_' + val).removeClass('vi_wcc_hidden');
        }
    });
    jQuery('input[type="checkbox"]').unbind().on('change', function () {
        if (jQuery(this).prop('checked')) {
            jQuery(this).parent().find('input[type="hidden"]').val('1');
        } else {
            jQuery(this).parent().find('input[type="hidden"]').val('');
        }
    });
    jQuery('body').on('click', function () {
        jQuery('.iris-picker').hide();
    });
    jQuery('.color-picker').unbind().iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
        },
        hide: true,
        border: true
    }).on('click', function (e) {
        jQuery('.iris-picker').hide();
        jQuery(this).parent().find('.iris-picker').show();
        e.stopPropagation();
    }).on('keyup', function () {
        jQuery(this).parent().find('.color-picker').css({'background': jQuery(this).val()});
    });

    //css for button "shop now"
    jQuery('#wcc_button_shop_now_bg_color').iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            jQuery('.wcc-button-shop-now').css({backgroundColor: ui.color.toString()});
        }
    }).on('keyup', function () {
        jQuery(this).parent().find('.color-picker').css({'background': jQuery(this).val()});
        jQuery('.wcc-button-shop-now').css({'background': jQuery(this).val()});
    });
    jQuery('#wcc_button_shop_now_color').iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
            jQuery('.wcc-button-shop-now').css({color: ui.color.toString()});
        }
    }).on('keyup', function () {
        jQuery(this).parent().find('.color-picker').css({'background': jQuery(this).val()});
        jQuery('.wcc-button-shop-now').css({'color': jQuery(this).val()});
    });
    jQuery('#wcc_button_shop_now_size').on('change', function () {
        jQuery('.wcc-button-shop-now').css({'font-size': jQuery(this).val() + 'px'});
    });
    jQuery('#wcc_button_shop_now_border_radius').on('change', function () {
        jQuery('.wcc-button-shop-now').css({'border-radius': jQuery(this).val() + 'px'});
    });
    jQuery('#wcc_button_shop_now_url').on('change', function () {
        jQuery('.wcc-button-shop-now').attr('href', jQuery(this).val());
    });
    jQuery('#wcc_button_shop_now_title').on('change', function () {
        jQuery('.wcc-button-shop-now').html(jQuery(this).val());
    });


    /*preview email*/
    jQuery('.preview-emails-html-overlay').on('click', function () {
        jQuery('.preview-emails-html-container').addClass('vi_wcc_hidden');
    })
    jQuery('.wcc-preview-emails-button').on('click', function () {
        jQuery(this).html('Please wait...');
        jQuery.ajax({
            url: vi_wcc_admin_settings.ajax_url,
            type: 'GET',
            dataType: 'JSON',
            data: {
                action: 'wcc_preview_emails',
                heading: jQuery('#wcc_mail_heading').val(),
                content: tinyMCE.get('wcc_mail_content') ? tinyMCE.get('wcc_mail_content').getContent() : jQuery('#wcc_mail_content').val(),
                button_shop_title: jQuery('#wcc_button_shop_now_title').val(),
                button_shop_url: jQuery('#wcc_button_shop_now_url').val(),
                button_shop_bg_color: jQuery('#wcc_button_shop_now_bg_color').val(),
                button_shop_color: jQuery('#wcc_button_shop_now_color').val(),
                button_shop_size: jQuery('#wcc_button_shop_now_size').val(),
                button_shop_border_radius: jQuery('#wcc_button_shop_now_border_radius').val(),
            },
            success: function (response) {
                jQuery('.wcc-preview-emails-button').html('Preview emails');
                if (response) {
                    jQuery('.preview-emails-html').html(response.html);
                    jQuery('.preview-emails-html-container').removeClass('vi_wcc_hidden');
                }
            },
            error: function (err) {
                jQuery('.wcb-preview-emails-button').html('Preview emails');
            }
        })
    });

    //reset setting
    jQuery('.vi_wcc_settings_default').unbind().click(function () {
        if (confirm(vi_wcc_admin_settings.setting_default)) {
            jQuery(this).attr('type', 'submit');
        }
    });
});