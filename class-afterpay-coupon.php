<?php
/**
 * AfterPay Coupon
 *
 * Add customer individual score option to the default Woocommerce Coupon system
 *
 * @class      Afterpay_Coupon
 * @package    Arvato_AfterPay
 * @category   Class
 * @author     arvato Finance B.V.
 * @copyright  since 2011 arvato Finance B.V.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly..
}

/**
 * AfterPay Coupon
 *
 * Add customer individual score option to the default Woocommerce Coupon system
 *
 * @class         Afterpay_Coupon
 * @author        AfterPay
 */
#[AllowDynamicProperties]
class Afterpay_Coupon {

	/**
	 * Setup Afterpay coupon once woocommerce has been loaded
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'woocommerce_init', array( $this, 'on_init' ) );
	}

	/**
	 * Setup Auto Coupon hooks
	 *
	 * @return void
	 */
	public function on_init() {

		// Apply filter to Coupon codes.
		if ( is_admin() ) {
			add_filter( 'woocommerce_coupon_data_tabs', array( $this, 'coupon_data_tabs' ) );
			add_action( 'woocommerce_coupon_data_panels', array( $this, 'coupon_data_panel' ) );
			add_action( 'woocommerce_coupon_options_save', array( $this, 'coupon_data_save' ) );
		}
	}

	/**
	 * Add AfterPay Customer Individual Score Tab
	 *
	 * @param array $tabs Already existing Woocommerce tab list.
	 * @return array $tabs
	 */
	public function coupon_data_tabs( $tabs = array() ) {
		// add auto-coupon tab to list.
		$tabs['afterpay-cis'] = array(
			'label'  => __( 'AfterPay Customer Individual Score', 'afterpay-payment-gateway-for-woocommerce' ),
			'target' => 'afterpay_cis',
			'class'  => 'afterpay_cis',
		);

		return $tabs;
	}

	/**
	 * Add AfterPay Customer Individual Score Input Field
	 *
	 * @return void
	 */
	public function coupon_data_panel() {
		?>
		<div id="afterpay_cis" class="panel woocommerce_options_panel">
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => 'afterpay_cis_code',
					'label'       => __( 'AfterPay CIS code ', 'afterpay-payment-gateway-for-woocommerce' ),
					'description' => __( 'Fill this field with corresponding CIS code', 'afterpay-payment-gateway-for-woocommerce' ),
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Save AfterPay Customer Individual Score field as metadata of the coupon
	 *
	 * @param int $post_id WordPress Post ID.
	 * @return void
	 */
	public function coupon_data_save( $post_id = null ) {

		$afterpay_cis_code = isset( $_POST['afterpay_cis_code'] ) ? wc_clean( sanitize_text_field( wp_unslash( $_POST['afterpay_cis_code'] ) ) ) : '';
		update_post_meta( $post_id, 'afterpay_cis_code', $afterpay_cis_code );
	}
}

// Initiate the class.
new Afterpay_Coupon();
