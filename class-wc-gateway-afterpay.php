<?php
/**
 * Riverty payment gateway for Woocommerce
 *
 * @package    WC_Payment_Gateway
 * @author     AfterPay
 * @copyright  since 2011 arvato Finance B.V.
 *
 * @wordpress-plugin
 * Plugin Name: Riverty payment gateway for Woocommerce
 * Plugin URI: https://www.riverty.com
 * Description: Extends WooCommerce. Provides a <a href="http://www.riverty.com" target="_blank">Riverty</a> payment gateway for WooCommerce. Riverty is the new AfterPay.
 * Version: 7.1.6
 * Author: Riverty
 * Author URI: http://www.riverty.com
 * Text Domain: afterpay-payment-gateway-for-woocommerce
 * Domain Path: /languages
 * WC tested up to: 9.2.3
 * WC requires at least: 4.5.0
 */

/**
 * Copyright (c) copyright  since 2011 arvato Finance B.V.
 *
 * AfterPay reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of AfterPay.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly..
}

/**
 * Check if WooCommerce is active
 */
$woo_active = false;
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	$woo_active = true;
}
if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}
if ( is_plugin_active_for_network( 'woocommerce/woocommerce.php' ) && true !== $woo_active ) {
	$woo_active = true;
}
if ( false === $woo_active ) {
	deactivate_plugins( plugin_basename( __FILE__ ) );
	wp_die( 'The WooCommerce plugin should be installed and activated before using the Riverty Woocommerce Plugin' );
}

add_action( 'before_woocommerce_init', function(){
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

/**
 * Load AfterPay extension on the WC Coupons to add specific AfterPay Coupon
 * field needed for Customer Individual Score
 */
require_once 'class-afterpay-coupon.php';

/**
 * Initiate AfterPay Gateway, load necessary files and classes.
 *
 * @access public
 * @return void
 **/
function init_afterpay_gateway() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'afterpay-payment-gateway-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Define AfterPay root Dir.
	define( 'AFTERPAY_DIR', dirname( __FILE__ ) . '/' );

	// Define AfterPay lib Dir.
	define( 'AFTERPAY_LIB', dirname( __FILE__ ) . '/vendor/payintegrator/afterpay/lib/' );

	/**
	 * AfterPay Payment Gateway
	 *
	 * @class          WC_Gateway_Afterpay
	 * @extends        WC_Payment_Gateway
	 * @package        WooCommerce/Classes/Payment
	 * @author         Willem Fokkens
	 */
	#[AllowDynamicProperties]
	class WC_Gateway_Afterpay extends WC_Payment_Gateway {

		/**
		 * Constructor for the gateway.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			global $woocommerce;
		}
	}

	// Load the AfterPay Base class and then the country specific payment methods.
	require_once 'class-wc-gateway-afterpay-base.php';

	// Netherlands.
	require_once 'class-wc-gateway-afterpay-nl-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-nl-directdebit.php';
	require_once 'class-wc-gateway-afterpay-nl-business.php';
	require_once 'class-wc-gateway-afterpay-nl-openinvoice-extra.php';
	require_once 'class-wc-gateway-afterpay-nl-business-extra.php';

	// Belgium.
	require_once 'class-wc-gateway-afterpay-be-openinvoice.php';

	// Load the AfterPay Base class for the countries using REST interface.
	require_once 'class-wc-gateway-afterpay-base-rest.php';

	// Netherlands.
	require_once 'class-wc-gateway-afterpay-nl-openinvoice-rest.php';
	require_once 'class-wc-gateway-afterpay-nl-directdebit-rest.php';
	require_once 'class-wc-gateway-afterpay-nl-business-rest.php';
	require_once 'class-wc-gateway-afterpay-nl-payinx.php';

	// Belgium.
	require_once 'class-wc-gateway-afterpay-be-openinvoice-rest.php';

	// Germany.
	require_once 'class-wc-gateway-afterpay-de-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-de-installments.php';
	require_once 'class-wc-gateway-afterpay-de-directdebit.php';

	// Austria.
	require_once 'class-wc-gateway-afterpay-at-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-at-installments.php';
	require_once 'class-wc-gateway-afterpay-at-directdebit.php';

	// Switzerland.
	require_once 'class-wc-gateway-afterpay-ch-openinvoice.php';

	// Denmark.
	require_once 'class-wc-gateway-afterpay-dk-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-dk-installments.php';
	require_once 'class-wc-gateway-afterpay-dk-flex.php';
	require_once 'class-wc-gateway-afterpay-dk-campaign.php';
	require_once 'class-wc-gateway-afterpay-dk-b2b-openinvoice.php';

	// Sweden.
	require_once 'class-wc-gateway-afterpay-se-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-se-installments.php';
	require_once 'class-wc-gateway-afterpay-se-flex.php';
	require_once 'class-wc-gateway-afterpay-se-campaign.php';
	require_once 'class-wc-gateway-afterpay-se-b2b-openinvoice.php';

	// Finland.
	require_once 'class-wc-gateway-afterpay-fi-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-fi-installments.php';
	require_once 'class-wc-gateway-afterpay-fi-flex.php';
	require_once 'class-wc-gateway-afterpay-fi-campaign.php';
	require_once 'class-wc-gateway-afterpay-fi-b2b-openinvoice.php';

	// Norway.
	require_once 'class-wc-gateway-afterpay-no-openinvoice.php';
	require_once 'class-wc-gateway-afterpay-no-installments.php';
	require_once 'class-wc-gateway-afterpay-no-flex.php';
	require_once 'class-wc-gateway-afterpay-no-campaign.php';
	require_once 'class-wc-gateway-afterpay-no-b2b-openinvoice.php';

}

// If plugins are loaded then initiate AfterPay Gateway.
add_action( 'plugins_loaded', 'init_afterpay_gateway', 0 );

/**
 * Add the gateway to WooCommerce
 *
 * @access public
 * @param array $methods Woocommerce Payment Gateways.
 * @return array
 **/
function add_afterpay_gateway( $methods ) {

	$country_obj      = new WC_Countries();
	$countries        = $country_obj->get_allowed_countries();
	$afterpay_methods = array();

	if ( array_key_exists( 'NL', $countries ) ) {
		// The Netherlands.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Openinvoice_Rest';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Directdebit_Rest';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Business_Rest';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Payinx';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Openinvoice_Extra';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Directdebit';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Business';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Nl_Business_Extra';
	}

	if ( array_key_exists( 'BE', $countries ) ) {
		// Belgium.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Be_Openinvoice_Rest';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Be_Openinvoice';
	}

	if ( array_key_exists( 'DE', $countries ) ) {
		// Germany.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_De_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_De_Installments';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_De_Directdebit';
	}

	if ( array_key_exists( 'AT', $countries ) ) {
		// Austria.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_At_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_At_Installments';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_At_Directdebit';
	}

	if ( array_key_exists( 'CH', $countries ) ) {
		// Switzerland.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Ch_Openinvoice';
	}

	if ( array_key_exists( 'DK', $countries ) ) {
		// Denmark.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Dk_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Dk_Installments';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Dk_Flex';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Dk_Campaign';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Dk_B2B_Openinvoice';
	}

	if ( array_key_exists( 'SE', $countries ) ) {
		// Sweden.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Se_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Se_Installments';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Se_Flex';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Se_Campaign';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Se_B2B_Openinvoice';
	}

	if ( array_key_exists( 'FI', $countries ) ) {
		// Finland.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Fi_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Fi_Installments';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Fi_Flex';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Fi_Campaign';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_Fi_B2B_Openinvoice';
	}

	if ( array_key_exists( 'NO', $countries ) ) {
		// Norway.
		$afterpay_methods[] = 'WC_Gateway_Afterpay_No_Openinvoice';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_No_Installments';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_No_Flex';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_No_Campaign';
		$afterpay_methods[] = 'WC_Gateway_Afterpay_No_B2B_Openinvoice';
	}

	$methods = array_merge( $methods, $afterpay_methods );
	return $methods;
}

/**
 * AfterPay return page and order check
 *
 * @access public
 **/
function afterpay_return() {
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {

		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		if ( strpos( $request_uri, '/afterpay/return' ) !== false ) {
			$order_id = null;
			if ( isset( $_GET['order_id'] ) ) {
				$order_id = sanitize_text_field( wp_unslash( $_GET['order_id'] ) );
			} else {
				exit();
			}

			$order = wc_get_order( $order_id );

			// Check if the order is valid.
			if ( ! is_object( $order ) ) {
				exit();
			}
			// Check if the order has the status 'pending'.
			if ( $order->get_status() !== 'pending' ) {
				exit();
			}

			// Check if payment method is AfterPay.
			if ( strpos( $order->get_payment_method(), 'afterpay' ) === false ) {
				exit();
			}

			// Get an instance of the WC_Payment_Gateways object.
			$payment_gateways = WC_Payment_Gateways::instance();

			// Get the desired WC_Payment_Gateway object.
			$payment_gateway = $payment_gateways->payment_gateways()[ $order->get_payment_method() ];
			$status          = $payment_gateway->afterpay_check_status( $order );

			if ( 'Accepted' === $status ) {
				$order->add_order_note( __( 'Order is accepted after Riverty SCA', 'afterpay-payment-gateway-for-woocommerce' ) );

				if (
					isset( $payment_gateway->settings['captures'] )
					&& 'yes' === $payment_gateway->settings['captures']
					&& isset( $payment_gateway->settings['captures_way'] )
					&& 'auto_after_authorization' === $payment_gateway->settings['captures_way']
				) {
					// Capture payment.
					$payment_gateway->capture_afterpay_payment( null, $order );
				}

				if (
					isset( $payment_gateway->settings['captures'] )
					&& 'yes' === $payment_gateway->settings['captures']
					&& isset( $payment_gateway->settings['captures_way'] )
					&& 'auto_after_authorization' !== $payment_gateway->settings['captures_way']
				) {
					// Add note that the order is not captured yet.
					$order->add_order_note( __( 'Riverty capture needed, since the Capture mode was set to(Based on Woocommerce Status) when the order was placed.', 'afterpay-payment-gateway-for-woocommerce' ) );
				}

				$order->payment_complete();
				header( 'Location: ' . $order->get_checkout_order_received_url() );
				exit();
			} else {
				$order->add_order_note( __( 'Strong authentication failed or was aborted. Please try again or select another payment method.', 'afterpay-payment-gateway-for-woocommerce' ) );
				wc_add_notice( __( 'Strong authentication failed or was aborted. Please try again or select another payment method.', 'afterpay-payment-gateway-for-woocommerce' ), 'error' );
				WC()->session->set( 'order_awaiting_payment', false );
				$order->update_status( 'cancelled', '' );
				header( 'Location: ' . wc_get_checkout_url() );
				exit();
			}
		}
	}
}

/**
 * AfterPay cron and order check
 *
 * @access public
 **/
function afterpay_croncheck() {

	// Get all enabled AfterPay gateways.
	$gateways         = WC_Payment_Gateways::instance();
	$enabled_gateways = [];
	$ignored_gateways = [
		'afterpay_openinvoice',
		'afterpay_openinvoice_extra',
		'afterpay_directdebit',
		'afterpay_business',
		'afterpay_business_extra',
		'afterpay_belgium',
	];

	if ( $gateways->payment_gateways ) {
		foreach ( $gateways->payment_gateways as $gateway ) {
			if ( 'yes' === $gateway->enabled && strpos( $gateway->id, 'afterpay' ) !== false ) {
				$enabled_gateways[] = $gateway->id;
			}
		}
	}

	$enabled_gateways = array_diff( $enabled_gateways, $ignored_gateways );

	foreach ( $enabled_gateways as $enabled_gateway ) {
		$query = new WC_Order_Query();

		// Check only orders that are 'pending payment'.
		$query->set( 'status', 'pending' );

		// Check only orders from AfterPay.
		$query->set( 'payment_method', $enabled_gateway );

		// Check only orders are older than 30 minutes.
		$query->set( 'date_created', '<' . ( time() - 1800 ) );

		$orders = $query->get_orders();

		if ( count( $orders ) > 0 ) {
			// Get payment gateway object.
			$payment_gateway = $gateways->payment_gateways()[ $enabled_gateway ];
			foreach ( $orders as $order ) {
				$status = $payment_gateway->afterpay_check_status( $order );
				if ( 'Accepted' === $status ) {
					if (
						isset( $payment_gateway->settings['captures'] )
						&& 'yes' === $payment_gateway->settings['captures']
						&& isset( $payment_gateway->settings['captures_way'] )
						&& 'auto_after_authorization' === $payment_gateway->settings['captures_way']
					) {
						// Capture payment.
						$payment_gateway->capture_afterpay_payment( null, $order );
					}
					$order->add_order_note( __( 'Order is accepted after Riverty SCA', 'afterpay-payment-gateway-for-woocommerce' ) );
					$order->add_order_note( __( 'Riverty payment completed.', 'afterpay-payment-gateway-for-woocommerce' ) );

					if (
						isset( $payment_gateway->settings['captures'] )
						&& 'yes' === $payment_gateway->settings['captures']
						&& isset( $payment_gateway->settings['captures_way'] )
						&& 'auto_after_authorization' !== $payment_gateway->settings['captures_way']
					) {
						// Add note that the order is not captured yet.
						$order->add_order_note( __( 'Riverty capture needed, since the Capture mode was set to(Based on Woocommerce Status) when the order was placed.', 'afterpay-payment-gateway-for-woocommerce' ) );
					}

					$order->payment_complete();
				} else {
					$order->add_order_note( __( 'Strong authentication failed or was aborted.', 'afterpay-payment-gateway-for-woocommerce' ) );
					$order->update_status( 'cancelled', '' );
				}
			}
		}
	}
}


// Add AfterPay gateway to Woocommerce filters.
add_filter( 'woocommerce_payment_gateways', 'add_afterpay_gateway' );

/**
 * Initiate AfterPay Gateway css styles.
 *
 * @access public
 * @return void
 **/
function init_afterpay_styles() {
	wp_register_style(
		'afterpay',
		plugins_url( basename( dirname( __FILE__ ) ) . '/css/styles.css' )
	);
	wp_enqueue_style( 'afterpay' );
}

add_action( 'wp_enqueue_scripts', 'init_afterpay_styles' );
add_action( 'admin_enqueue_scripts', 'init_afterpay_styles' );

/**
 * Initiate AfterPay Gateway javascripts.
 *
 * @access public
 * @return void
 **/
function init_afterpay_scripts() {
	if( get_option( 'woocommerce_afterpay_elements_settings_enable' ) == 'yes' ) {
		wp_register_script(
			'afterpay_elements_esm',
			'https://cdn.myafterpay.com/elements/v1/build/afterpay-elements.esm.js'
		);
		wp_register_script(
			'afterpay_elements',
			'https://cdn.myafterpay.com/elements/v1/build/afterpay-elements.js'
		);
		wp_enqueue_script( 'afterpay_elements_esm' );
		wp_enqueue_script( 'afterpay_elements' );

		add_filter( 'script_loader_tag', 'add_attributes_to_script', 10, 3 );
		function add_attributes_to_script( $tag, $handle, $src ) {
			if ( 'afterpay_elements_esm' === $handle ) {
				$tag = '<script type="module" src="' . esc_url( $src ) . '" async="true"></script>';
			}
			if ( 'afterpay_elements' === $handle ) {
				$tag = '<script nomodule src="' . esc_url( $src ) . '" async="true"></script>';
			}
			return $tag;
		}
	}

	wp_register_script(
		'jquery_mask',
		plugins_url( basename( dirname( __FILE__ ) ) . '/js/jquery.mask.min.js' ),
		array( 'jquery' )
	);
	wp_enqueue_script( 'jquery_mask' );
}

/**
 * Initiate AfterPay Gateway admin panel javascripts.
 *
 * @access public
 * @return void
 **/
function init_afterpay_admin_scripts() {
	wp_register_script(
		'jquery_mask',
		plugins_url( basename( dirname( __FILE__ ) ) . '/js/jquery.mask.min.js' ),
		array( 'jquery' )
	);
	wp_register_script(
		'admin_script',
		plugins_url( basename( dirname( __FILE__ ) ) . '/js/admin_script.js' ),
	);
	wp_register_script(
		'afterpay_elements_esm',
		'https://cdn.myafterpay.com/elements/v1/build/afterpay-elements.esm.js'
	);
	wp_register_script(
		'afterpay_elements',
		'https://cdn.myafterpay.com/elements/v1/build/afterpay-elements.js'
	);

	wp_enqueue_script( 'jquery_mask' );
	wp_enqueue_script( 'admin_script' );
	wp_enqueue_script( 'afterpay_elements_esm' );
	wp_enqueue_script( 'afterpay_elements' );

	add_filter( 'script_loader_tag', 'add_attributes_to_admin_script', 10, 3 );
	function add_attributes_to_admin_script( $tag, $handle, $src ) {
		if ( 'afterpay_elements_esm' === $handle ) {
			$tag = '<script type="module" src="' . esc_url( $src ) . '" async="true"></script>';
		}
		if ( 'afterpay_elements' === $handle ) {
			$tag = '<script nomodule src="' . esc_url( $src ) . '" async="true"></script>';
		}
		return $tag;
	}
}

/**
 * Add Afterpay Custom Success Message to Thank You Page
 *
 * @access public
 * @return void
 **/
function afterpay_custom_thankyou( $thankyou_text, $order ){
		$store_name = get_bloginfo( 'name' );
	?>
		<script>
			const elements = document.getElementsByClassName("woocommerce-thankyou-order-received");
			if( elements.length > 0 ){
				elements[0].parentNode.removeChild(elements[0]);
			}
		</script>

		<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo $thankyou_text;?></p>

		<style>
			.afterpay-success-logo {
				height: 105px;
				width: 185px;
				float: right;
				margin-top: 20px;
				margin-right: 45px;
			}

			.afterpay-quote {
				font-weight: bold;
			}

			.afterpay-thanks-message {
				margin-top: 20px;
			}

			.checkout-custom-success {
				border: 4px grey solid;
				padding: 20px;
				margin-bottom: 10px;
			}

			@media (max-width:600px) {
				.afterpay-text,.afterpay-success-logo {
					float:none;
					width:100%;
					margin-top: 15px;
				}
			}
		</style>

		<div class="checkout-custom-success">
			<div>
				<img class="afterpay-success-logo" src="https://cdn.riverty.design/logo/riverty-checkout-logo.svg" alt="riverty_logo not found"/>
			</div>
			<div class="afterpay-text">
				<span class="afterpay-quote"><?= __( 'Shop first, pay later with Riverty', 'afterpay-payment-gateway-for-woocommerce' ) ?></span>
				<p class="afterpay-thanks-message"><?= __( 'Thank you for your purchase and for choosing Riverty as your payment method. An email with the order confirmation, details and tracking info is on the way, but feel free to discover what Riverty can do for you.', 'afterpay-payment-gateway-for-woocommerce' ) ?></p>
				<p><?= sprintf(__( 'Download our app on your smartphone or visit %s in your browser to find out the easiest way to keep track of outstanding payments, pause payments for returns or keep track and many more options.', 'afterpay-payment-gateway-for-woocommerce' ), '<a href="https://my.riverty.com/" target="_blank"><strong>MyRiverty</strong></a>' ) ?></p>
			</div>
		</div>
	<?php
}

/**
 * Add afterpay filter for thankyou page
 *
 * @access public
 * @return void
 **/
function add_custom_thankyou_filter( $order_id ){
	$order = new WC_Order( $order_id );
	$payment_method = explode( '_', $order->get_payment_method() )[0];

	if ( $payment_method == 'afterpay' ):
		add_filter( 'woocommerce_thankyou_order_received_text', 'afterpay_custom_thankyou', 10, 2);
	endif;
}
// Disable AfterPay Elements until further notice.
// add_filter( 'woocommerce_settings_tabs_array', 'add_afterpay_elements_tab', 50 );

/**
 * Add afterpay elements tab to woocommerce settings
 *
 * @access public
 * @param array $methods Woocommerce Payment Gateways.
 * @return array
 **/
function add_afterpay_elements_tab( $settings_tab ) {
	$settings_tab['afterpay_elements'] = __( 'AfterPay Elements', 'afterpay-payment-gateway-for-woocommerce' );
	return $settings_tab;
}

/**
 * @access public
 * @return void
 */
function get_afterpay_elements_form_fields() {

	$current_locale = strtolower( substr( get_locale(), 0, 2 ) );
	$language_code = '';
	$afterpay_language_codes = [ 'de','fi','no','sv-SE','da','nl' ];

	if ( in_array( $current_locale, $afterpay_language_codes ) ) {
		$language_code = $current_locale;
	}
	elseif ( $current_locale == 'sv' ) {
		$language_code = 'sv-SE';
	}
	elseif ( $current_locale == 'nn' || $current_locale == 'nb' ) {
		$language_code = 'no';
	}
	else {
		$language_code = 'en';
	}

	$invoice_element_demo = '<p style="margin-bottom: 20px; margin-top: 15px" class="invoice_element_example">Example:</p><span id="invoice_element_id" class="invoice_element_demo" data-value="' . $language_code .'"></span>';

	$offer_element_demo = '<p style="margin-bottom: 20px; margin-top: 15px" class="offer_element_example">Example:</p><span id="offer_element_id" class="offer_element_demo" data-value="' . $language_code .'"></span>';

	$settings = array(
		'section_title' => array(
			'id'   => 'woocommerce_afterpay_elements_settings_title',
			'desc' => 'AfterPay elements are informational components for customers and should be displayed on product page to ensure the customer is informed about AfterPay purchase option.
							More information on this can be found on the <a href="https://developer.afterpay.io/documentation/afterpay-elements/" target="_blank"> AfterPay developer portal </a> and the <a href="https://cdn.myafterpay.com/elements/v1/showcase.html" target="_blank"> AfterPay Elements Showcase </a> .',
			'type' => 'title',
			'name' => 'Afterpay Elements Configuration',
		),
		'afterpay_elements_enable' => array(
			'id'          => 'woocommerce_afterpay_elements_settings_enable',
			'title'       => __( 'Enable AfterPay Elements', 'afterpay-payment-gateway-for-woocommerce' ),
			'type'        => 'select',
			'options'     => array(
				'no'      => 'No',
				'yes'     => 'Yes'
			),
		),
		'afterpay_elements_show_offer_element' => array(
			'id'          => 'woocommerce_afterpay_elements_settings_show_offer_element',
			'title'       => __( 'Show offer element on product page', 'afterpay-payment-gateway-for-woocommerce' ),
			'desc'        => $offer_element_demo,
			'type'        => 'select',
			'options'     => array(
				'no'      => 'No',
				'default' => 'Yes, default theme',
				'light'   => 'Yes, light theme',
				'dark'    => 'Yes, dark theme'
			),
			'class'       => 'afterpay_elements_advanced_settings',
		),
		'afterpay_elements_show_invoice_element' => array(
			'id'          => 'woocommerce_afterpay_elements_settings_show_invoice_element',
			'title'       => __( 'Show invoice element on product page', 'afterpay-payment-gateway-for-woocommerce' ),
			'desc'        => $invoice_element_demo,
			'type'        => 'select',
			'options'     => array(
				'no'      => 'No',
				'default' => 'Yes, default theme',
				'light'   => 'Yes, light theme',
				'dark'    => 'Yes, dark theme'
			),
			'class'       => 'afterpay_elements_advanced_settings',
		),
		'afterpay_elements_merchant_id' => array(
			'id'          => 'woocommerce_afterpay_elements_settings_merchant_id',
			'title'       => __( 'Merchant ID', 'afterpay-payment-gateway-for-woocommerce' ),
			'type'        => 'text',
			'default'     => '0000',
			'class'       => 'afterpay_elements_advanced_settings',
		),
		'section_end' => array(
			'id'   => 'woocommerce_afterpay_elements_section_end',
			'type' => 'sectionend',
		),
	);

	return apply_filters( 'filter_afterpay_elements_settings', $settings );
}

/**
 * @access public
 * @return void
 */
function add_afterpay_elements_settings() {
	woocommerce_admin_fields( get_afterpay_elements_form_fields() );
}

/**
 * @access public
 * @return void
 */
function update_afterpay_elements_settings() {
	woocommerce_update_options( get_afterpay_elements_form_fields() );
}

/**
 * @access public
 * @return string
 */
function show_afterpay_elements() {
	global $product;
	$product_array = json_decode($product, true);
	$merchant_id = get_option( 'woocommerce_afterpay_elements_settings_merchant_id' );
	$current_locale = strtolower( substr( get_locale(), 0, 2 ) );
	$language_code = '';
	$afterpay_language_codes = [ 'de','fi','no','sv-SE','da','nl' ];
	$offer_element_theme = get_option( 'woocommerce_afterpay_elements_settings_show_offer_element' );
	$invoice_element_theme = get_option( 'woocommerce_afterpay_elements_settings_show_invoice_element' );

	if ( in_array( $current_locale, $afterpay_language_codes ) ) {
		$language_code = $current_locale;
	}
	elseif ( $current_locale == 'sv' ) {
		$language_code = 'sv-SE';
	}
	elseif ( $current_locale == 'nn' || $current_locale == 'nb' ) {
		$language_code = 'no';
	}
	else {
		$language_code = 'en';
	}

	if ( $invoice_element_theme != 'no' ) {
		echo '<afterpay-invoice language="' . $language_code . '" theme="' . $invoice_element_theme . '"></afterpay-invoice>';
	}
	else {
		echo '<afterpay-offer merchant="' . $merchant_id . '" language="' . $language_code . '" theme="' . $offer_element_theme . '" amount="' . $product_array['price'] . '"></afterpay-offer>';
	}
}

$afterpay_elements_enabled = get_option( 'woocommerce_afterpay_elements_settings_enable' );
$show_offer_element = get_option( 'woocommerce_afterpay_elements_settings_show_offer_element' );
$show_invoice_element = get_option( 'woocommerce_afterpay_elements_settings_show_invoice_element' );
$merchant_id = get_option( 'woocommerce_afterpay_elements_settings_merchant_id' );

if ( $afterpay_elements_enabled == 'yes' && ( $show_offer_element != 'no' || $show_invoice_element != 'no' ) && $merchant_id != null ) {
	add_action( 'woocommerce_single_product_summary', 'show_afterpay_elements', 11 );
}

/**
 * Register new afterpay endpoints
 *
 * @return array $args.
 **/
function wp_rest_afterpay_endpoints() {
	register_rest_route(
		'afterpay/v1',
		'bankaccount-validate',
	  	array(
		  'methods' => 'POST',
		  'callback' => 'validate_bankaccount',
		  'permission_callback' => '__return_true'
	  	)
  	);
	register_rest_route(
		'riverty/v1',
		'apikey-validate',
	  	array(
		  'methods' => 'POST',
		  'callback' => 'validate_api_key',
		  'permission_callback' => '__return_true'
	  	)
  	);
}

/**
* Validates an IBAN bankaccount
*
* @param  WP_REST_Request $request Full details about the request.
* @return array
*/
function validate_bankaccount( WP_REST_Request $request ) {

  // Load the AfterPay Library.
  require_once __DIR__ . '/vendor/autoload.php';

  $data = json_decode($request->get_body());

  $api_key = $data->api_key;
  $bank_account = $data->bank_account;
  $connection_mode = $data->connection_mode;

  // Create the AfterPay Object.
  $afterpay_bankvalidation = new \Afterpay\Afterpay();
  $afterpay_bankvalidation->setRest();
  $afterpay_bankvalidation->set_ordermanagement( 'validate_bankaccount' );

  // Set up the additional bank information.
  $bankdetails['bankAccount'] = $bank_account;

  // Create the order object for order management (OM).
  $afterpay_bankvalidation->set_order( $bankdetails, 'OM' );
  $authorisation['apiKey'] = $api_key;

  // Get connection mode.
  $afterpay_mode = $connection_mode;

  // Send the request to do the bank validation.
  $afterpay_bankvalidation->do_request( $authorisation, $afterpay_mode );

  // If there was a return and it was false, set the message as notice and return false.
  if ( isset( $afterpay_bankvalidation->order_result->return->isValid ) ) {
	  $is_valid = $afterpay_bankvalidation->order_result->return->isValid;

	  if ( $afterpay_bankvalidation->order_result->return->isValid == false ) {
		  $customer_facing_message = $afterpay_bankvalidation->order_result->return->riskCheckMessages[0]->customerFacingMessage;
		  $message = __( $customer_facing_message, 'afterpay-payment-gateway-for-woocommerce' );
	  }
	  else {
		  $message = __( 'Bank account validated', 'afterpay-payment-gateway-for-woocommerce' );
	  }
  }

  $bank_validation_response = [
	  'is_valid' => $is_valid,
	  'message' => $message
  ];

  return $bank_validation_response;
}

/**
* Validates an api key
*
* @param  WP_REST_Request $request Full details about the request.
* @return array
*/
function validate_api_key( WP_REST_Request $request ) {

	// Load the AfterPay Library.
	require_once __DIR__ . '/vendor/autoload.php';

	$data = json_decode($request->get_body());

	$payment_method_country = $data->payment_method_country;
	$currency = $data->currency;
	$additional_data = json_decode(json_encode($data->additional_data),true);
	$api_key = $data->api_key;
	$connection_mode = $data->connection_mode;

	// Create the AfterPay Object.
	$afterpay_payments = new \Afterpay\Afterpay();
	$afterpay_payments->setRest();
	$afterpay_payments->set_ordermanagement( 'available_payment_methods' );

	$requestData = [
		'conversationLanguage' => 'EN',
		'country' => $payment_method_country,
		'order' => [
			'totalGrossAmount' => '1000',
			'totalNetAmount' => '1000',
			'currency' => $currency
		],
		'additionalData' => $additional_data
	];

	// Create the order object for order management (OM).
	$afterpay_payments->set_order( $requestData, 'OM' );

	$authorisation['apiKey'] = $api_key;
	$afterpay_mode = $connection_mode;

	$afterpay_payments->do_request( $authorisation, $afterpay_mode );

	$response = json_decode( json_encode( $afterpay_payments->order_result->return ), true );

	if ( array_key_exists( 'paymentMethods', $response) ) {
		$is_valid = true;
		$message = __( "API key working", 'afterpay-payment-gateway-for-woocommerce' );
	}
	else {
		$is_valid = false;
		$message = __( "API key not working", 'afterpay-payment-gateway-for-woocommerce' );
	}
	
	$available_payments_response = [
		'is_valid' => $is_valid,
		'message' => $message
	];
  
	return $available_payments_response;
}

/** 
* Returns the payment method current connection mode stored in database 
*  
* @param object $order wooocommerce checkout order details
* @return string
*/
function get_current_connection_mode( $testmode ) {
	$current_connection_mode = '';

	if ( 'yes' === $testmode ) {
		$current_connection_mode = 'test';
	} elseif ( 'sandbox' === $testmode ) {
		$current_connection_mode = 'sandbox';
	} else {
		$current_connection_mode = 'live';
	}

	return $current_connection_mode;
}

/**
* Update payment methods api key configuration in database   
*
* @return void
*/
function update_api_key_database_config() {
	$riverty_payment_methods_ids = 
	[
		'afterpay_at_directdebit',
		'afterpay_at_installments',
		'afterpay_at_openinvoice',
		'afterpay_be_openinvoice_rest',
		'afterpay_ch_openinvoice',
		'afterpay_de_directdebit',
		'afterpay_de_installments',
		'afterpay_de_openinvoice',
		'afterpay_dk_flex',
		'afterpay_dk_installments',
		'afterpay_dk_openinvoice',
		'afterpay_dk_campaign',
		'afterpay_dk_b2b_openinvoice',
		'afterpay_fi_flex',
		'afterpay_fi_installments',
		'afterpay_fi_openinvoice',
		'afterpay_fi_campaign',
		'afterpay_fi_b2b_openinvoice',
		'afterpay_business_rest',
		'afterpay_directdebit_rest',
		'afterpay_nl_openinvoice_rest',
		'afterpay_nl_payinx',
		'afterpay_no_flex',
		'afterpay_no_installments',
		'afterpay_no_openinvoice',
		'afterpay_no_campaign',
		'afterpay_no_b2b_openinvoice',
		'afterpay_se_flex',
		'afterpay_se_installments',
		'afterpay_se_openinvoice',
		'afterpay_se_campaign',
		'afterpay_se_b2b_openinvoice',
	];

	foreach ( $riverty_payment_methods_ids as $method_id ) {
		$payment_method_config_settings = get_option('woocommerce_'.$method_id.'_settings');
		if ( isset( $payment_method_config_settings['api_key'] ) ) {
			if ( isset( $payment_method_config_settings['testmode'] ) && $payment_method_config_settings['api_key'] != '' ) {
				$api_key_field_id = get_current_connection_mode($payment_method_config_settings['testmode']) . '_api_key';
				$payment_method_config_settings[$api_key_field_id] = $payment_method_config_settings['api_key'];
			}
			unset($payment_method_config_settings['api_key']);
			update_option('woocommerce_'.$method_id.'_settings', $payment_method_config_settings);
		}
        if ( isset( $payment_method_config_settings['testmode'] ) && $payment_method_config_settings['testmode'] == 'sandbox' ) {
            $payment_method_config_settings['testmode'] = 'yes';
            update_option('woocommerce_'.$method_id.'_settings', $payment_method_config_settings);
        }
	}
}

/**
* Save profile tracking domain config value for DACH payment methods in database   
*
* @return void
*/
function add_tracking_domain_config_value() {
	$is_new_merchant = true;
	$DACH_countries_payments = 	[
		'afterpay_at_directdebit',
		'afterpay_at_installments',
		'afterpay_at_openinvoice',
		'afterpay_ch_openinvoice',
		'afterpay_de_directdebit',
		'afterpay_de_installments',
		'afterpay_de_openinvoice',
	];
	$riverty_payment_methods_ids = 
	[
		'afterpay_at_directdebit',
		'afterpay_at_installments',
		'afterpay_at_openinvoice',
		'afterpay_be_openinvoice_rest',
		'afterpay_belgium',
		'afterpay_ch_openinvoice',
		'afterpay_de_directdebit',
		'afterpay_de_installments',
		'afterpay_de_openinvoice',
		'afterpay_dk_flex',
		'afterpay_dk_installments',
		'afterpay_dk_openinvoice',
		'afterpay_dk_campaign',
		'afterpay_dk_b2b_openinvoice',
		'afterpay_fi_flex',
		'afterpay_fi_installments',
		'afterpay_fi_openinvoice',
		'afterpay_fi_campaign',
		'afterpay_fi_b2b_openinvoice',
		'afterpay_business_extra',
		'afterpay_business_rest',
		'afterpay_business',
		'afterpay_directdebit_rest',
		'afterpay_directdebit',
		'afterpay_openinvoice_extra',
		'afterpay_nl_openinvoice_rest',
		'afterpay_openinvoice',
		'afterpay_nl_payinx',
		'afterpay_no_flex',
		'afterpay_no_installments',
		'afterpay_no_openinvoice',
		'afterpay_no_campaign',
		'afterpay_no_b2b_openinvoice',
		'afterpay_se_flex',
		'afterpay_se_installments',
		'afterpay_se_openinvoice',
		'afterpay_se_campaign',
		'afterpay_se_b2b_openinvoice',
	];

	foreach ( $riverty_payment_methods_ids as $method_id ) {
		$payment_method_config_settings = get_option('woocommerce_'.$method_id.'_settings');
		if ( $payment_method_config_settings ) {
			$is_new_merchant = false;
			break;
		}
	}

	foreach ( $riverty_payment_methods_ids as $method_id ) {
		if( in_array( $method_id, $DACH_countries_payments ) ) {
			$payment_method_config_settings = get_option('woocommerce_'.$method_id.'_settings');
			if( is_array($payment_method_config_settings) ) { 
				if( !array_key_exists( 'tracking_domain', $payment_method_config_settings ) ) {
					$payment_method_config_settings['tracking_domain'] = $is_new_merchant ? 'whm.asip.cloud' : 'uc8.tv';
				}
			}
			else { 
				$payment_method_config_settings = [];
				$payment_method_config_settings['tracking_domain'] = $is_new_merchant ? 'whm.asip.cloud' : 'uc8.tv';
			}
			update_option('woocommerce_'.$method_id.'_settings', $payment_method_config_settings);
		}
	}
}

add_action( 'plugins_loaded', 'update_api_key_database_config' );

add_action( 'plugins_loaded', 'add_tracking_domain_config_value' );

add_action( 'rest_api_init', 'wp_rest_afterpay_endpoints' );

add_action( 'woocommerce_settings_tabs_afterpay_elements', 'add_afterpay_elements_settings' );

add_action( 'woocommerce_update_options_afterpay_elements', 'update_afterpay_elements_settings' );

add_action( 'admin_enqueue_scripts', 'init_afterpay_admin_scripts' );

add_action( 'wp_enqueue_scripts', 'init_afterpay_scripts' );

add_action( 'parse_request', 'afterpay_return' );

add_action( 'woocommerce_before_thankyou', 'add_custom_thankyou_filter' );

// Check status of pending orders based on WP Cron scheduler.
add_action( 'afterpay_check_pending', 'afterpay_croncheck' );

if ( ! wp_next_scheduled( 'afterpay_check_pending' ) ) {
	wp_schedule_event( time(), 'hourly', 'afterpay_check_pending' );
}
