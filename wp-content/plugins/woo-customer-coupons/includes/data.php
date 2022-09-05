<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WOO_CUSTOMER_COUPONS_Data {
	private $params;
	private $default;
	private $prefix;

	/**
	 * VI_WOO_CUSTOMER_COUPONS_Data constructor.
	 * Init setting
	 */
	public function __construct() {
		global $vi_wcc_settings;
		if ( ! $vi_wcc_settings ) {
			$vi_wcc_settings = get_option( 'wcc_options', array() );
		}
		$this->prefix  = 'vi-wcc-';
		$this->default = array(
			'wcc_template'                   => '1',
			'wcc-template-one'               => array(
				'wcc-temple-one-background-color'          => '#ffffff',
				'wcc-temple-one-content-background-color1' => '#ffbb00ab',
				'wcc-temple-one-content-background-color2' => '#f47b7b',
				'wcc-temple-one-content-title-color'       => '#ffffff',
				'wcc-temple-one-content-des-color'         => '#fde7e7',
				'wcc-temple-one-content-expire-color'      => '#f5f5f5',
				'wcc-temple-one-button-background-color'   => '#f5e2a057',
				'wcc-temple-one-button-text-color'         => '#320463'
			),
			'wcc-template-two'               => array(
				'wcc-temple-two-background-color' => '#4bbb89',
				'wcc-temple-two-title-color'      => '#1d0b0b',
				'wcc-temple-two-border-color'     => '#0a0a46',
			),
			'wcc-template-three'             => array(
				'wcc-temple-three-background-color' => '#fff',
				'wcc-temple-three-title-color'      => '#5eb707',
				'wcc-temple-three-term-color'       => '#5eb707',
				'wcc-temple-three-expire-color'     => '#5eb707',
				'wcc-temple-three-border-color'     => '#d40404',
				'wcc-temple-three-border-type'      => 'dotted',
			),
			'wcc-template-four'              => array(
				'wcc-temple-four-background-color'       => '#ffffff',
				'wcc-temple-four-title-color'            => '#f7f7f7',
				'wcc-temple-four-title-background-color' => '#d14b1b',
				'wcc-temple-four-term-color'             => '#6f6060',
				'wcc-temple-four-border-color'           => '#d14b1b',
				'wcc-temple-four-border-type'            => 'none',
				'wcc-temple-four-border-radius'          => '3px'
			),
			'wcc_coupon-single_pro_page_pos' => '0',
			'wcc_send-mail-subject'          => 'send you a Coupon form {site_title}',
//			'wcc_enable_send_mail'           => 'yes',
			'wcc_date_format'                => 'd-m-Y',
			'wcc_mail_heading'               => '{coupon_value} OFF DISCOUNT COUPON CODE OFFER',
			'wcc_mail_content'               => 'Get {coupon_title} {coupon_des} until {last_valid_date}. Don\'t miss out this great chance on our shop.

{coupon_code}

{shop_now}',

			'wcc_button_shop_now_title'         => 'Shop Now',
			'wcc_button_shop_now_url'           => get_bloginfo( 'url' ),
			'wcc_button_shop_now_bg_color'      => '#52d2aa',
			'wcc_button_shop_now_color'         => '#fff',
			'wcc_button_shop_now_size'          => '16',
			'wcc_button_shop_now_border_radius' => '3',
		);
		$this->params  = apply_filters( 'vi_wcc_settings_args', wp_parse_args( $vi_wcc_settings, $this->default ) );
	}

	public function get_params( $name = "" ) {
		if ( ! $name ) {
			return $this->params;
		} elseif ( isset( $this->params[ $name ] ) ) {
			return apply_filters( 'vi_wcc_settings-' . $name, $this->params[ $name ] );
		}else {
			return false;
		}
	}

	public function get_default( $name = "" ) {
		if ( ! $name ) {
			return $this->default;
		} elseif ( isset( $this->default[ $name ] ) ) {
			return apply_filters( 'vi_wcc_settings_default-' . $name, $this->default[ $name ] );
		} else {
			return false;
		}
	}

	public function get_style_coupon( $type = "", $name = '' ) {
		$result = isset( $this->get_params( $type )[ $name ] ) ? $this->get_params( $type )[ $name ] : $this->get_default( $type )[ $name ];

		return $result;
	}

	public function set( $name ) {
		if ( is_array( $name ) ) {
			return implode( ' ', array_map( array( $this, 'set' ), $name ) );

		} else {
			return esc_attr__( $this->prefix . $name );

		}
	}
}

new VI_WOO_CUSTOMER_COUPONS_Data();