<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOO_CUSTOM_COUPONS_Frontend_Frontend {
	protected $settings;
	protected $single_pro_page_pos;

	public function __construct() {
		$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
		add_action( 'init', array( $this, 'add_list_coupons_endpoint' ) );
		add_filter( 'query_vars', array( $this, 'list_coupons_query_vars' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'woocommerce_account_menu_items' ), 10, 1 );
		add_action( 'woocommerce_account_coupons_endpoint', array( $this, 'show_coupons' ) );
		add_action( 'woocommerce_after_cart_table', array( $this, 'show_coupons' ), 10, 0 );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'show_coupons' ), 10, 1 );
		$this->single_pro_page_pos = $this->settings->get_params( 'wcc_coupon-single_pro_page_pos' );
		if ( $this->single_pro_page_pos ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'woocommerce_single_product_summary' ), $this->single_pro_page_pos );
		}
		add_action( 'wp_ajax_vi_wcc_apply_coupon', array( $this, 'vi_wcc_apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_vi_wcc_apply_coupon', array( $this, 'vi_wcc_apply_coupon' ) );
	}

	public function add_list_coupons_endpoint() {
		add_rewrite_endpoint( 'coupons', EP_ROOT | EP_PAGES );
		add_shortcode( 'vi_wcc_coupon', array( $this, 'register_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'shortcode_enqueue_script' ), 99 );
	}

	public function list_coupons_query_vars( $vars ) {
		$vars[] = 'coupons';

		return $vars;
	}

	public function woocommerce_account_menu_items( $items ) {
		$edit_address = $items['edit-address'];
		$edit_account = $items['edit-account'];
		$logout       = $items['customer-logout'];
		unset( $items['edit-address'] );
		unset( $items['edit-account'] );
		unset( $items['customer-logout'] );
		$items['coupons']         = esc_html__( 'Coupons', 'woo-customer-coupons' );
		$items['edit-address']    = $edit_address;
		$items['edit-account']    = $edit_account;
		$items['customer-logout'] = $logout;

		return $items;
	}

	public function vi_wcc_apply_coupon() {
		$result      = array(
			'status'   => 'error',
			'shop_url' => '',
			'message'  => '',
		);
		$coupon_code = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';
		if ( ! empty( $coupon_code ) ) {
			if ( ! empty( WC()->cart->get_cart() ) ) {
				WC()->cart->add_discount( $coupon_code );
				$result['status'] = 'successfully';
				ob_start();
				wc_print_notices();
				$result['message'] = ob_get_clean();
			} else {
				$result['shop_url'] = get_permalink( wc_get_page_id( 'shop' ) );
			}
		} else {
			$result['message'] = esc_html__( 'No coupon code to apply', 'woo-customer-coupons' );
		}

		wp_send_json( $result );
	}

	public function register_shortcode( $attrs ) {
		extract( shortcode_atts( array(
			'id' => '',
		), $attrs ) );
		$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
		$coupon         = new WC_Coupon( $id );
		$coupon_title   = get_post_meta( $id, 'wcc_custom_coupon_title', true ) ?? '';
		$coupon_des     = get_post_meta( $id, 'wcc_custom_coupon_terms', true ) ?? '';
		$coupon_code    = $coupon->get_code();
		if ( is_cart() ) {
			$type_button = 'submit';
		} else {
			$type_button = 'button';
		}
		$wcc_template = $this->settings->get_params( 'wcc_template' );
		$date_format  = $this->settings->get_params( 'wcc_date_format' );
		$expire       = ( empty( $coupon->get_date_expires() ) ) ? '' : $coupon->get_date_expires()->date( $date_format );
		$expire       = $expire ? esc_html__( 'Expires on: ', 'woo-customer-coupons' ) . $expire : '';

		$coupon_minimum     = $coupon->get_minimum_amount();
		$coupon_maximum     = $coupon->get_maximum_amount();
		$coupon_limit       = $coupon->get_usage_limit();
		$coupon_limit_count = $coupon->get_usage_count();
		$coupon_limit_use   = $coupon->get_usage_limit_per_user();
		$coupon_product_id  = $coupon->get_product_ids();
//		$coupon_category_id = $coupon->get_product_categories();
		$coupon_hidden = false;
		if ( ! is_admin() ) {
			$cart_total   = floatval( WC()->session->cart_totals['subtotal'] );
			$cart_items   = WC()->cart->get_cart();
			$cart_product = 0;
			foreach ( $cart_items as $item => $values ) {
				$_product = $values['data']->get_id();
				if ( in_array( $cart_product, $coupon_product_id ) ) {
					$cart_product ++;
				}
			}
			if ( $cart_product ) {
				$coupon_hidden = true;
			} elseif ( $coupon_maximum != '' && $coupon_minimum != '' ) {
				if ( $cart_total > $coupon_maximum || $cart_total < $coupon_minimum ) {
					$coupon_hidden = true;
				}
			} elseif ( $coupon_maximum != '' && $coupon_minimum == '' ) {
				if ( $cart_total > $coupon_maximum ) {
					$coupon_hidden = true;
				}

			} elseif ( $coupon_maximum == '' && $coupon_minimum != '' ) {
				if ( $cart_total < $coupon_minimum ) {
					$coupon_hidden = true;
				}

			} elseif ( $coupon_limit > 0 && $coupon_limit_use > 0 ) {
				if ( $coupon_limit == $coupon_limit_count || $coupon_limit_count == $coupon_limit_use ) {
					$coupon_hidden = true;
				}
			} elseif ( $coupon_limit > 0 && $coupon_limit_use = 0 ) {
				if ( $coupon_limit == $coupon_limit_count ) {
					$coupon_hidden = true;
				}
			} elseif ( $coupon_limit = 0 && $coupon_limit_use > 0 ) {
				if ( $coupon_limit_count == $coupon_limit_use ) {
					$coupon_hidden = true;
				}
			}
		}
		ob_start();
		switch ( $wcc_template ) {
			case '2':
				?>
                <button class="vi_wcc_woo_custumer_coupon_button_click vi_wcc_woo_custumer_coupon vi_wcc_woo_custumer_coupon-<?php echo esc_attr( $wcc_template ); ?>"
                        name="vi_wcc_woo_customer_coupon_button_click" type="<?php echo esc_attr( $type_button ); ?>" value="<?php echo esc_attr( $coupon_code ) ?>">
                    <div class="vi_wcc_woo_custumer_coupon-content">
                        <div class="vi_wcc_woo_custumer_coupon-title"> <?php echo esc_html( $coupon_title ); ?></div>
                        <div class="vi_wcc_woo_custumer_coupon-des"> <?php echo esc_html( $coupon_des ); ?></div>
                        <div class="vi_wcc_coupon_content_expire">
                            <span><?php echo esc_html( $expire ); ?></span>
                        </div>
                    </div>
                    <div class="vi_wcc_lds-dual-ring vi_wcc_lds-dual-ring-<?php echo esc_attr( $coupon_code ); ?>"></div>
                </button>
				<?php
				break;
			case '3':
				?>
                <button class="vi_wcc_woo_custumer_coupon_button_click vi_wcc_woo_custumer_coupon vi_wcc_woo_custumer_coupon-<?php echo esc_attr( $wcc_template ); ?>"
                        name="vi_wcc_woo_customer_coupon_button_click" type="<?php echo esc_attr( $type_button ); ?>" value="<?php echo esc_attr( $coupon_code ) ?>">
                    <div class="vi_wcc_woo_custumer_coupon-content">
                        <div class="vi_wcc_woo_custumer_coupon-title"> <?php echo esc_html( $coupon_title ); ?></div>
                        <div class="vi_wcc_woo_custumer_coupon-des"> <?php echo esc_html( $coupon_des ); ?></div>
                        <div class="vi_wcc_coupon_content_expire">
                            <span><?php echo esc_html( $expire ); ?></span>
                        </div>
                    </div>
                    <div class="vi_wcc_lds-dual-ring vi_wcc_lds-dual-ring-<?php echo esc_attr( $coupon_code ); ?>"></div>
                </button>
				<?php
				break;
			case '4':
				?>
                <button class="vi_wcc_woo_custumer_coupon_button_click vi_wcc_woo_custumer_coupon vi_wcc_woo_custumer_coupon-<?php echo esc_attr( $wcc_template ); ?>"
                        name="vi_wcc_woo_customer_coupon_button_click" type="<?php echo esc_attr( $type_button ); ?>" value="<?php echo esc_attr( $coupon_code ) ?>">
                    <div class="vi_wcc_woo_custumer_coupon-title"> <?php echo esc_html( $coupon_title ); ?></div>
                    <div class="vi_wcc_woo_custumer_coupon-content">
                        <div class="vi_wcc_woo_custumer_coupon-des"> <?php echo esc_html( $coupon_des ); ?></div>
                        <div class="vi_wcc_coupon_content_expire">
                            <span><?php echo esc_html( $expire ); ?></span>
                        </div>
                    </div>
                    <div class="vi_wcc_lds-dual-ring vi_wcc_lds-dual-ring-<?php echo esc_attr( $coupon_code ); ?>"></div>
                </button>
				<?php
				break;
			default:
				?>
                <div class="vi_wcc_woo_custumer_coupon vi_wcc_woo_custumer_coupon-<?php echo esc_attr( $wcc_template ); ?>">
                    <div class="vi_wcc_woo_custumer_coupon-content">
                        <div class="vi_wcc_woo_custumer_coupon-title"> <?php echo esc_html( $coupon_title ); ?></div>
                        <div class="vi_wcc_woo_custumer_coupon-des"> <?php echo esc_html( $coupon_des ); ?></div>
                        <div class="vi_wcc_coupon_content_expire">
                            <span><?php echo esc_html( $expire ); ?></span>
                        </div>
                    </div>
                    <div class="vi_wcc_woo_custumer_coupon_button">
                        <button class="vi_wcc_woo_custumer_coupon_button_click " name="vi_wcc_woo_customer_coupon_button_click" type="<?php echo esc_attr( $type_button ); ?>"
                                value="<?php echo esc_attr( $coupon_code ) ?>">
                            <span><?php esc_html_e( 'Apply Coupon', 'woo-customer-coupons' ); ?></span>
                        </button>
                    </div>
                    <div class="vi_wcc_lds-dual-ring vi_wcc_lds-dual-ring-<?php echo esc_attr( $coupon_code ); ?>"></div>
                </div>
			<?php
		}

		if ( $coupon_hidden ) {
			printf( '<div class="vi_wcc_coupon_terms"></div>');
		}

		$content = ob_get_clean();

		return $content;
	}

	public function shortcode_enqueue_script() {
		if ( is_account_page() || is_cart() || is_checkout() ) {
			wp_enqueue_style( 'vi_wcc_slick-style', WOO_CUSTOM_COUPONS_CSS . 'slick.min.css', '', '1.9.0' );
			wp_enqueue_style( 'vi_wcc_slick-theme-style', WOO_CUSTOM_COUPONS_CSS . 'slick-theme.min.css', '', '1.9.0' );
			wp_enqueue_style( 'vi_wcc_shortcode_css', WOO_CUSTOM_COUPONS_CSS . 'shortcode.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'wcc-coupon-myaccount-style', WOO_CUSTOM_COUPONS_CSS . 'my-account-page.css', '', WOO_CUSTOM_COUPONS_VERSION );

			wp_enqueue_script( 'vi_wcc_slick-script', WOO_CUSTOM_COUPONS_JS . 'slick.min.js', array( 'jquery' ), '1.9.0', true );
			wp_enqueue_script( 'wcc-shortcode-js', WOO_CUSTOM_COUPONS_JS . 'shortcode.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
			wp_localize_script( 'wcc-shortcode-js', 'coupon_wcc_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'is_cart'  => is_cart(),
			) );
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
		if ( $this->single_pro_page_pos && is_product() && is_single() ) {
			wp_enqueue_style( 'wcc-coupon-content-product', WOO_CUSTOM_COUPONS_CSS . 'content-product.css', '', WOO_CUSTOM_COUPONS_VERSION );
		}
	}

	public function show_coupons() {
		$coupon_code = '';
		if ( is_cart() && isset( $_POST['vi_wcc_woo_customer_coupon_button_click'] ) ) {
			$coupon_code = sanitize_text_field( $_POST['vi_wcc_woo_customer_coupon_button_click'] );
			WC()->cart->add_discount( esc_attr( $coupon_code ) );
			wc_print_notices();
		}
		$coupons = $this->get_list_coupons();
		if ( ! empty( $coupons ) ) {
			?>
            <div class="vi_wcc-account-list-coupon">
                <a href="#" class="vi_wcc_coupon-notices"></a>
                <h3><?php esc_html_e( 'Available Coupons! Click on a coupon to use it', 'woo-customer-coupons' ) ?></h3>
                <div class="vi_wcc-account-list-coupon-content">
					<?php
					foreach ( $coupons as $coupon_id ) {
						printf( '<div class="vi_wcc_woo_custumer_coupon-wrap">');
						echo wp_kses_post( do_shortcode( '[vi_wcc_coupon id=' . $coupon_id . ' ]' ));
						printf( '</div>');
					}
					?>
                </div>
            </div>
			<?php
		} else {
			if ( is_account_page() ) {
				printf( '<h3>%s</h3>',esc_html__( 'No coupon.', 'woo-customer-coupons' ));
			}
		}
	}

	private function get_list_coupons( $is_single = false ) {
		global $woocommerce, $user_ID;
		//check user
		$email = '';
		if ( ! empty( $user_ID ) ) {
			$user  = get_user_by( 'ID', $user_ID );
			$email = $user->user_email;
		}
		//get coupon
		$args       = array(
			'post_type'  => 'shop_coupon',
			'status'     => 'publish',
			'meta_key'   => 'wcc_coupon_enable',
			'meta_value' => 'yes'
		);
		$the_query  = new WP_Query( $args );
		$coupon_ids = array();
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$coupon_ids[] = get_the_ID();
			}
		}
		wp_reset_postdata();
		$coupons = array();
		if ( ! empty( $coupon_ids ) ) {
			$cart_coupon = WC()->cart->get_applied_coupons() ?: array();
			$cart_item   = WC()->cart->get_cart();
			$products    = array();
			if ( ! $is_single && ! empty( $cart_item ) ) {
				foreach ( $cart_item as $item ) {
					$product_id = $item['variation_id'] ?: $item['product_id'];
					$products[] = wc_get_product( $product_id );
				}
			}
			$now = current_time( 'timestamp' );
			foreach ( $coupon_ids as $coupon_id ) {
				$coupon      = new WC_Coupon( $coupon_id );
				$date_show   = strtotime( get_post_meta( $coupon_id, 'wcc_custom_coupon_start_date', true ) );
				$date_expire = ! empty( $coupon->get_date_expires() ) ? strtotime( $coupon->get_date_expires( 'edit' )->date( 'Y-m-d' ) ) : '';
				if ( $now > $date_show && ( ! $date_expire || $date_expire > $now ) ) {
					if ( ! $is_single && in_array( $coupon->get_code(), $cart_coupon ) ) {
						continue;
					}
					if ( ! empty( $products ) ) {
						$continue = false;
						if ( ! $coupon->is_type( wc_get_product_coupon_types() ) ) {
							if ( $coupon->get_exclude_sale_items() ) {
								foreach ( $products as $product ) {
									if ( $product->is_on_sale() ) {
										$continue = true;
										break;
									}
								}
								if ( $continue ) {
									continue;
								}
							}
							if ( count( $coupon->get_excluded_product_ids() ) > 0 ) {
								foreach ( $products as $product ) {
									if ( in_array( $product->get_id(), $coupon->get_excluded_product_ids(), true ) || in_array( $product->get_parent_id(), $coupon->get_excluded_product_ids(), true ) ) {
										$continue = true;
										break;
									}
								}
								if ( $continue ) {
									continue;
								}
							}
							if ( count( $coupon->get_excluded_product_categories() ) > 0 ) {
								foreach ( $products as $product ) {
									$product_cats = wc_get_product_cat_ids( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
									if ( ! count( array_intersect( $product_cats, $coupon->get_excluded_product_categories() ) ) ) {
										$continue = true;
										break;
									}
								}
								if ( $continue ) {
									continue;
								}
							}

						} else {
							foreach ( $products as $product ) {
								$continue = $coupon->is_valid_for_product( $product );
								if ( $continue ) {
									break;
								}
							}
							if ( ! $continue ) {
								continue;
							}
						}
					}
					$email_coupon = $coupon->get_email_restrictions();
					if ( ! empty( $email_coupon ) ) {
						if ( $email && in_array( $email, $email_coupon ) ) {
							$coupons[] = $coupon_id;
						}
					} else {
						$coupons[] = $coupon_id;
					}
				}
			}
		}

		return array_unique( $coupons );
	}

	public function woocommerce_single_product_summary() {
		global $product;
		$coupons = $this->get_list_coupons( true );
		foreach ( $coupons as $coupon_id ) {
			$coupon = new WC_Coupon( $coupon_id );
			if ( ! $coupon->is_type( wc_get_product_coupon_types() ) ) {
				if ( $coupon->get_exclude_sale_items() && $product->is_on_sale() ) {
					unset( $coupons[ array_search( $coupon_id, $coupons ) ] );
					continue;
				}
				if ( count( $coupon->get_excluded_product_ids() ) > 0 ) {
					if ( ! in_array( $product->get_id(), $coupon->get_excluded_product_ids(), true ) && ! in_array( $product->get_parent_id(), $coupon->get_excluded_product_ids(), true ) ) {
						unset( $coupons[ array_search( $coupon_id, $coupons ) ] );
						continue;
					}
				}
				if ( count( $coupon->get_excluded_product_categories() ) > 0 ) {
					$product_cats = wc_get_product_cat_ids( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
					if ( count( array_intersect( $product_cats, $coupon->get_excluded_product_categories() ) ) ) {
						unset( $coupons[ array_search( $coupon_id, $coupons ) ] );
						continue;
					}
				}
			} elseif ( ! $coupon->is_valid_for_product( $product ) ) {
				unset( $coupons[ array_search( $coupon_id, $coupons ) ] );
				continue;
			}
		}
		if ( ! empty( $coupons ) ) {
			$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
			$date_format    = $this->settings->get_params( 'wcc_date_format' );
			ob_start();
			?>
            <div class="vi_wcc-coupon-single-product">
                <div class="vi_wcc-coupon-single-product-title">
                    <div class="vi_wcc-coupon-single-product-title1"><?php esc_html_e( 'Promotions', 'woo-customer-coupons' ) ?></div>
                    <div class="vi_wcc-coupon-single-product-title2">
						<?php
						foreach ( $coupons as $coupon_id ) {
							$coupon_title = get_post_meta( $coupon_id, 'wcc_custom_coupon_title', true );
							printf( '<div class="vi_wcc-coupon-single-product-title3">%s</div>',esc_html( $coupon_title ) );
						}
						?>
                    </div>
                </div>
                <div class="vi_wcc-coupon-single-product-content">
					<?php
					foreach ( $coupons as $coupon_id ) {
						$coupon       = new WC_Coupon( $coupon_id );
						$coupon_title = get_post_meta( $coupon_id, 'wcc_custom_coupon_title', true ) ?? '';
						$coupon_des   = get_post_meta( $coupon_id, 'wcc_custom_coupon_terms', true ) ?? '';
						$expire       = empty( $coupon->get_date_expires() ) ? '' : $coupon->get_date_expires()->date( $date_format );
						$expire       = $expire ? esc_html__( 'Expires on: ', 'woo-customer-coupons' ) . $expire : esc_html__( 'Never expire', 'woo-customer-coupons' );
						?>
                        <div class="vi_wcc-coupon-single-product-description">
                            <div>
                                <div class="vi_wcc-coupon-single-product-description-title"><?php echo esc_html( $coupon_title ); ?></div>
                            </div>
                            <div class="vi_wcc-coupon-single-product-description-terms-expire">
                                <div class="vi_wcc-coupon-single-product-description-terms"><?php echo esc_html( $coupon_des ); ?></div>
                                <div class="vi_wcc-coupon-single-product-description-terms"><?php echo esc_html( $expire ); ?></div>
                            </div>
                        </div>
						<?php
					}
					?>
                </div>
            </div>
			<?php
			$html = ob_get_clean();
			echo wp_kses_post($html);
		}
	}
}