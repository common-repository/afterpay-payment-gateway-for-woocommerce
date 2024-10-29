<?php
/**
 * AfterPay B2B Open Invoice payment method for the Netherlands
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
 * AfterPay B2B Open Invoice payment method for the Netherlands
 *
 * @class         WC_Gateway_Afterpay_Nl_Business
 * @extends       WC_Gateway_Afterpay_Base
 * @package  Arvato_AfterPay
 * @author        AfterPay
 */
#[AllowDynamicProperties]
class WC_Gateway_Afterpay_Nl_Business extends WC_Gateway_Afterpay_Base {

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		parent::__construct();

		$this->id           = 'afterpay_business';
		$this->method_title = __( 'Riverty Netherlands B2B invoice (Legacy SOAP)', 'afterpay-payment-gateway-for-woocommerce' );
		$this->has_fields   = true;

		// Load the form fields.
		$this->init_form_fields();
		$this->show_bankaccount = false;
		$this->show_dob         = 'no';
		$this->show_ssn         = false;
		$this->show_companyname = true;
		$this->show_coc         = true;
		$this->order_type       = 'B2B';

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
		$afterpay_invoice_terms     = 'https://documents.riverty.com/terms_conditions/payment_methods/invoice/'. $country_code . 'default/';
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
			'afterpay_nl_business_form_fields', array(
				'enabled'                               => array(
					'title'   => __( 'Enable/Disable', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Riverty Netherlands B2B invoice (Legacy SOAP)', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => 'no',
				),
				'title'                                 => array(
					'title'       => __( 'Title', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'afterpay-payment-gateway-for-woocommerce' ),
					'default'     => 'Achteraf betalen voor bedrijven',
				),
				'extra_information'                     => array(
					'title'       => __( 'Extra information', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Extra information to show to the customer in the checkout', 'afterpay-payment-gateway-for-woocommerce' ),
					'default'     => 'Shop nu, betaal later',
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

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
		global $woocommerce;

		if ( 'yes' === $this->testmode ) : ?>
			<div class="test_mode_div"><?php esc_html_e( 'TEST MODE ENABLED', 'afterpay-payment-gateway-for-woocommerce' ); ?></div>
		<?php endif; ?>

		<?php if ( 'sandbox' === $this->testmode ) : ?>
			<div class="test_mode_div"><?php esc_html_e( 'TEST SANDBOX MODE ENABLED', 'afterpay-payment-gateway-for-woocommerce' ); ?></div>
		<?php endif; ?>

		<?php if ( '' !== $this->extra_information ) : ?>
			<p><?php echo esc_html( $this->extra_information ); ?></p>
		<?php endif; ?>

		<?php $this->get_selling_points() ?>

		<fieldset class="riverty_fieldset">
			<?php if ( $this->can_show_introduction_text() ) : ?>
				<?php echo $this->get_introduction_text(); ?>
			<?php endif; ?>
			<p class="form-row validate-required">
				<label for="<?php esc_html_e( $this->id ); ?>_companyname"><strong><?php esc_html_e( 'Company name', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php esc_html_e( $this->id ); ?>_companyname" />
			</p>
			<div class="clear"></div>
			<p class="form-row validate-required">
				<label for="<?php esc_html_e( $this->id ); ?>_cocnumber"><strong><?php esc_html_e( 'Chamber of Commerce number', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php esc_html_e( $this->id ); ?>_cocnumber" />
			</p>
			<?php if ( 'yes' === $this->show_phone ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required validate-phone">
				<label for="<?php esc_html_e( $this->id ); ?>_phone"><strong><?php esc_html_e( 'Phone number', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_html_e( $this->id ); ?>_phone" />
			</p>
			<?php endif; ?>
			<div class="clear"></div>
			<?php echo $this->get_terms_block(); ?>
			<div class="clear"></div>
			<?php echo $this->get_code_of_conduct(); ?>
		</fieldset>
		<?php
	}

	/**
	 * Validate form fields.
	 *
	 * @access public
	 * @return boolean
	 */
	public function validate_fields() {
		global $woocommerce;
		$validation_messages = [];

		if ( empty( $_POST[ $this->id . '_companyname' ] ) ) {
			array_push( $validation_messages, __( 'Company name is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( empty( $_POST[ $this->id . '_cocnumber' ] ) ) {
			array_push( $validation_messages, __( 'Chamber of Commerce number is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( $this->customer_consent == true && ( ! isset( $_POST[ $this->id . '_terms' ] ) || empty( $_POST[ $this->id . '_terms' ] ) ) ) {
			array_push( $validation_messages, __( 'Please accept the General Terms and Conditions for the Riverty payment method', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( 'yes' === $this->show_phone && empty( $_POST[ $this->id . '_phone' ] ) ) {
			array_push( $validation_messages, __( 'Phone number is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}

		// Send error notice to indicate required fields for user to fill in if empty
		if ( $this->get_error_message( $validation_messages ) != null ) {
			wc_add_notice( $this->get_error_message( $validation_messages ), 'error' );
			return false;
		}
		
		return true;
	}
}
