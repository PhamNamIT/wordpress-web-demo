<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WOO_CUSTOM_COUPONS_Admin_Settings {
	protected $settings;

	public function __construct() {
		$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_option' ), 99, 3 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 999999 );
		/*preview email*/
		add_action( 'media_buttons', array( $this, 'preview_emails_button' ) );
		add_action( 'wp_ajax_wcc_preview_emails', array( $this, 'preview_emails_ajax' ) );
		add_action( 'admin_footer', array( $this, 'preview_emails_html' ) );
		add_action( 'admin_init', array( $this, 'save_data' ), 99 );
	}

	public function admin_menu() {
		global $wcc_screen_option;
		$wcc_screen_option = add_submenu_page(
			'woocommerce-marketing',
			esc_html__( 'Customer Coupons', 'woo-customer-coupons' ),
			esc_html__( 'Customer Coupons', 'woo-customer-coupons' ),
			'manage_options',
			'woo_customer_coupons',
			array( $this, 'setting_callback' )
		);
		add_action( "load-$wcc_screen_option", array( $this, "vi_wcc_screen_options" ) );
	}

	public function vi_wcc_screen_options() {
		global $wcc_screen_option;
		$screen = get_current_screen();
		// get out of here if we are not on our settings page
		if ( ! is_object( $screen ) || $screen->id != $wcc_screen_option || isset( $_GET['subpage'] ) ) {
			return;
		}
		$args = array(
			'label'   => esc_html__( 'Number of per page', 'woo-customer-coupons' ),
			'default' => 20,
			'option'  => 'wcc_per_page'
		);
		add_screen_option( 'per_page', $args );
	}

	public function save_screen_option( $status, $option, $value ) {
		if ( 'wcc_per_page' == $option ) {
			return $value;
		}
		return $status;
	}

	public function preview_emails_button( $editor_id ) {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page === 'woo_customer_coupons' ) {
			if ( $editor_id === 'wcc_mail_content' ) {
				ob_start();
				?>
                <span class="button wcc-preview-emails-button"
                      data-wcb_language="<?php echo esc_attr( str_replace( 'wcc_mail_content', '', $editor_id ) ) ?>"><?php esc_html_e( 'Preview emails', 'woo-customer-coupons' ) ?></span>
				<?php
				wp_kses_post( ob_get_clean() );
			}
		}
	}

	function preview_emails_html() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page === 'woo_customer_coupons' ) {
			?>
            <div class="preview-emails-html-container vi_wcc_hidden">
                <div class="preview-emails-html-overlay"></div>
                <div class="preview-emails-html"></div>
            </div>
			<?php
		}
	}

	public function preview_emails_ajax() {
		$arg                       = array();
		$arg['content']            = isset( $_GET['content'] ) ? wp_kses_post( stripslashes( $_GET['content'] ) ) : '';
		$arg['heading']            = isset( $_GET['heading'] ) ? sanitize_text_field( stripslashes( $_GET['heading'] ) ) : '';
		$button_shop_title         = isset( $_GET['button_shop_title'] ) ? sanitize_text_field( stripslashes( $_GET['button_shop_title'] ) ) : '';
		$button_shop_url           = isset( $_GET['button_shop_url'] ) ? sanitize_text_field( stripslashes( $_GET['button_shop_url'] ) ) : '';
		$button_shop_color         = isset( $_GET['button_shop_color'] ) ? sanitize_text_field( stripslashes( $_GET['button_shop_color'] ) ) : '';
		$button_shop_bg_color      = isset( $_GET['button_shop_bg_color'] ) ? sanitize_text_field( stripslashes( $_GET['button_shop_bg_color'] ) ) : '';
		$button_shop_size          = isset( $_GET['button_shop_size'] ) ? sanitize_text_field( ( $_GET['button_shop_size'] ) ) : '';
		$button_shop_border_radius = isset( $_GET['button_shop_border_radius'] ) ? sanitize_text_field( ( $_GET['button_shop_border_radius'] ) ) : '';
		$button_css = 'text-decoration:none;display:inline-block;padding:10px 30px;margin:10px 0;';
		$button_css .= 'background:' . $button_shop_bg_color . ';';
		$button_css .= 'color:' . $button_shop_color . ';';
		$button_css .= 'font-size:' . $button_shop_size . 'px;';
		$button_css .= 'border-radius:' . $button_shop_border_radius . 'px;';
		$button_shop_now = '<a href="' . $button_shop_url . '" target="_blank" style="' . $button_css . '">' . $button_shop_title . '</a>';
		$coupon_value    = '10%';
		$coupon_code     = 'HAPPY';
		$coupon_title    = '10% OFF';
		$coupon_des      = 'for every $200 purchase';
		$date_expires    = strtotime( '+30 days' );
		$site_title      = get_bloginfo( 'name' );
		$date_format     = $this->settings->get_params( 'wcc_date_format' ) ?: 'Y-m-d';
		$coupon_code     = '<span style="font-size: x-large;">' . strtoupper( $coupon_code ) . '</span>';
		$date_expires_t  = empty( $date_expires ) ? esc_html__( 'never expires', 'woo-customer-coupons' ) : date_i18n( $date_format, ( $date_expires ) );
		$last_valid_date = empty( $date_expires ) ? esc_html__( '', 'woo-customer-coupons' ) : date_i18n( $date_format, ( $date_expires - 86400 ) );
		$content         = array();
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
		// load the mailer class
		$mailer = WC()->mailer();
		// create a new email
		$email = new WC_Email();
		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $content['heading'], $content['content'] ) ) );
		// print the preview email
		wp_send_json(
			array(
				'html' => $message,
			)
		);
	}

	public function setting_callback() {
		$active = isset( $_GET['subpage'] ) ? 1 : '';
		?>
        <div class="wrap">
            <h2><?php esc_html_e( 'Customer Coupons for WooCommerce', 'woo-customer-coupons' ) ?></h2>
            <div class="vi-ui secondary pointing menu">
                <a class="item <?php echo esc_attr( ! $active ? 'active' : '' ); ?>"
                   href="<?php echo esc_url( admin_url( 'admin.php?page=woo_customer_coupons' ) ) ?>"><?php esc_html_e( 'Coupons', 'woo-customer-coupons' ) ?></a>
                <a class="item <?php echo esc_attr( $active ? 'active' : '' ); ?>"
                   href="<?php echo esc_url( admin_url( 'admin.php?page=woo_customer_coupons&subpage=settings' ) ) ?>"><?php esc_html_e( 'Options', 'woo-customer-coupons' ) ?></a>
            </div>
			<?php
			if ( ! $active ) {
				$list_coupon = new WCC_List_Table_Coupons_Class();
				$list_coupon->prepare_items();
				?>
                <form action="admin.php?page=woo_customer_coupons" method="post" name="vi_wcc-form_search_coupon" id="vi_wcc-form_search_coupon">
					<?php
					$coupon_enable = $coupon_total = $coupon_disable = 0;
					$coupons_id    = array();
					$args          = array(
						'post_type'   => 'shop_coupon',
						'post_status' => 'publish'
					);
					$the_query     = new WP_Query( $args );
					if ( $the_query->have_posts() ) {
						while ( $the_query->have_posts() ) {
							$the_query->the_post();
							$coupons_id[] = get_the_ID();
						}
					}
					wp_reset_postdata();
					if ( ! empty( $coupons_id ) ) {
						foreach ( $coupons_id as $item ) {
							$coupon_total ++;
							if ( get_post_meta( $item, 'wcc_coupon_enable', true ) === 'yes' ) {
								$coupon_enable ++;
							} else {
								$coupon_disable ++;
							}
						}
					}
					?>
                    <ul class="vi_wcc_status_enable">
                        <li>
                            <a href="admin.php?page=woo_customer_coupons"><?php esc_html_e( "All", 'woo-customer-coupons' ) ?></a>(<?php echo esc_html( $coupon_total ) ?>)
                        </li>
                        <li>
                            |<a href="admin.php?page=woo_customer_coupons&count=enable"><?php esc_html_e( "Enable", 'woo-customer-coupons' ) ?></a>(<?php echo esc_html( $coupon_enable ) ?>)
                        </li>
                        <li>
                            |<a href="admin.php?page=woo_customer_coupons&count=disable"><?php esc_html_e( "Disable", 'woo-customer-coupons' ) ?></a>(<?php echo esc_html( $coupon_disable ) ?>)
                        </li>
                    </ul>
					<?php
					$list_coupon->search_box( esc_html__( "Search coupons", 'woo-customer-coupons' ), 'vi_wcc-_search_coupon' );
					$list_coupon->display();
					?>
                </form>
				<?php
			} else {
				$this->settings      = new VI_WOO_CUSTOMER_COUPONS_Data();
				$wcc_date_format     = $this->settings->get_params( 'wcc_date_format' );
				$wcc_template        = $this->settings->get_params( 'wcc_template' );
				$single_pro_page_pos = $this->settings->get_params( 'wcc_coupon-single_pro_page_pos' );
				?>
                <form action="" class="vi-ui form" method="post">
					<?php wp_nonce_field( '_vi_wcc_option_nonce_action', '_vi_wcc_option_nonce' ) ?>
                    <div class="vi-ui styled fluid accordion vi-wcc-accordion">
                        <div class="title active">
                            <i class="dropdown icon"></i>
							<?php esc_html_e( 'General', 'woo-customer-coupons' ) ?>
                        </div>
                        <div class="content active">
                            <table class="form-table">
                                <tr valign="top">
                                    <th>
                                        <label for="wcc_date_format"><?php esc_html_e( 'Date format', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <select name="wcc_date_format" id="wcc_date_format" class="vi-ui fluid dropdown vi_wcc_date_format">
                                            <option value="F j, Y" <?php selected( $wcc_date_format, 'F j, Y' ) ?>>
												<?php esc_html_e( 'F j, Y', 'woo-customer-coupons' ); ?>
                                            </option>
                                            <option value="Y-m-d" <?php selected( $wcc_date_format, 'Y-m-d' ) ?>>
												<?php esc_html_e( 'Y-MM-DD', 'woo-customer-coupons' ); ?>
                                            </option>
                                            <option value="d-m-Y" <?php selected( $wcc_date_format, 'd-m-Y' ) ?>>
												<?php esc_html_e( 'DD-MM-Y', 'woo-customer-coupons' ); ?>
                                            </option>
                                            <option value="m/d/Y" <?php selected( $wcc_date_format, 'm/d/Y' ) ?>>
												<?php esc_html_e( 'MM/DD/YY', 'woo-customer-coupons' ); ?>
                                            </option>
                                            <option value="d/m/Y" <?php selected( $wcc_date_format, 'd/m/Y' ) ?>>
												<?php esc_html_e( 'DD/MM/YY', 'woo-customer-coupons' ); ?>
                                            </option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Select the date format.', 'woo-customer-coupons' ) ?> </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="title">
                            <i class="dropdown icon"></i>
							<?php esc_html_e( 'Email', 'woo-customer-coupons' ) ?>
                        </div>
                        <div class="content">
							<?php
							$wcc_mail_enable                   = $this->settings->get_params( 'wcc_enable_send_mail' );
							$wcc_mail_subject                  = $this->settings->get_params( 'wcc_send-mail-subject' );
							$wcc_mail_heading                  = $this->settings->get_params( 'wcc_mail_heading' );
							$wcc_mail_content                  = $this->settings->get_params( 'wcc_mail_content' );
							$wcc_button_shop_now_url           = $this->settings->get_params( 'wcc_button_shop_now_url' );
							$wcc_button_shop_now_title         = $this->settings->get_params( 'wcc_button_shop_now_title' );
							$wcc_button_shop_now_bg_color      = $this->settings->get_params( 'wcc_button_shop_now_bg_color' );
							$wcc_button_shop_now_color         = $this->settings->get_params( 'wcc_button_shop_now_color' );
							$wcc_button_shop_now_size          = $this->settings->get_params( 'wcc_button_shop_now_size' );
							$wcc_button_shop_now_border_radius = $this->settings->get_params( 'wcc_button_shop_now_border_radius' );
							?>
                            <table class="form-table">
                                <tr>
                                    <th>
                                        <label for="wcc_send-mail-subject"><?php esc_html_e( 'Email subject', 'woocommerce-coupon-box' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="vi_wcc_send-mail-subject" id="vi_wcc_send-mail-subject"
                                               value="<?php echo wp_kses_post( $wcc_mail_subject ? $wcc_mail_subject : '' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_mail_heading"><?php esc_html_e( 'Email heading', 'woocommerce-coupon-box' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="wcc_mail_heading" id="wcc_mail_heading"
                                               value="<?php echo wp_kses_post( $wcc_mail_heading ? $wcc_mail_heading : '' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_mail_content"><?php esc_html_e( 'Email content', 'woocommerce-coupon-box' ) ?></label>
                                    </th>
                                    <td>
										<?php
										wp_editor( wp_unslash( $wcc_mail_content ), 'wcc_mail_content', array( 'editor_height' => 300 ) );
										?>
                                    </td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <p>{coupon_title}
                                            - <?php esc_html_e( ' The coupon title that you set on the coupons tab', 'woo-customer-coupons' ) ?></p>
                                        <p>{coupon_des}
                                            - <?php esc_html_e( ' The terms of coupon that you set on the coupons tab', 'woo-customer-coupons' ) ?></p>
                                        <p>{coupon_value}
                                            - <?php esc_html_e( 'The value of coupon, can be percentage or currency amount depending on coupon type', 'woo-customer-coupons' ) ?></p>
                                        <p>{coupon_code}
                                            - <?php esc_html_e( 'The code of coupon that will be sent to your customer', 'woo-customer-coupons' ) ?></p>
                                        <p>{date_expires}
                                            - <?php esc_html_e( 'From the date that given coupon will no longer be available', 'woo-customer-coupons' ) ?></p>
                                        <p>{last_valid_date}
                                            - <?php esc_html_e( 'That last day that coupon is valid', 'woo-customer-coupons' ) ?></p>
                                        <p>{site_title}
                                            - <?php esc_html_e( 'The title of your website', 'woo-customer-coupons' ) ?></p>
                                        <p>{shop_now}
                                            - <?php esc_html_e( 'Button ' );
											printf( '<a class="wcc-button-shop-now" href="%s" target="_blank" >%s</a>',
												$wcc_button_shop_now_url ? $wcc_button_shop_now_url : get_bloginfo( 'url' ),
												$wcc_button_shop_now_title ); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_button_shop_now_title"><?php esc_html_e( 'Button "Shop now" title', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="wcc_button_shop_now_title" id="wcc_button_shop_now_title"
                                               value="<?php echo wp_kses_post( $wcc_button_shop_now_title ? $wcc_button_shop_now_title : '' ); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_button_shop_now_url"><?php esc_html_e( 'Button "Shop now" url', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="wcc_button_shop_now_url" id="wcc_button_shop_now_url"
                                               value="<?php echo wp_kses_post( $wcc_button_shop_now_url ? $wcc_button_shop_now_url : '' ) ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_button_shop_now_bg_color"><?php esc_html_e( 'Button "Shop now" background', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="wcc_button_shop_now_bg_color" id="wcc_button_shop_now_bg_color"
                                               class="color-picker"
                                               value="<?php echo esc_attr( $wcc_button_shop_now_bg_color ); ?>"
                                               style="background-color: <?php echo esc_attr( $wcc_button_shop_now_bg_color ) ?>;">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_button_shop_now_color"><?php esc_html_e( 'Button "Shop now" color', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <input type="text" name="wcc_button_shop_now_color" id="wcc_button_shop_now_color"
                                               class="color-picker"
                                               value="<?php echo esc_attr( $wcc_button_shop_now_color ); ?>"
                                               style="background-color: <?php echo esc_attr( $wcc_button_shop_now_color ) ?>;">
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_button_shop_now_size"><?php esc_html_e( 'Button "Shop now" font size', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui right labeled input">
                                            <input type="number" name="wcc_button_shop_now_size" id="wcc_button_shop_now_size"
                                                   value="<?php echo esc_attr( $wcc_button_shop_now_size ); ?>">
                                            <div class="vi-ui basic label"><?php echo esc_html( 'Px' ); ?></div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <label for="wcc_button_shop_now_border_radius"><?php esc_html_e( 'Button "Shop now" border radius', 'woo-customer-coupons' ) ?></label>
                                    </th>
                                    <td>
                                        <div class="vi-ui right labeled input">
                                            <input type="number" name="wcc_button_shop_now_border_radius" id="wcc_button_shop_now_border_radius"
                                                   value="<?php echo esc_attr( $wcc_button_shop_now_border_radius ); ?>">
                                            <div class="vi-ui basic label"><?php echo esc_html( 'Px' ); ?></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="title">
                            <i class="dropdown icon"></i>
							<?php esc_html_e( 'Design', 'woo-customer-coupons' ) ?>
                        </div>
                        <div class="content">
                            <div class="field">
                                <div class="equal width fields">
                                    <div class="field">
                                        <label for=""><?php esc_html_e( 'Template', 'woo-customer-coupons' ) ?></label>
                                        <select name="wcc_template" id="wcc_template" class="vi-ui fluid dropdown vi_wcc_template">
                                            <option value="1" <?php selected( $wcc_template, '1' ) ?>>
												<?php esc_html_e( 'Template One', 'woo-customer-coupons' ) ?>
                                            </option>
                                            <option value="2" <?php selected( $wcc_template, '2' ) ?>>
												<?php esc_html_e( 'Template Two', 'woo-customer-coupons' ) ?>
                                            </option>
                                            <option value="3" <?php selected( $wcc_template, '3' ) ?>>
												<?php esc_html_e( 'Template Three', 'woo-customer-coupons' ) ?>
                                            </option>
                                            <option value="4" <?php selected( $wcc_template, '4' ) ?>>
												<?php esc_html_e( 'Template Four', 'woo-customer-coupons' ) ?>
                                            </option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Select the coupon\'s template.', 'woo-customer-coupons' ) ?> </p>
                                    </div>
                                    <div class="field">
                                        <label><?php esc_html_e( 'Position on the single product page', 'woo-customer-coupons' ); ?></label>
                                        <select name="wcc_coupon-single_pro_page_pos" class="vi-ui fluid dropdown vi_wcc_coupon-single_pro_page_pos">
                                            <option value="0" <?php selected( $single_pro_page_pos, '0' ) ?>><?php esc_html_e( 'Not show', 'woo-customer-coupons' ); ?></option>
                                            <option value="5" <?php selected( $single_pro_page_pos, '5' ) ?>><?php esc_html_e( 'Before title', 'woo-customer-coupons' ); ?></option>
                                            <option value="10" <?php selected( $single_pro_page_pos, '10' ) ?>><?php esc_html_e( 'After title', 'woo-customer-coupons' ); ?></option>
                                            <option value="20" <?php selected( $single_pro_page_pos, '20' ) ?>><?php esc_html_e( 'After price', 'woo-customer-coupons' ); ?></option>
                                            <option value="30" <?php selected( $single_pro_page_pos, '30' ) ?>><?php esc_html_e( 'Before cart', 'woo-customer-coupons' ); ?></option>
                                            <option value="40" <?php selected( $single_pro_page_pos, '40' ) ?>><?php esc_html_e( 'After cart', 'woo-customer-coupons' ); ?></option>
                                            <option value="50" <?php selected( $single_pro_page_pos, '50' ) ?>><?php esc_html_e( 'After list category', 'woo-customer-coupons' ); ?></option>
                                        </select>
                                        <p class="description"><?php esc_html_e( 'Select the location to show coupon\'s description .', 'woo-customer-coupons' ) ?></p>
                                    </div>
                                </div>
                                <div class="field vi_wcc_template_style vi_wcc_template_1 <?php echo esc_attr( $wcc_template === '1' ? '' : 'vi_wcc_hidden' ); ?>">
									<?php
									$template_1_bg_color             = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-background-color' );
									$template_1_bg_color1            = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-background-color1' );
									$template_1_bg_color2            = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-background-color2' );
									$template_1_title_color          = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-title-color' );
									$template_1_content_des_color    = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-des-color' );
									$template_1_content_expire_color = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-content-expire-color' );
									$template_1_button_bg_color      = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-button-background-color' );
									$template_1_button_color         = $this->settings->get_style_coupon( 'wcc-template-one', 'wcc-temple-one-button-text-color' );
									?>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Background color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-one-background-color"
                                                   name="wcc-template-one[wcc-temple-one-background-color]"
                                                   value="<?php echo esc_attr( $template_1_bg_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_1_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Linear gradient for content background color', 'woo-customer-coupons' ) ?></label>
                                            <div class="equal width fields">
                                                <div class="field">
                                                    <input type="text"
                                                           class="color-picker wcc-temple-one-content-background-color1"
                                                           name="wcc-template-one[wcc-temple-one-content-background-color1]"
                                                           value="<?php echo esc_attr( $template_1_bg_color1 ) ?>"
                                                           style="background:<?php echo esc_attr( $template_1_bg_color1 ) ?>">
                                                </div>
                                                <div class="field">
                                                    <input type="text"
                                                           class="color-picker wcc-temple-one-content-background-color2"
                                                           name="wcc-template-one[wcc-temple-one-content-background-color2]"
                                                           value="<?php echo esc_attr( $template_1_bg_color2 ) ?>"
                                                           style="background:<?php echo esc_attr( $template_1_bg_color2 ) ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Content title color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-one-content-title-color"
                                                   name="wcc-template-one[wcc-temple-one-content-title-color]"
                                                   value="<?php echo esc_attr( $template_1_title_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_1_title_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Coupon description color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-one-content-des-color"
                                                   name="wcc-template-one[wcc-temple-one-content-des-color]"
                                                   value="<?php echo esc_attr( $template_1_content_des_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_1_content_des_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Coupon expire color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-one-content-expire-color"
                                                   name="wcc-template-one[wcc-temple-one-content-expire-color]"
                                                   value="<?php echo esc_attr( $template_1_content_expire_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_1_content_expire_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Button background color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-one-button-background-color"
                                                   name="wcc-template-one[wcc-temple-one-button-background-color]"
                                                   value="<?php echo esc_attr( $template_1_button_bg_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_1_button_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Button text color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-one-button-text-color"
                                                   name="wcc-template-one[wcc-temple-one-button-text-color]"
                                                   value="<?php echo esc_attr( $template_1_button_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_1_button_color ) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="field vi_wcc_template_style vi_wcc_template_2 <?php echo esc_attr( $wcc_template === '2' ? '' : 'vi_wcc_hidden' ); ?>">
									<?php
									$template_2_bg_color     = $this->settings->get_style_coupon( 'wcc-template-two', 'wcc-temple-two-background-color' );
									$template_2_color        = $this->settings->get_style_coupon( 'wcc-template-two', 'wcc-temple-two-title-color' );
									$template_2_border_color = $this->settings->get_style_coupon( 'wcc-template-two', 'wcc-temple-two-border-color' );
									?>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Background', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-two-background-color"
                                                   name="wcc-template-two[wcc-temple-two-background-color]"
                                                   value="<?php echo esc_attr( $template_2_bg_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_2_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-two-title-color"
                                                   name="wcc-template-two[wcc-temple-two-title-color]"
                                                   value="<?php echo esc_attr( $template_2_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_2_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Border color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-two-border-color"
                                                   name="wcc-template-two[wcc-temple-two-border-color]"
                                                   value="<?php echo esc_attr( $template_2_border_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_2_border_color ) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="field vi_wcc_template_style vi_wcc_template_3 <?php echo esc_attr( $wcc_template === '3' ? '' : 'vi_wcc_hidden' ); ?>">
									<?php
									$template_3_bg_color     = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-background-color' );
									$template_3_title_color  = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-title-color' );
									$template_3_term_color   = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-term-color' );
									$template_3_expire_color = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-expire-color' );
									$template_3_border_color = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-border-color' );
									$template_3_border_type  = $this->settings->get_style_coupon( 'wcc-template-three', 'wcc-temple-three-border-type' );
									?>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Background', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-three-background-color"
                                                   name="wcc-template-three[wcc-temple-three-background-color]"
                                                   value="<?php echo esc_attr( $template_3_bg_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_3_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Title color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-three-title-color"
                                                   name="wcc-template-three[wcc-temple-three-title-color]"
                                                   value="<?php echo esc_attr( $template_3_title_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_3_title_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Terms color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-three-term-color"
                                                   name="wcc-template-three[wcc-temple-three-term-color]"
                                                   value="<?php echo esc_attr( $template_3_term_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_3_term_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Expire color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-three-expire-color"
                                                   name="wcc-template-three[wcc-temple-three-expire-color]"
                                                   value="<?php echo esc_attr( $template_3_expire_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_3_expire_color ) ?>">
                                        </div>
                                    </div>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Border type', 'woo-customer-coupons' ) ?></label>
                                            <select name="wcc-template-three[wcc-temple-three-border-type]" class="vi-ui fluid dropdown wcc-temple-three-border-type">
                                                <option value="dotted" <?php selected( $template_3_border_type, 'dotted' ) ?>>
													<?php esc_html_e( 'Dotted', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="solid" <?php selected( $template_3_border_type, 'solid' ) ?>>
													<?php esc_html_e( 'Solid', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="dashed" <?php selected( $template_3_border_type, 'dashed' ) ?>>
													<?php esc_html_e( 'Dashed', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="double" <?php selected( $template_3_border_type, 'double' ) ?>>
													<?php esc_html_e( 'Double', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="groove" <?php selected( $template_3_border_type, 'groove' ) ?>>
													<?php esc_html_e( 'Groove', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="ridge" <?php selected( $template_3_border_type, 'ridge' ) ?>>
													<?php esc_html_e( 'Ridge', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="outset" <?php selected( $template_3_border_type, 'outset' ) ?>>
													<?php esc_html_e( 'Outset', 'woo-customer-coupons' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Border color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-three-border-color"
                                                   name="wcc-template-three[wcc-temple-three-border-color]"
                                                   value="<?php echo esc_attr( $template_3_border_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_3_border_color ) ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="field vi_wcc_template_style vi_wcc_template_4 <?php echo esc_attr( $wcc_template === '4' ? '' : 'vi_wcc_hidden' ); ?>">
									<?php
									$template_4_bg_color       = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-background-color' );
									$template_4_title_bg_color = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-title-background-color' );
									$template_4_title_color    = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-title-color' );
									$template_4_term_color     = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-term-color' );
									$template_4_border_color   = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-border-color' );
									$template_4_border_type    = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-border-type' );
									$template_4_border_radius  = $this->settings->get_style_coupon( 'wcc-template-four', 'wcc-temple-four-border-radius' );
									?>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Background', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-four-background-color"
                                                   name="wcc-template-four[wcc-temple-four-background-color]"
                                                   value="<?php echo esc_attr( $template_4_bg_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_4_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Title background color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-four-title-background-color"
                                                   name="wcc-template-four[wcc-temple-four-title-background-color]"
                                                   value="<?php echo esc_attr( $template_4_title_bg_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_4_title_bg_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Title color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-four-title-color"
                                                   name="wcc-template-four[wcc-temple-four-title-color]"
                                                   value="<?php echo esc_attr( $template_4_title_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_4_title_color ) ?>">
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Term and Expire date color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-four-term-color"
                                                   name="wcc-template-four[wcc-temple-four-term-color]"
                                                   value="<?php echo esc_attr( $template_4_term_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_4_term_color ) ?>">
                                        </div>
                                    </div>
                                    <div class="equal width fields">
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Border type', 'woo-customer-coupons' ) ?></label>
                                            <select name="wcc-template-four[wcc-temple-four-border-type]" class="vi-ui fluid dropdown wcc-temple-four-border-type">
                                                <option value="dotted"<?php selected( $template_4_border_type, 'dotted' ) ?> >
													<?php esc_html_e( 'Dotted', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="solid"<?php selected( $template_4_border_type, 'solid' ) ?> >
													<?php esc_html_e( 'Solid', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="dashed"<?php selected( $template_4_border_type, 'dashed' ) ?> >
													<?php esc_html_e( 'Dashed', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="double"<?php selected( $template_4_border_type, 'double' ) ?> >
													<?php esc_html_e( 'Double', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="groove"<?php selected( $template_4_border_type, 'groove' ) ?> >
													<?php esc_html_e( 'Groove', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="ridge"<?php selected( $template_4_border_type, 'ridge' ) ?> >
													<?php esc_html_e( 'Ridge', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="outset"<?php selected( $template_4_border_type, 'outset' ) ?> >
													<?php esc_html_e( 'Outset', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="none"<?php selected( $template_4_border_type, 'none' ) ?> >
													<?php esc_html_e( 'None', 'woo-customer-coupons' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Border radius', 'woo-customer-coupons' ) ?></label>
                                            <select name="wcc-template-four[wcc-temple-four-border-radius]" class="vi-ui fluid dropdown wcc-temple-four-border-radius">
                                                <option value="3px"<?php selected( $template_4_border_radius, '3px' ) ?> >
													<?php esc_html_e( '3px', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="10px"<?php selected( $template_4_border_radius, '10px' ) ?> >
													<?php esc_html_e( '10px', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="20px"<?php selected( $template_4_border_radius, '20px' ) ?> >
													<?php esc_html_e( '20px', 'woo-customer-coupons' ); ?>
                                                </option>
                                                <option value="none"<?php selected( $template_4_border_radius, 'none' ) ?> >
													<?php esc_html_e( 'None', 'woo-customer-coupons' ); ?>
                                                </option>
                                            </select>
                                        </div>
                                        <div class="field">
                                            <label for=""><?php esc_html_e( 'Border color', 'woo-customer-coupons' ) ?></label>
                                            <input type="text"
                                                   class="color-picker wcc-temple-four-border-color"
                                                   name="wcc-template-four[wcc-temple-four-border-color]"
                                                   value="<?php echo esc_attr( $template_4_border_color ) ?>"
                                                   style="background:<?php echo esc_attr( $template_4_border_color ) ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p>
                        <button class="vi-ui button primary vi_wcc_settings_save"
                                name="vi_wcc_settings_save">
							<?php esc_html_e( 'Save', 'woo-customer-coupons' ) ?>
                        </button>
                        <button type="button" class="vi-ui button vi_wcc_settings_default"
                                name="vi_wcc_settings_default">
							<?php esc_html_e( 'Settings default', 'woo-customer-coupons' ) ?>
                        </button>
                    </p>
                </form>
				<?php
			}
			?>
        </div>
		<?php
		do_action( 'villatheme_support_woo_customer_coupons' );
	}

	public function save_data() {
		$page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		$subpage = isset( $_REQUEST['subpage'] ) ? 1 : '';
		if ( $page !== 'woo_customer_coupons' || ! $subpage ) {
			return;
		}
		global $vi_wcc_settings;
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! isset( $_POST['_vi_wcc_option_nonce'] ) || ! wp_verify_nonce( wc_clean($_POST['_vi_wcc_option_nonce']), '_vi_wcc_option_nonce_action' ) ) {
			return;
		}
		$arg      = array();
		$arg_map1 = array(
			'wcc_date_format',
			'wcc_template',
			'wcc_coupon-single_pro_page_pos',
			'wcc_enable_send_mail',
			'wcc_button_shop_now_title',
			'wcc_button_shop_now_bg_color',
			'wcc_button_shop_now_color',
			'wcc_button_shop_now_size',
			'wcc_button_shop_now_border_radius',
			'wcc_button_shop_now_url',
		);
		$arg_map2 = array(
			'vi_wcc_send-mail-subject',
			'wcc_mail_heading',
			'wcc_mail_content',
		);
		$arg_map3 = array(
			'wcc-template-one',
			'wcc-template-two',
			'wcc-template-three',
			'wcc-template-four',
		);
		if ( isset( $_POST['vi_wcc_settings_save'] ) ) {
			foreach ( $arg_map1 as $item ) {
				$arg[ $item ] = isset( $_POST[ $item ] ) ? sanitize_text_field( wp_unslash( $_POST[ $item ] ) ) : '';
			}
			foreach ( $arg_map2 as $item ) {
				$arg[ $item ] = isset( $_POST[ $item ] ) ? vi_stripslashes_deep_kses( $_POST[ $item ] ) : array();
			}
			foreach ( $arg_map3 as $item ) {
				$arg[ $item ] = isset( $_POST[ $item ] ) ? vi_stripslashes_deep( $_POST[ $item ] ) : array();
			}
			$arg = wp_parse_args( $arg, get_option( 'wcc_options', $vi_wcc_settings ) );
			update_option( 'wcc_options', $arg );
			$vi_wcc_settings = $arg;
		}
		if ( isset( $_POST['vi_wcc_settings_default'] ) ) {
			$arg = $this->settings->get_default();
			update_option( 'wcc_options', $arg );
			$vi_wcc_settings = $arg;
		}
	}

	public function admin_enqueue_scripts() {
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		if ( $page === 'woo_customer_coupons' ) {
			global $wp_scripts;
			$scripts = $wp_scripts->registered;
			foreach ( $scripts as $k => $script ) {
				preg_match( '/^\/wp-/i', $script->src, $result );
				if ( count( array_filter( $result ) ) ) {
					preg_match( '/^(\/wp-content\/plugins|\/wp-content\/themes)/i', $script->src, $result1 );
					if ( count( array_filter( $result1 ) ) ) {
						wp_dequeue_script( $script->handle );
					}
				} else {
					if ( $script->handle != 'query-monitor' ) {
						wp_dequeue_script( $script->handle );
					}
				}
			}
			wp_enqueue_style( 'vi-wcc-menu', WOO_CUSTOM_COUPONS_CSS . 'menu.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi-wcc-segment', WOO_CUSTOM_COUPONS_CSS . 'segment.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi-wcc-css', WOO_CUSTOM_COUPONS_CSS . 'admin-settings.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi-wcc-form', WOO_CUSTOM_COUPONS_CSS . 'form.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi-wcc-button', WOO_CUSTOM_COUPONS_CSS . 'button.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi-wcc-icon', WOO_CUSTOM_COUPONS_CSS . 'icon.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
			wp_enqueue_style( 'vi-wcc-villatheme-support', WOO_CUSTOM_COUPONS_CSS . 'villatheme-support.css', '', WOO_CUSTOM_COUPONS_VERSION );
			if ( isset( $_GET['subpage'] ) ) {
				if ( isset( $wp_scripts->registered['jquery-ui-accordion'] ) ) {
					unset( $wp_scripts->registered['jquery-ui-accordion'] );
					wp_dequeue_script( 'jquery-ui-accordion' );
				}
				if ( isset( $wp_scripts->registered['accordion'] ) ) {
					unset( $wp_scripts->registered['accordion'] );
					wp_dequeue_script( 'accordion' );
				}
				wp_enqueue_style( 'vi-wcc-accordion', WOO_CUSTOM_COUPONS_CSS . 'accordion.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_style( 'vi-wcc-checkbox', WOO_CUSTOM_COUPONS_CSS . 'checkbox.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_style( 'vi-wcc-dropdown', WOO_CUSTOM_COUPONS_CSS . 'dropdown.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_style( 'vi-wcc-label', WOO_CUSTOM_COUPONS_CSS . 'label.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_style( 'vi-wcc-input', WOO_CUSTOM_COUPONS_CSS . 'input.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_style( 'vi-wcc-transition', WOO_CUSTOM_COUPONS_CSS . 'transition.min.css', '', WOO_CUSTOM_COUPONS_VERSION );
				/*Color picker*/
				wp_enqueue_script( 'iris',
					admin_url( 'js/iris.min.js' ),
					array(
						'jquery-ui-draggable',
						'jquery-ui-slider',
						'jquery-touch-punch',
					),
					false, 1 );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'vi-wcc-accordion', WOO_CUSTOM_COUPONS_JS . 'accordion.min.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_script( 'vi-wcc-address', WOO_CUSTOM_COUPONS_JS . 'address.min.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_script( 'vi-wcc-checkbox', WOO_CUSTOM_COUPONS_JS . 'checkbox.min.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_script( 'vi-wcc-dropdown', WOO_CUSTOM_COUPONS_JS . 'dropdown.min.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_script( 'vi-wcc-form', WOO_CUSTOM_COUPONS_JS . 'form.min.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_script( 'vi-wcc-transition', WOO_CUSTOM_COUPONS_JS . 'transition.min.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				wp_enqueue_script( 'vi-wcc-js', WOO_CUSTOM_COUPONS_JS . 'admin-settings.js', array( 'jquery' ), WOO_CUSTOM_COUPONS_VERSION );
				$this->settings = new VI_WOO_CUSTOMER_COUPONS_Data();
				$css            = '';
				$css            .= '.wcc-button-shop-now{';
				if ( $wcc_button_shop_now_bg_color = $this->settings->get_params( 'wcc_button_shop_now_bg_color' ) ) {
					$css .= 'background:' . $wcc_button_shop_now_bg_color . ';';
				}
				if ( $wcc_button_shop_now_color = $this->settings->get_params( 'wcc_button_shop_now_color' ) ) {
					$css .= 'color:' . $wcc_button_shop_now_color . ';';
				}
				if ( $wcc_button_shop_now_size = $this->settings->get_params( 'wcc_button_shop_now_size' ) ) {
					$css .= 'font-size:' . $wcc_button_shop_now_size . 'px;';
				}
				if ( $wcc_button_shop_now_border_radius = $this->settings->get_params( 'wcc_button_shop_now_border_radius' ) ) {
					$css .= 'border-radius:' . $wcc_button_shop_now_border_radius . 'px;';
				}
				$css .= '}';
				$css .= '.wcc-button-shop-now:hover{';
				if ( $wcc_button_shop_now_color = $this->settings->get_params( 'wcc_button_shop_now_color' ) ) {
					$css .= 'color:' . $wcc_button_shop_now_color . ';';
				}
				$css .= '}';
				wp_add_inline_style( 'vi-wcc-css', $css );
				$arg = array(
					'ajax_url'        => admin_url( 'admin-ajax.php' ),
					'setting_default' => esc_html__( 'Would you want to reset to default settings?', 'woo-customer-coupons' ),
				);
				wp_localize_script( 'vi-wcc-js', 'vi_wcc_admin_settings', $arg );
			}
		}
	}
}