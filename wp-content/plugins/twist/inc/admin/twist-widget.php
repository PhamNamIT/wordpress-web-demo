<?php
namespace ElementorTwist\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

/**
 * Elementor Hello World
 *
 * Elementor widget for hello world.
 *
 * @since 1.0.0
 */
class Twist_Widget extends Widget_Base {

    /**
     * Retrieve the widget name.
     *
     * @access public
     * @since 1.0.0
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'twist-product-gallery';
    }

    /**
     * Retrieve the widget title.
     *
     * @access public
     * @since 1.0.0
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'Product Gallery Slider', 'wpgs-td' );
    }

    /**
     * Retrieve the widget icon.
     *
     * @access public
     * @since 1.0.0
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-product-images';
    }

    /**
     * Retrieve the list of categories the widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * Note that currently Elementor supports only one category.
     * When multiple categories passed, Elementor uses the first one.
     *
     * @access public
     * @since 1.0.0
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['product'];
    }

    /**
     * Retrieve the list of scripts the widget depended on.
     *
     * Used to set scripts dependencies required to run the widget.
     *
     * @access public
     * @since 1.0.0
     *
     * @return array Widget scripts dependencies.
     */
    public function get_script_depends() {
        return []; // [ 'elementor-hello-world' ];
    }

    /**
     * Register the widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @access protected
     * @since 1.0.0
     */
    protected function _register_controls() {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Style', 'elementor-hello-world' ),
            ]
        );

        $this->add_control(
            'important_note',
            [
                'label'           => false,
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => __( 'Use "Product Gallery Slider" Setting page for Edit The Gallery Style', 'wpgs-td' ),
                'content_classes' => 'elementor-nerd-box',
            ]
        );

        $this->add_control(
            'sale_flash',
            [
                'label'        => __( 'Sale Flash', 'wpgs-td' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Show', 'wpgs-td' ),
                'label_off'    => __( 'Hide', 'wpgs-td' ),
                'render_type'  => 'template',
                'return_value' => 'yes',
                'default'      => 'yes',
                'prefix_class' => '',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @access protected
     * @since 1.0.0
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        global $product;

        $twist_product = wc_get_product();

        if ( empty( $twist_product ) ) {
            return;
        }

        if ( 'yes' === $settings['sale_flash'] ) {
            wc_get_template( 'loop/sale-flash.php' );
        }

        wc_get_template( 'single-product/product-image.php' );
        echo '&nbsp;';

        // On render widget from Editor - trigger the init manually.
        if ( wp_doing_ajax() ) {
            ?>
			<script>

					jQuery('.wpgs-wrapper').css('opacity','1');

					jQuery('.wpgs-image').slick({
						asNavFor: '.wpgs-thumb',
						lazyLoad:'progressive',
					});
					jQuery('.wpgs-thumb').slick({
						slidesToShow: 4,
						asNavFor: '.wpgs-image',
					});

			</script>
			<?php
}
    }
}
