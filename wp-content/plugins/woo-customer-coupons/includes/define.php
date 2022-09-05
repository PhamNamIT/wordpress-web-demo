<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'WOO_CUSTOM_COUPONS_DIR', WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woo-customer-coupons" . DIRECTORY_SEPARATOR );
define( 'WOO_CUSTOM_COUPONS_LANGUAGES', WOO_CUSTOM_COUPONS_DIR . "languages" . DIRECTORY_SEPARATOR );
define( 'WOO_CUSTOM_COUPONS_INCLUDES', WOO_CUSTOM_COUPONS_DIR . "includes" . DIRECTORY_SEPARATOR );
define( 'WOO_CUSTOM_COUPONS_ADMIN', WOO_CUSTOM_COUPONS_INCLUDES . "admin" . DIRECTORY_SEPARATOR );
define( 'WOO_CUSTOM_COUPONS_FRONTEND', WOO_CUSTOM_COUPONS_INCLUDES . "frontend" . DIRECTORY_SEPARATOR );
$plugin_url = plugins_url( 'woo-customer-coupons/assets' );
//$plugin_url = plugins_url( '', __FILE__ );
$plugin_url = str_replace( '/includes', '/assets', $plugin_url );
define( 'WOO_CUSTOM_COUPONS_CSS', $plugin_url . "/css/" );
define( 'WOO_CUSTOM_COUPONS_CSS_DIR', WOO_CUSTOM_COUPONS_DIR . "css" . DIRECTORY_SEPARATOR );
define( 'WOO_CUSTOM_COUPONS_JS', $plugin_url . "/js/" );
define( 'WOO_CUSTOM_COUPONS_JS_DIR', WOO_CUSTOM_COUPONS_DIR . "js" . DIRECTORY_SEPARATOR );
define( 'WOO_CUSTOM_COUPONS_IMG', $plugin_url . "/images/" );

/*Include functions file*/
if ( is_file( WOO_CUSTOM_COUPONS_INCLUDES . "functions.php" ) ) {
	require_once WOO_CUSTOM_COUPONS_INCLUDES . "functions.php";
}

if ( is_file( WOO_CUSTOM_COUPONS_INCLUDES . "data.php" ) ) {
	require_once WOO_CUSTOM_COUPONS_INCLUDES . "data.php";
}
if ( is_file( WOO_CUSTOM_COUPONS_INCLUDES . "coupons-table.php" ) ) {
	require_once WOO_CUSTOM_COUPONS_INCLUDES . "coupons-table.php";
}

if ( is_file( WOO_CUSTOM_COUPONS_INCLUDES . "support.php" ) ) {
	require_once WOO_CUSTOM_COUPONS_INCLUDES . "support.php";
}


vi_include_folder( WOO_CUSTOM_COUPONS_ADMIN, 'WOO_CUSTOM_COUPONS_Admin_' );
vi_include_folder( WOO_CUSTOM_COUPONS_FRONTEND, 'WOO_CUSTOM_COUPONS_Frontend_' );