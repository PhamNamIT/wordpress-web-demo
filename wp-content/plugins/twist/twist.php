<?php

/**
 * The plugin bootstrap file
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Product Gallery Slider for WooCommerce - Twist
 * Plugin URI:        https://www.codeixer.com/product-gallery-slider-for-woocommerce/
 * Description:       Fully customizable image gallery slider for the product page.comes with vertical and horizontal gallery layouts, clicking, sliding, image navigation, fancybox 3 & many more exciting features.
 * Version:           3.3
 * Author:            Codeixer
 * Author URI:        https://codeixer.com
 * Text Domain:       wpgs-td
 * Domain Path:       /languages
 * Tested up to: 6.0.1
 * WC requires at least: 4.0
 * WC tested up to: 6.8.1
 * Requires PHP: 7.2
 * @package           twist
 *
 * @link              http://codeixer.com
 * @since             1.0.0
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}
// Check if the free version is enabled, and if so, disable it
if ( in_array( 'woo-product-gallery-slider/woo-product-gallery-slider.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	deactivate_plugins( 'woo-product-gallery-slider/woo-product-gallery-slider.php' );
}

require __DIR__ . '/vendor/autoload.php';

define( 'WPGS', 'TRUE' );
define( 'WPGS_VERSION', '3.2.13' );
define( 'WPGS_NAME', 'Product Gallery Slider for WooCommerce' );
define( 'WPGS_INC', plugin_dir_path( __FILE__ ) . 'inc/' );
define( 'WPGS_ROOT', plugin_dir_path( __FILE__ ) . '' );
define( 'WPGS_ROOT_URL', plugin_dir_url( __FILE__ ) . '' );
define( 'WPGS_INC_URL', plugin_dir_url( __FILE__ ) . 'inc/' );
define( 'WPGS_PLUGIN_BASE', plugin_basename( __FILE__ ) );

class cix_wpgs {
	/**
	 * @var mixed
	 */
	private $divi_builder;

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'core_files' ) );
		add_action( 'plugins_loaded', array( $this, 'after_woo_hooks' ) );
		add_action( 'after_setup_theme', array( $this, 'remove_woo_support' ), 20 );
		$this->divi_builder = ( self::option( 'check_divi_builder' ) == '1' ) ? 'true' : 'false';
		register_activation_hook( __FILE__, array( $this, 'twist_plugin_activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'twist_plugin_deactivate' ) );
		// Switch for Divi Page builder conflict Issue
		if ( $this->divi_builder == 'false' ) {
			add_action( 'woocommerce_before_single_product_summary', array( $this, 'wpgs_templates' ) );
		}

		add_action( 'plugins_loaded', array( $this, 'load_plugin_code' ) );
		add_action( 'admin_init', array( 'PAnD', 'init' ) );

		add_action( 'admin_notices', [$this, 'sdo_notice'] );
		// check for plugin using plugin name
		if ( in_array( 'deposits-for-woocommerce/deposits-for-woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			//plugin is activated
			remove_action( 'admin_notices', [$this, 'sdo_notice'] );
		}

	}
	/**
	 * @return null
	 */
	function sdo_notice() {
		if ( !\PAnD::is_admin_notice_active( 'woo_deposits1-notice-25' ) ) {
			return;
		}

		?>
		<div id="wptrt-notice-woo_deposits1" data-dismissible="woo_deposits1-notice-25" class="notice is-dismissible notice-info codeixer-notice-wrapper">
        <p> <img src="https://ps.w.org/deposits-for-woocommerce/assets/icon-128x128.png" /></p>
       <p> <strong>2000+ </strong>WooCommerce powered shop uses our plugin to enable customers to pay for products using a deposit payment.</p>
       <p> <a target="_blank" href="<?php echo admin_url( '/plugin-install.php?s=bayna&tab=search&type=term' ); ?>" class="button button-alt">Install Now</a></p>
		</div>
		<?php
}
	/**
	 * Run code on plugin activation
	 *
	 * @return void
	 */
	public function twist_plugin_activate() {
		if ( !get_option( 'twist_activation_time' ) ) {
			update_option( 'twist_activation_time', current_time( 'timestamp' ) );
		}
	}
	public function twist_plugin_deactivate() {
		wp_clear_scheduled_hook( 'cix_plugin_list_cron' );
	}

	public function remove_woo_support() {
		remove_theme_support( 'wc-product-gallery-lightbox' );
		remove_theme_support( 'wc-product-gallery-slider' );
		remove_theme_support( 'wc-product-gallery-zoom' );

	}

	public function after_woo_hooks() {
		remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 ); // Remove Default Image Gallery
	}

	public static function wpgs_templates() {

		// Override with 'wpgs_get_template' Filter for displays
		// Custom Page
		wc_get_template( 'single-product/product-image.php' );
	}

	public function core_files() {
		require WPGS_ROOT . 'core/codestar-framework/codestar-framework.php';
		require WPGS_ROOT . 'core/codeixer-core.php';
		require WPGS_INC . 'admin/class-image-sizes.php';
		require WPGS_INC . 'admin/admin.php';
		require WPGS_INC . 'admin/elementor-twist.php';
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param  string  $option  settings field name
	 * @param  string  $section the section name this field belongs to
	 * @param  string  $default default text if it's not found
	 * @return mixed
	 */
	public static function option( $option, $default = '' ) {
		$options = get_option( 'wpgs_form' );

		if ( isset( $options[$option] ) ) {
			return $options[$option];
		}

		return $default;
	}

	public function load_plugin_code() {
		require WPGS_INC . 'public/public.php';
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'wpgs-td', false, plugin_dir_url( __FILE__ ) . "/languages" );
	}
}

$cix_wpgs = new cix_wpgs;

/**
 *  Elite Licenser Activation Code
 */
require WPGS_INC . 'admin/TwistBase.php';

class Twist {
	/**
	 * @var mixed
	 */
	public $plugin_file = __FILE__;
	/**
	 * @var mixed
	 */
	public $responseObj;
	/**
	 * @var mixed
	 */
	public $licenseMessage;
	/**
	 * @var mixed
	 */
	public $showMessage = true;
	/**
	 * @var string
	 */
	public $slug = "twist";
	/**
	 * @var string
	 */
	public $slug_page = "codeixer-dashboard";
	public function __construct() {
		$licenseKey = (cix_wpgs::option('purchse_key_ovrd') == 1) ? str_replace( ' ', '', cix_wpgs::option('purchse_key') ) : str_replace( ' ', '', get_option( "Twist_lic_Key", "" ) );
		$liceEmail  = get_option( "Twist_lic_email", "" );
		TwistBase::addOnDelete( function () {
			delete_option( "Twist_lic_Key" );
		} );
		add_filter( 'has_codeixer_pro', '__return_true' );
		if ( TwistBase::CheckWPPlugin( $licenseKey, $liceEmail, $this->licenseMessage, $this->responseObj, __FILE__ ) ) {
			add_action( 'admin_post_Twist_el_deactivate_license', [$this, 'action_deactivate_license'] );

			//Write you plugin's code here

			add_action( 'codeixer_license_data', [$this, 'Activated'] );
		} else {
			if ( !empty( $licenseKey ) && !empty( $this->licenseMessage ) ) {
				$this->showMessage = true;
			}
			update_option( "Twist_lic_Key", "" ) || add_option( "Twist_lic_Key", "" );
			add_action( 'admin_post_Twist_el_activate_license', [$this, 'action_activate_license'] );

			// codexier-lisence-active
			add_action( 'codeixer_license_form', [$this, 'LicenseForm'] );
			add_action( 'admin_notices', [$this, 'admin_notice'] );
		}
	}
	public function admin_notice() {
		?>
        <div class="notice notice-error codeixer-notice">

            <p><?php _e( ' Would you like to receive automatic updates, awesome support? Please', 'twist' );?> <a href="<?php echo admin_url( 'admin.php?page=codeixer-dashboard' ); ?>"><?php _e( 'activate your copy', 'twist' );?></a> <?php _e( 'of ', 'twist' );
		echo '<b>' . WPGS_NAME . '</b>';?> </p>

        </div>
       <?php
}

	public function action_activate_license() {
		check_admin_referer( 'el-license' );
		$licenseKey   = !empty( $_POST['el_license_key'] ) ? $_POST['el_license_key'] : "";
		$licenseEmail = !empty( $_POST['el_license_email'] ) ? $_POST['el_license_email'] : "";
		update_option( "Twist_lic_Key", $licenseKey ) || add_option( "Twist_lic_Key", $licenseKey );
		update_option( "Twist_lic_email", $licenseEmail ) || add_option( "Twist_lic_email", $licenseEmail );
		wp_safe_redirect( admin_url( 'admin.php?page=' . $this->slug_page ) );
	}
	public function action_deactivate_license() {
		check_admin_referer( 'el-license' );
		$message = "";
		if ( TwistBase::RemoveLicenseKey( __FILE__, $message ) ) {
			update_option( "Twist_lic_Key", "" ) || add_option( "Twist_lic_Key", "" );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . $this->slug_page ) );
	}
	public function Activated() {

		?>
        <div class="cix-card-active">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="Twist_el_deactivate_license"/>
                <div class="el-license-container">
                      <h3> Activate <b>Product Gallery Slider for WooCommerce PRO</b> by entering your license key to get professional support and automatic update from your WordPress dashboard.</h3>


						<div class="el-license-field">
							<input type="text" class=" txt-disable" disabled value="<?php echo esc_attr( $this->responseObj->license_key ); ?>">
						</div>

                    <div class="el-license-active-btn deactivate-btn">
                        <?php wp_nonce_field( 'el-license' );?>
                        <?php submit_button( 'Deactivate License' );?>
                    </div>
                </div>
            </form>
            <div class="infobox">

				<div class="simpletext"><span><?php echo $this->responseObj->msg; ?></span></div>
			</div>
        </div>
    <?php }

	public function LicenseForm() {?>

        <div class="cix-card-active">
            <h3> Activate <b>Product Gallery Slider for WooCommerce PRO</b> by entering your license key to get professional support and automatic update from your WordPress dashboard.</h3>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="Twist_el_activate_license"/>
                <div class="el-license-container">

                <?php

		if ( !empty( $this->showMessage ) && !empty( $this->licenseMessage ) ) {
			?>
                    <div class="notice-error">
                            <p><?php echo _e( $this->licenseMessage, $this->slug ); ?></p>
                        </div>
                        <?php
}?>

                    <div class="el-license-field">

                        <input type="text" class=" code" name="el_license_key" size="50" placeholder="Enter Purchase Code" required="required" >
						<a target="_blank" href="https://www.codeixer.com/docs/where-is-my-purchase-code/" class="href">Where Is My Purchase Code?</a>
                    </div>

                    <div class="el-license-active-btn">
                        <?php wp_nonce_field( 'el-license' );?>
                        <?php submit_button( 'Activate License' );?>
                    </div>
                </div>
            </form>

        </div>

    <?php }
}

new Twist;

/* Elite Licenser Activation Code End */
