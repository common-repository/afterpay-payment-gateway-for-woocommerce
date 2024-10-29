<?php
/**
 * AfterPay Flex payment method for Sweden
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
 * AfterPay Flex payment method for Sweden
 *
 * @class         WC_Gateway_Afterpay_Se_Flex
 * @extends       WC_Gateway_Afterpay_Base_Rest
 * @package       Arvato_AfterPay
 * @author        AfterPay
 */
#[AllowDynamicProperties]
class WC_Gateway_Afterpay_Se_Flex extends WC_Gateway_Afterpay_Base_Rest {

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		parent::__construct();

		$this->id           = 'afterpay_se_flex';
		$this->method_title = __( 'Riverty Sweden Flex payment', 'afterpay-payment-gateway-for-woocommerce' );
		$this->has_fields   = true;

		// Setup field to load available flex information in.
		$this->available_flex_information = array();

		// Load the form fields.
		$this->init_form_fields();
		$this->show_bankaccount = false;
		$this->show_dob         = 'no';
		$this->show_ssn         = true;
		$this->show_companyname = false;
		$this->show_coc         = false;
		$this->order_type       = 'B2C';

		// Load the settings.
		$this->init_settings();

		// Define user set variables for basic settings.
		$this->enabled           = ( isset( $this->settings['enabled'] ) ) ?
			$this->settings['enabled'] : '';
		$this->title             = 'Dela upp din betalning';
		$this->extra_information = 'Betala i din egna takt. Lägg till flera inköp i en månadsfaktura';
		$this->test_api_key      = ( isset( $this->settings['test_api_key'] ) ) ?
		$this->settings['test_api_key'] : '';
		$this->live_api_key      = ( isset( $this->settings['live_api_key'] ) ) ?
		$this->settings['live_api_key'] : '';
		$this->merchantid        = ( isset( $this->settings['merchantid'] ) ) ?
			$this->settings['merchantid'] : '';
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
		$this->show_termsandconditions               = ( isset( $this->settings['show_termsandconditions'] ) ) ?
			$this->settings['show_termsandconditions'] : '';
		$this->exclude_shipping_methods              = ( isset( $this->settings['exclude_shipping_methods'] ) ) ?
			$this->settings['exclude_shipping_methods'] : '';
		$this->use_custom_housenumber_field          = ( isset( $this->settings['use_custom_housenumber_field'] ) ) ?
			$this->settings['use_custom_housenumber_field'] : '';
		$this->use_custom_housenumber_addition_field =
			( isset( $this->settings['use_custom_housenumber_addition_field'] ) ) ?
			$this->settings['use_custom_housenumber_addition_field'] : '';
		$this->ip_restriction                        = ( isset( $this->settings['ip_restriction'] ) ) ?
			$this->settings['ip_restriction'] : '';

		// Captures and refunds.
		$this->captures                     = ( isset( $this->settings['captures'] ) ) ?
			$this->settings['captures'] : '';
		$this->captures_way                 = ( isset( $this->settings['captures_way'] ) ) ?
			$this->settings['captures_way'] : '';
		$this->captures_way_based_on_status = ( isset( $this->settings['captures_way_based_on_status'] ) ) ?
			$this->settings['captures_way_based_on_status'] : '';
		$this->refunds                      = ( isset( $this->settings['refunds'] ) ) ?
			$this->settings['refunds'] : '';
		$this->refund_tax_percentage        = ( isset( $this->settings['refund_tax_percentage'] ) ) ?
			$this->settings['refund_tax_percentage'] : '';

		if ( isset( $this->settings['refunds'] ) && 'yes' === $this->settings['refunds'] ) {
			$this->supports = array( 'refunds' );
		}

		$afterpay_country           = 'SE';
		$afterpay_language          = 'SE';
		$afterpay_currency          = 'SEK';
		$current_locale			    = strtolower( substr( get_locale(), 0, 2 ) );
		$merchantid                 = ( '' !== $this->merchantid ) ? $this->merchantid . '/' : 'default/';
		$country_code 			    =  ( $current_locale == 'sv' ) ? 'sv_se/' : 'en_se/';
		$afterpay_invoice_terms     = 'https://documents.myafterpay.com/consumer-terms-conditions/' . $country_code . $merchantid . 'part_payment';
		$afterpay_installment_terms = '';
		$afterpay_privacy_statement = 'https://documents.myafterpay.com/privacy-statement/' . $country_code . $merchantid;
		$afterpay_invoice_icon      = 'https://cdn.riverty.design/logo/riverty-checkout-logo.svg';

		// Apply filters to Country and language.
		$this->afterpay_country           = apply_filters( 'afterpay_country', $afterpay_country );
		$this->afterpay_language          = apply_filters( 'afterpay_language', $afterpay_language );
		$this->afterpay_currency          = apply_filters( 'afterpay_currency', $afterpay_currency );
		$this->afterpay_invoice_terms     = apply_filters( 'afterpay_invoice_terms', $afterpay_invoice_terms );
		$this->afterpay_installment_terms = apply_filters( 'afterpay_installment_terms', $afterpay_installment_terms );
		$this->afterpay_privacy_statement = apply_filters( 'afterpay_privacy_statement', $afterpay_privacy_statement );
		$this->icon                       = apply_filters( 'afterpay_invoice_icon', $afterpay_invoice_icon );

		// Actions.
		/* 2.0.0 */
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array( $this, 'process_admin_options' )
		);

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
			'afterpay_se_flex_form_fields', array(
				'enabled'                               => array(
					'title'   => __( 'Enable/Disable', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Riverty Sweden Flex payment', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => 'no',
				),
				'test_api_key'                               => array(
					'title'       => __( 'Test mode API key	', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Connect to the Riverty test environment and place test orders. New to Riverty?',
						'afterpay-payment-gateway-for-woocommerce'
					).' <a href="https://www.riverty.com/en/business/products/get-started-riverty-buy-now-pay-later/" target="_blank">'.__( 'Sign up here ', 'afterpay-payment-gateway-for-woocommerce' ).'</a>'.
					__(
						'to get access and a test API key on the',
						'afterpay-payment-gateway-for-woocommerce'
					).' <a href="https://merchantportal-pt.riverty.com/login" target="_blank">'.__( 'Riverty Merchant Portal', 'afterpay-payment-gateway-for-woocommerce' ).'</a>.'.$this->get_apikey_validation_button('test'),
					'default'     => '',
				),
				'live_api_key'                               => array(
					'title'       => __( 'Production mode API key', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Start accepting payment by connecting to the Riverty production environment. Get your production API key on the',
						'afterpay-payment-gateway-for-woocommerce'
					).' <a href="https://merchantportal.riverty.com/login" target="_blank">'.__( 'Riverty Merchant Portal', 'afterpay-payment-gateway-for-woocommerce' ).'</a>.'.$this->get_apikey_validation_button('live'),
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
					'title'       => __( 'Environment', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'select',
					'description' => __(
						'Enable or disable test mode. Use the test option to test with API keys from',
						'afterpay-payment-gateway-for-woocommerce'
					).' <a href="https://merchantportal-pt.riverty.com/login" target="_blank">'.__( 'Riverty Merchant Portal', 'afterpay-payment-gateway-for-woocommerce' ).'</a>.',
					'options'     => array(
						'yes'     => 'Test',
						'no'      => 'Production',
					),
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
				'show_termsandconditions'               => array(
					'title'       => __( 'Show terms and conditions', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'checkbox',
					'description' => __( 'Show terms and conditions of Riverty', 'afterpay-payment-gateway-for-woocommerce' ),
					'default'     => 'yes',
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
				'merchantid'                            => array(
					'title'       => __( 'Merchant ID', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'default'     => '',
					'class'       => 'afterpay_advanced_setting',
					'description' => __( 'The merchant ID can be used for merchant specific terms and conditions.' ),
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
					'default' => 'yes',
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
					'default' => 'yes',
					'class'   => 'afterpay_advanced_setting',
				),
				'refund_tax_percentage'                 => array(
					'title'   => __( 'Refund tax percentage', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'text',
					'description' => __( 'Fill in the tax percentage of items refunded from Woocommerce, f.e. 21 in case of 21%.', 'afterpay-payment-gateway-for-woocommerce' ),
					'label'   => __( 'Default percentage calculated on refunds to Riverty', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => '0',
					'class'   => 'afterpay_advanced_setting',
				),
			)
		);
	}

	/**
	 * Payment form on checkout page
	 *
	 * @acces public
	 * @return void
	 */
	public function payment_fields() {
		global $woocommerce;

		if ( 'yes' === $this->testmode ) : ?>
			<div class="test_mode_div"><?php esc_html_e( 'TEST MODE ENABLED', 'afterpay-payment-gateway-for-woocommerce' ); ?></div>
		<?php endif; ?>

		<?php if ( '' !== $this->extra_information ) : ?>
			<p><?php echo esc_html( $this->extra_information ); ?></p>
		<?php endif; ?>

		<?php $this->get_selling_points() ?>

		<style>
			.installment_plan {
				display: none;
			}
			.installment_active {
				display: block;
			}
			.installment_plan ul li {
				list-style-type: disc;
			}
		</style>

		<fieldset class="riverty_fieldset">
			<?php if ( $this->can_show_introduction_text() ) : ?>
				<?php echo $this->get_introduction_text(); ?>
			<?php endif; ?>
			<p class="form-row">
				<p>Månadspris: <?php echo esc_html( round( $this->available_flex_information['installmentAmount'], 2 ) ); ?> kr eller ca 10 % av kreditbeloppet</p>
				<ul class="afterpay_installment_bullets" style="margin-top: 10px;">
					<li style="list-style-type: circle !important;">Ingen uppläggningskostnad</li>
					<li style="list-style-type: circle !important;">Aviseringsavgift: <?php echo esc_html( number_format( $this->available_flex_information['monthlyFee'], 2 ) ); ?> kr / mnd</li>
					<li style="list-style-type: circle !important;">Årlig ränta: <?php echo esc_html( $this->available_flex_information['interestRate'] ); ?> %</li>
				</ul>
				<p>Ansökan om delbetalning behöver slutföras efter att köpet har slutförts.</p>
				<br/>
				<p>Exempel: Om Kredittagaren till exempel handlar för 10 000 kr och delar upp betalningen på 12 månader kommer den effektiva årsräntan att uppgå till 28,72 %. Det totala beloppet att betala blir i detta exempel 11 435 kr. Läs <a href="<?php echo $this->available_flex_information['readMore']; ?>" target="blank">Standardiserad Europeisk Konsumentinformation (SECCI)</a>.</p>
				<br/>
				<input type="hidden" name="<?php echo esc_attr( $this->id ); ?>_flex_profile" value="<?php echo $this->available_flex_information['profileNo']; ?>"/>
			</p>
			<div class="clear"></div>
			<p class="form-row">
				<label for="<?php echo esc_attr( $this->id ); ?>_ssn"><strong>Skri v in ditt personnummer (12 siffror) för att slutföra köpet: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_ssn" placeholder="ÅÅÅÅMMDDNNNN"/>
			</p>
			<div class="clear"></div>
			<?php if ( 'yes' === $this->show_phone ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required validate-phone">
				<label for="<?php echo esc_attr( $this->id ); ?>_phone"><strong><?php echo esc_html_e( 'Phone number', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_phone" />
			</p>
			<?php endif; ?>
			<?php if ( 'yes' === $this->show_termsandconditions ) : ?>
			<div class="clear"></div>
			<?php echo $this->get_terms_block(); ?>
			<?php endif; ?>
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

		if ( 'yes' === $this->show_phone && empty( $_POST[ $this->id . '_phone' ] ) ) {
			array_push( $validation_messages, __( 'Phone number is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( $this->customer_consent == true && 'yes' === $this->show_termsandconditions && ( ! isset( $_POST[ $this->id . '_terms' ] ) || empty( $_POST[ $this->id . '_terms' ] ) ) ) {
			array_push( $validation_messages, __( 'Please accept the General Terms and Conditions for the Riverty payment method', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( empty( $_POST[ $this->id . '_ssn' ] ) ) {
			array_push( $validation_messages, __( 'Social security number is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}

		// Send error notice to indicate required fields for user to fill in if empty
		if ( $this->get_error_message( $validation_messages ) != null ) {
			wc_add_notice( $this->get_error_message( $validation_messages ), 'error' );
			return false;
		}

		return true;
	}
}
