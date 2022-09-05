<?php
/**
 * Plugin Name: Customer Coupons for WooCommerce
 * Plugin URI:https://villatheme.com/extensions/woocommerce-customer-coupons/
 * Description: Customer Coupons for WooCommerce helps you display your coupons on website.
 * Author: VillaTheme
 * Author URI:https://villatheme.com
 * Version: 1.2.0
 * Text Domain: woo-customer-coupons
 * Domain Path: /languages
 * Copyright 2019-2022 VillaTheme.com. All rights reserved.
 * Requires PHP: 7.0
 * Requires at least: 5.0
 * Tested up to: 6.0
 * WC requires at least: 5.0
 * WC tested up to: 6.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WOO_CUSTOM_COUPONS_VERSION', '1.2.0' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	$init_file = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woo-customer-coupons" . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "define.php";
	require_once $init_file;
} else {
	if ( ! function_exists( 'vi_woo_customer_coupons_notification' ) ) {
		function vi_woo_customer_coupons_notification() {
			?>
            <div id="message" class="error">
                <p><?php _e( 'Please install and activate WooCommerce to use Customer Coupons for WooCommerce.', 'woo-customer-coupons' ); ?></p>
            </div>
			<?php
		}
	}
	add_action( 'admin_notices', 'vi_woo_customer_coupons_notification' );
}