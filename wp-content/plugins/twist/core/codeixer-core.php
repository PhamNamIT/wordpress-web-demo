<?php

// default codes for our plugins
if ( !class_exists( 'Codeixer_Plugin_Core' ) ) {
	class Codeixer_Plugin_Core {
		/**
		 * @var mixed
		 */
		public $has_unyson_plugin;
		/**
		 * @var string
		 */
		public static $plugin_list = 'https://codeixer.com/plugin-list';

		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'codeixer_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'codeixer_admin_menu' ) );
			add_action( 'admin_menu', array( $this, 'codeixer_sub_menu' ) );
			add_action( 'admin_menu', array( $this, 'later' ), 99 );
			add_action( 'codeixer_license_data', array( $this, 'cix_plugin_list' ), 99 );

			add_action( 'fw_backend_add_custom_extensions_menu', array( $this, '_action_theme_custom_fw_settings_menu' ) );

			add_action( 'admin_init', function () {
				if ( is_plugin_active( 'unyson/unyson.php' ) ) {
					$this->has_unyson_plugin = true;
				}
			} );
			if ( !wp_next_scheduled( 'cix_plugin_list_cron' ) ) {
				wp_schedule_event( time(), 'daily', 'cix_plugin_list_cron' );
			}

			add_action( 'cix_plugin_list_cron', array( $this, 'plugin_list_data' ), 99 );

		}

		public function plugin_list_data() {
			// info.json is the file with the actual plugin information on your server
			$remote = wp_remote_get( self::$plugin_list, array(
				'timeout' => 10,
				'headers' => array(
					'Accept' => 'application/json',
				) )
			);

			if ( !is_wp_error( $remote ) && isset( $remote['response']['code'] ) && $remote['response']['code'] == 200 && !empty( $remote['body'] ) ) {

				set_transient( 'codeixer_get_plugin_list', json_decode( $remote['body'] ), 268 * HOUR_IN_SECONDS );

			}
		}
		/**
		 * @return mixed
		 */
		public function cix_plugin_list() {
			$plugin_list_array = '[{"img":"https:\/\/www.codeixer.com\/wp-content\/uploads\/2020\/07\/00_preview_codeixer-300x300.png","title":"1 Bayna \u2013 Deposits for WooCommerce","ex":"Enable customers to pay for products using a deposit payment.","btn_txt":"From: $49","btn_url":"https:\/\/www.codeixer.com\/product\/woocommerce-deposits\/"},{"img":"https:\/\/www.codeixer.com\/wp-content\/uploads\/2020\/06\/00_preview_codeixer-300x300.jpg","title":"WooCommerce Additional variation images","ex":"By default, Woocommerce Allow to adding a single image per variation on Variable Products.\nbut with WooCommerce Additional variation images plugin, you can add unlimited images per variation which is very effective for Boost your sales and conversion rate.","btn_txt":"From: $29","btn_url":"https:\/\/www.codeixer.com\/product\/woocommerce-additional-variation-images\/"},{"img":"https:\/\/brightplugins.com\/wp-content\/uploads\/2020\/04\/woocommerce-preorder-plugin-single-300x300.jpg","title":"Preorders for WooCommerce","ex":"An efficient system that easily translates to the specific needs of a WooCommerce storefront, our plugin allows you to follow up on pre-sales in a comprehensive way.","btn_txt":"From: $69","btn_url":"https:\/\/brightplugins.com\/woocommerce-preorder-plugin-review\/"},{"img":"https:\/\/brightplugins.com\/wp-content\/uploads\/2021\/04\/Min-Max-Quantities-For-WooCommerce-300x300.png","title":"Min\/Max Quantities for WooCommerce","ex":"This helpful little extension will allow you to determine both maximum and minimum thresholds and group\/multiple amounts for each product, with variations, included. This is an effective way of restricting the quantities of products that consumers can purchase.","btn_txt":"From: $19","btn_url":"https:\/\/brightplugins.com\/min-max-quantities-for-woocommerce-review\/"}]';

			$plugins_data = ( get_transient( 'codeixer_get_plugin_list' ) ) ? get_transient( 'codeixer_get_plugin_list' ) : json_decode( $plugin_list_array );
			?>

            <div class="fw-cix-plugin-wrapper">

                <div id="fw-dashboard-tabs">
                <ul>
                    <li><a href="#fw-premium-plugins">Premium Plugins</a></li>


                </ul>
                <div id="fw-premium-plugins" class="fw-tab-data">
                    <div class="fw-row fw-extensions-list">
                    <?php foreach ( $plugins_data as $key => $plugin ) {?>
                    <div class="fw-col-xs-12 fw-col-lg-6 fw-extensions-list-item">
                            <a class="fw-ext-anchor" name="ext-brizy"></a>
                            <div class="inner">
                                <div class="fw-extension-list-item-table">
                                    <div class="fw-extension-list-item-table-row">
                                        <div class="fw-extension-list-item-table-cell cell-1">
                                            <div class="fw-extensions-list-item-thumbnail-wrapper">
                                                <img src="<?php echo esc_url( $plugin->img ); ?>"
                                                    alt="icon" class="fw-extensions-list-item-thumbnail">
                                            </div>
                                        </div>
                                        <div class="fw-extension-list-item-table-cell cell-2">

                                            <h3 class="fw-extensions-list-item-title"><?php echo esc_html( $plugin->title ); ?> </h3>

                                            <p class="fw-extensions-list-item-desc"><?php echo esc_html( $plugin->ex ); ?></p>
                                            <a target="_blank" href="<?php echo esc_url( $plugin->btn_url ); ?>" class="button fw-btn-link"><?php echo esc_html( $plugin->btn_txt ); ?></a>

                                        </div>
                                        <div class="fw-extension-list-item-table-cell cell-3"></div>

                                    </div>
                                </div>
                                <!-- -->
                            </div>
                        </div>


                    <?php }?>
                    </div>
                </div>


            </div>
            <script>
            jQuery(document).ready(function($) {
                jQuery('#fw-dashboard-tabs').tabs();
            })
            </script>

            </div>
        <?php }
		/**
		 * @param  $data
		 * @return null
		 */
		public function _action_theme_custom_fw_settings_menu( $data ) {
			if ( true == $this->has_unyson_plugin ) {
				return;
			}
			add_menu_page(
				'placeholder',
				'placeholder',
				$data['capability'],
				$data['slug'],
				$data['content_callback']
			);
			remove_menu_page( $data['slug'] );
		}

		public function codeixer_admin_scripts() {
			wp_enqueue_style( 'ci-fw', plugins_url( '/assets/css/fw.css', __FILE__ ), [], WPGS_VERSION );
			wp_enqueue_style( 'ci-admin', plugins_url( '/assets/css/ci-admin.css', __FILE__ ), [], WPGS_VERSION );
			wp_enqueue_script( "jquery-ui-tabs" );

		}
		public function later() {
			/* === Remove Codeixer Sub-Links === */
			remove_submenu_page( 'codeixer', 'codeixer' );
		}

		public function codeixer_admin_menu() {
			add_menu_page( 'Codeixer', 'Codeixer', 'manage_options', 'codeixer', null, 'dashicons-codeixer', 60 );
		}

		public function codeixer_license() {?>
    <div class="wrap">

           <h2>Codeixer License Activation</h2>


      <p class="about-description">Enter your Purchase key here, to activate the product, and get full feature updates and premium support.</p>


    <?php
do_action( 'codeixer_license_form' );
			do_action( 'codeixer_license_data' );
        ?>
        </div>
        <?php
		}

		public function codeixer_sub_menu() {
			// * == License Activation Page ==
			add_submenu_page( 'codeixer', 'Dashboard', 'Dashboard', 'manage_options', 'codeixer-dashboard', array( $this, 'codeixer_license' ) );
		}
	}

	new Codeixer_Plugin_Core();
}
