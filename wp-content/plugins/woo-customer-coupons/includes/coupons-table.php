<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WCC_List_Table_Coupons_Class extends WP_List_Table {
	public function prepare_items() {

		$orderby       = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( $_REQUEST['orderby'] ) : '';
		$order         = isset( $_REQUEST['order'] ) ? sanitize_text_field( $_REQUEST['order'] ) : '';
		$search_key    = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$filter        = isset( $_REQUEST['category-filter'] ) ? sanitize_text_field( $_REQUEST['category-filter'] ) : '';
		$filter_enable = isset( $_REQUEST['count'] ) ? sanitize_text_field( $_REQUEST['count'] ) : '';

		$data          = $this->list_coupon_data( $orderby, $order, $search_key, $filter, $filter_enable );
		$user          = get_current_user_id();
		$screen        = get_current_screen();
		$screen_option = $screen->get_option( 'per_page', 'option' );
		$per_page      = get_user_meta( $user, $screen_option, true );

		if ( empty ( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}
		$current_page = $this->get_pagenum();
		$total_items  = count( $data );
		$data         = array_slice( $data, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->items = $data;
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );


		$column                = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $column, $hidden, $sortable );
	}

	public function list_coupon_data( $orderby = '', $order = '', $search_key = '', $filter = '', $filter_enable = '' ) {

		$coupons   = array();
		$data      = array();
		$args      = array(
			'post_type'   => 'shop_coupon',
			'post_status' => 'publish'
		);
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$coupons[] = new WC_Coupon( get_the_ID() );

			}
		}
		wp_reset_postdata();


		$stt = 0;

		foreach ( $coupons as $coupon ) {
			$data[ $stt ]['id']          = $coupon->get_id();
			$data[ $stt ]['code']        = ' <strong>
                                        <a class="row-title" href="' . admin_url( 'post.php?post=' . $coupon->get_id() . '&#038;action=edit' ) . '">' . $coupon->get_code() . '</a>
                                     </strong>';
			$data[ $stt ]['description'] = $coupon->get_description();
			$data[ $stt ]['enable']      = ( get_post_meta( $coupon->get_id(), 'wcc_coupon_enable', true ) === 'yes' ) ? 'Enable' : '-';
			if ( get_post_meta( $coupon->get_id(), 'wcc_coupon_enable', true ) === 'yes' ) {
				$data[ $stt ]['enable'] = 'yes';
				$coupon_title           = empty( get_post_meta( $coupon->get_id(), 'wcc_custom_coupon_title', true ) ) ? esc_html__( 'No title', 'woo-customer-coupons' ) : get_post_meta( $coupon->get_id(), 'wcc_custom_coupon_title', true );
				$data[ $stt ]['title']  = $coupon_title;

				$data[ $stt ]['terms'] = empty( get_post_meta( $coupon->get_id(), 'wcc_custom_coupon_terms', true ) ) ? '-' : get_post_meta( $coupon->get_id(), 'wcc_custom_coupon_terms', true );

			} else {
				$data[ $stt ]['enable'] = '-';
				$data[ $stt ]['title']  = '-';
				$data[ $stt ]['terms']  = '-';
			}

			$product_id   = empty( $coupon->get_product_ids() ) ? '' : $coupon->get_product_ids();
			$product_name = '';
			if ( ! empty( $product_id ) ) {
				foreach ( $product_id as $id_product ) {
					$product      = get_post( $id_product );
					$product_name .= ',' . $product->post_title;
				}
			} else {
				$product_name = '-';
			}
			$data[ $stt ]['product'] = trim( $product_name, ',' );

			$category_id   = empty( $coupon->get_product_categories() ) ? '' : $coupon->get_product_categories();
			$category_name = '';
			if ( ! empty( $category_id ) ) {
				foreach ( $category_id as $id_category ) {
					$category      = get_term( $id_category );
					$category_name .= ',' . $category->name;
				}
			} else {
				$category_name = '-';
			}
			$data[ $stt ]['category'] = trim( $category_name, ',' );
			$date_format              = 'm/d/Y';

			$data[ $stt ++ ]['date_start'] = date_format( date_create( get_post_meta( $coupon->get_id(), 'wcc_custom_coupon_start_date', true ) ), $date_format );


		}
		if ( ! empty( $search_key ) ) {
			$search_couppon = array();
			foreach ( $data as $item ) {
				if ( preg_match( '/' . $search_key . '/', $item['code'] ) || preg_match( '/' . $search_key . '/', $item['title'] )
				     || preg_match( '/' . $search_key . '/', $item['terms'] )
				) {
					$search_couppon[] = $item;
				}
			}
			$data = $search_couppon;
		}
		$category_filter = array();
		if ( ! empty( $filter ) ) {
			$filter = get_term( $filter );
			foreach ( $data as $item ) {
				$category = empty( $item['category'] ) ? '' : explode( ',', $item['category'] );
				if ( ! empty( $category ) ) {
					for ( $i = 0; $i < count( $category ); $i ++ ) {
						if ( $filter->name == strip_tags( $category[ $i ], 'a' ) ) {
							$category_filter[] = $item;
						}
					}
				}
			}
			$data = $category_filter;
		}
		$coupon_enable  = array();
		$coupon_disable = array();
		if ( ! empty( $filter_enable ) ) {
			foreach ( $data as $item ) {
				if ( $item['enable'] == 'yes' ) {
					$coupon_enable[] = $item;
				} else {
					$coupon_disable[] = $item;
				}
			}
			if ( $filter_enable == 'enable' ) {
				$data = $coupon_enable;
			} elseif ( $filter_enable == 'disable' ) {
				$data = $coupon_disable;
			}
		}

		$asc_start = array();
		if ( $orderby == 'date_start' && $order == 'asc' ) {
			foreach ( $data as $datum ) {
				$asc_start[] = $datum['date_start'];
			}
			$asc_start = array_unique( $asc_start );
			sort( $asc_start );

			for ( $i = 0; $i < count( $asc_start ); $i ++ ) {
				foreach ( $data as $item ) {
					if ( $asc_start[ $i ] === $item['date_start'] ) {
						$tam[] = $item;
					}
				}
			}
			$data = $tam;
		} elseif ( $orderby = 'date_start' && $order == 'desc' ) {
			foreach ( $data as $datum ) {
				$asc_start[] = $datum['date_start'];
			}
			$asc_start = array_unique( $asc_start );
			rsort( $asc_start );

			for ( $i = 0; $i < count( $asc_start ); $i ++ ) {
				foreach ( $data as $item ) {
					if ( $asc_start[ $i ] === $item['date_start'] ) {
						$tam[] = $item;
					}
				}
			}
			$data = $tam;

		}

		return $data;
	}

	public function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			?>

            <div class="alignleft actions ">
				<?php
				$terms = get_terms( array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				) );
				?>
                <select name="category-filter" class="wcc-filter-category">
                    <option value=""> <?php esc_html_e( 'Filter by Category', 'woo-sticky-add-to-cart' ) ?></option>
					<?php
					foreach ( $terms as $term ) {
						$selected = '';
						if ( isset( $_GET['category-filter'] ) && ( sanitize_text_field( $_GET['category-filter'] ) === $term->term_id ) ) {
							$selected = ' selected = "selected"';
						}
						?>
                        <option value="<?php echo esc_attr($term->term_id); ?>" <?php echo esc_html($selected) ?> ><?php echo wp_kses_post($term->name) ?></option>
					<?php }
					?>
                </select>
				<?php

				submit_button( esc_html__( 'Filter', 'woo-customer-coupons' ), 'button', 'vi_wcc_button_filter', false );

				?>

            </div>

			<?php
		}

	}

	public function get_columns() {
		$columns = array(
			'code'        => esc_html__( 'Coupon Code', 'woo-customer-coupons' ),
			'enable'      => esc_html__( 'Enable', 'woo-customer-coupons' ),
			'title'       => esc_html__( 'Title', 'woo-customer-coupons' ),
			'terms'       => esc_html__( 'Terms', 'woo-customer-coupons' ),
			'description' => esc_html__( 'Description', 'woo-customer-coupons' ),
			'product'     => esc_html__( 'Product', 'woo-customer-coupons' ),
			'category'    => esc_html__( 'Product Category', 'woo-customer-coupons' ),
			'date_start'  => esc_html__( 'Start date', 'woo-customer-coupons' )
		);

		return $columns;
	}

	public function get_sortable_columns() {
		return $sort = array(
			'date_start' => array( 'date_start', false ),
		);
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'code':
			case 'title':
			case 'enable':
			case 'terms':
			case 'description':
			case 'product':
			case 'category':
			case 'date_start':
				return $item[ $column_name ];
			default:
				return '-';
		}
	}
}