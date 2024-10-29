<?php
/**
 * AfterPay Direct Debit payment method for Germany
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
 * AfterPay Direct Debit payment method for Germany
 *
 * @class       WC_Gateway_Afterpay_De_Directdebit
 * @extends     WC_Gateway_Afterpay_Base_Rest
 * @package     Arvato_AfterPay
 * @author      AfterPay
 */
#[AllowDynamicProperties]
class WC_Gateway_Afterpay_De_Directdebit extends WC_Gateway_Afterpay_Base_Rest {

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		parent::__construct();

		$this->id           = 'afterpay_de_directdebit';
		$this->method_title = __( 'Riverty Germany Direct Debit', 'afterpay-payment-gateway-for-woocommerce' );
		$this->has_fields   = true;

		// Load the form fields.
		$this->init_form_fields();
		$this->show_bankaccount = true;
		$this->show_ssn         = false;
		$this->show_companyname = false;
		$this->show_coc         = false;
		$this->order_type       = 'B2C';
		$this->tracking_session_id = '';

		// Load the settings.
		$this->init_settings();

		// Define user set variables for basic settings.
		$this->enabled           = ( isset( $this->settings['enabled'] ) ) ?
			$this->settings['enabled'] : '';
		$this->title             = 'Lastschrift';
		$this->extra_information = 'Zahle bequem per Lastschrifteinzug';
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
		$this->show_dob                              = ( isset( $this->settings['show_dob'] ) ) ?
			$this->settings['show_dob'] : '';
		$this->show_termsandconditions               = ( isset( $this->settings['show_termsandconditions'] ) ) ?
			$this->settings['show_termsandconditions'] : '';
		$this->exclude_shipping_methods              = ( isset( $this->settings['exclude_shipping_methods'] ) ) ?
			$this->settings['exclude_shipping_methods'] : '';
		$this->tracking_id                           = ( isset( $this->settings['tracking_id'] ) ) ?
			$this->settings['tracking_id'] : '';
		$this->use_custom_housenumber_field          = ( isset( $this->settings['use_custom_housenumber_field'] ) ) ?
			$this->settings['use_custom_housenumber_field'] : '';
		$this->use_custom_housenumber_addition_field =
			( isset( $this->settings['use_custom_housenumber_addition_field'] ) ) ?
			$this->settings['use_custom_housenumber_addition_field'] : '';
		$this->compatibility_germanized              = ( isset( $this->settings['compatibility_germanized'] ) ) ?
			$this->settings['compatibility_germanized'] : '';

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

		// Customer Individual Score.
		$this->customer_individual_score = ( isset( $this->settings['customer_individual_score'] ) ) ?
			$this->settings['customer_individual_score'] : '';

		if ( isset( $this->settings['refunds'] ) && 'yes' === $this->settings['refunds'] ) {
			$this->supports = array( 'refunds' );
		}

		$afterpay_country           = 'DE';
		$afterpay_language          = 'DE';
		$afterpay_currency          = 'EUR';
		$current_locale			    = strtolower( substr( get_locale(), 0, 2 ) );
		$merchantid                 = ( '' !== $this->merchantid ) ? $this->merchantid . '/' : 'default/';
		$country_code 			    = ( $current_locale == 'de' ) ? 'de_de/' : 'en_de/';
		$afterpay_invoice_terms     = 'https://documents.myafterpay.com/consumer-terms-conditions/'. $country_code . $merchantid . 'direct_debit';
		$afterpay_privacy_statement = 'https://documents.myafterpay.com/privacy-statement/' . $country_code . $merchantid;
		$afterpay_invoice_icon      = 'https://cdn.riverty.design/logo/riverty-checkout-logo.svg';

		// Apply filters to Country and language.
		$this->afterpay_country           = apply_filters( 'afterpay_country', $afterpay_country );
		$this->afterpay_language          = apply_filters( 'afterpay_language', $afterpay_language );
		$this->afterpay_currency          = apply_filters( 'afterpay_currency', $afterpay_currency );
		$this->afterpay_invoice_terms     = apply_filters( 'afterpay_invoice_terms', $afterpay_invoice_terms );
		$this->afterpay_privacy_statement = apply_filters( 'afterpay_privacy_statement', $afterpay_privacy_statement );
		$this->icon                       = apply_filters( 'afterpay_invoice_icon', $afterpay_invoice_icon );

		// Actions
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
			'afterpay_de_directdebit_form_fields', array(
				'enabled'                               => array(
					'title'   => __( 'Enable/Disable', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Riverty Germany Direct Debit', 'afterpay-payment-gateway-for-woocommerce' ),
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
				'show_dob'                              => array(
					'title'       => __( 'Show date of birth', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'checkbox',
					'description' => __(
						'Date of birth is needed for payment and should be asked to the consumer in the checkout. This option will show the date of birth in the Riverty payment section.',
						'afterpay-payment-gateway-for-woocommerce'
					),
					'default'     => 'yes',
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
				'tracking_id'          => array(
					'title'       => __( 'Client ID For profile tracking', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'text',
					'description' => __(
						'Please make sure to enter Tracking ID in order to activate Profile Tracking Services',
						'afterpay'
					),
					'default'     => '',
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
				'compatibility_germanized'              => array(
					'title'       => __( 'Compatibility with Germanized', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'        => 'checkbox',
					'label'       => __( 'Use the title field from the Germanized plugin', 'afterpay-payment-gateway-for-woocommerce' ),
					'description' => __(
						'This functionality hides the gender field at Riverty and uses the title field from the Germanized plugin',
						'afterpay'
					),
					'default'     => 'no',
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
				'customer_individual_score'             => array(
					'title'   => __( 'Enable customer individual score', 'afterpay-payment-gateway-for-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable customer individual score', 'afterpay-payment-gateway-for-woocommerce' ),
					'default' => 'no',
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

		<script type="text/javascript">
			jQuery( document ).ready(function($) {
				var tracking_session_id = '<?php echo $this->get_profile_tracking_session_id(); ?>';
				var tracking_client_id = '<?php echo $this->tracking_id; ?>';
				var tracking_domain = '<?php echo get_option('woocommerce_' . $this->id . '_settings')['tracking_domain'] ?>';

				$('input[type=radio][id=payment_method_' + '<?php echo esc_attr( $this->id ); ?>' + ']').on('click', function(e) {
					var tracking_script = document.getElementById('afterpay_tracking_pixel');
                	var tracking_noscript = document.getElementById('afterpay_tracking_noscript');

					if (tracking_script) {
						tracking_script.remove();
						tracking_noscript.remove();
					}
					get_tracking_pixel();
				});	

				if($('input[type=radio][id=payment_method_' + '<?php echo esc_attr( $this->id ); ?>' + ']').is(':checked')) { 
					var tracking_script = document.getElementById('afterpay_tracking_pixel');
                	var tracking_noscript = document.getElementById('afterpay_tracking_noscript');

					if (tracking_script) {
						tracking_script.remove();
						tracking_noscript.remove();
					}
					get_tracking_pixel();
				}

				function get_tracking_pixel() {
					if (document.getElementById('afterpay_tracking_pixel')) {
						return;
					}
					window._itt = {
						c: tracking_client_id, s: tracking_session_id, t: 'CO'
					};
					load_profile_tracking_no_script(tracking_domain);
					load_profile_tracking_script(tracking_domain);
				}

				function load_profile_tracking_no_script(domain) {
					var noscriptElement = document.createElement('noscript');
					noscriptElement.id = 'afterpay_tracking_noscript';
					var imageElement = document.createElement('img');
					imageElement.src = '//' + domain + '/img/7982/' + tracking_session_id;
					imageElement.border = '0';
					imageElement.height = '0';
					imageElement.width = '0';

					noscriptElement.appendChild(imageElement);
					var scriptElementTag = document.getElementsByTagName('script')[0];
					scriptElementTag.parentNode.insertBefore(noscriptElement, scriptElementTag);
				}

				function load_profile_tracking_script(domain) {
					var scriptElement = document.createElement('script');
					scriptElement.type = 'text/javascript';
					scriptElement.async = true;
					scriptElement.src = '//' + domain + '/7982.js';
					scriptElement.id = 'afterpay_tracking_pixel';

					var noscriptElementTag = document.getElementById('afterpay_tracking_noscript');
					noscriptElementTag.parentNode.insertBefore(scriptElement, noscriptElementTag);
				}
			});
		</script>

		<fieldset class="riverty_fieldset">
			<?php if ( $this->can_show_introduction_text() ) : ?>
				<?php echo $this->get_introduction_text(); ?>
			<?php endif; ?>
			<p class="form-row">
				<label for="<?php echo esc_attr( $this->id ); ?>_bankaccount"><strong><?php echo esc_html_e( 'Bankverbindung (IBAN):', 'afterpay-payment-gateway-for-woocommerce' ); ?> <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_bankaccount" id="<?php echo esc_attr( $this->id ); ?>_bankaccount" />
				<span class="bank_validation_loader" id="<?php echo esc_attr( $this->id ); ?>_loader"></span>
				<span class="check_mark" id="<?php echo esc_attr( $this->id ); ?>_check_mark"></span><span id="<?php echo esc_attr( $this->id ); ?>_validation_response" class="bank_validation_response"></span>
				<script type="text/javascript">
					jQuery( document ).ready(function($) {
						//bankaccount input masking
						$( "#<?php echo esc_attr( $this->id ); ?>_bankaccount" ).mask("<?php echo $this->get_bankaccount_placeholder( $this->afterpay_country  ) ?>", {placeholder: "<?php echo $this->get_bankaccount_placeholder( $this->afterpay_country  ) ?>"});

						//validating bank account using afterpay api request
						let input = document.getElementById('<?php echo esc_attr( $this->id ); ?>_bankaccount');
						let timeout = null;
						let preloader_timeout = null;

						input.addEventListener('keyup', function (e) {
							var length = this.value.replace(/\s/g, '').length;
							if ( length >= 15 ) {
								clearTimeout(preloader_timeout);
								clearTimeout(timeout);
								preloader_timeout = setTimeout(preloader, 500);
								timeout = setTimeout(sendRequest, 1000);
							}
						});

						function preloader(){
							$("#<?php echo esc_attr( $this->id ); ?>_loader").addClass("blockUI blockOverlay");
						}

						function sendRequest()
						{
							var bank_account_value = $("#<?php echo esc_attr( $this->id ); ?>_bankaccount").val().replace(/\s/g, '');
							var api_key            = '<?php echo $this->get_api_key(); ?>';
							var connection_mode    = '<?php echo $this->get_connection_mode(); ?>';
							var bankaccount_verification_url = '<?php echo get_site_url()."/wp-json/afterpay/v1/bankaccount-validate" ?>';

							var payload = {
								"bank_account": bank_account_value,
								"api_key": api_key,
								"connection_mode": connection_mode
							};

							$.ajax({
								url: bankaccount_verification_url,
								type: 'POST',
								data: JSON.stringify(payload),
								async: false,
								contentType: 'application/json'
							}).done(
								function (response) {
									$("#<?php echo esc_attr( $this->id ); ?>_loader").removeClass("blockUI blockOverlay");
									if ( response.is_valid ) {
										$("#<?php echo esc_attr( $this->id ); ?>_check_mark").css('margin-top', '10px');
										$("#<?php echo esc_attr( $this->id ); ?>_check_mark").css('display', 'inline-block');
										$("#<?php echo esc_attr( $this->id ); ?>_validation_response").removeClass("invalid_account");
										$("#<?php echo esc_attr( $this->id ); ?>_validation_response").css('display', 'inline');
										$("#<?php echo esc_attr( $this->id ); ?>_validation_response").text(response.message);
										$('#payment #place_order').removeAttr("disabled");
									}
									else {
										$("#<?php echo esc_attr( $this->id ); ?>_check_mark").css('display', 'none');
										$("#<?php echo esc_attr( $this->id ); ?>_validation_response").addClass("invalid_account");
										$("#<?php echo esc_attr( $this->id ); ?>_validation_response").css('display', 'inline-block');
										$("#<?php echo esc_attr( $this->id ); ?>_validation_response").text(response.message);
										$('#payment #place_order').attr("disabled","disabled");
									}
								}
							).fail(
								function () {
									$("#<?php echo esc_attr( $this->id ); ?>_loader").removeClass("blockUI blockOverlay");
									$("#<?php echo esc_attr( $this->id ); ?>_validation_response").addClass("invalid_account");
									$("#<?php echo esc_attr( $this->id ); ?>_validation_response").css('display', 'inline-block');
									$("#<?php echo esc_attr( $this->id ); ?>_validation_response").text("something went wrong");
									$('#payment #place_order').removeAttr("disabled");
								}
							);
						}
					});
				</script>
			</p>
			<?php if ( 'yes' === $this->show_dob ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required">
				<label for="<?php echo esc_attr( $this->id ); ?>_dob"><strong><?php esc_html_e( 'Date of birth', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<script type="text/javascript">
					jQuery( document ).ready(function($) {
						$( "#<?php echo esc_attr( $this->id ); ?>_dob" ).mask("00/00/0000", {placeholder: "<?php echo $this->get_birthdate_placeholder() ?>"});
					});
				</script>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_dob" id="<?php echo esc_attr( $this->id ); ?>_dob" />
			</p>
			<?php endif; ?>
			<?php if ( 'yes' === $this->show_phone ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required validate-phone">
				<label for="<?php echo esc_attr( $this->id ); ?>_phone"><strong><?php echo esc_html_e( 'Phone number', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_phone" />
			</p>
			<?php endif; ?>
			<div class="clear"></div>
			<p class="form-row" style="margin-top:15px">
				<input type="hidden" value="<?php echo $this->tracking_session_id; ?>" id="<?php echo esc_attr( $this->id ); ?>_tracking_input_id" name="<?php echo esc_attr( $this->id ); ?>_profile_tracking" />
				<label style="display:inline">
					<?php echo $this->get_tracking_text('direct_debit', 'de'); ?>
				</label>
			</p>
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

		if ( 'yes' === $this->show_phone && empty( $_POST[ esc_attr( $this->id ) . '_phone' ] ) ) {
			array_push( $validation_messages, esc_html__( 'Phone number is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( 'yes' === $this->show_dob ) {
			$dob_validation_message = $this->validate_dob($_POST[ esc_attr( $this->id ) . '_dob' ]);
			if ( !empty( $dob_validation_message ) ) {
				array_push( $validation_messages, $dob_validation_message );
			}
		}
		if ( $this->show_bankaccount && empty( $_POST[ $this->id . '_bankaccount' ] ) ) {
			array_push( $validation_messages, __( 'Bankaccount is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		if ( $this->customer_consent == true && 'yes' === $this->show_termsandconditions && ( ! isset( $_POST[ $this->id . '_terms' ] ) || empty( $_POST[ $this->id . '_terms' ] ) ) ) {
			array_push( $validation_messages, __( 'Please accept the General Terms and Conditions for the Riverty payment method', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		
		// Send error notice to indicate required fields for user to fill in if empty
		if ( $this->get_error_message( $validation_messages ) != null ) {
			wc_add_notice( $this->get_error_message( $validation_messages ), 'error' );
			return false;
		}
		
		// Remove spaces in masked bank account input value after request submission.
		if ( isset( $_POST[ ( $this->id ) . '_bankaccount' ] ) ) {
			$_POST[ ( $this->id ) . '_bankaccount' ] = str_replace(' ', '', $_POST[ ( $this->id ) . '_bankaccount' ]);
		}

		// Validate bank account.
		$afterpay_bankaccount = ( isset( $_POST[ ( $this->id ) . '_bankaccount' ] ) ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ ( $this->id ) . '_bankaccount' ] ) ) ) : '';
		if ( '' !== $afterpay_bankaccount ) {
			if ( ! $this->validate_afterpay_bankaccount( $afterpay_bankaccount ) ) {
				return false;
			}
		}
		
		return true;
	}
}
