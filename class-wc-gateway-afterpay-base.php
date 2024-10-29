<?php
/**
 * AfterPay Payment Gateway Base
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
 * AfterPay Payment Gateway Base
 *
 * Provides an common base for AfterPay Payment methods
 *
 * @class      WC_Gateway_Afterpay_Base
 * @extends    WC_Gateway_Afterpay
 * @package    WC_Payment_Gateway
 * @author     AfterPay
 */
#[AllowDynamicProperties]
class WC_Gateway_Afterpay_Base extends WC_Gateway_Afterpay {

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;

		parent::__construct();

		$afterpay_invoice_icon = 'https://cdn.riverty.design/logo/riverty-checkout-logo.svg';

		$this->customer_consent = false;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_validation_message_language() {
		$current_locale = strtoupper( substr( get_locale(), 0, 2 ) );
		$allowed_validation_language_codes = [ 'NL','FR' ];

		if ( in_array( $current_locale, $allowed_validation_language_codes ) ) {
			$validation_language = $current_locale;
		}
		else {
			$validation_language = 'EN';
		}

        return $validation_language;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_payment_method_type() {
		if ( strpos( $this->id, 'installments' ) ) {
			$payment_method_type = 'Installment';
		}
		elseif ( strpos( $this->id, 'flex' ) ) {
			$payment_method_type = 'Account';
		}
		elseif ( strpos( $this->id, 'payinx' ) ) {
			$payment_method_type = 'PayinX';
		}
		else {
			$payment_method_type = 'Invoice';
		}

		return $payment_method_type;
	}

	/**
	 * Show the admin options
	 *
	 * @access public
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php echo esc_html( $this->method_title ); ?></h3>
		<p style="font-weight: bold">
			<?php esc_html_e( 'Do you want to offer this or any other Riverty payment method?', 'afterpay-payment-gateway-for-woocommerce') ?> 
			<a href="https://www.riverty.com/en/business/products/get-started-riverty-buy-now-pay-later/?source=plugin-backend-configuration&ecommerce-platform=woocommerce" target="_blank"><?php esc_html_e( 'Sign up here', 'afterpay-payment-gateway-for-woocommerce') ?></a> 
		</p>
		<script>
		( function ( $ ) {
			$(document).ready(function() {
				let timeout = null;
				let preloader_timeout = null;

				// Show or hide advanced fields.
				$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_show_advanced').change(function (event) {
					var value = $('#woocommerce_<?php echo esc_attr( $this->id ); ?>_show_advanced').val();
					if(value === 'yes')
					{
						$('.afterpay_advanced_setting').closest( 'table' ).show();
						$('h3.afterpay_advanced_setting').show();
						$('strong.developer_settings_section_description').show();
					}
					else if(value === 'no')
					{
						$('.afterpay_advanced_setting').closest( 'table' ).hide();
						$('h3.afterpay_advanced_setting').hide();
						$('strong.developer_settings_section_description').hide();
					}
				}).change();
				// Depend showing capture field on
				$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures').change(function (event) {
					var value = $('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way').val();
					if($('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures').is(":checked"))
					{
						$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way').closest( 'tr' ).show();
						if(value === 'based_on_status')
						{
							$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way_based_on_status').closest( 'tr' ).show();
						}
						else if(value === 'auto_after_authorization')
						{
							$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way_based_on_status').closest( 'tr' ).hide();
						}
						$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_refunds').closest( 'tr' ).show();
					}
					else
					{
						$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way').closest( 'tr' ).hide();
						$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way_based_on_status').closest( 'tr' ).hide();
						$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_refunds').closest( 'tr' ).hide();
					}
				}).change();
				// Depend showing capture field on
				$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way').change(function (event) {
					var value = $('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way').val();
					if(value === 'based_on_status')
					{
						if($('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures').is(":checked"))
						{
							$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way_based_on_status').closest( 'tr' ).show();
						}
						else 
						{
							$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way_based_on_status').closest( 'tr' ).hide();
						}
					}
					else if(value === 'auto_after_authorization')
					{
						$('#woocommerce_<?php echo esc_attr( $this->id ); ?>_captures_way_based_on_status').closest( 'tr' ).hide();
					}
				}).change();
				$('#mainform').submit(function (event) {
					var tracking_id_value = $('#woocommerce_<?php echo esc_attr( $this->id ); ?>_tracking_id').val();
					if(tracking_id_value === ""){
						alert('Please make sure to enter Tracking ID in order to activate Profile Tracking Services');
						event.preventDefault();
					}
				});
				<?php if( isset( $this->test_api_key ) ): ?>
					// Send Request to Validate Test Api Key
					$('#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_button').click(function (event) {
						event.preventDefault();
						clearTimeout(preloader_timeout);
						clearTimeout(timeout);
						preloader_timeout = setTimeout(showTestApiKeyloader, 500);
						timeout = setTimeout(sendTestApiKeyValidationRequest, 1000);

						function showTestApiKeyloader() {
							$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").css('display', 'none');
							$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_check_mark").css('display', 'none');
							$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_loader").show();
						}

						function sendTestApiKeyValidationRequest() {
							var apikey_validation_url = '<?php echo get_site_url()."/wp-json/riverty/v1/apikey-validate" ?>';
							<?php $additional_data = json_encode($this->get_additional_data()); ?>

							var payload = {
								"payment_method_country": '<?php echo $this->afterpay_country ?>',
								"currency": '<?php echo $this->afterpay_currency ?>',
								"additional_data": <?php echo $additional_data; ?>,
								"api_key": $("#woocommerce_<?php echo esc_attr( $this->id ); ?>_test_api_key").val(),
								"connection_mode": 'test'
							};

							$.ajax({
								url: apikey_validation_url,
								type: 'POST',
								data: JSON.stringify(payload),
								async: false,
								contentType: 'application/json'
							}).done(
								function (response) {
									if ( response.is_valid ) {
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_loader").hide();
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_check_mark").css('display', 'inline-block');
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").removeClass("invalid_account");
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").css('display', 'inline');
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").text(response.message);
									}
									else {
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_loader").hide();
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_check_mark").css('display', 'none');
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").addClass("invalid_account");
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").css('display', 'inline-block');
										$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").text(response.message);
									}
								}
							).fail(
								function () {
									$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_loader").hide();
									$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_check_mark").css('display', 'none');
									$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").addClass("invalid_account");
									$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").css('display', 'inline-block');
									$("#<?php echo esc_attr( $this->id ); ?>_test_apikey_validation_response").text("something wrong happened , please try again");
								}
							);
						}
					});
					// Send Request to Validate Live Api Key
					$('#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_button').click(function (event) {
						event.preventDefault();
						clearTimeout(preloader_timeout);
						clearTimeout(timeout);
						preloader_timeout = setTimeout(showLiveApiKeyloader, 500);
						timeout = setTimeout(sendLiveApiKeyValidationRequest, 1000);

						function showLiveApiKeyloader() {
							$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").css('display', 'none');
							$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_check_mark").css('display', 'none');
							$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_loader").show();
						}

						function sendLiveApiKeyValidationRequest() {
							var apikey_validation_url = '<?php echo get_site_url()."/wp-json/riverty/v1/apikey-validate" ?>';
							<?php $additional_data = json_encode($this->get_additional_data()); ?>

							var payload = {
								"payment_method_country": '<?php echo $this->afterpay_country ?>',
								"currency": '<?php echo $this->afterpay_currency ?>',
								"additional_data": <?php echo $additional_data; ?>,
								"api_key": $("#woocommerce_<?php echo esc_attr( $this->id ); ?>_live_api_key").val(),
								"connection_mode": 'live'
							};

							$.ajax({
								url: apikey_validation_url,
								type: 'POST',
								data: JSON.stringify(payload),
								async: false,
								contentType: 'application/json'
							}).done(
								function (response) {
									if ( response.is_valid ) {
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_loader").hide();
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_check_mark").css('display', 'inline-block');
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").removeClass("invalid_account");
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").css('display', 'inline');
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").text(response.message);
									}
									else {
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_loader").hide();
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_check_mark").css('display', 'none');
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").addClass("invalid_account");
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").css('display', 'inline-block');
										$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").text(response.message);
									}
								}
							).fail(
								function () {
									$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_loader").hide();
									$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_check_mark").css('display', 'none');
									$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").addClass("invalid_account");
									$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").css('display', 'inline-block');
									$("#<?php echo esc_attr( $this->id ); ?>_live_apikey_validation_response").text("something wrong happened , please try again");
								}
							);
						}
					});
				<?php endif ?>
			});
		})(jQuery);
		</script>
		<style>
			fieldset label[for="woocommerce_<?php echo esc_attr( $this->id ); ?>_show_dob"],
			fieldset label[for="woocommerce_<?php echo esc_attr( $this->id ); ?>_show_phone"] {
				font-size: 0;
			}

			fieldset label[for="woocommerce_<?php echo esc_attr( $this->id ); ?>_show_dob"]:after,
			fieldset label[for="woocommerce_<?php echo esc_attr( $this->id ); ?>_show_phone"]:after {
				content:'';
				font-size: initial;
			}
		</style>
		<table class="form-table">
			<?php $this->generate_settings_html(); ?>
		</table>
		<?php
	}

	/**
	 * Check if this gateway is enabled and available in the user's country
	 *
	 * @access public
	 * @return boolean
	 */
	public function is_available() {
		global $woocommerce;
		if ( 'yes' === $this->enabled ) :
			// Cart totals check - Lower threshold.
			if ( ! is_admin() && '' !== $this->lower_threshold && isset( $woocommerce->cart->total ) ) {
				if ( $woocommerce->cart->total < $this->lower_threshold ) {
					return false;
				}
			}
			// Cart totals check - Upper threshold.
			if ( ! is_admin() && '' !== $this->upper_threshold && isset( $woocommerce->cart->total ) ) {
				if ( $woocommerce->cart->total > $this->upper_threshold ) {
					return false;
				}
			}
			// Only activate the payment gateway if the customers country is the same as the filtered shop country.
			if ( ! is_admin() && isset( $woocommerce->customer ) ) {
				if ( $woocommerce->customer->get_billing_country() !== $this->afterpay_country ) {
					return false;
				}
			}
			// Check if variable with ip's contains the ip of the client.
			if ( ! is_admin() && '' !== $this->ip_restriction ) {
				if ( strpos( $this->ip_restriction, $this->get_afterpay_client_ip() ) === false ) {
					return false;
				}
			}
			// Check if the shipping method is not in the list of excluded payment methods.
			if ( ! is_admin() && isset( $this->exclude_shipping_methods ) && isset( WC()->session ) ) {
				$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
				$chosen_shipping_method  = explode( ':', $chosen_shipping_methods[0] );
				$chosen_shipping_method  = $chosen_shipping_method[0];

				$excluded_shipping_methods = $this->exclude_shipping_methods;

				if ( is_array( $excluded_shipping_methods ) && in_array( $chosen_shipping_method, $excluded_shipping_methods ) ) {
					return false;
				}
			}
			return true;
		endif;
		return false;
	}

	/**
	 * Render selling points box
	 *
	 * @access public
	 * @return void
	 */
	public function get_selling_points() {
		if ( strpos( $this->id, 'invoice') && strpos( $this->id, 'b2b') == false ) {
			$selling_points = 	'<div class="afterpay_selling_points_block">
									<ul>
										<li>' . __( 'Pay within 14 days. No fees.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
										<li>' . __( 'Manage all your payments in the Riverty app.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
										<li>' . __( 'Receive your order before payment.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
									</ul>
								</div>';
		}
		elseif ( strpos( $this->id, 'directdebit') ) {
			$selling_points = 	'<div class="afterpay_selling_points_block">
									<ul>
										<li>' . __( 'Easy and secure', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
										<li>' . __( 'Your payment will be withdrawn automatically.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
										<li>' . str_replace('%1', '.', __( 'Manage all your payments in the Riverty app%1', 'afterpay-payment-gateway-for-woocommerce' ) ) . '</li>
									</ul>
								</div>';
		}
		elseif ( strpos( $this->id, 'installments') ) {
			$number_of_installments_array = array_unique( array_column( $this->available_installment_plans, 'numberOfInstallments' ) );
			sort($number_of_installments_array);
			$installment_amounts = '';

			foreach( $number_of_installments_array as $installment){

				if ( count($number_of_installments_array) >= 2 && $number_of_installments_array[count($number_of_installments_array) - 2] == $installment ) {
					$installment_amounts .= $installment . ' ' . __( 'or', 'afterpay-payment-gateway-for-woocommerce' ) . ' ';
				}
				elseif ( $number_of_installments_array[count($number_of_installments_array) - 1] == $installment ) {
					$installment_amounts .= $installment;
				}
				else {
					$installment_amounts .= $installment . ', ';
				}
			}

			$lowest_installment_amount = min( array_column( $this->available_installment_plans, 'installmentAmount' ) );
			$lowest_installment_amount = number_format($lowest_installment_amount,2,'.','') . ' ' . get_woocommerce_currency_symbol();
			$selling_points = 	'<div class="afterpay_selling_points_block">
									<ul>
										<li>' . str_replace( ['%1','%2'], [$installment_amounts,$lowest_installment_amount], __( 'Pay in %1 instalments, starting at %2 per month.', 'afterpay-payment-gateway-for-woocommerce' ) ) . '</li>
										<li>' . __( 'Manage all your payments in the Riverty app.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
										<li>' . __( 'Receive your order before payment.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
									</ul>
								</div>';
		}
		elseif ( strpos( $this->id, 'campaign') ) {
			$selling_points = 	'<div class="afterpay_selling_points_block">
									<ul>
										<li>' . __( 'Manage all your payments in the Riverty app.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
										<li>' . __( 'Receive your order before payment.', 'afterpay-payment-gateway-for-woocommerce' ) . '</li>
									</ul>
								</div>';
		}
		else {
			$selling_points = '';
		}

		echo $selling_points;
	}

	/**
	 * Check whether introduction text will be visible or not based on payment fields
	 *
	 * @access public
	 * @return boolean
	 */
	public function can_show_introduction_text() {
		if ( 'yes' === $this->show_phone || 'yes' === $this->show_dob || true === $this->show_ssn || true === $this->show_bankaccount || true === $this->show_companyname ) {
			return true;
		}
		return false;
	}

	/**
	 * Create introduction text for required payment fields
	 *
	 * @access public
	 * @return string
	 */
	public function get_introduction_text( $change_order = false ) {
		$introduction_text = '';
		$afterpay_required_payment_fields = array();

		if ( $change_order )
		{
			if ('yes' === $this->show_dob ) {
				$afterpay_required_payment_fields[] = __( 'date of birth', 'afterpay-payment-gateway-for-woocommerce') . ', ';
			}

			if ( $this->show_bankaccount ) {
				$afterpay_required_payment_fields[] = __( 'bank account number', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
			}
		}
		else
		{
			if ( $this->show_bankaccount ) {
				$afterpay_required_payment_fields[] = __( 'bank account number', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
			}

			if ('yes' === $this->show_dob ) {
				$afterpay_required_payment_fields[] = __( 'date of birth', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
			}
		}

		if ( $this->show_companyname ) {
			$afterpay_required_payment_fields[] = __( 'company name', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
		}

		if ( $this->show_coc ) {
			$nordics = [ 'DK','FI','NO','SE' ];
			if( in_array( $this->afterpay_country, $nordics ) ) {
				$afterpay_required_payment_fields[] = __( 'vat number', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
			}
			else {
				$afterpay_required_payment_fields[] = __( 'chamber of commerce number', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
			}
		}

		if ( $this->show_ssn ) {
			$afterpay_required_payment_fields[] = __( 'social security number', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
		}

		if ( 'yes' === $this->show_phone ) {
			$afterpay_required_payment_fields[] = __( 'phone number', 'afterpay-payment-gateway-for-woocommerce' ) . ', ';
		}

		if (count($afterpay_required_payment_fields) >= 2) {
			$and = ' ' . __( 'and', 'afterpay-payment-gateway-for-woocommerce' ) . ' ';
			$afterpay_required_payment_fields[count($afterpay_required_payment_fields) - 2] =  str_replace(', ', $and, $afterpay_required_payment_fields[count($afterpay_required_payment_fields) - 2]);
		}

		$afterpay_required_payment_fields[count($afterpay_required_payment_fields) - 1] = str_replace(', ', '.', $afterpay_required_payment_fields[count($afterpay_required_payment_fields) - 1]);

		foreach ( $afterpay_required_payment_fields as $index => $field ){
			if ( $index == array_key_first($afterpay_required_payment_fields) ) {
				$introduction_text .= ' ' . $field;
			}
			else {
				$introduction_text .= $field;
			}
		}

		$fields_specific_text = str_replace( [ '%1', '%2' ], [ $introduction_text, str_replace('.', ' ', $introduction_text) . 'in te vullen.' ], __( 'In order to guarantee a secure checkout, we need to verify your %1', 'afterpay-payment-gateway-for-woocommerce' ) );

		$introduction_text = 	'<div class="introduction_text_field">
									<label class="introduction_text_label">
										<span>' . __( 'Enter your credentials', 'afterpay-payment-gateway-for-woocommerce' ) . '</span>
									</label>
									<div>
										<span>' . $fields_specific_text . '</span>
									</div>
								</div>';

		return $introduction_text;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_bankaccount_placeholder( $country ) {
		switch ( $country ) {
			case "AT":
				$placeholder = "AT00 0000 0000 0000 0000";
			    break;
			case "BE":
				$placeholder = "BE00 0000 0000 0000";
			    break;
			case "DK":
				$placeholder = "DK00 0000 0000 0000 00";
			    break;
			case "FI":
				$placeholder = "FI00 0000 0000 0000 00";
				break;
			case "DE":
				$placeholder = "DE00 0000 0000 0000 0000 00";
				break;
			case "NL":
				$placeholder = "NL00 XXXX 0000 0000 00";
				break;
			case "NO":
				$placeholder = "NO00 0000 0000 000";
				break;
			case "SE":
				$placeholder = "SE00 0000 0000 0000 0000 0000";
				break;
			case "CH":
				$placeholder = "CH00 0000 0000 0000 0000 0";
				break;
			default:
			  $placeholder = "SS00 0000 0000 0000 0000 00";
		}

		return $placeholder;
	}

	/** 
	 * Render afterpay fields error messages if exists
	 * 
	 * @access public
	 * @return string
	 */
	public function get_error_message( $validation_messages ) {
		$error_message = null;
		if( ! empty( $validation_messages ) ) {
			foreach ( $validation_messages as $msg ) {
				$error_message .= $msg . '</br>';
			}
		}

		return $error_message;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_birthdate_placeholder() {
		$placeholder = __( 'DD/MM/YYYY', 'afterpay-payment-gateway-for-woocommerce' );
		return $placeholder;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_conditions_text() {
		if( strpos( $this->id, 'payinx' ) ) {
			$conditions_text = $this->terms_and_conditions_text;
		}
		else {
			if ( $this->customer_consent ) {
				$conditions_link = '<a href="'. $this->afterpay_invoice_terms . '">' . esc_html__( 'General Terms and Conditions', 'afterpay-payment-gateway-for-woocommerce' ) . '</a>';
				$privacy_link = '<a href="' . $this->afterpay_privacy_statement . '">' . esc_html__( 'Privacy Policy', 'afterpay-payment-gateway-for-woocommerce' ) . '</a>'; 
				$conditions_text = str_replace( ['%1','%2'], [$conditions_link,$privacy_link], esc_html__( 'I have read and accept the %1 for the Riverty payment method and the %2 of Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) );
			} 
			else {
				$conditions_link = '<a href="'. $this->afterpay_invoice_terms . '">' . esc_html__( 'General Terms and Conditions', 'afterpay-payment-gateway-for-woocommerce' ) . '</a>';
				$privacy_link = '<a href="' . $this->afterpay_privacy_statement . '">' . esc_html__( 'here', 'afterpay-payment-gateway-for-woocommerce' ) . '</a>';
				$conditions_text = str_replace( ['%1','%2'], [$conditions_link,$privacy_link], esc_html__( 'The %1 for the Riverty payment method apply. The Privacy Policy of Riverty can be found %2.', 'afterpay-payment-gateway-for-woocommerce' ) );
			}
		}

		return $conditions_text;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_terms_block() {
		if ( $this->customer_consent ) {
			$terms_block = '<p class="form-row validate-required riverty_terms" style="margin-top:10px">
								<input type="checkbox" class="input-checkbox" name="' . esc_attr( $this->id ) . '_terms" /><span style="margin-right: 3px" class="required">*</span>'
								. $this->get_conditions_text() .
							'</p>
							<script type="text/javascript">
								jQuery( document ).ready(function($) {
									$(".riverty_terms a").click(function(){
										window.open(this.href);
										return false;
										});
								});
							</script>';
		}
		else {
			$terms_block = '<p class="form-row afterpay_terms_and_conditions riverty_terms" style="margin-top:10px">'
								. $this->get_conditions_text() .
							'</p>
							<script type="text/javascript">
								jQuery( document ).ready(function($) {
									$(".riverty_terms a").click(function(){
										window.open(this.href);
										return false;
									  });
								});
							</script>';
		}
		
		return $terms_block;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_apikey_validation_button($connection_mode) {
		$apikey_validation_button = '<div>
										<button class="apikey_validation_button" id="' . esc_attr( $this->id ) . '_' . $connection_mode . '_apikey_validation_button">Test API key</button>
										<span class="check_mark" id="' . esc_attr( $this->id ) . '_' . $connection_mode . '_apikey_validation_check_mark"></span>
										<span id="' . esc_attr( $this->id ) . '_' . $connection_mode . '_apikey_validation_response"></span>
										<span class="api_key_validation_loader" id="' . esc_attr( $this->id ) . '_' . $connection_mode . '_apikey_validation_loader">
											<img style="height: 15px; width: 25px" src="' . plugin_dir_url(__FILE__) . '/images/spinner.gif">
										</span>
									 </div>';

		return $apikey_validation_button;
	}

	/**
	 * check if profile tracking input is required or not 
	 * 
	 * @return bool
	 */
	public function is_tracking_required() {
		if ( $this->tracking_active == 'mandatory' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return string
	 */
	private function get_tracking_conditions_link($payment_method, $country) {
		$current_locale			    = strtolower( substr( get_locale(), 0, 2 ) );
		$merchantid                 = ( '' !== $this->merchantid ) ? $this->merchantid : 'default';
		$country_code            	= ( $current_locale == 'de' ) ? $country . '_de/' : $country . '_en/';
		$tracking_conditions_link   = 'https://documents.riverty.com/terms_conditions/payment_methods/' . $payment_method . '/' . $country_code . $merchantid;
		return $tracking_conditions_link;
	}

	/**
	 * @return string
	 */
	private function get_tracking_privacy_link($country) {
		$current_locale	         = strtolower( substr( get_locale(), 0, 2 ) );
		$country_code            = ( $current_locale == 'de' ) ? $country . '_de' : $country . '_en';
		$checkout_method         = strpos( $this->id, 'b2b' ) ? 'b2b_checkout/' : 'checkout/';
		$tracking_privacy_link   = 'https://documents.riverty.com/privacy_statement/' . $checkout_method . $country_code;
		return $tracking_privacy_link;
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_tracking_text($payment_method, $country) {
		$tracking_conditions_link = '<a href="' . $this->get_tracking_conditions_link($payment_method, $country) . '" target="_blank">' . esc_html__( 'Terms and Conditions for the selected Riverty payment method.', 'afterpay-payment-gateway-for-woocommerce' ) . '</a>';
		$tracking_data_protection_link = '<a href="' . $this->get_tracking_privacy_link($country) . '" target="_blank">' . esc_html__( "Riverty's data protection information", 'afterpay-payment-gateway-for-woocommerce' ) . '</a>'; 
		$tracking_text = str_replace( ['%1','%2'], [$tracking_conditions_link,$tracking_data_protection_link], esc_html__( 'I agree to the %1 I have taken note of %2 and I agree that Riverty will take Merchant and Buyer protection measures (e.g. by using end device information by setting cookies).', 'afterpay-payment-gateway-for-woocommerce' ) );
		return $tracking_text;
	}

	/**
	 * @return string
	 */
	public function get_profile_tracking_session_id() {
		$profile_tracking_session_id = substr(substr(md5($this->tracking_id), -6) . '-' . mt_rand(1000, 9999), 0, 11);
		$this->tracking_session_id = $profile_tracking_session_id;
		return $profile_tracking_session_id;
	}

	/**
	 * @return string
	 */
	public function get_b2b_statement_link($type) {
		$current_locale = strtolower( substr( get_locale(), 0, 2 ) );
		$merchantid = ( '' !== $this->merchantid ) ? $this->merchantid . '/' : 'default/';
		$country_code = ( $current_locale == 'nl' ) ? 'nl_nl/' : 'nl_en/';
		if( $type == 'privacy' ) {
			$statement_link = 'https://documents.riverty.com/privacy_statement/b2b_checkout/' . $country_code;
		}
		else {
			$statement_link = 'https://documents.riverty.com/terms_conditions/payment_methods/b2b_invoice/'. $country_code . $merchantid;
		}
		return $statement_link;
	}

	/**
	 * @return string
	 */
	public function validate_dob($date_of_birth) {
		$dob_validation_message = '';
		$date_object = DateTime::createFromFormat('d/m/Y', $date_of_birth);
		$today = new DateTime();
		$minimum_age = 18;
		$minimum_date = $today->sub(new DateInterval('P'.$minimum_age.'Y'));
		if ( empty($date_of_birth) ) {
			$dob_validation_message = __( 'Birthday is a required field', 'afterpay-payment-gateway-for-woocommerce' ) ;
		}
		else if ( !($date_object && $date_object->format('d/m/Y') === $date_of_birth) ) {
			$dob_validation_message = __( 'Please make sure that your date of birth is correct.', 'afterpay-payment-gateway-for-woocommerce' );
		}
		else if ( !($date_object && $date_object <= $minimum_date) ) {
			$dob_validation_message = __( 'To make use of the Riverty payment method, your age has to be 18 years or older.', 'afterpay-payment-gateway-for-woocommerce' );
		}	
		return $dob_validation_message;
	}

	/**
	 * @return string
	 */
	public function get_code_of_conduct() {
		$code_of_conduct = '';
		if( $this->afterpay_country == 'NL' ) {
			if( isset( $this->code_of_conduct_text ) ) {
				$code_of_conduct = $this->code_of_conduct_text;
			}
			else {
				$code_of_conduct = '<p class="form-row">
										<label>' . esc_html__( "You must be at least 18+ to use this service. If you pay on time, you will avoid additional costs and ensure that you can use Riverty services again in the future. By continuing, you accept the Terms and Conditions and confirm that you have read the Privacy Statement and Cookie Statement.", "afterpay-payment-gateway-for-woocommerce" ) . '</label>
									</p>';
			}
		}
		return $code_of_conduct;					
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

		<fieldset class="riverty_fieldset">
			<?php if ( $this->can_show_introduction_text() ) : ?>
				<?php echo $this->get_introduction_text(true); ?>
			<?php endif; ?>
			<p class="form-row validate-required">
				<label for="<?php echo esc_attr( $this->id ); ?>_dob"><strong><?php esc_html_e( 'Date of birth', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<script type="text/javascript">
					jQuery( document ).ready(function($) {
						$( "#<?php echo esc_attr( $this->id ); ?>_dob" ).mask("00/00/0000", {placeholder: "<?php echo $this->get_birthdate_placeholder() ?>"});
					});
				</script>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_dob" id="<?php echo esc_attr( $this->id ); ?>_dob" />
			</p>
			<div class="clear"></div>
			<?php if ( $this->show_bankaccount ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required">
				<label for="afterpay_bankaccount"><strong><?php esc_html_e( 'Bankaccount', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_bankaccount" id="<?php echo esc_attr( $this->id ); ?>_bankaccount" />
				<script type="text/javascript">
					jQuery( document ).ready(function($) {
						//bankaccount input masking
						$( "#<?php echo esc_attr( $this->id ); ?>_bankaccount" ).mask("<?php echo str_replace( 'XXXX', 'SSSS', $this->get_bankaccount_placeholder( $this->afterpay_country  ) ) ?>", {placeholder: "<?php echo $this->get_bankaccount_placeholder( $this->afterpay_country  ) ?>"});
					});
				</script>
			</p>
			<?php endif; ?>
			<?php if ( 'yes' === $this->show_phone ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required validate-phone">
				<label for="<?php echo esc_attr( $this->id ); ?>_phone"><strong><?php esc_html_e( 'Phone number', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<input type="input" class="input-text" name="<?php echo esc_attr( $this->id ); ?>_phone" />
			</p>
			<?php endif; ?>
			<?php if ( 'yes' === $this->show_gender ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required">
				<label for="<?php echo esc_attr( $this->id ); ?>_gender"><strong><?php esc_html_e( 'Gender', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
				<select name="<?php echo esc_attr( $this->id ); ?>_gender">
						<option value="M"><?php esc_html_e( 'Male', 'afterpay-payment-gateway-for-woocommerce' ); ?></option>
						<option value="V" selected><?php esc_html_e( 'Female', 'afterpay-payment-gateway-for-woocommerce' ); ?></option>
				</select>
			</p>
			<?php endif; ?>
			<?php if ( 'yes' === $this->show_termsandconditions ) : ?>
			<div class="clear"></div>
			<?php echo $this->get_terms_block(); ?>
			<?php endif; ?>
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
		$dob_validation_message = $this->validate_dob($_POST[ esc_attr( $this->id ) . '_dob' ]);

		// Check if date of birth is not empty and validate it in case there is a value added by user
		if ( !empty( $dob_validation_message ) ) {
			array_push( $validation_messages, $dob_validation_message );
		}
		// Check if bankaccount is set, if this option is enabled.
		if ( $this->show_bankaccount && empty( $_POST[ $this->id . '_bankaccount' ] ) ) {
			array_push( $validation_messages, __( 'Bankaccount is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		// Check if phonenumber is set, if this option is enabled.
		if ( 'yes' === $this->show_phone && empty( $_POST[ $this->id . '_phone' ] ) ) {
			array_push( $validation_messages, __( 'Phone number is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		// Check if gender is set, if this option is enabled.
		if ( 'yes' === $this->show_gender && empty( $_POST[ $this->id . '_gender' ] ) ) {
			array_push( $validation_messages, __( 'Gender is a required field', 'afterpay-payment-gateway-for-woocommerce' ) );
		}
		// Check if terms and conditions are set, if this option is enabled.
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

		return true;
	}

	/**
	 * Process the payment and return the result
	 *
	 * @access public
	 * @param int $order_id Order ID.
	 * @return array
	 **/
	public function process_payment( $order_id ) {
		global $woocommerce;
		$_tax  = new WC_Tax();
		$order = wc_get_order( $order_id );
		require_once __DIR__ . '/vendor/autoload.php';

		// Create AfterPay object.
		$afterpay = new \Afterpay\Afterpay();

		// Get values from afterpay form on checkout page.
		// Set form fields per payment option.
		// Collect the dob.
		$afterpay_dob = '';
		// Check if it is the old or new way of requesting the date of birth.
		if ( isset( $_POST[ $this->id . '_dob' ] ) ) {
			$order_dob       = wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_dob' ] ) ) );
			$order_dob_array = explode( '/', $order_dob );
			if ( is_array( $order_dob_array ) ) {
				$afterpay_dob = $order_dob_array[2] . '-' . $order_dob_array[1] . '-' . $order_dob_array[0] . 'T00:00:00';
			} else {
				$afterpay_dob = '';
			}
		}
		if ( 'B2B' === $this->order_type ) {
			$afterpay_dob = '1970-01-01T00:00:00';
		}

		$afterpay_bankacount = isset( $_POST[ $this->id . '_bankaccount' ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_bankaccount' ] ) ) ) : '';

		$afterpay_phone = isset( $_POST[ $this->id . '_phone' ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_phone' ] ) ) ) : $order->get_billing_phone();

		if ( 'B2B' === $this->order_type ) {
			$afterpay_cocnumber   = isset( $_POST[ $this->id . '_cocnumber' ] )
				? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_cocnumber' ] ) ) ) : '';
			$afterpay_companyname = isset( $_POST[ $this->id . '_companyname' ] )
				? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_companyname' ] ) ) ) : '';
		}

		// Split address into House number and House extension for NL customers.
		$afterpay_billing_address_1        = $order->get_billing_address_1();
		$afterpay_billing_address_2        = $order->get_billing_address_2();
		$afterpay_billing_address          = trim( $afterpay_billing_address_1 . ' ' . $afterpay_billing_address_2 );
		$splitted_address                  = $this->split_afterpay_address( $afterpay_billing_address );
		$afterpay_billing_address          = $splitted_address[0];
		$afterpay_billing_house_number     = $splitted_address[1];
		$afterpay_billing_house_extension  = $splitted_address[2];
		$afterpay_shipping_address_1       = $order->get_shipping_address_1();
		$afterpay_shipping_address_2       = $order->get_shipping_address_2();
		$afterpay_shipping_address         = trim( $afterpay_shipping_address_1 . ' ' . $afterpay_shipping_address_2 );
		$splitted_address                  = $this->split_afterpay_address( $afterpay_shipping_address );
		$afterpay_shipping_address         = $splitted_address[0];
		$afterpay_shipping_house_number    = $splitted_address[1];
		$afterpay_shipping_house_extension = $splitted_address[2];

		// If special field is being used for housenumber then use that field.
		if ( '' !== $this->settings['use_custom_housenumber_field'] ) {
			$afterpay_billing_house_number =
			isset( $_POST[ 'billing_' . $this->settings['use_custom_housenumber_field'] ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ 'billing_' . $this->settings['use_custom_housenumber_field'] ] ) ) )
			: $afterpay_billing_house_number;

			$afterpay_shipping_house_number =
			isset( $_POST[ 'shipping_' . $this->settings['use_custom_housenumber_field'] ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ 'shipping_' . $this->settings['use_custom_housenumber_field'] ] ) ) )
			: $afterpay_shipping_house_number;
		}

		// If special field is being used for housenumber addition then use that field.
		if ( '' !== $this->settings['use_custom_housenumber_addition_field'] ) {
			$afterpay_billing_house_extension =
			isset( $_POST[ 'billing_' . $this->settings['use_custom_housenumber_addition_field'] ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ 'billing_' . $this->settings['use_custom_housenumber_addition_field'] ] ) ) )
			: $afterpay_billing_house_extension;

			$afterpay_shipping_house_extension =
			isset( $_POST[ 'shipping_' . $this->settings['use_custom_housenumber_addition_field'] ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ 'shipping_' . $this->settings['use_custom_housenumber_addition_field'] ] ) ) )
			: $afterpay_shipping_house_extension;
		}

		// Store afterpay specific form values in order as post meta.
		$order->update_meta_data( 'afterpay_dob', $afterpay_dob );
		$order->save();

		// Get connection mode.
		$afterpay_mode = $this->get_connection_mode();

		$authorisation['merchantid']  = $this->settings['merchantid'];
		$authorisation['portfolioid'] = $this->settings['portfolioid'];
		$authorisation['password']    = $this->settings['password'];

		// Create the order.
		// Cart Contents.
		if ( count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				// Get product to retrieve sku or product id.
				$_product = $item->get_product();
				// Get SKU or product id.
				if ( $_product->get_sku() ) {
					$sku = $_product->get_sku();
				} else {
					$sku = $_product->get_id();
				}
				$item_tax_category = $this->get_afterpay_tax_class( $order->get_line_total( $item, false ), $order->get_line_tax( $item ) );

				// apply_filters to item price so we can filter this if needed.
				$afterpay_item_price_including_tax = $order->get_item_total( $item, true );
				$item_price                        = apply_filters( 'afterpay_item_price_including_tax', $afterpay_item_price_including_tax );
				$item_price                        = round( $item_price * 100, 0 );
				$afterpay->create_order_line( $sku, $item['name'], $item['qty'], $item_price, $item_tax_category );
			}
		}

		// Set the shipping data.
		if ( $order->get_shipping_total() > 0 ) {
			// We manually calculate the shipping tax percentage here.
			$calculated_shipping_tax_percentage = ( $order->get_shipping_tax() / $order->get_shipping_total() ) * 100;
			$calculated_shipping_tax_decimal    = ( $order->get_shipping_tax() / $order->get_shipping_total() ) + 1;
			$shipping_tax_rate                  = $this->get_afterpay_tax_class( $order->get_total_shipping(), $order->get_shipping_tax() );

			// apply_filters to Shipping so we can filter this if needed.
			$afterpay_shipping_price_including_tax = $order->get_shipping_total() * $calculated_shipping_tax_decimal;
			$shipping_price                        = apply_filters( 'afterpay_shipping_price_including_tax', $afterpay_shipping_price_including_tax );
			$shipping_sku                          = __( 'SHIPPING', 'afterpay-payment-gateway-for-woocommerce' );
			$shipping_description                  = __( 'Shipping', 'afterpay-payment-gateway-for-woocommerce' );
			$shipping_price                        = round( $shipping_price * 100, 0 );
			$afterpay->create_order_line( $shipping_sku, $shipping_description, 1, $shipping_price, $shipping_tax_rate );
		}

		$fees = $woocommerce->cart->get_fees();

		if ( count( $fees ) > 0 ) {
			foreach ( $fees as $fee ) {
				$fee_sku         = __( 'PAYMENTCOST', 'afterpay-payment-gateway-for-woocommerce' );
				$fee_description = $fee->name;
				$fee_price       = round( ( $fee->amount + $fee->tax ) * 100 );
				$afterpay->create_order_line( $fee_sku, $fee_description, 1, $fee_price, 1 );
			}
		}

		// Check value for gender.
		$afterpay_gender             = '';
		$payment_methods_with_gender = array(
			'afterpay_openinvoice',
			'afterpay_directdebit',
			'afterpay_belgium',
		);

		if ( in_array( $this->id, $payment_methods_with_gender, true ) ) {
			$afterpay_gender = isset( $_POST[ $this->id . '_gender' ] )
			? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_gender' ] ) ) ) : '';
		}

		$aporder['billtoaddress']['city']                           = utf8_decode( $order->get_billing_city() );
		$aporder['billtoaddress']['housenumber']                    = utf8_decode( $afterpay_billing_house_number );
		$aporder['billtoaddress']['housenumberaddition']            = utf8_decode( $afterpay_billing_house_extension );
		$aporder['billtoaddress']['isocountrycode']                 = $order->get_billing_country();
		$aporder['billtoaddress']['postalcode']                     = utf8_decode( $order->get_billing_postcode() );
		$aporder['billtoaddress']['referenceperson']['dob']         = $afterpay_dob;
		$aporder['billtoaddress']['referenceperson']['email']       = $order->get_billing_email();
		$aporder['billtoaddress']['referenceperson']['gender']      = $afterpay_gender;
		$aporder['billtoaddress']['referenceperson']['initials']    = utf8_decode( $order->get_billing_first_name() );
		$aporder['billtoaddress']['referenceperson']['isolanguage'] = $this->afterpay_language;
		$aporder['billtoaddress']['referenceperson']['lastname']    = utf8_decode( $order->get_billing_last_name() );
		$aporder['billtoaddress']['referenceperson']['phonenumber'] = $afterpay_phone;
		$aporder['billtoaddress']['streetname']                     = utf8_decode( $afterpay_billing_address );

		// Shipping address.
		if ( $order->get_shipping_method() === '' ) {
			// Use billing address if Shipping is disabled in Woocommerce.
			$aporder['shiptoaddress'] = $aporder['billtoaddress'];
		} else {
			$aporder['shiptoaddress']['city']                           = utf8_decode( $order->get_shipping_city() );
			$aporder['shiptoaddress']['housenumber']                    = utf8_decode( $afterpay_shipping_house_number );
			$aporder['shiptoaddress']['housenumberaddition']            = utf8_decode( $afterpay_shipping_house_extension );
			$aporder['shiptoaddress']['isocountrycode']                 = $order->get_shipping_country();
			$aporder['shiptoaddress']['postalcode']                     = utf8_decode( $order->get_shipping_postcode() );
			$aporder['shiptoaddress']['referenceperson']['dob']         = $afterpay_dob;
			$aporder['shiptoaddress']['referenceperson']['email']       = $order->get_billing_email();
			$aporder['shiptoaddress']['referenceperson']['gender']      = $afterpay_gender;
			$aporder['shiptoaddress']['referenceperson']['initials']    = utf8_decode( $order->get_shipping_first_name() );
			$aporder['shiptoaddress']['referenceperson']['isolanguage'] = $this->afterpay_language;
			$aporder['shiptoaddress']['referenceperson']['lastname']    = utf8_decode( $order->get_shipping_last_name() );
			$aporder['shiptoaddress']['referenceperson']['phonenumber'] = $afterpay_phone;
			$aporder['shiptoaddress']['streetname']                     = utf8_decode( $afterpay_shipping_address );
		}

		// Check if shipping method 'local_pickup' is used, if so use the location of the store.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping_method  = explode( ':', $chosen_shipping_methods[0] );
		$chosen_shipping_method  = $chosen_shipping_method[0];

		if ( 'local_pickup' == $chosen_shipping_method ) {

			$aporder['shiptoaddress']['referenceperson']['initials'] = 'P';
			$aporder['shiptoaddress']['referenceperson']['lastname'] = utf8_decode( 'Pickup ' . get_bloginfo( 'name' ) );

			$store_address_1                                 = get_option( 'woocommerce_store_address' );
			$store_address_2                                 = get_option( 'woocommerce_store_address_2' );
			$store_address                                   = trim( $store_address_1 . ' ' . $store_address_2 );
			$splitted_address                                = $this->split_afterpay_address( $store_address );
			$afterpay_shipping_address                       = $splitted_address[0];
			$afterpay_shipping_house_number                  = $splitted_address[1];
			$afterpay_shipping_house_extension               = substr( $splitted_address[2], 0, 10 );
			$aporder['shiptoaddress']['streetname']          = utf8_decode( $afterpay_shipping_address );
			$aporder['shiptoaddress']['housenumber']         = utf8_decode( $afterpay_shipping_house_number );
			$aporder['shiptoaddress']['housenumberaddition'] = utf8_decode( $afterpay_shipping_house_extension );

			$store_city                       = get_option( 'woocommerce_store_city' );
			$aporder['shiptoaddress']['city'] = utf8_decode( $store_city );

			$store_postcode                         = get_option( 'woocommerce_store_postcode' );
			$aporder['shiptoaddress']['postalcode'] = utf8_decode( $store_postcode );

			// The country/state
			$store_raw_country = get_option( 'woocommerce_default_country' );

			// Split the country/state
			$split_country = explode( ':', $store_raw_country );

			// Country and state separated:
			$store_country                              = $split_country[0];
			$aporder['shiptoaddress']['isocountrycode'] = utf8_decode( $store_country );
		}

		// Shipping compatibility checks
		$shipping_compatibility_checked = false;

		// Start compatibility with SendCloud.
		$shipping_items = $order->get_items( 'shipping' );
		$order_shipping = reset( $shipping_items );
		if ( is_object( $order_shipping ) ) {
			$shipping_method = $order_shipping->get_method_id();
			if (
				strpos( $shipping_method, 'service_point_shipping_method' ) !== false
				&& $order->meta_exists( 'sendcloudshipping_service_point_meta' )
			) {
				$sendcloud_meta_data = $order->get_meta( 'sendcloudshipping_service_point_meta' );
				if ( isset( $sendcloud_meta_data['extra'] ) ) {
					$sendcloud_shipping_data                                 = explode( '|', $sendcloud_meta_data['extra'] );
					$sendcloud_shipping_name                                 = isset( $sendcloud_shipping_data[0] ) ? $sendcloud_shipping_data[0] : '';
					$aporder['shiptoaddress']['referenceperson']['initials'] = 'S';
					$aporder['shiptoaddress']['referenceperson']['lastname'] = utf8_decode( $sendcloud_shipping_name );
					$sendcloud_address                                       = $this->split_afterpay_address( $sendcloud_shipping_data[1] );
					$sendcloud_shipping_street                               = isset( $sendcloud_address[0] ) ? $sendcloud_address[0] : '';
					$aporder['shiptoaddress']['streetname']                  = utf8_decode( $sendcloud_shipping_street );
					$sendcloud_shipping_house_number                         = isset( $sendcloud_address[1] ) ? $sendcloud_address[1] : '';
					$aporder['shiptoaddress']['housenumber']                 = $sendcloud_shipping_house_number;
					$sendcloud_shipping_house_extension                      = isset( $sendcloud_address[2] ) ? $sendcloud_address[2] : '';
					$aporder['shiptoaddress']['housenumberaddition']         = utf8_decode( $afterpay_shipping_house_extension );
					$sendcloud_shipping_pcandcity                            = explode( ' ', $sendcloud_shipping_data['2'] );
					$sendcloud_shipping_postalcode                           = isset( $sendcloud_shipping_pcandcity[0] ) ? $sendcloud_shipping_pcandcity[0] : '';
					$aporder['shiptoaddress']['postalcode']                  = utf8_decode( $sendcloud_shipping_postalcode );
					$sendcloud_shipping_city                                 = isset( $sendcloud_shipping_pcandcity[1] ) ? $sendcloud_shipping_pcandcity[1] : '';
					$aporder['shiptoaddress']['city']                        = utf8_decode( $sendcloud_shipping_city );
					$shipping_compatibility_checked                          = true;
				}
			}
		}
		// End compatibility with SendCloud.
		// Start compatibility with PostNL.
		// Check if the order is sent with postnl.
		if (
			 $order->meta_exists( '_postnl_delivery_options' )
			&& $shipping_compatibility_checked == false
		) {

			// Get the PostNL meta data.
			$postnl_meta_data = $order->get_meta( '_postnl_delivery_options' );

			// Check if the pickup points of PostNL are used.
			if ( $postnl_meta_data !== '' ) {

				// Check if the pickup points of PostNL are used.
				if ( isset( $postnl_meta_data['location'] ) ) {
					$shipping_compatibility_checked == true;
					$location_name                = 'POSTNL ' . $postnl_meta_data['location'];
					$postnl_shipping_street       = isset( $postnl_meta_data['street'] ) ? $postnl_meta_data['street'] : '';
					$postnl_shipping_house_number = isset( $postnl_meta_data['number'] ) ? $postnl_meta_data['number'] : '';
					$postnl_shipping_postalcode   = isset( $postnl_meta_data['postal_code'] ) ? $postnl_meta_data['postal_code'] : '';
					$postnl_shipping_city         = isset( $postnl_meta_data['city'] ) ? $postnl_meta_data['city'] : '';
					$aporder['shiptoaddress']['referenceperson']['initials'] = 'P';
					$aporder['shiptoaddress']['referenceperson']['lastname'] = utf8_decode( $location_name );
					$aporder['shiptoaddress']['streetname']                  = utf8_decode( $postnl_shipping_street );
					$aporder['shiptoaddress']['housenumber']                 = $postnl_shipping_house_number;
					$aporder['shiptoaddress']['postalcode']                  = utf8_decode( $postnl_shipping_postalcode );
					$aporder['shiptoaddress']['city']                        = utf8_decode( $postnl_shipping_city );
				} elseif ( @unserialize( $postnl_meta_data ) !== false ) {
					// The serialized data from PostNL is protected, so a ReflectionClass is needed to get the proper data.
					$postnl_meta_data  = unserialize( $postnl_meta_data );
					$postnl_reflection = new ReflectionClass( $postnl_meta_data );
					if ( $postnl_reflection->hasProperty( 'pickupLocation' ) ) {
						$postnl_pickup_location = $postnl_reflection->getProperty( 'pickupLocation' );
						$postnl_pickup_location->setAccessible( true );
						$postnl_pickup_location   = $postnl_pickup_location->getValue( $postnl_meta_data );
						$postnl_pickup_reflection = new ReflectionClass( $postnl_pickup_location );
						if ( $postnl_pickup_reflection->hasProperty( 'location_name' ) ) {
							$postnl_location_name = $postnl_pickup_reflection->getProperty( 'location_name' );
							$postnl_location_name->setAccessible( true );
							$location_name = 'POSTNL ' . $postnl_location_name->getValue( $postnl_pickup_location );
							$aporder['shiptoaddress']['referenceperson']['initials'] = 'P';
							$aporder['shiptoaddress']['referenceperson']['lastname'] = utf8_decode( $location_name );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'street' ) ) {
							$postnl_street = $postnl_pickup_reflection->getProperty( 'street' );
							$postnl_street->setAccessible( true );
							$aporder['shiptoaddress']['streetname'] = utf8_decode( $postnl_street->getValue( $postnl_pickup_location ) );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'number' ) ) {
							$postnl_number = $postnl_pickup_reflection->getProperty( 'number' );
							$postnl_number->setAccessible( true );
							$aporder['shiptoaddress']['housenumber'] = $postnl_number->getValue( $postnl_pickup_location );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'postal_code' ) ) {
							$postnl_postal_code = $postnl_pickup_reflection->getProperty( 'postal_code' );
							$postnl_postal_code->setAccessible( true );
							$aporder['shiptoaddress']['postalcode'] = utf8_decode( $postnl_postal_code->getValue( $postnl_pickup_location ) );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'city' ) ) {
							$postnl_city = $postnl_pickup_reflection->getProperty( 'city' );
							$postnl_city->setAccessible( true );
							$aporder['shiptoaddress']['city'] = utf8_decode( $postnl_city->getValue( $postnl_pickup_location ) );
						}
					}
				}
			}
		}
		// End compatibility with PostNL.
		// Start compatibility with MyParcel.
		// If PostNL Pickup points are used, use location from pickup point as address data.
		if (
			$order->meta_exists( '_myparcel_delivery_options' )
			&& $shipping_compatibility_checked == false
		) {
			// Get the MyParcel meta data.
			$mpc_meta_delivery_options = $order->get_meta( '_myparcel_delivery_options' );
			if ( $mpc_meta_delivery_options !== null ) {
				$mpc_meta_data = json_decode( $mpc_meta_delivery_options );
				if ( $mpc_meta_data->isPickup === true ) {
					$mpc_pickup_name       = isset( $mpc_meta_data->pickupLocation->location_name ) ? $mpc_meta_data->pickupLocation->location_name : '';
					$mpc_pickup_street     = isset( $mpc_meta_data->pickupLocation->street ) ? $mpc_meta_data->pickupLocation->street : '';
					$mpc_pickup_number     = isset( $mpc_meta_data->pickupLocation->number ) ? $mpc_meta_data->pickupLocation->number : '';
					$mpc_pickup_postalcode = isset( $mpc_meta_data->pickupLocation->postal_code ) ? $mpc_meta_data->pickupLocation->postal_code : '';
					$mpc_pickup_city       = isset( $mpc_meta_data->pickupLocation->city ) ? $mpc_meta_data->pickupLocation->city : '';
					$aporder['shiptoaddress']['referenceperson']['initials'] = 'P';
					$aporder['shiptoaddress']['referenceperson']['lastname'] = utf8_decode( $mpc_pickup_name );
					$aporder['shiptoaddress']['streetname']                  = utf8_decode( $mpc_pickup_street );
					$aporder['shiptoaddress']['housenumber']                 = utf8_decode( $mpc_pickup_number );
					$aporder['shiptoaddress']['postalcode']                  = utf8_decode( $mpc_pickup_postalcode );
					$aporder['shiptoaddress']['city']                        = utf8_decode( $mpc_pickup_city );
				}
			}
		}
		// End compatibility with MyParcel.
		// Check if housenumber field is filled for shipping, else use housenumber of billing.
		if (
			'' === $aporder['shiptoaddress']['housenumber']
		) {
			$aporder['shiptoaddress']['housenumber']         = $aporder['billtoaddress']['housenumber'];
			$aporder['shiptoaddress']['housenumberaddition'] = $aporder['billtoaddress']['housenumberaddition'];
		}

		$aporder['ordernumber']       = $order->get_order_number();
		$aporder['bankaccountnumber'] = $afterpay_bankacount;
		$aporder['currency']          = 'EUR';
		$aporder['ipaddress']         = $this->get_afterpay_client_ip();

		if ( 'B2B' === $this->order_type ) {
			$aporder['company']['cocnumber']         = $afterpay_cocnumber;
			$aporder['company']['companyname']       = $afterpay_companyname;
			$aporder['person']['dob']                = $afterpay_dob;
			$aporder['person']['emailaddress']       = $order->get_billing_email();
			$aporder['person']['initials']           = utf8_decode( substr( $order->get_billing_first_name(), 0, 1 ) );
			$aporder['person']['isolanguage']        = 'NL';
			$aporder['person']['lastname']           = utf8_decode( $order->get_billing_last_name() );
			$aporder['person']['phonenumber1']       = $afterpay_phone;
			$aporder['billtoaddress']['isolanguage'] = 'NL';
			$aporder['shiptoaddress']['isolanguage'] = 'NL';
		}

		try {
			// Transmit all the specified data, from the steps above, to afterpay.
			$afterpay->set_order( $aporder, $this->order_type );
			$afterpay->do_request( $authorisation, $afterpay_mode, 'nl' );

			$this->send_afterpay_debug_mail( $afterpay );

			// Retreive response.
			if ( isset( $afterpay->order_result->return->statusCode ) ) {
				switch ( $afterpay->order_result->return->statusCode ) {
					case 'A':
						// If capturing is enabled, and way of capture is set.
						// to automatically after authorization, then capture the full order.
						$notification_mail = 'Order ' . $aporder['ordernumber'] . ' is accepted ';

						if (
							isset( $this->settings['captures'] )
							&& 'yes' === $this->settings['captures']
							&& isset( $this->settings['captures_way'] )
							&& 'auto_after_authorization' === $this->settings['captures_way']
						) {
							$order->add_order_note( __( 'Riverty payment completed.', 'afterpay-payment-gateway-for-woocommerce' ) );

							// Capture payment.
							$this->capture_afterpay_payment( null, $order );
							$notification_mail .= 'and captured by Riverty. The order can be processed.';
						}

						if (
							isset( $this->settings['captures'] )
							&& 'yes' === $this->settings['captures']
							&& isset( $this->settings['captures_way'] )
							&& 'auto_after_authorization' !== $this->settings['captures_way']
						) {
							// Add note that the order is not captured yet.
							$order->add_order_note( __( 'Riverty capture needed, since the Capture mode was set to(Based on Woocommerce Status) when the order was placed.', 'afterpay-payment-gateway-for-woocommerce' ) );
							$notification_mail .= 'by Riverty, but not yet captured. The order can be processed when the order is captured. The order will be captured when the order state is changed to: ' . $this->settings['captures_way_based_on_status'];
						}

						if (
							isset( $this->settings['captures'] )
							&& 'no' === $this->settings['captures']
						) {
							$notification_mail .= 'by Riverty. The order can be processed.';
						}

						// Payment complete.
						$order->payment_complete();

						// Send AfterPay notification mail
						if (
							isset( $this->settings['notification_mail'] )
							&& '' !== $this->settings['notification_mail']
						) {
							wp_mail( $this->settings['notification_mail'], 'Riverty Order Notification for order #' . $aporder['ordernumber'], $notification_mail );
						}

						// Remove cart.
						$woocommerce->cart->empty_cart();

						// Return thank you redirect.
						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $order ),
						);
					case 'P':
						$order->add_order_note( __( 'Riverty payment pending.', 'afterpay-payment-gateway-for-woocommerce' ) );

						// Payment complete.
						$order->update_status( 'on-hold', __( 'Awaiting Riverty payment', 'afterpay-payment-gateway-for-woocommerce' ) );

						// Remove cart.
						$woocommerce->cart->empty_cart();

						// Return thank you redirect.
						return array(
							'result'   => 'success',
							'redirect' => $afterpay->order_result->return->extrafields->valueField,
						);
					case 'W':
						// Order is denied, store it in a database.
						$order->add_order_note( esc_html__( 'Riverty payment denied.', 'afterpay-payment-gateway-for-woocommerce' ) );
						$order->add_order_note( esc_html__( $afterpay->order_result->return->messages->message, 'afterpay-payment-gateway-for-woocommerce' ) );

						$rejection_code   = ( isset( $afterpay->order_result->return->rejectCode ) ? $afterpay->order_result->return->rejectCode : 'fallback' );
						$rejection_result = \Afterpay\check_rejection_error( $rejection_code, strtolower( $this->get_validation_message_language() ) );
						wc_add_notice( $rejection_result['description'], 'error' );

						// Cancel order to make new order possible.
						WC()->session->set( 'order_awaiting_payment', false );
						$order->update_status( 'cancelled', '' );

						return;
				}
			} else {

				// Check for validation errors.
				if ( 2 === $afterpay->order_result->return->resultId ) {
					// Unknown response, store it in a database.
					$order->add_order_note( esc_html__( 'There is a problem with submitting this order to Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) );
					$validationmsg  = esc_html__( 'There is a problem with submitting this order to Riverty, please check the following issues: ', 'afterpay-payment-gateway-for-woocommerce' );
					$validationmsg .= '<ul>';
					if ( ! is_object( $afterpay->order_result->return->failures ) ) {
						foreach ( $afterpay->order_result->return->failures as $failure ) {
							$validationmsg .= '<li style="list-style: inherit">' . \Afterpay\check_validation_error( $failure->failure, $failure->fieldname, strtolower( $this->get_validation_message_language() ) ) . '</li>';
							$order->add_order_note( esc_html__( $value->description, 'afterpay-payment-gateway-for-woocommerce' ) );
						}
					} else {
						$failure        = $afterpay->order_result->return->failures;
						$validationmsg .= '<li style="list-style: inherit">' . \Afterpay\check_validation_error( $failure->failure, $failure->fieldname, strtolower( $this->get_validation_message_language() ) ) . '</li>';
					}
					$validationmsg .= '</ul>';

					wc_add_notice( $validationmsg, 'error' );
				} else {
					// Unknown response, store it in a database.
					$order->add_order_note( esc_html__( 'Unknown response from Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) );
					wc_add_notice( esc_html__( 'Unknown response from Riverty. Please contact our customer service', 'afterpay-payment-gateway-for-woocommerce' ), 'error' );

					// Cancel order to make new order possible.
					WC()->session->set( 'order_awaiting_payment', false );
					$order->update_status( 'cancelled', '' );
				}

				return;
			}
		} catch ( Exception $e ) {
			// The purchase was denied or something went wrong, print the message.
			// translators: %1$s: error message, %2$s: error code.
			wc_add_notice( sprintf( __( '%1$s (Error code: %2$s)', 'afterpay-payment-gateway-for-woocommerce' ), utf8_encode( $e->getMessage() ), $e->getCode() ), 'error' );
			return;
		}
	}

	/**
	 * Is called when the Status of an order is changed
	 *
	 * @param int      $id    The order id.
	 * @param string   $from  The order Status before the change.
	 * @param string   $to    The order Status after the change.
	 * @param resource $order The order object itself.
	 */
	public function order_status_change_callback( $id, $from, $to, $order ) {
		// If capture is enabled.
		if ( 'yes' === $this->captures ) {

			// If capture is based on status change.
			if ( 'based_on_status' === $this->captures_way ) {

				// If this order was made with this payment method.
				if ( $order->get_payment_method() === $this->id ) {

					// If the new status is the trigger status.
					if ( $to === $this->captures_way_based_on_status ) {

						// Capture payment.
						$this->capture_afterpay_payment( $id, $order );
					}
				}
			}
		}
	}

	/**
	 * Captures a payment for an order that has not yet been captured
	 *
	 * @param int      $id    The order ID.
	 * @param resource $order The order object itself.
	 */
	public function capture_afterpay_payment( $id, $order ) {
		require_once __DIR__ . '/vendor/autoload.php';

		try {
			// Get connection mode.
			$afterpay_mode = $this->get_connection_mode();

			// API Authorization.
			$authorisation['merchantid']  = $this->settings['merchantid'];
			$authorisation['portfolioid'] = $this->settings['portfolioid'];
			$authorisation['password']    = $this->settings['password'];

			$afterpay_capture = new \Afterpay\Afterpay();
			$afterpay_capture->set_ordermanagement( 'capture_full' );

			// Set up the additional information.
			$capture_details['invoicenumber'] = $order->get_order_number();
			$capture_details['ordernumber']   = $order->get_order_number();

			// Set the country.
			$capture_details['billtoaddress']['isocountrycode'] = $this->afterpay_country;

			// Add order total in cents.
			$capture_details['totalamount'] = $order->get_total() * 100;

			// Create the order object for order management (OM).
			$afterpay_capture->set_order( $capture_details, 'OM' );
			$afterpay_capture->do_request( $authorisation, $afterpay_mode, 'nl' );

			$this->send_afterpay_debug_mail( $afterpay_capture );

			if ( isset( $afterpay_capture->order_result->return->resultId ) ) {
				if ( 0 === $afterpay_capture->order_result->return->resultId ) {

					// Payment complete.
					$order->payment_complete();

					$order->add_order_note( __( 'Riverty payment completed.', 'afterpay-payment-gateway-for-woocommerce' ) );
					$order->add_order_note( __( 'Riverty payment captured.', 'afterpay-payment-gateway-for-woocommerce' ) );
				} else {
					$order->add_order_note( __( 'Problem with capturing order.', 'afterpay-payment-gateway-for-woocommerce' ) );
				}
			}
		} catch ( Exception $e ) {
			// The purchase was denied or something went wrong, print the message.
			// translators: %1$s: error message, %2$s: error code.
			wc_add_notice( sprintf( __( '%1$s (Error code: %2$s)', 'afterpay-payment-gateway-for-woocommerce' ), $e->getMessage(), $e->getCode() ), 'error' );
			return;
		}
	}

	/**
	 * Function to show a specific message on the succes page
	 *
	 * @access public
	 * @param resource $order Woocommerce order.
	 * @return void
	 **/
	public function receipt_page( $order ) {
		echo '<p>' . esc_html__( 'Thank you for your order, you will receive a payment invoice for your order from Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) . '</p>';
	}

	/**
	 * Function to send AfterPay debug email using the AfterPay Library debuglog function
	 *
	 * @access public
	 * @param resource $afterpay The afterpay object.
	 * @return void
	 **/
	public function send_afterpay_debug_mail( $afterpay ) {
		if ( '' !== $this->debug_mail ) {
			wp_mail( $this->debug_mail, 'DEBUG MAIL WOOCOMMERCE RIVERTY', $afterpay->client->getDebugLog() );
		}
	}

	/**
	 * Function to get the the IP address of the client
	 *
	 * @access public
	 * @return string $ipaddress
	 **/
	public function get_afterpay_client_ip() {
		if ( array_key_exists( 'HTTP_X_FORWARDED_FOR', $_SERVER ) && isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ipaddress = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			return trim( $ipaddress[0] );
		} elseif ( array_key_exists( 'REMOTE_ADDR', $_SERVER ) && isset( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		} elseif ( array_key_exists( 'HTTP_CLIENT_IP', $_SERVER ) && isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		return '';
	}

	/**
	 * Function to get the AfterPay NL Tax Category based on full amount and tax amount
	 *
	 * @access public
	 * @param  float $total_amount Total amount.
	 * @param  float $tax_amount Tax amount.
	 * @return int $item_tax_category
	 **/
	public function get_afterpay_tax_class( $total_amount, $tax_amount ) {

		// We manually calculate the tax percentage here.
		if ( $tax_amount > 0 ) {
			// Calculate tax percentage.
			$item_tax_percentage = number_format( ( $tax_amount / $total_amount ) * 100, 2, '.', '' );
		} else {
			$item_tax_percentage = 0.00;
		}

		if ( $item_tax_percentage > 10 ) {
			$item_tax_category = 1;
		} elseif ( $item_tax_percentage > 0 ) {
			$item_tax_category = 2;
		} else {
			$item_tax_category = 3;
		}
		return $item_tax_category;
	}

	/**
	 * Split address
	 *
	 * @access public
	 * @param  string $address                 Address in one string.
	 * @parem  bool   $attach_single_extension If true, an extension of one character will be added to the housenumber.
	 *
	 * @return array
	 */
	public function split_afterpay_address(
		$address,
		$attach_single_extension = false,
		$attach_whole_extension = false
	) {
		$address = is_array( $address ) ? implode( $address, ' ' ) : $address;
		$ret     = [
			'streetname'          => '',
			'housenumber'         => '',
			'houseNumberAddition' => '',
		];

		if ( preg_match( '/^(.+?)([0-9]+)(.*)/', $address, $matches ) ) {
			$ret['streetname']          = trim( $matches[1] );
			$ret['housenumber']         = trim( $matches[2] );
			$ret['houseNumberAddition'] = trim( $matches[3] );
		}

		// If the streetname is empty after splitting, and the address contains characters, then just use the address.
		if ( $ret['streetname'] == '' && strlen( trim( $address ) ) > 0 ) {
			$ret['streetname'] = $address;
		}

		if ( $attach_single_extension == true && strlen( $ret['houseNumberAddition'] ) == 1 ) {
			$ret['housenumber']         = $ret['housenumber'] . $ret['houseNumberAddition'];
			$ret['houseNumberAddition'] = '';
		}

		if ( $attach_whole_extension == true ) {
			$ret['housenumber']         = $ret['housenumber'] . $ret['houseNumberAddition'];
			$ret['houseNumberAddition'] = '';
		}

		return [ $ret['streetname'], $ret['housenumber'], $ret['houseNumberAddition'] ];
	}

	/**
	 * Process refunds.
	 * WooCommerce 2.2 or later.
	 *
	 * @param  int    $order_id Woocommerce order ID.
	 * @param  float  $amount Amount of the refund.
	 * @param  string $reason Optional reason for the refund.
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {

		try {

			global $woocommerce;
			$order = wc_get_order( $order_id );

			// Load AfterPay Library.
			require_once __DIR__ . '/vendor/autoload.php';

			// Create AfterPay object.
			$afterpay = new \Afterpay\Afterpay();

			// Set order management action to partial refund.
			$afterpay->set_ordermanagement( 'refund_partial' );

			// Check the refund id.
			if ( $order->meta_exists( '_afterpay_refund_id' ) ) {
				$refund_id = $order->get_meta( '_afterpay_refund_id' ) + 1;
			} else {
				$refund_id = 1;
			}

			// Set up the additional information.
			$aporder['invoicenumber']       = $order->get_order_number();
			$aporder['ordernumber']         = $order->get_order_number();
			$aporder['creditinvoicenumber'] = 'REFUND-' . $order->get_order_number() . '-' . $refund_id;

			// Set the country.
			$aporder['billtoaddress']['isocountrycode'] = $this->afterpay_country;

			// Set refund line.
			$sku  = 'REFUND';
			$name = 'REFUND';

			// If a reason has been set, use it in  the name/description.
			if ( '' !== $reason ) {
				$name = $name . ': ' . $reason;
			}
			$qty          = 1;
			$price        = round( $amount * 100, 0 ) * -1;
			$tax_category = 1; // 1 = high, 2 = low, 3, zero, 4 no tax
			$afterpay->create_order_line( $sku, $name, $qty, $price, $tax_category );

			// Create the order object for order management (OM).
			$afterpay->set_order( $aporder, 'OM' );

			// Get connection mode.
			$afterpay_mode = $this->get_connection_mode();

			// Set up the AfterPay credentials and sent the request.
			$authorisation['merchantid']  = $this->settings['merchantid'];
			$authorisation['portfolioid'] = $this->settings['portfolioid'];
			$authorisation['password']    = $this->settings['password'];

			$afterpay->do_request( $authorisation, $afterpay_mode, 'nl' );

			$this->send_afterpay_debug_mail( $afterpay );

			if ( 'A' === $afterpay->order_result->return->statusCode ) {
				if ( 1 === $refund_id ) {
					$order->add_meta_data( '_afterpay_refund_id', 1 );
				} else {
					$order->update_meta_data( '_afterpay_refund_id', $refund_id );
				}
				$order->save();
				return true;
			} else {
				return new WP_Error( 'afterpay_refund_error', $afterpay->order_result->return->messages[0]->description );
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'afterpay_refund_error', $e->getMessage() );
		}
		return false;
	}

	/**
	 * Calculate vat amount based on totalamount and vat percentage
	 *
	 * @param int $price_incl_vat Price including taxes.
	 * @param int $vat_percentage Tax percentage.
	 *
	 * @return float $vat_amount
	 */
	public function calculate_afterpay_vat_amount( $price_incl_vat, $vat_percentage ) {
		$vat_amount     = 0;
		$price_excl_vat = ( $price_incl_vat / ( $vat_percentage + 100 ) ) * 100;
		$vat_amount     = $price_incl_vat - $price_excl_vat;
		return round( $vat_amount, 2 );
	}

	/**
	 * Returns all the possible order statuses
	 *
	 * @return array The possible order statuses
	 */
	public function get_all_possible_order_statuses() {
		$temporary_statuses = wc_get_order_statuses();

		$result_statuses = array();

		foreach ( $temporary_statuses as $key => $value ) {
			$key                     = str_replace( 'wc-', '', $key );
			$result_statuses[ $key ] = $value;
		}

		return $result_statuses;
	}

	/**
	 * Returns all the shipping methods
	 *
	 * @return array The shipping methods
	 */
	public function get_all_shipping_methods() {
		$configured_shipping_methods = WC()->shipping->get_shipping_methods();

		if ( count( $configured_shipping_methods ) > 0 ) {
			foreach ( $configured_shipping_methods as $key => $value ) {
				$shipping_methods[ $key ] = $value->method_title;
			}
		} else {
			$shipping_methods['no'] = __( 'No shipping methods available' );
		}

		return $shipping_methods;
	}

	/**
	 * Validates an IBAN bankaccount
	 *
	 * @param string $bank_account The IBAN bank account.
	 * @return bool
	 */
	public function validate_afterpay_bankaccount( $bank_account ) {

		try {
			// Load the AfterPay Library.
			require_once __DIR__ . '/vendor/autoload.php';

			// Create the AfterPay Object.
			$afterpay_bankvalidation = new \Afterpay\Afterpay();
			$afterpay_bankvalidation->setRest();
			$afterpay_bankvalidation->set_ordermanagement( 'validate_bankaccount' );

			// Set up the additional bank information.
			$bankdetails['bankAccount'] = $bank_account;

			// Create the order object for order management (OM).
			$afterpay_bankvalidation->set_order( $bankdetails, 'OM' );
			$authorisation['apiKey'] = $this->get_api_key();

			// Get connection mode.
			$afterpay_mode = $this->get_connection_mode();

			// Sent the request to do the bank validation.
			$afterpay_bankvalidation->do_request( $authorisation, $afterpay_mode );

			// If there was a return and it was false, set the message as notice and return false.
			if (
				isset( $afterpay_bankvalidation->order_result->return )
				&& ! isset( $afterpay_bankvalidation->order_result->return->isValid )
			) {
				wc_add_notice( __( 'There is a problem with your bank data:', 'afterpay-payment-gateway-for-woocommerce' ), 'error' );
				if (
					is_object( $afterpay_bankvalidation->order_result->return )
				) {
					foreach ( $afterpay_bankvalidation->order_result->return as $message ) {
						if ( isset( $message->fieldReference ) && isset( $message->message ) ) {
							wc_add_notice(
								__( $message->fieldReference . ': ' . $message->message, 'afterpay-payment-gateway-for-woocommerce' ),
								'error'
							);
						}
					}
				}
				return false;
			}
		} catch ( Exception $e ) {
			// Something went wrong, print the message.
			// translators: %1$s: error message, %2$s: error code.
			wc_add_notice( sprintf( __( '%1$s (Error code: %2$s)', 'afterpay-payment-gateway-for-woocommerce' ), $e->getMessage(), $e->getCode() ), 'error' );
			return false;
		}

		// All went well, bankaccount was valid return true.
		return true;
	}

	/**
	 * Returns the connection mode
	 *
	 * @return string The connection modus (live, test).
	 */
	public function get_connection_mode() {
		$afterpay_mode = $this->testmode === 'yes' ? 'test' : 'live';
		return $afterpay_mode;
	}

	/**
	 * get payment method api key based on current chosen environment (connection mode)
	 * 
	 * @return string
	 */
	public function get_api_key() {
        $api_key = $this->get_connection_mode() == 'test' ? $this->test_api_key : $this->live_api_key;
		return $api_key;
	}
}
