<?php
/**
 * AfterPay Direct Debit payment method for the Netherlands
 *
 * @category   Class
 * @package    WC_Payment_Gateway
 * @author     arvato Finance B.V.
 * @copyright  since 2011 arvato Finance B.V.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * AfterPay Direct Debit payment method for the Netherlands
 *
 * @class         WC_Gateway_Afterpay_Nl_Directdebit
 * @extends       WC_Gateway_Afterpay_Base
 * @package  Arvato_AfterPay
 * @author        AfterPay
 */
#[AllowDynamicProperties]
class WC_Gateway_Afterpay_Nl_Directdebit extends WC_Gateway_Afterpay_Base {

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		parent::__construct();

		$this->id           = 'afterpay_directdebit';
		$this->method_title = __( 'Riverty Netherlands Direct Debit (Legacy SOAP)', 'afterpay-payment-gateway-for-woocommerce' );
		$this->has_fields   = true;

		// Load the form fields.
		$this->init_form_fields();
		$this->show_bankaccount = true;
		$this->show_dob         = 'yes';
		$this->show_ssn         = false;
		$this->show_companyname = false;
		$this->show_coc         = false;
		$this->order_type       = 'B2C';

		// Load the settings.
		$this->init_settings();

		// Define user set variables for basic settings.
		$this->enabled           = ( isset( $this->settings['enabled'] ) ) ?
			$this->settings['enabled'] : '';
		$this->title             = ( isset( $this->settings['title'] ) ) ?
			$this->settings['title'] : '';
		$this->extra_information = ( isset( $this->settings['extra_information'] ) ) ?
			$this->settings['extra_information'] : '';
		$this->merchantid        = ( isset( $this->settings['merchantid'] ) ) ?
			$this->settings['merchantid'] : '';
		$this->portfolioid       = ( isset( $this->settings['portfolioid'] ) ) ?
			$this->settings['portfolioid'] : '';
		$this->password          = ( isset( $this->settings['password'] ) ) ?
			$this->settings['password'] : '';
		$this->lower_threshold   = ( isset( $this->settings['lower_threshold'] ) ) ?
			$this->settings['lower_threshold'] : '';
		$this->upper_threshold   = ( isset( $this->settings['upper_threshold'] ) ) ?
			$this->settings['upper_threshold'] : '';
		$this->testmode          = ( isset( $this->settings['testmode'] ) ) ?
			$this->settings['testmode'] : '';
		$this->debug_mail        = ( isset( $this->settings['debug_mail'] ) ) ?
			$this->settings['debug_mail'] : '';
		$this->ip_restriction    = ( isset( $this->settings['ip_restriction'] ) ) ?
			$this->settings['ip_restriction'] : '';
		$this->show_advanced     = ( isset( $this->settings['show_advanced'] ) ) ?
			$this->settings['show_advanced'] : 'no';

		// Advanced settings.
		$this->show_phone                            = ( isset( $this->settings['show_phone'] ) ) ?
			$this->settings['show_phone'] : '';
		$this->show_gender                           = ( isset( $this->settings['show_gender'] ) ) ?
			$this->settings['show_gender'] : '';
		$this->show_termsandconditions               = 'yes';
		$this->exclude_shipping_methods              = ( isset( $this->settings['exclude_shipping_methods'] ) ) ?
			$this->settings['exclude_shipping_methods'] : '';
		$this->use_custom_housenumber_field          = ( isset( $this->settings['use_custom_housenumber_field'] ) ) ?
			$this->settings['use_custom_housenumber_field'] : '';
		$this->use_custom_housenumber_addition_field =
			( isset( $this->settings['use_custom_housenumber_addition_field'] ) ) ?
			$this->settings['use_custom_housenumber_addition_field'] : '';

		// Captures and refunds.
		$this->captures                     = ( isset( $this->settings['captures'] ) ) ?
			$this->settings['captures'] : '';
		$this->captures_way                 = ( isset( $this->settings['captures_way'] ) ) ?
			$this->settings['captures_way'] : '';
		$this->captures_way_based_on_status = ( isset( $this->settings['captures_way_based_on_status'] ) ) ?
			$this->settings['captures_way_based_on_status'] : '';
		$this->refunds                      = ( isset( $this->settings['refunds'] ) ) ?
			$this->settings['refunds'] : '';

		if ( isset( $this->settings['refunds'] ) && 'yes' === $this->settings['refunds'] ) {
			$this->supports = array( 'refunds' );
		}

		// Country information.
		$afterpay_country       	= 'NL';
		$afterpay_language      	= 'NL';
		$afterpay_currency      	= 'EUR';
		$current_locale			    = strtolower( substr( get_locale(), 0, 2 ) );
		$merchantid                 = ( '' !== $this->merchantid ) ? $this->merchantid . '/' : 'default/';
		$country_code 			    = ( $current_locale	 == 'nl' ) ? 'nl_nl/' : 'nl_en/';
		$afterpay_invoice_terms     = 'https://documents.riverty.com/terms_conditions/payment_methods/direct_debit/'. $country_code . 'default/';
		$afterpay_privacy_statement = 'https://documents.riverty.com/privacy_statement/checkout/' . $country_code;
		$afterpay_invoice_icon      = 'https://cdn.riverty.design/logo/riverty-checkout-logo.svg';

		// Apply filters to Country and language.
		$this->afterpay_country           = apply_filters( 'afterpay_country', $afterpay_country );
		$this->afterpay_language          = apply_filters( 'afterpay_language', $afterpay_language );
		$this->afterpay_currency          = apply_filters( 'afterpay_currency', $afterpay_currency );
		$this->afterpay_invoice_terms     = apply_filters( 'afterpay_invoice_terms', $afterpay_invoice_terms );
		$this->afterpay_privacy_statement = apply_filters( 'afterpay_privacy_statement', $afterpay_privacy_statement );
		$this->icon                       = apply_filters( 'afterpay_invoice_icon', $afterpay_invoice_icon );

		// Actions.
		/* 2.0.0 */
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_receipt_afterpay', array( &$this, 'receipt_page' ) );

		// Add event handler for an order Status change.
		add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_change_callback' ), 1000, 4 );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 *
	 * @access public
	 * @return void
	 */
	public function init_form_fields() {

		$this->form_fields = apply_filters(
			'afterpay_nl_directdebit_form_fields', array(
				'enabled'                               => array(
					'title'   => __( 'Enable/Disable', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Riverty Netherlands Direct Debit (Legacy SOAP)', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => 'no',
				),
				'title'                                 => array(
					'title'       => __( 'Title', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'afterpay-payment-gateway-for-woocommerce' ),
					'default'     => 'Automatische incasso',
				),
				'extra_information'                     => array(
					'title'       => __( 'Extra information', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Extra information to show to the customer in the checkout', 'afterpay-payment-gateway-for-woocommerce' ),
					'default'     => 'Betaal via een automatische afschrijving van je rekening',
				),
				'merchantid'                            => array(
					'title'       => __( 'Merchant ID', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Please enter your Riverty Merchant ID; this is needed in order to take payment!',
						'afterpay'
					),
					'default'     => '',
				),
				'portfolioid'                           => array(
					'title'       => __( 'Portfolio number', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Please enter your Riverty Portfolio Number; this is needed in order to take payment!',
						'afterpay'
					),
					'default'     => '',
				),
				'password'                              => array(
					'title'       => __( 'Portfolio password', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Please enter your Riverty Portfolio Password; this is needed in order to take payment!',
						'afterpay'
					),
					'default'     => '',
				),
				'lower_threshold'                       => array(
					'title'       => __( 'Lower threshold', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Disable Riverty Invoice if Cart Total is lower than the specified value. Leave blank to disable this feature.',
						'afterpay'
					),
					'default'     => '5',
				),
				'upper_threshold'                       => array(
					'title'       => __( 'Upper threshold', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Disable Riverty Invoice if Cart Total is higher than the specified value. Leave blank to disable this feature.',
						'afterpay'
					),
					'default'     => '',
				),
				'testmode'                              => array(
					'title'   => __( 'Test Mode', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __(
						'Enable Riverty Test Mode. This will only work if you have a Riverty test account. For test purchases with a live account.',
						'afterpay'
					),
					'default' => 'yes',
				),
				'debug_mail'                            => array(
					'title'       => __( 'Debug mail', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Use debug mail to send the complete Riverty request to your mail, for debug functionality only. Leave empty to disable.',
						'afterpay'
					),
					'default'     => '',
				),
				'ip_restriction'                        => array(
					'title'       => __( 'IP restriction', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Fill in IP address to only show the payment method for that specific IP address. Leave empty to disable',
						'afterpay'
					),
					'default'     => '',
				),
				'show_advanced'                         => array(
					'title'       => __( 'Show advanced settings', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'select',
					'description' => __(
						'Show advanced settings'
					),
					'options'     => array(
						'yes' => 'Yes',
						'no'  => 'No',
					),
					'default'     => 'no',
				),
				'display_settings'                      => array(
					'title'       => __( 'Display settings', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'title',
					'description' => '',
					'class'       => 'afterpay_advanced_setting',
				),
				'show_phone'                            => array(
					'title'       => __( 'Show phone number', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'checkbox',
					'description' => __(
						'The phone number is needed for payment and should be asked to the consumer in the checkout. This option will show the phone number in the Riverty payment section.',
						'afterpay-payment-gateway-for-woocommerce'
					),
					'default'     => 'no',
					'class'       => 'afterpay_advanced_setting',
				),
				'show_gender'                           => array(
					'title'       => __( 'Show gender', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'checkbox',
					'description' => __(
						'Show gender field in Riverty form in the checkout',
						'afterpay'
					),
					'default'     => 'no',
					'class'       => 'afterpay_advanced_setting',
				),
				'exclude_shipping_methods'              => array(
					'title'       => __( 'Exclude shipping methods', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'multiselect',
					'description' => __( 'Do not show Riverty if selected shipping methods are used (CTRL + click to select and deselect)', 'afterpay-payment-gateway-for-woocommerce' ),
					'options'     => $this->get_all_shipping_methods(),
					'default'     => 'yes',
					'class'       => 'afterpay_advanced_setting',
				),
				'use_custom_housenumber_field'          => array(
					'title'       => __( 'Use custom housenumber field', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Fill in the custom field name used for the housenumber, without billing_ or shipping_',
						'afterpay'
					),
					'default'     => '',
					'class'       => 'afterpay_advanced_setting',
				),
				'use_custom_housenumber_addition_field' => array(
					'title'       => __( 'Use custom housenumber addition field', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Fill in the custom field name used for the housenumber addition, without billing_ or shipping_',
						'afterpay'
					),
					'default'     => '',
					'class'       => 'afterpay_advanced_setting',
				),
				'captures_and_refunds_section'          => array(
					'title'       => __( 'Captures and refunds', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'title',
					'description' => '<strong style="color:red" class="developer_settings_section_description">' . __( 'This section contains developer settings that can only be set in contact with a consultant of Riverty. Please contact Riverty for more information.' ) . '</strong>',
					'class'       => 'afterpay_advanced_setting',
				),
				'captures'                              => array(
					'title'   => __( 'Enable captures', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable capturing', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => 'no',
					'class'   => 'afterpay_advanced_setting',
				),
				'captures_way'                          => array(
					'title'   => __( 'Way of captures', 'afterpay-payment-gateway-for-woocommerce' ),
					'label'   => __( 'Way of capturing', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'select',
					'default' => 'auto_after_authorization',
					'options' => array(
						'auto_after_authorization' => __( 'Automatically after authorization', 'afterpay-payment-gateway-for-woocommerce' ),
						'based_on_status'          => __( 'Based on Woocommerce Status', 'afterpay-payment-gateway-for-woocommerce' ),
					),
					'class'   => 'afterpay_advanced_setting',
				),
				'captures_way_based_on_status'          => array(
					'title'   => __( 'Status to capture based on', 'afterpay-payment-gateway-for-woocommerce' ),
					'label'   => __( 'Status to capture based on', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'select',
					'options' => $this->get_all_possible_order_statuses(),
					'class'   => 'afterpay_advanced_setting',
				),
				'refunds'                               => array(
					'title'   => __( 'Enable refunds', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable refunding', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => 'no',
					'class'   => 'afterpay_advanced_setting',
				),
			)
		);
	}
}
