<?php
/**
 * Flatsome functions and definitions
 *
 * @package flatsome
 */

require get_template_directory() . '/inc/init.php';

/**
 * Note: It's not recommended to add any custom code here. Please use a child theme so that your customizations aren't lost during updates.
 * Learn more here: http://codex.wordpress.org/Child_Themes
 */
add_filter( 'widget_text', 'do_shortcode' );

// Add button "Mua ngay"
add_action('woocommerce_after_add_to_cart_button','devvn_quickbuy_after_addtocart_button');
function devvn_quickbuy_after_addtocart_button(){
    global $product;
    ?>
    <style>
        .devvn-quickbuy button.single_add_to_cart_button.loading:after {
            display: none;
        }
        .devvn-quickbuy button.single_add_to_cart_button.button.alt.loading {
            color: #fff;
            pointer-events: none !important;
        }
        .devvn-quickbuy button.buy_now_button {
            position: relative;
            color: rgba(255,255,255,0.05);
        }
        .devvn-quickbuy button.buy_now_button:after {
            animation: spin 500ms infinite linear;
            border: 2px solid #fff;
            border-radius: 32px;
            border-right-color: transparent !important;
            border-top-color: transparent !important;
            content: "";
            display: block;
            height: 16px;
            top: 50%;
            margin-top: -8px;
            left: 50%;
            margin-left: -8px;
            position: absolute;
            width: 16px;
        }
    </style>
    <button type="button" class="button buy_now_button">
        <?php _e('Mua ngay', 'devvn'); ?>
    </button>
    <input type="hidden" name="is_buy_now" class="is_buy_now" value="0" autocomplete="off"/>
    <script>
        jQuery(document).ready(function(){
            jQuery('body').on('click', '.buy_now_button', function(e){
                e.preventDefault();
                var thisParent = jQuery(this).parents('form.cart');
                if(jQuery('.single_add_to_cart_button', thisParent).hasClass('disabled')) {
                    jQuery('.single_add_to_cart_button', thisParent).trigger('click');
                    return false;
                }
                thisParent.addClass('devvn-quickbuy');
                jQuery('.is_buy_now', thisParent).val('1');
                jQuery('.single_add_to_cart_button', thisParent).trigger('click');
            });
        });
    </script>
    <?php
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout');
function redirect_to_checkout($redirect_url) {
    if (isset($_REQUEST['is_buy_now']) && $_REQUEST['is_buy_now']) {
        $redirect_url = wc_get_checkout_url(); //or wc_get_cart_url()
    }
    return $redirect_url;
}
// Chen bo loc san pham
add_action('woocommerce_before_main_content', 'filter_custom');
function filter_custom(){
	if( !is_product() ) { ?>
		<div class="title_page"><?php woocommerce_page_title(); ?></div>
		<?php if(!wp_is_mobile()){;?>
		<div class="sort_custom">
			<div class="titlesort">Ưu tiên xem: </div>
			<form id="pricedesc">
				<div class="range-check">
					<input class="pt-checkbox" type="checkbox" value="price-desc" id="price-desc" name="orderby" onChange="this.form.submit()" />
					<label for="price-desc">Giá giảm dần</label>
				</div>
			</form>
			<form id="pricesmall">
				<div class="range-check">
					<input class="pt-checkbox" type="checkbox" value="price" id="price" name="orderby" onChange="this.form.submit()" />
					<label for="price">Giá tăng dần</label>
				</div>
			</form>
			<form id="datecheck">
				<div class="range-check">
				   <input class="pt-checkbox" type="checkbox" value="date" id="date" name="orderby" onChange="this.form.submit()" />
				   <label for="date">Mới nhất</label>
				</div>
			</form>
			<form id="oldproduct">
				<div class="range-check">
					<input class="pt-checkbox" type="checkbox" value="old-product" id="old-product" name="orderby" onChange="this.form.submit()" />
					<label for="old-product">Cũ nhất</label>
				</div>
			</form>
		</div>
	<?php }else{ ?>
		<div class="clear"></div>
	<?php woocommerce_catalog_ordering();
	}}
}
add_shortcode('filter_sidebar_custom', 'filter_sidebar');
function filter_sidebar(){ ?>
	<div class="catalog-filter">
		<div class="catalog-filter-title">
			<h3>Bộ Lọc</h3>
		</div>
		<div class="catalog-filter-widget">
			<div class="catalog-filter-widget-title">Hãng sản xuất</div>
			<div class="catalog-filter-widget-content">
				<?php 
				$args=array('hide_empty'=>0,'taxonomy'=>'product_cat','orderby'=>'id','parent'=>0);
				$categories=get_categories($args);
				foreach($categories as $category){
					$category_link = get_category_link($category->cat_ID); ?>
					<form id="form-<?php echo $category->slug ?>" action="/demo/danh-muc-san-pham/<?php echo $category->slug ?>">
						<div class="flex-center-between">
							<label for="<?php echo $category->slug ?>"><span><?php echo $category->name ?></span></label>
							<input type="checkbox" id="<?php echo $category->slug ?>" value="<?php echo $category->slug ?>" name="" onChange="this.form.submit()">
						</div>
					</form>
				<?php } ?>
			</div>
		</div>
	</div>
<?php };
add_action('wp_footer','add_js');
function add_js(){?>
    <script type="text/javascript">
		jQuery(document).ready(function() {
    		if (window.location.href.indexOf("price-desc") > -1)
          		jQuery('#pricedesc input[type="checkbox"]').prop('checked', true);
    		else if (window.location.href.indexOf("price") > -1)
          		jQuery('#pricesmall input[type="checkbox"]').prop('checked', true);
    		else if (window.location.href.indexOf("date") > -1)
          		jQuery('#datecheck input[type="checkbox"]').prop('checked', true);
    		else if (window.location.href.indexOf("old-product") > -1)
				jQuery('#oldproduct input[type="checkbox"]').prop('checked', true);
		});
			
		jQuery(document).ready(function() {
			<?php
			$args=array('hide_empty'=>0,'taxonomy'=>'product_cat','orderby'=>'id','parent'=>0);
			$categories=get_categories($args);
			foreach($categories as $category){ ?>
				if (window.location.href.indexOf("<?php echo $category->slug ?>") > -1)
					jQuery('#form-<?php echo $category->slug ?> input[type="checkbox"]').prop('checked', true);
				<?php }
			?>
		});
		
		jQuery("a.deselect").each(function(){
			this.search = "";
		});
		
// 		jQuery(document).ready(function() {
// 			var data_id = $(this).attr('href');
// 			$('html, body').animate({
//     			scrollTop: $(data_id).offset().top
//   			}, '500');
// 		})
	</script>
<?php };

add_shortcode('menu_account_page', 'menu_account_page');
function menu_account_page() { ?>
	<nav class="menu-account">
		<ul>
			<a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>">
				<li class="menu-account-item">Thông tin cá nhân</li>
			</a>
		</ul>
		<ul>
			<a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>don-hang">
				<li class="menu-account-item">Đơn hàng</li>
			</a>
		</ul>
		<ul>
			<a href="<?php echo get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ); ?>customer-logout/?_wpnonce=2a6709fe50">
				<li class="menu-account-item">Đăng xuất</li>
			</a>
		</ul>
	</nav>
<?php }
add_shortcode('thong_tin_ca_nhan', 'show_info_account');
function show_info_account() {
	if( !is_user_logged_in() ) { ?>
		<h2 style="text-align:center">Thông Tin Cá Nhân</h2>
		<p>Bạn chưa đăng nhập. Bấm <a href="">vào đây</a> để đăng nhập.</p>
	<?php } else { 
		$user = wp_get_current_user();
	?>	
		<style>
			.user_info{margin-bottom:10px}
			.user_info_title{display:inline-block;width:50px;margin-right:20px;font-size:1.1em;font-weight:700}
			.user_info_avatar img{border-radius:50%}
		</style>
		<h2 style="text-align:center">Thông Tin Cá Nhân</h2>
		<div class="user_info user_info_avatar"><?php echo get_avatar($user->ID, 96) ?></div>
		<div class="user_info user_info_name">
			<span class="user_info_title">Tên:</span>
			<span><?php echo $user->display_name; ?></span>
		</div>
		<div class="user_info user_info_email">
			<span class="user_info_title">Email:</span>
			<span><?php echo $user->user_email; ?></span>
		</div>
		<div>
			<a href="sua-thong-tin/">
				<button class="btn">Sửa thông tin</button>
			</a>
		</div>
	<?php }
}
add_shortcode('sua_thong_tin', 'edit_info_account');
function edit_info_account() {
	if( !is_user_logged_in() ) { ?>
		<h2 style="text-align:center">Thông Tin Cá Nhân</h2>
		<p>Bạn chưa đăng nhập. Bấm <a href="">vào đây</a> để đăng nhập.</p>
	<?php } else { 
		do_action( 'woocommerce_before_edit_account_form' );
		$user = wp_get_current_user();
	?>
		<h2 style="text-align:center">Sửa Thông Tin Cá Nhân</h2>
		<form class="edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>
			<div class="flex-center-between">
				<div class="first-name">
					<label for="first_name">Họ <span class="required">*</span></label>
					<input type="text" class="input-text" name="first_name" id="first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
				</div>
				<div class="last-name">
					<label for="last_name">Tên <span class="required">*</span></label>
					<input type="text" class="input-text" name="last_name" id="last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
				</div>
				<div class="display-name">
					<label for="display_name">Tên hiển thị <span class="required">*</span></label>
					<input type="text" class="input-text" name="display_name" id="display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
				</div>
			</div>
			<div class="account-email" style="max-width:335px">
				<label for="account_email">Địa chỉ Email <span class="required">*</span></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</div>
			<fieldset>
				<legend>Thay đổi mật khẩu</legend>
				<div class="flex-center-between">
					<div class="password-current">
						<label for="password_current">Mật khẩu hiện tại</label>
						<input type="password" class="input-text" name="password_current" id="password_current" autocomplete="off" />
					</div>
					<div class="new-password">
						<label for="new_password">Mật khẩu mới</label>
						<input type="password" class="input-text" name="new_password" id="new_password" autocomplete="off" />
					</div>
					<div class="renew-password">
						<label for="renew_password">Nhập lại mật khẩu</label>
						<input type="password" class="input-text" name="renew_password" id="renew_password" autocomplete="off" />
					</div>
				</div>
			</fieldset>
			<?php do_action( 'woocommerce_edit_account_form' ); ?>
			<div>
				<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
				<button type="submit" class="btn" name="save_account_details" value="Lưu thay đổi">Lưu thay đổi</button>
				<input type="hidden" name="action" value="save_account_details" />
			</div>
			<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
		</form>
	<?php }
};
add_shortcode('don_hang', 'orders');
function orders() { ?>
	<h2 style="text-align:center">Đơn hàng đã đặt</h2>
	<table class="">
		<thead>
			<tr>
				<?php foreach(wc_get_account_orders_columns() as $column_id => $column_name ) { ?>
				<th class="<?php echo esc_attr( $column_id ); ?>">
					<span class="nobr"><?php echo esc_html( $column_name ); ?></span>
				</th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php
				$customer_orders = get_posts( apply_filters( 'woocommerce_my_account_my_orders_query', array(
					'numberposts' => -1,
					'meta_key'    => '_customer_user',
					'meta_value'  => get_current_user_id(),
					'post_type'   => wc_get_order_types( 'view-orders' ),
					'post_status' => array_keys( wc_get_order_statuses() )
				) ) );
				foreach($customer_orders as $customer_order ) {
					$order = wc_get_order( $customer_order );
					$item_count = $order->get_item_count() - $order->get_item_count_refunded();
			?>
				<tr class="<?php echo esc_attr( $order->get_status() ); ?>">
					<?php foreach(wc_get_account_orders_columns() as $column_id => $column_name ) { ?>
					<td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
						<?php if(has_action('woocommerce_my_account_my_orders_column_' . $column_id )) {
							do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order );
						} else if('order-number' === $column_id) { ?>
							<a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>xem-don-hang?id=<?php echo $order->get_order_number(); ?>">
								<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
							</a>
						<?php } else if('order-date' === $column_id) { ?>
							<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
								<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
							</time>
						<?php } else if('order-status' === $column_id) {
							echo esc_html( wc_get_order_status_name( $order->get_status() ) );
						} else if('order-total' === $column_id) {
							echo wp_kses_post( sprintf( _n( '%1$s cho %2$s sản phẩm', '%1$s cho %2$s sản phẩm', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
						} else if('order-actions' === $column_id) {
							$actions = wc_get_account_orders_actions( $order );
							if(! empty( $actions )) {
								foreach ( $actions as $key => $action ) { 
									echo '<a href="'.get_permalink(get_option('woocommerce_myaccount_page_id')).'xem-don-hang?id='.$order->get_order_number().'" class="button">'.esc_html( $action['name'] ).'</a>';
								}
							}
						} ?>
					</td>
					<?php } ?>
			</tr>
			<?php } ?>
		</tbody>
	</table>
<?php }
add_shortcode('chi_tiet_don_hang', 'view_order');
function view_order() {
	if(! isset($_REQUEST['id'])) { ?>
		<h2>Đường dẫn không hợp lệ</h2>
	<?php } else {
		$id = $_REQUEST['id'];
		$order = wc_get_order( $id );
		$order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) ); ?>
		<section class="woocommerce-order-details">
			<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>
			<h2 style="text-align:center"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>
			<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
				<thead>
					<tr>
						<th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
						<th class="product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php do_action( 'woocommerce_order_details_before_order_table_items', $order );
					foreach ( $order_items as $item_id => $item ) {
						$product = $item->get_product();
						wc_get_template( 'order/order-details-item.php',
							array(
								'order'              => $order,
								'item_id'            => $item_id,
								'item'               => $item,
								'show_purchase_note' => '',
								'purchase_note'      => $product ? $product->get_purchase_note() : '',
								'product'            => $product,
							)
						);
					}
					do_action( 'woocommerce_order_details_after_order_table_items', $order ); ?>
				</tbody>
				<tfoot>
				<?php foreach ( $order->get_order_item_totals() as $key => $total ) { ?>
					<tr>
						<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
						<td><?php echo ('payment_method' === $key) ? esc_html($total['value']) : wp_kses_post($total['value']); ?></td>
					</tr>
				<?php }
				if ( $order->get_customer_note() ) { ?>
					<tr>
						<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
						<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
					</tr>
				<?php } ?>
				</tfoot>
			</table>
			<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>
		</section>
	<?php }
}