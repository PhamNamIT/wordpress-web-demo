<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOO_CUSTOM_COUPONS_Admin_Coupons {
	protected $settings;

	public function __construct() {
		$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
		// add the filter
		add_filter( 'woocommerce_coupon_data_tabs', array( $this, 'woocommerce_coupon_data_tabs' ), 99, 1 );
		//add new coupon
		add_action( 'woocommerce_coupon_data_panels', array( $this, 'woocommerce_coupon_data_panels' ), 99, 2 );
		add_action( 'save_post', array( $this, 'wcc_save_metabox_data' ), 10, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 999999 );
	}

	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( $screen->id == 'shop_coupon' ) {
			wp_enqueue_style( 'vi_wcc-post-coupon-style', WOO_CUSTOM_COUPONS_CSS . 'admin-post-coupon.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi_wcc_shortcode_css', WOO_CUSTOM_COUPONS_CSS . 'shortcode.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_script( 'vi_wcc-post-coupon-script', WOO_CUSTOM_COUPONS_JS . 'wcc-add-coupon.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION, true );
			wp_localize_script(
				'vi_wcc-post-coupon-script',
				'vi_wcc_coupon',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'currency' => esc_attr( get_woocommerce_currency_symbol() ),
				)
			);
			$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
			$wcc_template   = $this->settings->get_params( 'wcc_template' );
			$css            = '';
			switch ( $wcc_template ) {
				case '2':
					$template_two_background = $this->settings->get_style_coupon( 'wcc-template-two', 'wcc-temple-two-background-color' );
					$template_two_text       = $this->settings->get_style_coupon( 'wcc-template-two', 'wcc-temple-two-title-color' );
					$template_two_border     = $this->settings->get_style_coupon( 'wcc-template-two', 'wcc-temple-two-border-color' );
					$css                     .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-2 {';
					if ( $template_two_background ) {
						$css .= 'background: ' . $template_two_background . ' ;';
					}
					$css .= '}';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-2 .vi_wcc_woo_custumer_coupon-content{';
					if ( $template_two_text ) {
						$css .= 'color: ' . $template_two_text . ' ;';
					}
					if ( $template_two_border ) {
						$css .= 'border: 1px dotted ' . $template_two_border . ' ;';
					}
					$css .= '}';
					break;
				case '3':

					$template_three_background   = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-background-color' );
					$template_three_title        = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-title-color' );
					$template_three_term         = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-term-color' );
					$template_three_expire       = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-expire-color' );
					$template_three_border_color = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-border-color' );
					$template_three_border_type  = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-border-type' );
					$css                         .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-3.vi_wcc_woo_custumer_coupon_button_click{';
					if ( $template_three_background ) {
						$css .= 'background: ' . $template_three_background . ' ;';
					}
					$css .= '}';
					if ( $template_three_border_color && $template_three_border_type ) {
						list( $r, $g, $b ) = sscanf( $template_three_border_color, "#%02x%02x%02x" );
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-3.vi_wcc_woo_custumer_coupon_button_click{';
						$css .= ' border: 2px  ' . $template_three_border_type . ' ' . $template_three_border_color . ' ;';
						$css .= '}';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-3.vi_wcc_woo_custumer_coupon_button_click:hover {';
						$css .= '  box-shadow: inset 0 0 2px rgba(' . $r . ',' . $g . ',' . $b . ',0.2) , 0 3px 5px rgba(' . $r . ',' . $g . ',' . $b . ',0.3) ;';
						$css .= '}';
					}
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-3 .vi_wcc_woo_custumer_coupon-content .vi_wcc_woo_custumer_coupon-title{';
					if ( $template_three_title ) {
						$css .= 'color: ' . $template_three_title . ';';
					}
					$css .= '}';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-3 .vi_wcc_woo_custumer_coupon-content .vi_wcc_woo_custumer_coupon-des{';
					if ( $template_three_term ) {
						$css .= 'color: ' . $template_three_term . ';';
					}
					$css .= '}';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-3 .vi_wcc_woo_custumer_coupon-content .vi_wcc_coupon_content_expire{';
					if ( $template_three_expire ) {
						$css .= 'color: ' . $template_three_expire . ';';
					}
					$css .= '}';
					break;
				case '4':
					$template_four_background       = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-background-color' );
					$template_four_title            = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-title-color' );
					$template_four_background_title = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-title-background-color' );
					$template_four_term             = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-term-color' );
					$template_four_border_color     = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-border-color' );
					$template_four_border_type      = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-border-type' );
					$template_four_border_radius    = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-border-radius' );
					$css                            .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4.vi_wcc_woo_custumer_coupon_button_click{';
					if ( $template_four_background ) {
						$css .= 'background: ' . $template_four_background . ';';
					}
					$css .= '}';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-title{';
					if ( $template_four_background_title ) {
						$css .= 'background: ' . $template_four_background_title . ';';
					}
					if ( $template_four_title ) {
						$css .= 'color: ' . $template_four_title . ';';
					}
					$css .= '}';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-content{';
					if ( $template_four_term ) {
						$css .= 'color: ' . $template_four_term . ';';
					}
					$css .= '}';

					if ( $template_four_border_type === 'none' ) {
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4.vi_wcc_woo_custumer_coupon_button_click{';
						if ( $template_four_term ) {
							$css .= 'box-shadow: inset 0 0 10px rgba(0,0,0,0.3);';
						}
						$css .= '}';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4.vi_wcc_woo_custumer_coupon_button_click:hover{';
						if ( $template_four_term ) {
							$css .= 'box-shadow: inset 0 0 5px rgba(0,0,0,0.1), 0 3px 5px rgba(0,0,0,0.4);';
						}
						$css .= '}';
					} else {
						list( $r, $g, $b ) = sscanf( $template_four_border_color, "#%02x%02x%02x" );
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4  .vi_wcc_woo_custumer_coupon-content{';
						if ( $template_four_term ) {
							$css .= 'border-top: unset !important;';
							$css .= 'border: 1px ' . $template_four_border_type . ' ' . $template_four_border_color . ' ;';
						}
						$css .= '}';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4.vi_wcc_woo_custumer_coupon_button_click:hover{';
						$css .= 'box-shadow: inset 0 0 5px rgba(' . $r . ',' . $g . ',' . $b . ' ,0.5);';
						$css .= '}';
					}
					if ( $template_four_border_radius === 'none' ) {
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4.vi_wcc_woo_custumer_coupon_button_click,';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-title,';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-content{';
						$css .= ' border-radius: unset;';
						$css .= '}';
					} else {
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4.vi_wcc_woo_custumer_coupon_button_click{';
						$css .= ' border-radius: ' . $template_four_border_radius . ';';
						$css .= '}';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-title{';
						$css .= ' border-top-left-radius: ' . $template_four_border_radius . ';';
						$css .= ' border-top-right-radius: ' . $template_four_border_radius . ';';
						$css .= '}';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-content,';
						$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-4 .vi_wcc_woo_custumer_coupon-content .vi_wcc_coupon_content_expire{';
						$css .= ' border-bottom-left-radius: ' . $template_four_border_radius . ';';
						$css .= ' border-bottom-right-radius: ' . $template_four_border_radius . ';';
						$css .= '}';
					}
					break;
				default:
					$template_one_bg_color        = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-background-color' );
					$template_one_bg_color1       = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-background-color1' );
					$template_one_bg_color2       = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-background-color2' );
					$template_one_title_color     = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-title-color' );
					$template_one_des_color       = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-des-color' );
					$template_one_expire_color    = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-expire-color' );
					$template_one_button_bg_color = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-button-background-color' );
					$template_one_button_color    = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-button-text-color' );
					$css                          .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1 .vi_wcc_woo_custumer_coupon-content {';
					if ( $template_one_bg_color1 ) {
						$css .= 'background-color: ' . $template_one_bg_color1 . ' ;';
						$css .= 'background-image: linear-gradient(315deg, ' . $template_one_bg_color1 . ' 0%, ' . $template_one_bg_color2 . ' 85% );';
					}
					$css .= '}';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1 .vi_wcc_woo_custumer_coupon-content:after,';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1 .vi_wcc_woo_custumer_coupon-content:before,';
					$css .= '.vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon_button:before ,';
					$css .= ' .vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon_button:after {';
					if ( $template_one_bg_color ) {
						$css .= 'background-color: ' . $template_one_bg_color . ' ;';
					}
					$css .= '}';
					$css .= ' .vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon-content .vi_wcc_woo_custumer_coupon-title{';
					if ( $template_one_title_color ) {
						$css .= 'color: ' . $template_one_title_color . ' ;';
					}
					$css .= '}';
					$css .= ' .vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon-content .vi_wcc_woo_custumer_coupon-des{';
					if ( $template_one_des_color ) {
						$css .= 'color: ' . $template_one_des_color . ' ;';
					}
					$css .= '}';
					$css .= ' .vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon-content .vi_wcc_coupon_content_expire{';
					if ( $template_one_expire_color ) {
						$css .= 'color: ' . $template_one_expire_color . ' ;';
					}
					$css .= '}';
					$css .= ' .vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon_button,';
					$css .= ' .vi_wcc_woo_custumer_coupon.vi_wcc_woo_custumer_coupon-1  .vi_wcc_woo_custumer_coupon_button_click{';
					if ( $template_one_button_bg_color ) {
						$css .= 'background: ' . $template_one_button_bg_color . ' ;';
					}
					if ( $template_one_button_color ) {
						$css .= 'color: ' . $template_one_button_color . ' ;';
					}
					$css .= '}';
			}
			wp_add_inline_style( 'vi_wcc_shortcode_css', $css );
		}
	}

	public function woocommerce_coupon_data_tabs( $array ) {
		$array['vi_wcc_customer_coupon'] = array(
			'label'  => esc_html__( 'Customer Coupon', 'woo-customer-coupons' ),
			'target' => 'vi_wcc_customer_coupon_data',
			'class'  => '',
		);

		return $array;
	}

	public function woocommerce_coupon_data_panels( $coupon_get_id, $coupon ) {
		printf('<div class="wcc-coupons-field panel woocommerce_options_panel" id="vi_wcc_customer_coupon_data">');
		wp_nonce_field( 'wcc_custom_coupon_data_panels_nonce_action', '_wcc_custom_coupon_data_panels_nonce' );
		$coupon_enable       = empty( get_post_meta( $coupon_get_id, 'wcc_coupon_enable', true ) ) ? 'no' : get_post_meta( $coupon_get_id, 'wcc_coupon_enable', true );
		$coupon_email_enable = get_post_meta( $coupon_get_id, 'wcc_coupon_mail_enable', true ) ?: 0;

		$expiry_date  = get_post_meta( $coupon_get_id, 'wcc_custom_coupon_start_date', true ) ? get_post_meta( $coupon_get_id, 'wcc_custom_coupon_start_date', true ) : '';
		$coupon_title = ( empty( get_post_meta( $coupon_get_id, 'wcc_custom_coupon_title', true ) ) || get_post_meta( $coupon_get_id, 'wcc_custom_coupon_title', true ) === 'No title' ) ? '' : get_post_meta( $coupon_get_id, 'wcc_custom_coupon_title', true );
		$coupon_terms = ( empty( get_post_meta( $coupon_get_id, 'wcc_custom_coupon_terms', true ) ) || ( get_post_meta( $coupon_get_id, 'wcc_custom_coupon_terms', true ) === 'No terms' ) ) ? '' : get_post_meta( $coupon_get_id, 'wcc_custom_coupon_terms', true );

		woocommerce_wp_checkbox(
			array(
				'id'          => 'wcc_coupon_enable',
				'label'       => esc_html__( 'Enable', 'woo-customer-coupons' ),
				'description' => esc_html__( 'Check  this box if this coupon will show on your site.', 'woo-customer-coupons' ),
				'value'       => $coupon_enable,
			)
		);

		woocommerce_wp_checkbox(
			array(
				'id'          => 'wcc_coupon_mail_enable',
				'label'       => esc_html__( 'Send email', 'woo-customer-coupons' ),
				'description' => esc_html__( 'Send an email to your customer if this coupon is created or updated.', 'woo-customer-coupons' ),
				'value'       => $coupon_email_enable,
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => 'wcc_custom_coupon_title',
				'value'       => esc_attr( $coupon_title ),
				'label'       => esc_html__( 'Title', 'woo-customer-coupons' ),
				'placeholder' => '50% OFF',
				'description' => esc_html__( 'The title of coupon', 'woo-customer-coupons' ),
				'class'       => '',
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => 'wcc_custom_coupon_terms',
				'label'       => esc_html__( 'Terms', 'woo-customer-coupons' ),
				'value'       => esc_attr( $coupon_terms ),
				'placeholder' => esc_html__( 'Minimum order 20$', 'woo-customer-coupons' ),
				'class'       => '',
				'description' => esc_html__( 'The terms of coupon', 'woo-customer-coupons' ),
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => 'wcc_custom_coupon_start_date',
				'value'       => esc_attr( $expiry_date ),
				'label'       => esc_html__( 'Show on', 'woo-customer-coupons' ),
				'placeholder' => 'YYYY-MM-DD',
				'description' => esc_html__( 'This coupon will show on your site that day(default date created)', 'woo-customer-coupons' ),
				'class'       => 'date-picker',
			)
		);

		?>
        <div class=" form-field vi_wcc_coupon_template ">
			<?php
			echo wp_kses_post(do_shortcode( '[vi_wcc_coupon id=' . $coupon_get_id . ' ]' ));
			?>
        </div>
        </div>

		<?php
		do_action( 'woocommerce_coupon_vi_wcc_customer_coupon', $coupon_get_id, $coupon );
	}

	public function wcc_save_metabox_data( $post_id, $post, $update ) {
		global $post_type;
		if ( 'shop_coupon' != $post_type || ! isset( $_POST['_wcc_custom_coupon_data_panels_nonce'] ) || ! wp_verify_nonce( wc_clean($_POST["_wcc_custom_coupon_data_panels_nonce"]), 'wcc_custom_coupon_data_panels_nonce_action' ) ) {
			return;
		}

		$coupon_enable          = isset( $_POST['wcc_coupon_enable'] ) ? 'yes' : 'no';
		$coupon_title           = isset( $_POST['wcc_custom_coupon_title'] ) ? sanitize_text_field( $_POST['wcc_custom_coupon_title'] ) : '';
		$coupon_des             = isset( $_POST['wcc_custom_coupon_terms'] ) ? sanitize_text_field( $_POST['wcc_custom_coupon_terms'] ) : '';
		$wcc_coupon_mail_enable = isset( $_POST['wcc_coupon_mail_enable'] ) ? sanitize_text_field( $_POST['wcc_coupon_mail_enable'] ) : '';
		$coupon                 = new WC_Coupon( $post_id );
		$date                   = empty( $coupon->get_date_created() ) ? date( 'Y-m-d' ) : $coupon->get_date_created()->date( 'Y-m-d' );
		$date_t                 = empty( $_POST['wcc_custom_coupon_start_date'] ) ? $date : sanitize_text_field( wp_unslash( $_POST['wcc_custom_coupon_start_date'] ) );
		$coupon_date_start      = date_format( date_create( $date_t ), 'Y-m-d' );
		update_post_meta( $post_id, 'wcc_coupon_enable', $coupon_enable );
		update_post_meta( $post_id, 'wcc_custom_coupon_title', $coupon_title );
		update_post_meta( $post_id, 'wcc_custom_coupon_terms', $coupon_des );
		update_post_meta( $post_id, 'wcc_custom_coupon_start_date', $coupon_date_start );
		update_post_meta( $post_id, 'wcc_coupon_mail_enable', $wcc_coupon_mail_enable );
		$get_mails = $coupon->get_email_restrictions();
		if ( ! empty( $get_mails ) && $wcc_coupon_mail_enable ) {
			if ( $coupon->get_discount_type() == 'percent' ) {
				$coupon_value = $coupon->get_amount() . '%';
			} else {
				$coupon_value = wc_price( $coupon->get_amount() );
			}
			$coupon_code  = wp_specialchars_decode( $coupon->get_code(), ENT_QUOTES );
			$date_expires = $coupon->get_date_expires();
			foreach ( $get_mails as $email ) {
				$this->send_mail( $email, $coupon_code, $date_t, $date_expires, $coupon_value, $coupon_title, $coupon_des );
			}
		}
	}

	private function send_mail( $user_email, $coupon_code, $date_start = '', $date_expires = '', $coupon_value = '', $coupon_title = '', $coupon_des = '' ) {
		$wcc_enable_send_mail = $this->settings->get_params( 'wcc_enable_send_mail' );
		if ( $wcc_enable_send_mail !== 'yes' ) {
			return;
		}
		$date_format         = $this->settings->get_params( 'wcc_date_format' ) ?: 'Y-m-d';
		$button_shop_now_url = $this->settings->get_params( 'wcc_button_shop_now_url' );
		$button_css          = 'text-decoration:none;display:inline-block;padding:10px 30px;margin:10px 0;';
		$button_css          .= 'font-size:' . $this->settings->get_params( 'wcc_button_shop_now_size' ) . 'px;';
		$button_css          .= 'color:' . $this->settings->get_params( 'wcc_button_shop_now_color' ) . ';';
		$button_css          .= 'background:' . $this->settings->get_params( 'wcc_button_shop_now_bg_color' ) . ';';
		$button_css          .= 'border-radius:' . $this->settings->get_params( 'wcc_button_shop_now_border_radius' ) . 'px;';
		$button_title        = $this->settings->get_params( 'wcc_button_shop_now_title' );
		$button_shop_now     = '<a href="' . ( $button_shop_now_url ? $button_shop_now_url : get_bloginfo( 'url' ) ) . '" target="_blank" style="' . esc_attr( $button_css ) . '">' . esc_html( $button_title ) . '</a>';
		$headers             = "Content-Type: text/html\r\n";
		$arg                 = array(
			'subject' => stripslashes( $this->settings->get_params( 'wcc_send-mail-subject' ) ),
			'heading' => stripslashes( $this->settings->get_params( 'wcc_mail_heading' ) ),
			'content' => stripslashes( $this->settings->get_params( 'wcc_mail_content' ) ),
		);
		$date_expires_t      = empty( $date_expires ) ? esc_html__( 'never expires', 'woo-customer-coupons' ) : date( $date_format, strtotime( $date_expires ) );
		$last_valid_date     = empty( $date_expires ) ? '' : date( $date_format, strtotime( $date_expires ) - 86400 );
		$site_title          = get_bloginfo( 'name' );
		$coupon_code         = '<span style="font-size: x-large;">' . strtoupper( $coupon_code ) . '</span>';
		$content             = array();
		foreach ( $arg as $key => $value ) {
			$value           = str_replace( '{coupon_title}', $coupon_title, $value );
			$value           = str_replace( '{coupon_des}', $coupon_des, $value );
			$value           = str_replace( '{coupon_value}', $coupon_value, $value );
			$value           = str_replace( '{coupon_code}', $coupon_code, $value );
			$value           = str_replace( '{date_expires}', $date_expires_t, $value );
			$value           = str_replace( '{last_valid_date}', $last_valid_date, $value );
			$value           = str_replace( '{site_title}', $site_title, $value );
			$value           = str_replace( '{shop_now}', $button_shop_now, $value );
			$content[ $key ] = $value;
		}
		$mailer  = WC()->mailer();
		$email   = new WC_Email();
		$subject = $content['subject'];
		$content = $email->style_inline( $mailer->wrap_message( $content['heading'], $content['content'] ) );
		$email->send( $user_email, $subject, $content, $headers, array() );
	}
}