<?php
/**
 * AfterPay Payment Gateway Base for REST API
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
 * AfterPay Payment Gateway Base for REST API
 *
 * @class       WC_Gateway_Afterpay_De_Openinvoice
 * @extends     WC_Gateway_Afterpay_Base
 * @package     Arvato_AfterPay
 * @author      AfterPay
 */
#[AllowDynamicProperties]
class WC_Gateway_Afterpay_Base_Rest extends WC_Gateway_Afterpay_Base {

	/**
	 * Constructor for the gateway.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		global $woocommerce;
		parent::__construct();

		// Set defaults.
		$afterpay_language = 'DE';
		$afterpay_currency = 'EUR';

		$this->afterpay_language = apply_filters( 'afterpay_language', $afterpay_language );
		$this->afterpay_currency = apply_filters( 'afterpay_currency', $afterpay_currency );

		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'set_payment_method_info' ) );
	}

	/**
	 * Initiate payinx element javascripts.
	 *
	 * @access public
	 * @return void
	 **/
	public function init_payinx_element_scripts() {
		wp_register_script(
			'riverty_payinx_elements_esm',
			'https://cdn.bnpl.riverty.io/elements/v1/build/riverty-elements.esm.js'
		);
		wp_register_script(
			'riverty_payinx_elements',
			'https://cdn.bnpl.riverty.io/elements/v1/build/riverty-elements.js'
		);
		wp_enqueue_script( 'riverty_payinx_elements_esm' );
		wp_enqueue_script( 'riverty_payinx_elements' );

		add_filter( 'script_loader_tag', 'add_attributes_to_script', 10, 3 );
		function add_attributes_to_script( $tag, $handle, $src ) {
			if ( 'riverty_payinx_elements_esm' === $handle ) {
				$tag = '<script type="module" src="' . esc_url( $src ) . '" async="true"></script>';
			}
			if ( 'riverty_payinx_elements' === $handle ) {
				$tag = '<script nomodule src="' . esc_url( $src ) . '" async="true"></script>';
			}
			return $tag;
		}
   	}

	/**
	 * @access public
	 * @return string
	*/
	public function get_payinx_element() {
		global $woocommerce;
		$total_gross_amount = round( (float) $woocommerce->cart->total, 2 );
		$current_locale = strtolower( substr( get_locale(), 0, 2 ) );
		$language_code = '';
		$riverty_language_codes = [ 'de','fi','sv-SE','da','nl' ];
	
		if ( in_array( $current_locale, $riverty_language_codes ) ) {
			$language_code = $current_locale;
		}
		elseif ( $current_locale == 'sv' ) {
			$language_code = 'sv-SE';
		}
		else {
			$language_code = 'en';
		}

		return '<div class="riverty_payinx_element">
					<riverty-split language="' . $language_code . '" theme="checkout" amount="' . $total_gross_amount . '" split-in-parts="3" show-offer="false"></riverty-split>
				</div>';
	}

	/**
	 * @access public
	 * @return string
	 */
	public function get_conversation_language() {
		$current_locale = strtoupper( substr( get_locale(), 0, 2 ) );
		$afterpay_language_codes = [ 'DE','FI','SV','DA','NL','FR' ];

		if ( in_array( $current_locale, $afterpay_language_codes ) ) {
			$language_code = $current_locale;
		}
		elseif ( $current_locale == 'NN' || $current_locale == 'NB' ) {
			$language_code = 'NO';
		}
		else {
			$language_code = 'EN';
		}

        return $language_code;
	}

	/**
	* @access public
	* @param string $api_key
	* @param string $connection_mode
	* @return array
	*/
	public function get_available_payments( $api_key, $connection_mode ) {
		global $woocommerce;

		// Load the AfterPay Library.
		require_once __DIR__ . '/vendor/autoload.php';

		// Create the AfterPay Object.
		$afterpay_payments = new \Afterpay\Afterpay();
		$afterpay_payments->setRest();
		$afterpay_payments->set_ordermanagement( 'available_payment_methods' );

		$conversation_language = $this->get_conversation_language();
		$total_gross_amount = round( (float) $woocommerce->cart->total, 2 );
		$total_net_amount = round( (float) $woocommerce->cart->total - $woocommerce->cart->get_total_tax(), 2 ) ;
		$currency = get_woocommerce_currency();

		$requestData = [
			'conversationLanguage' => $conversation_language,
			'country' => $this->afterpay_country,
			'order' => [
				'totalGrossAmount' => $total_gross_amount,
				'totalNetAmount' => $total_net_amount,
				'currency' => $currency
			],
			'additionalData' => $this->get_additional_data()
		];

		// Create the order object for order management (OM).
		$afterpay_payments->set_order( $requestData, 'OM' );

		$authorisation['apiKey'] = $api_key;
		$afterpay_mode = $connection_mode;

		$afterpay_payments->do_request( $authorisation, $afterpay_mode );

		$response = json_decode( json_encode( $afterpay_payments->order_result->return ), true );

		return $response;
	}

	/**
	 * @access public
     * @return array
     */
    public function get_current_gateway_available_payments_response() {
		global $woocommerce;
        $shipping_country = WC()->session->get('customer')['shipping_country'];
		$response_data = WC()->session->get('afterpay_available_payments') ?? [];
        if ( ! array_key_exists( $this->id, $response_data ) ) {
            $new_available_payments_response = $this->get_available_payments( $this->get_api_key(), $this->get_connection_mode() );
            if ( ! empty($response_data) ) {
                if ( WC()->session->get('afterpay_current_country') != $shipping_country ) {
                    $country_available_payments = [];
                }
                else {
                    $country_available_payments = $response_data;
                }
            }
			$country_available_payments[$this->id]['total_gross_amount'] = $woocommerce->cart->total;
			$country_available_payments[$this->id]['api_key'] = $this->get_api_key();
			$country_available_payments[$this->id]['connection_mode'] = $this->get_connection_mode();
			$country_available_payments[$this->id]['conversation_language'] = strtoupper( substr( get_locale(), 0, 2 ) );
			$country_available_payments[$this->id]['currency'] = get_woocommerce_currency();
            $country_available_payments[$this->id]['response'] = $new_available_payments_response;
			WC()->session->set('afterpay_current_country', $shipping_country);
			WC()->session->set('afterpay_available_payments', $country_available_payments);
			$response_data = WC()->session->get('afterpay_available_payments');
        }
		else {
			if ( $response_data[$this->id]['api_key'] != $this->get_api_key()
				|| $response_data[$this->id]['connection_mode'] != $this->get_connection_mode()
				|| $response_data[$this->id]['conversation_language'] != strtoupper( substr( get_locale(), 0, 2 ) )
				|| $response_data[$this->id]['currency'] != get_woocommerce_currency()
				|| $response_data[$this->id]['total_gross_amount'] != $woocommerce->cart->total ) {
				unset($response_data[$this->id]);
				$new_available_payments_response = $this->get_available_payments( $this->get_api_key(), $this->get_connection_mode() );
				$country_available_payments = $response_data;
				$country_available_payments[$this->id]['total_gross_amount'] = $woocommerce->cart->total;
				$country_available_payments[$this->id]['api_key'] = $this->get_api_key();
				$country_available_payments[$this->id]['connection_mode'] = $this->get_connection_mode();
				$country_available_payments[$this->id]['conversation_language'] = strtoupper( substr( get_locale(), 0, 2 ) );
				$country_available_payments[$this->id]['currency'] = get_woocommerce_currency();
				$country_available_payments[$this->id]['response'] = $new_available_payments_response;
				WC()->session->set('afterpay_current_country', $shipping_country);
				WC()->session->set('afterpay_available_payments', $country_available_payments);
				$response_data = WC()->session->get('afterpay_available_payments');
			}
		}
        return $response_data[$this->id]['response'];
    }

	/**
	 * @access public
	 * @param Array $available_gateways
	 * @return array
	 */
	public function set_payment_method_info( $available_gateways ) {
		global $woocommerce;

		if( is_checkout() ) {
			$shipping_country = WC()->session->get('customer')['shipping_country'];
			if ( $shipping_country == $this->afterpay_country ) {

				if( ! array_key_exists( $this->id, $available_gateways ) ) {
					return $available_gateways;
				}

				$available_payments_response = $this->get_current_gateway_available_payments_response();

				if ( array_key_exists( 'paymentMethods', $available_payments_response ) ) {
					$index = 0;
					$payment_method_exist = false;
					$afterpay_available_payment_methods = $available_payments_response['paymentMethods'];
					$payment_method_types = array_unique( array_column( $afterpay_available_payment_methods, 'type' ) );

					if ( ! in_array( $this->get_payment_method_type(), $payment_method_types ) ) {
						unset( $available_gateways[$this->id] );
						return $available_gateways;
					}

					for ( $i = 0; $i <= count($afterpay_available_payment_methods) - 1; $i++) {
						if ( $afterpay_available_payment_methods[$i]['type'] == $this->get_payment_method_type() ) {
							if ( $afterpay_available_payment_methods[$i]['type'] == 'Invoice' ) {
								if ( strpos( $this->id, 'directdebit' ) && array_key_exists( 'directDebit', $afterpay_available_payment_methods[$i] )
									&& $afterpay_available_payment_methods[$i]['directDebit']['available'] == true
								) {
									$payment_method_exist = true;
									$index = $i;
									break;
								}
								elseif ( strpos( $this->id, 'campaign' ) && array_key_exists( 'campaigns', $afterpay_available_payment_methods[$i] ) ) {
									$this->available_campaign_information = $afterpay_available_payment_methods[$i]['campaigns'];
									$payment_method_exist = true;
									$index = $i;
									break;
								}
								else {
									if ( ! strpos( $this->id, 'directdebit' ) && ! strpos( $this->id, 'campaign' )
										&& ! array_key_exists( 'directDebit', $afterpay_available_payment_methods[$i] ) && ! array_key_exists( 'campaigns', $afterpay_available_payment_methods[$i] )
									) {
										$payment_method_exist = true;
										$index = $i;
										break;
									}
								}
							}
							else {
								if ( $afterpay_available_payment_methods[$i]['type'] == 'Installment' ) {
									if ( $this->is_installments_available( $afterpay_available_payment_methods ) ) {
										$payment_method_exist = true;
										$index = $i;
										break;
									}
								}
								else if ( $afterpay_available_payment_methods[$i]['type'] == 'Account' ) {
									if( array_key_exists( 'account', $afterpay_available_payment_methods[$i] ) ) {
										if ( $this->is_flex_available( $afterpay_available_payment_methods[$i]['account'] ) ) {
											$payment_method_exist = true;
											$index = $i;
											break;
										}
									}
								}
								else if ( $afterpay_available_payment_methods[$i]['type'] == 'PayinX' ) {
									if( array_key_exists( 'payInX', $afterpay_available_payment_methods[$i] ) ) {
										$this->available_payinx_information = $afterpay_available_payment_methods[$i]['payInX'];
										$payment_method_exist = true;
										$index = $i;
										break;
									}
								}
								else {
									$payment_method_exist = true;
									$index = $i;
									break;
								}
							}
						}
					}

					if( $payment_method_exist == true ) {
						if( strpos( $this->id, 'b2b' ) == false ) {
							$this->title = strpos( $this->id, 'business' ) ? $afterpay_available_payment_methods[$index]['title'] . ' ' . __( 'for businesses', 'afterpay-payment-gateway-for-woocommerce' ) : $afterpay_available_payment_methods[$index]['title'];
							$this->extra_information = $afterpay_available_payment_methods[$index]['tag'];
						}
						$this->icon = $afterpay_available_payment_methods[$index]['logo'];
						$this->afterpay_invoice_terms = strpos( $this->id, 'business' ) ? $this->get_b2b_statement_link('conditions') : $afterpay_available_payment_methods[$index]['legalInfo']['termsAndConditionsUrl'];
						$this->afterpay_privacy_statement = strpos( $this->id, 'business' ) ? $this->get_b2b_statement_link('privacy') : $afterpay_available_payment_methods[$index]['legalInfo']['privacyStatementUrl'];
						$this->customer_consent = $afterpay_available_payment_methods[$index]['legalInfo']['requiresCustomerConsent'];
						$this->terms_and_conditions_text = $afterpay_available_payment_methods[$index]['legalInfo']['text'];
						if($this->afterpay_country == 'NL') {
							if( isset( $afterpay_available_payment_methods[$index]['legalInfo']['codeOfConduct'] ) ) {
								$this->code_of_conduct_text = $afterpay_available_payment_methods[$index]['legalInfo']['codeOfConduct'];
							}
						}
					}
					else {
						unset( $available_gateways[$this->id] );
					}
				}
				else {
					unset( $available_gateways[$this->id] );
				}
			}
		}

		return $available_gateways;
	}

	/**
	 * Check if this installments method is enabled and available in the user's country
	 *
	 * @access public
	 * @return boolean
	 */
	public function is_installments_available( $afterpay_available_payment_methods ) {
		global $woocommerce;

		if ( 'yes' === $this->enabled && $woocommerce->cart ) {

			// Cart totals check - Lower threshold.
			if ( ! is_admin() && '' !== $this->lower_threshold ) {
				if ( $woocommerce->cart->subtotal < $this->lower_threshold ) {
					return false;
				}
			}
			// Cart totals check - Upper threshold.
			if ( ! is_admin() && '' !== $this->upper_threshold ) {
				if ( $woocommerce->cart->subtotal > $this->upper_threshold ) {
					return false;
				}
			}
			// Only activate the payment gateway if the customers country is the same as the filtered shop country.
			if ( ! is_admin() ) {
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

			// Check if subtotal is more than 0, if setup request for available installment plans.
			if ( $woocommerce->cart->total > 0 ) {
				if ( ! empty( $afterpay_available_payment_methods ) ) {
					$this->available_installment_plans = array_column( $afterpay_available_payment_methods, 'installment' );
					for ( $i = 0; $i <= count($this->available_installment_plans) - 1; $i++) {
						$this->available_installment_plans[$i]['readMore'] = array_key_exists( 'readMore', $this->available_installment_plans[$i] ) ? $this->available_installment_plans[$i]['readMore'] : '#';
						$this->available_installment_plans[$i] = (object) $this->available_installment_plans[$i];
					}
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if this flex method is enabled and available in the user's country
	 *
	 * @access public
	 * @return boolean
	 */
	public function is_flex_available( $account_details ) {
		global $woocommerce;

		if ( 'yes' === $this->enabled && $woocommerce->cart ) {

			// Cart totals check - Lower threshold.
			if ( ! is_admin() && '' !== $this->lower_threshold ) {
				if ( $woocommerce->cart->subtotal < $this->lower_threshold ) {
					return false;
				}
			}
			// Cart totals check - Upper threshold.
			if ( ! is_admin() && '' !== $this->upper_threshold ) {
				if ( $woocommerce->cart->subtotal > $this->upper_threshold ) {
					return false;
				}
			}
			// Only activate the payment gateway if the customers country is the same as the filtered shop country.
			if ( ! is_admin() ) {
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

			// Check if subtotal is more than 0, if setup request for available installment plans.
			if ( $woocommerce->cart->total > 0 ) {
				$accountInformation = array();
				$accountInformation['monthlyFee']        = $account_details['monthlyFee'];
				$accountInformation['installmentAmount'] = $account_details['installmentAmount'];
				$accountInformation['interestRate']      = $account_details['interestRate'];
				$accountInformation['readMore']          = array_key_exists( 'readMore', $account_details ) ? $account_details['readMore'] : '#' ;
				$accountInformation['profileNo']         = $account_details['profileNo'];
				$this->available_flex_information = $accountInformation;
				return true;
			}
		}
		return false;
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
				<?php echo $this->get_introduction_text(); ?>
			<?php endif; ?>
			<?php if ( 'yes' === $this->show_phone ) : ?>
			<div class="clear"></div>
			<p class="form-row validate-required validate-phone">
				<label for="<?php echo esc_attr( $this->id ); ?>_phone"><strong><?php esc_html_e( 'Phone number', 'afterpay-payment-gateway-for-woocommerce' ); ?>: <span class="required">*</span></strong></label>
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

		// Send error notice to indicate required fields for user to fill in if empty
		if ( $this->get_error_message( $validation_messages ) != null ) {
			wc_add_notice( $this->get_error_message( $validation_messages ), 'error' );
			return false;
		}

		return true;
	}

	/**
	 * Process the payment and return the result
	 *
	 * @access public
	 * @param int $order_id Woocommerce order ID.
	 * @return array
	 **/
	public function process_payment( $order_id ) {
		global $woocommerce;

		$_tax = new WC_Tax();

		$order = wc_get_order( $order_id );

		require_once __DIR__ . '/vendor/autoload.php';

		// Create AfterPay object.
		$afterpay = new \Afterpay\Afterpay();
		$afterpay->setRest();

		// Get values from afterpay form on checkout page.
		// Set form fields per payment option.
		// Collect the dob for NL, BE, DE, AT, CH
		$afterpay_dob = '';
		if ( in_array( $order->get_billing_country(), array( 'NL', 'BE', 'DE', 'AT', 'CH' ), true ) ) {
			// Check if it is the old or new way of requesting the date of birth.
			if ( isset( $_POST[ $this->id . '_dob' ] ) ) {
				$order_dob       = wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_dob' ] ) ) );
				$order_dob_array = explode( '/', $order_dob );
				if ( is_array( $order_dob_array ) ) {
					$afterpay_dob = $order_dob_array[2] . '-' . $order_dob_array[1] . '-' . $order_dob_array[0];
				} else {
					$afterpay_dob = '';
				}
			}
		}

		// Get values from afterpay form on checkout page.
		// Set form fields per payment option.
		$afterpay_phone  = isset( $_POST[ $this->id . '_phone' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_phone' ] ) ) ) : $order->get_billing_phone();
		$afterpay_gender = isset( $_POST[ $this->id . '_gender' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_gender' ] ) ) ) : '';

		// Set POST data for Installments profile and bankdetails.
		$afterpay_installment_profile     = isset( $_POST[ $this->id . '_installmentplan' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_installmentplan' ] ) ) ) : '';
		$afterpay_installment_bankaccount = isset( $_POST[ $this->id . '_bankaccount' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_bankaccount' ] ) ) ) : '';

		// Set POST data for Account/Flex profile.
		$afterpay_flex_profile = isset( $_POST[ $this->id . '_flex_profile' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_flex_profile' ] ) ) ) : '';

		// Set POST data for Direct Debit bankdetails.
		$afterpay_bankaccount = isset( $_POST[ $this->id . '_bankaccount' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_bankaccount' ] ) ) ) : '';

		// Set POST data for Pay In X due amount.
		$afterpay_payinx_due_amount	= isset( $_POST[ $this->id . '_due_amount' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_due_amount' ] ) ) ) : '';

		// Set POST data for Campaign payment info.
		$afterpay_campaign_number	= isset( $_POST[ $this->id . '_campaign_number' ] ) ?
		wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_campaign_number' ] ) ) ) : '';

		// Set POST data for social security number.
		$afterpay_ssn = isset( $_POST[ $this->id . '_ssn' ] ) ?
			wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_ssn' ] ) ) ) : '';

		// Set POST data for profile tracking id.
		$afterpay_profile_tracking_id = isset( $_POST[ $this->id . '_profile_tracking' ] ) ?
		wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_profile_tracking' ] ) ) ) : '';

		// Check if Customer Individual Score is enabled and if so set the code.
		$afterpay_cis_code = '';
		if ( isset( $this->customer_individual_score ) && 'yes' === $this->customer_individual_score ) {
			// Check if coupons are used, and if it contains AfterPay CIS code.
			$afterpay_cis_code = '20';

			if ( $order->get_used_coupons() && count( $order->get_used_coupons() ) > 0 ) {
				foreach ( $order->get_used_coupons() as $coupon_name ) {

					// Retrieving the coupon ID.
					$coupon_post_obj = get_page_by_title( $coupon_name, OBJECT, 'shop_coupon' );
					$coupon_id       = $coupon_post_obj->ID;

					// Get an instance of WC_Coupon object in an array(necesary to use WC_Coupon methods).
					$coupons_obj = new WC_Coupon( $coupon_id );

					// Afterpay Coupon code.
					if ( $coupons_obj->meta_exists( 'afterpay_cis_code' ) ) {
						$afterpay_cis_code = $coupons_obj->get_meta( 'afterpay_cis_code' );
					}
				}
			}
		}

		// Check if Germanized plugin is used for the gender / title.
		$germanized_billing_title = $order->get_meta( '_billing_title' );
		if ( isset( $this->compatibility_germanized ) && 'yes' === $this->compatibility_germanized && null !== $germanized_billing_title ) {
			switch ( $germanized_billing_title ) {
				case '1':
					$afterpay_gender = 'M';
					break;
				case '2':
					$afterpay_gender = 'V';
					break;
			}
		}

		// Check if rest B2B invoice is used for adding companyname and cocnumber to order information
		if ( 'B2B' === $this->order_type ) {
			$afterpay_cocnumber   = isset( $_POST[ $this->id . '_cocnumber' ] )
				? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_cocnumber' ] ) ) ) : '';
			$afterpay_companyname = isset( $_POST[ $this->id . '_companyname' ] )
				? wc_clean( sanitize_text_field( wp_unslash( $_POST[ $this->id . '_companyname' ] ) ) ) : '';
		}

		// Split address into House number and House extension.
		$afterpay_billing_address_1        = $order->get_billing_address_1();
		$afterpay_billing_address_2        = $order->get_billing_address_2();
		$afterpay_billing_address          = $afterpay_billing_address_1 . ' ' . $afterpay_billing_address_2;
		$splitted_address                  = $this->split_afterpay_address( $afterpay_billing_address, true );
		$afterpay_billing_address          = $splitted_address[0];
		$afterpay_billing_house_number     = $splitted_address[1];
		$afterpay_billing_house_extension  = $splitted_address[2];
		$afterpay_shipping_address_1       = $order->get_shipping_address_1();
		$afterpay_shipping_address_2       = $order->get_shipping_address_2();
		$afterpay_shipping_address         = $afterpay_shipping_address_1 . ' ' . $afterpay_shipping_address_2;
		$splitted_address                  = $this->split_afterpay_address( $afterpay_shipping_address, true );
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

		// Get connection mode.
		$afterpay_mode = $this->get_connection_mode();

		$authorisation['apiKey'] = $this->get_api_key();

		// Create the order.
		$order_items = $this->get_order_items( $order );

		// Cart Contents.
		if ( count( $order_items ) > 0 ) {
			foreach ( $order_items as $item ) {
				$afterpay->create_order_line(
					$item['sku'],
					$item['name'],
					$item['qty'],
					$item['price'],
					$item['tax_category'],
					$item['tax_amount'],
					( isset( $item['google_product_category_id'] ) ? $item['google_product_category_id'] : null ),
					( isset( $item['google_product_category'] ) ? $item['google_product_category'] : null ),
					( isset( $item['product_url'] ) ? $item['product_url'] : null ),
					( isset( $item['image_url'] ) ? $item['image_url'] : null )
				);
			}
		}

		$aporder['billtoaddress']['city']                     = $order->get_billing_city();
		$aporder['billtoaddress']['housenumber']              = $afterpay_billing_house_number;
		$aporder['billtoaddress']['housenumberaddition']      = $afterpay_billing_house_extension;
		$aporder['billtoaddress']['isocountrycode']           = $order->get_billing_country();
		$aporder['billtoaddress']['postalcode']               = $order->get_billing_postcode();
		$aporder['billtoaddress']['referenceperson']['email'] = $order->get_billing_email();
		if ( '' !== $afterpay_gender ) {
			$aporder['billtoaddress']['referenceperson']['gender'] = $afterpay_gender;
		}
		if ( '' !== $afterpay_dob ) {
			$aporder['billtoaddress']['referenceperson']['dob'] = $afterpay_dob;
		}
		$aporder['billtoaddress']['referenceperson']['firstname']   = $order->get_billing_first_name();
		$aporder['billtoaddress']['referenceperson']['isolanguage'] = $this->get_conversation_language();
		$aporder['billtoaddress']['referenceperson']['lastname']    = $order->get_billing_last_name();
		$aporder['billtoaddress']['referenceperson']['phonenumber'] = $afterpay_phone;
		$aporder['billtoaddress']['streetname']                     = $afterpay_billing_address;

		// Check if social security number is set, if so put it in the billtoaddress.
		if ( '' !== $afterpay_ssn ) {
			$aporder['billtoaddress']['referenceperson']['ssn'] = $afterpay_ssn;
		}

		// Shipping address.
		if ( '' === $order->get_shipping_method() ) {
			// Use billing address if Shipping is disabled in Woocommerce.
			$aporder['shiptoaddress'] = $aporder['billtoaddress'];
		} else {
			$aporder['shiptoaddress']['city']                     = $order->get_shipping_city();
			$aporder['shiptoaddress']['housenumber']              = $afterpay_shipping_house_number;
			$aporder['shiptoaddress']['housenumberaddition']      = $afterpay_shipping_house_extension;
			$aporder['shiptoaddress']['isocountrycode']           = $order->get_shipping_country();
			$aporder['shiptoaddress']['postalcode']               = $order->get_shipping_postcode();
			$aporder['shiptoaddress']['referenceperson']['email'] = $order->get_billing_email();
			if ( '' !== $afterpay_gender ) {
				$aporder['shiptoaddress']['referenceperson']['gender'] = $afterpay_gender;
			}
			if ( '' !== $afterpay_dob ) {
				$aporder['shiptoaddress']['referenceperson']['dob'] = $afterpay_dob;
			}
			$aporder['shiptoaddress']['referenceperson']['firstname']   = $order->get_shipping_first_name();
			$aporder['shiptoaddress']['referenceperson']['isolanguage'] = $this->get_conversation_language();
			$aporder['shiptoaddress']['referenceperson']['lastname']    = $order->get_shipping_last_name();
			$aporder['shiptoaddress']['referenceperson']['phonenumber'] = $afterpay_phone;
			$aporder['shiptoaddress']['streetname']                     = $afterpay_shipping_address;
		}

		// Check if shipping method 'local_pickup' is used, if so use the location of the store.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping_method  = explode( ':', $chosen_shipping_methods[0] );
		$chosen_shipping_method  = $chosen_shipping_method[0];

		if ( 'local_pickup' == $chosen_shipping_method ) {

			$aporder['shiptoaddress']['referenceperson']['firstname'] = 'P';
			$aporder['shiptoaddress']['referenceperson']['lastname']  = mb_convert_encoding( 'Pickup ' . get_bloginfo( 'name' ), 'ISO-8859-1', 'UTF-8' );

			$store_address_1                                 = get_option( 'woocommerce_store_address' );
			$store_address_2                                 = get_option( 'woocommerce_store_address_2' );
			$store_address                                   = trim( $store_address_1 . ' ' . $store_address_2 );
			$splitted_address                                = $this->split_afterpay_address( $store_address );
			$afterpay_shipping_address                       = $splitted_address[0];
			$afterpay_shipping_house_number                  = $splitted_address[1];
			$afterpay_shipping_house_extension               = substr( $splitted_address[2], 0, 10 );
			$aporder['shiptoaddress']['streetname']          = mb_convert_encoding( $afterpay_shipping_address, 'ISO-8859-1', 'UTF-8' );
			$aporder['shiptoaddress']['housenumber']         = mb_convert_encoding( $afterpay_shipping_house_number, 'ISO-8859-1', 'UTF-8' );
			$aporder['shiptoaddress']['housenumberaddition'] = mb_convert_encoding( $afterpay_shipping_house_extension, 'ISO-8859-1', 'UTF-8' );

			$store_city                       = get_option( 'woocommerce_store_city' );
			$aporder['shiptoaddress']['city'] = mb_convert_encoding( $store_city, 'ISO-8859-1', 'UTF-8' );

			$store_postcode                         = get_option( 'woocommerce_store_postcode' );
			$aporder['shiptoaddress']['postalcode'] = mb_convert_encoding( $store_postcode, 'ISO-8859-1', 'UTF-8' );

			$aporder['shiptoaddress']['addresstype'] = 'PickUpPoint';

			// The country/state
			$store_raw_country = get_option( 'woocommerce_default_country' );

			// Split the country/state
			$split_country = explode( ':', $store_raw_country );

			// Country and state separated:
			$store_country                              = $split_country[0];
			$aporder['shiptoaddress']['isocountrycode'] = mb_convert_encoding( $store_country, 'ISO-8859-1', 'UTF-8' );
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
					$aporder['shiptoaddress']['referenceperson']['lastname'] = mb_convert_encoding( $sendcloud_shipping_name, 'ISO-8859-1', 'UTF-8' );
					$sendcloud_address                                       = $this->split_afterpay_address( $sendcloud_shipping_data[1] );
					$sendcloud_shipping_street                               = isset( $sendcloud_address[0] ) ? $sendcloud_address[0] : '';
					$aporder['shiptoaddress']['streetname']                  = mb_convert_encoding( $sendcloud_shipping_street, 'ISO-8859-1', 'UTF-8' );
					$sendcloud_shipping_house_number                         = isset( $sendcloud_address[1] ) ? $sendcloud_address[1] : '';
					$aporder['shiptoaddress']['housenumber']                 = $sendcloud_shipping_house_number;
					$sendcloud_shipping_house_extension                      = isset( $sendcloud_address[2] ) ? $sendcloud_address[2] : '';
					$aporder['shiptoaddress']['housenumberaddition']         = mb_convert_encoding( $afterpay_shipping_house_extension, 'ISO-8859-1', 'UTF-8' );
					$sendcloud_shipping_pcandcity                            = explode( ' ', $sendcloud_shipping_data['2'] );
					$sendcloud_shipping_postalcode                           = isset( $sendcloud_shipping_pcandcity[0] ) ? $sendcloud_shipping_pcandcity[0] : '';
					$aporder['shiptoaddress']['postalcode']                  = mb_convert_encoding( $sendcloud_shipping_postalcode, 'ISO-8859-1', 'UTF-8' );
					$sendcloud_shipping_city                                 = isset( $sendcloud_shipping_pcandcity[1] ) ? $sendcloud_shipping_pcandcity[1] : '';
					$aporder['shiptoaddress']['city']                        = mb_convert_encoding( $sendcloud_shipping_city, 'ISO-8859-1', 'UTF-8' );
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
					$aporder['shiptoaddress']['referenceperson']['lastname'] = mb_convert_encoding( $location_name, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['streetname']                  = mb_convert_encoding( $postnl_shipping_street, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['housenumber']                 = $postnl_shipping_house_number;
					$aporder['shiptoaddress']['postalcode']                  = mb_convert_encoding( $postnl_shipping_postalcode, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['city']                        = mb_convert_encoding( $postnl_shipping_city, 'ISO-8859-1', 'UTF-8' );
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
							$aporder['shiptoaddress']['referenceperson']['lastname'] = mb_convert_encoding( $location_name, 'ISO-8859-1', 'UTF-8' );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'street' ) ) {
							$postnl_street = $postnl_pickup_reflection->getProperty( 'street' );
							$postnl_street->setAccessible( true );
							$aporder['shiptoaddress']['streetname'] = mb_convert_encoding( $postnl_street->getValue( $postnl_pickup_location ), 'ISO-8859-1', 'UTF-8' );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'number' ) ) {
							$postnl_number = $postnl_pickup_reflection->getProperty( 'number' );
							$postnl_number->setAccessible( true );
							$aporder['shiptoaddress']['housenumber'] = $postnl_number->getValue( $postnl_pickup_location );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'postal_code' ) ) {
							$postnl_postal_code = $postnl_pickup_reflection->getProperty( 'postal_code' );
							$postnl_postal_code->setAccessible( true );
							$aporder['shiptoaddress']['postalcode'] = mb_convert_encoding( $postnl_postal_code->getValue( $postnl_pickup_location ), 'ISO-8859-1', 'UTF-8' );
						}
						if ( $postnl_pickup_reflection->hasProperty( 'city' ) ) {
							$postnl_city = $postnl_pickup_reflection->getProperty( 'city' );
							$postnl_city->setAccessible( true );
							$aporder['shiptoaddress']['city'] = mb_convert_encoding( $postnl_city->getValue( $postnl_pickup_location ), 'ISO-8859-1', 'UTF-8' );
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
					$aporder['shiptoaddress']['referenceperson']['lastname'] = mb_convert_encoding( $mpc_pickup_name, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['streetname']                  = mb_convert_encoding( $mpc_pickup_street, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['housenumber']                 = mb_convert_encoding( $mpc_pickup_number, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['postalcode']                  = mb_convert_encoding( $mpc_pickup_postalcode, 'ISO-8859-1', 'UTF-8' );
					$aporder['shiptoaddress']['city']                        = mb_convert_encoding( $mpc_pickup_city, 'ISO-8859-1', 'UTF-8' );
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
		$aporder['currency']          = $this->afterpay_currency;
		$aporder['ipaddress']         = $this->get_afterpay_client_ip();
		$aporder['profileTrackingId'] = $afterpay_profile_tracking_id;

		// Add additional data.
		$aporder['additionalData'] = $this->get_additional_data();

		// Check if direct debit data is set, if so add to the $aporder array.
		if ( '' !== $afterpay_bankaccount ) {
			$aporder['directDebit']['bankAccount'] = $afterpay_bankaccount;
		}

		// Check if installment data is set, if so add to the $aporder array.
		if ( '' !== $afterpay_installment_profile ) {
			$aporder['installment']['profileNo']   = $afterpay_installment_profile;
			$aporder['installment']['bankAccount'] = $afterpay_installment_bankaccount;

			// Unset direct debit data.
			unset( $aporder['directDebit'] );
		}

		// Check if flex profile is set, if so add to the $aporder array.
		if ( '' != $afterpay_flex_profile ) {
			$aporder['account']['profileNo'] = $afterpay_flex_profile;
		}

		// Check if campaign number is set, if so add to the $aporder array.
		if ( '' != $afterpay_campaign_number ) {
			$aporder['campaign']['campaignNumber'] = $afterpay_campaign_number;
		}

		// Check if pay in X due amount is set, if so add to the $aporder array.
		if ( '' != $afterpay_payinx_due_amount ) {
			$aporder['payInX']['dueAmount'] = $afterpay_payinx_due_amount;
		}

		// Check if customerIndividualScore is set, if so add to the $aporder array.
		if ( '' !== $afterpay_cis_code ) {
			$aporder['customerIndividualScore'] = $afterpay_cis_code;
		}

		// Chef if the Merchant ID is set, if so add to the $aporder array.
		if ( '' !== $this->merchantid ) {
			$aporder['apiMerchantId'] = $this->merchantid;
		}

		if ( 'B2B' === $this->order_type ) {
			$aporder['company']['companyname']                      = $afterpay_companyname;
			$aporder['billtoaddress']['referenceperson']['ssn']     = $afterpay_cocnumber;
		}

		try {
			// Transmit all the specified data, from the steps above, to afterpay.
			$afterpay->set_order( $aporder, $this->order_type );
			$afterpay->do_request( $authorisation, $afterpay_mode );
			$this->send_afterpay_debug_mail( $afterpay );

			// Retreive response.
			if ( isset( $afterpay->order_result->return->statusCode ) ) {
				switch ( $afterpay->order_result->return->statusCode ) {
					case 'A':
						// If capturing is enabled, and way of capture is set.
						// to automatically after authorization, then capture the full order.
						if (
							isset( $this->settings['captures'] )
							&& 'yes' === $this->settings['captures']
							&& isset( $this->settings['captures_way'] )
							&& 'auto_after_authorization' === $this->settings['captures_way']
						) {
							$order->add_order_note( __( 'Riverty payment completed.', 'afterpay-payment-gateway-for-woocommerce' ) );

							// Capture payment.
							$this->capture_afterpay_payment( null, $order );
						}

						if (
							isset( $this->settings['captures'] )
							&& 'yes' === $this->settings['captures']
							&& isset( $this->settings['captures_way'] )
							&& 'auto_after_authorization' !== $this->settings['captures_way']
						) {
							// Add note that the order is not captured yet.
							$order->add_order_note( __( 'Riverty capture needed, since the Capture mode was set to(Based on Woocommerce Status) when the order was placed.', 'afterpay-payment-gateway-for-woocommerce' ) );
						}

						// Payment complete.
						$order->payment_complete();

						// Remove cart.
						$woocommerce->cart->empty_cart();

						// Return thank you redirect.
						return array(
							'result'   => 'success',
							'redirect' => $this->get_return_url( $order ),
						);
					case 'P':
						$order->add_order_note( __( 'Riverty payment pending.', 'afterpay-payment-gateway-for-woocommerce' ) );

						// Payment to pending.
						$order->update_status( 'pending', __( 'Awaiting Riverty payment', 'afterpay-payment-gateway-for-woocommerce' ) );

						// If secureLoginUrl is provided, then redirect.
						if (
							isset( $afterpay->order_result->return->secureLoginUrl )
							&& '' !== $afterpay->order_result->return->secureLoginUrl
						) {
							$redirectUrl  = $afterpay->order_result->return->secureLoginUrl;
							$redirectUrl .= site_url( 'afterpay/return?order_id=' . $order->get_id() );

							return array(
								'result'   => 'success',
								'redirect' => $redirectUrl,
							);
						} else {
							// Remove cart.
							$woocommerce->cart->empty_cart();

							// Cancel order to make new order possible.
							WC()->session->set( 'order_awaiting_payment', false );
							$order->update_status( 'cancelled', '' );

							return;
						}
					case 'W':
						// Order is denied, store it in a database.
						$order->add_order_note( __( 'Riverty payment denied.', 'afterpay-payment-gateway-for-woocommerce' ) );
						$message = isset( $afterpay->order_result->return->messages->message ) ?
							$afterpay->order_result->return->messages->message : '';

						// Check if description failure isset, else set rejectCode to return default rejection message.
						if ( isset( $afterpay->order_result->return->riskCheckMessages )
							&& isset( $afterpay->order_result->return->riskCheckMessages[0]->customerFacingMessage )
							&& isset( $afterpay->order_result->return->riskCheckMessages[0]->code ) ) {
							// Get the rejection message from REST.
							$message = $afterpay->order_result->return->riskCheckMessages[0]->customerFacingMessage;

							// If the rejection is an address correction, show the address correction.
							if (
								in_array(
									$afterpay->order_result->return->riskCheckMessages[0]->code,
									array( '200.101', '200.103', '200.104' ),
									true
								)
								&& isset( $afterpay->order_result->return->customer->addressList[0] )
								&& is_object( $afterpay->order_result->return->customer->addressList[0] ) ) {
									$new_address = $afterpay->order_result->return->customer->addressList[0];
									$message     = $afterpay->order_result->return->riskCheckMessages[0]->customerFacingMessage;
									$message    .= '<br/><br/>';
									$message    .= $new_address->street . ' ' . $new_address->streetNumber . '<br/>';
									$message    .= $new_address->postalCode . ' ';
									$message    .= $new_address->postalPlace . '<br/>';
									$message    .= $new_address->countryCode;
							}
						}
						$order->add_order_note( $message );
						wc_add_notice( $message, 'error' );

						// Cancel order to make new order possible.
						WC()->session->set( 'order_awaiting_payment', false );
						$order->update_status( 'cancelled', '' );

						return;
				}
			} else {

				// Check for business errors.
				if ( 1 === $afterpay->order_result->return->resultId ) {
					// Unknown response, store it in a database.
					$order->add_order_note( __( 'There is a problem with submitting this order to Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) );
					$validationmsg  = __( 'There is a problem with submitting this order to Riverty, please check the following issues: ', 'afterpay-payment-gateway-for-woocommerce' );
					$validationmsg .= '<ul>';
					if ( isset( $afterpay->order_result->return->messages )
						&& ! is_object( $afterpay->order_result->return->messages ) ) {
						foreach ( $afterpay->order_result->return->messages as $value ) {
							$validationmsg .= '<li style="list-style: inherit">' . __( $value->description, 'afterpay-payment-gateway-for-woocommerce' ) . '</li>';
							$order->add_order_note( __( $value->description, 'afterpay-payment-gateway-for-woocommerce' ) );
						}
					} elseif ( isset( $afterpay->order_result->return->failures->failure ) ) {
						$validationmsg .= '<li style="list-style: inherit">' .
							__( $afterpay->order_result->return->failures->failure, 'afterpay-payment-gateway-for-woocommerce' ) . '</li>';
					}
					$validationmsg .= '</ul>';
					wc_add_notice( $validationmsg, 'error' );
				} elseif ( 2 === $afterpay->order_result->return->resultId ) {
					// Unknown response, store it in a database.
					$order->add_order_note( __( 'There is a problem with submitting this order to Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) );
					$validationmsg  = __( 'There is a problem with submitting this order to Riverty, please check the following issues: ', 'afterpay-payment-gateway-for-woocommerce' );
					$validationmsg .= '<ul>';
					if ( ! is_object( $afterpay->order_result->return->messages ) ) {
						foreach ( $afterpay->order_result->return->messages as $value ) {
							$validationmsg .= '<li style="list-style: inherit">' . __( $value->description, 'afterpay-payment-gateway-for-woocommerce' ) . '</li>';
							$order->add_order_note( __( $value->description, 'afterpay-payment-gateway-for-woocommerce' ) );
						}
					}
					$validationmsg .= '</ul>';
					wc_add_notice( $validationmsg, 'error' );
				} else {
					// Unknown response, store it in a database.
					$order->add_order_note( __( 'Unknown response from Riverty.', 'afterpay-payment-gateway-for-woocommerce' ) );
					wc_add_notice( __( 'Unknown response from Riverty. Please contact our customer service', 'afterpay-payment-gateway-for-woocommerce' ), 'error' );
				}

				// Cancel order to make new order possible.
				WC()->session->set( 'order_awaiting_payment', false );
				$order->update_status( 'cancelled', '' );

				return;
			}
		} catch ( Exception $e ) {
			// The purchase was denied or something went wrong, print the message.
			// translators: %1$s: error message, %2$s: error code.
			wc_add_notice( sprintf( __( '%1$s (Error code: %2$s)', 'afterpay-payment-gateway-for-woocommerce' ), $e->getMessage(), $e->getCode() ), 'error' );
			return;
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

			// API Key.
			$authorisation['apiKey'] = $this->get_api_key();

			$afterpay_capture = new \Afterpay\Afterpay();
			$afterpay_capture->setRest();
			$afterpay_capture->set_ordermanagement( 'capture_full' );

			// Set up the additional information.
			$capture_details['invoicenumber'] = $order->get_order_number();
			$capture_details['ordernumber']   = $order->get_order_number();

			// Create the order.
			$order_items = $this->get_order_items( $order );

			// Cart Contents.
			if ( count( $order_items ) > 0 ) {
				foreach ( $order_items as $item ) {
					$afterpay_capture->create_order_line(
						$item['sku'],
						$item['name'],
						$item['qty'],
						$item['price'],
						$item['tax_category'],
						$item['tax_amount'],
						( isset( $item['google_product_category_id'] ) ? $item['google_product_category_id'] : null ),
						( isset( $item['google_product_category'] ) ? $item['google_product_category'] : null ),
						( isset( $item['product_url'] ) ? $item['product_url'] : null ),
						( isset( $item['image_url'] ) ? $item['image_url'] : null )
					);
				}
			}

			// Add order total in cents.
			$capture_details['totalamount']    = $order->get_total() * 100;
			$capture_details['totalNetAmount'] = ( $order->get_total() - $order->get_total_tax() );

			// Create the order object for order management (OM).
			$afterpay_capture->set_order( $capture_details, 'OM' );
			$afterpay_capture->do_request( $authorisation, $afterpay_mode );

			// Send the debug mail.
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
	 * Get all the order items including shipment and fees and put them in an array to process.
	 *
	 * @param resource $order The order object itself.
	 * @return array
	 */
	private function get_order_items( $order ) {

		global $woocommerce;

		$order_lines = array();

		// Iterate through the order items.
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
				$item_tax_amount = round( $order->get_line_tax( $item ) / $item['qty'], 4 );

				// Apply_filters to item price so we can filter this if needed.
				$afterpay_item_price_including_tax = $order->get_item_total( $item, true );
				$item_price                        = apply_filters( 'afterpay_item_price_including_tax', $afterpay_item_price_including_tax );
				$item_price                        = round( $item_price * 100, 0 );

				// Get the product url.
				$product_url = get_permalink( $_product->get_id() );

				// Get the product image url, if not available return empty string.
				$image_url = wp_get_attachment_image_url( $_product->get_image_id(), 'full' );
				if ( false === $image_url ) {
					$image_url = '';
				}
				// Strip any query string from image url.
				$image_url = preg_replace( '/\?.*/', '', $image_url );

				$order_lines[] = array(
					'sku'                        => $sku,
					'name'                       => $item['name'],
					'qty'                        => $item['qty'],
					'price'                      => $item_price,
					'tax_category'               => null,
					'tax_amount'                 => $item_tax_amount,
					'google_product_category_id' => null, // $googleProductCategoryId
					'google_product_category'    => null, // $googleProductCategory
					'product_url'                => $product_url,
					'image_url'                  => $image_url,
				);
			}
		}

		// Shipping.
		if ( $order->get_shipping_total() > 0 ) {
			// We manually calculate the shipping tax percentage here.
			$calculated_shipping_tax_percentage = ( $order->get_shipping_tax() / $order->get_shipping_total() ) * 100;
			$calculated_shipping_tax_decimal    = ( $order->get_shipping_tax() / $order->get_shipping_total() ) + 1;

			// Apply_filters to Shipping so we can filter this if needed.
			$afterpay_shipping_price_including_tax = $order->get_shipping_total() * $calculated_shipping_tax_decimal;
			$shipping_price                        = apply_filters( 'afterpay_shipping_price_including_tax', $afterpay_shipping_price_including_tax );
			$shipping_sku                          = __( 'SHIPPING', 'afterpay-payment-gateway-for-woocommerce' );
			$shipping_description                  = __( 'Shipping', 'afterpay-payment-gateway-for-woocommerce' );
			$shipping_price                        = round( $shipping_price * 100, 0 );
			$shipping_tax                          = $order->get_shipping_tax();
			$order_lines[]                         = array(
				'sku'          => $shipping_sku,
				'name'         => $shipping_description,
				'qty'          => 1,
				'price'        => $shipping_price,
				'tax_category' => null,
				'tax_amount'   => $shipping_tax,
			);
		}

		// Check if any fees are set on the order.
		$fees = [];
		if( isset ( $woocommerce->cart ) ) {
			if ( method_exists( $woocommerce->cart, 'get_fees' ) ) {
				$fees = $woocommerce->cart->get_fees();
			}
		}

		if ( count( $fees ) > 0 ) {
			foreach ( $fees as $fee ) {
				if (
					! isset( $fee->name )
					&& ! isset( $fee->amount )
					&& ! isset( $fee->tax )
				) {
					next;
				}
				$fee_sku         = __( 'Service Fee', 'afterpay-payment-gateway-for-woocommerce' );
				$fee_description = $fee->name;
				$fee_price       = round( ( $fee->amount + $fee->tax ) * 100 );
				$order_lines[]   = array(
					'sku'          => $fee_sku,
					'name'         => $fee_description,
					'qty'          => 1,
					'price'        => $fee_price,
					'tax_category' => null,
					'tax_amount'   => $fee->tax,
				);
			}
		}

		return $order_lines;
	}

	/**
	 * Process refunds.
	 * WooCommerce 2.2 or later.
	 *
	 * @param  int    $order_id Woocommerce Order Id.
	 * @param  float  $amount   Refund amount.
	 * @param  string $reason   Optional refund reason.
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
			$afterpay->setRest();

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
			$qty            = 1;
			$price          = round( $amount * 100, 0 ) * -1;
			$tax_category   = 1; // 1 = high, 2 = low, 3, zero, 4 no tax.
			$tax_percentage = $this->refund_tax_percentage;
			$tax_amount     = $this->calculate_afterpay_vat_amount( $amount, $tax_percentage ) * -1;
			$afterpay->create_order_line(
				$sku,
				$name,
				$qty,
				$price,
				$tax_category,
				$tax_amount
			);

			// Create the order object for order management (OM).
			$afterpay->set_order( $aporder, 'OM' );

			// Get connection mode.
			$afterpay_mode = $this->get_connection_mode();

			// Set up the AfterPay credentials and sent the request.
			$authorisation['apiKey'] = $this->get_api_key();

			$afterpay->do_request( $authorisation, $afterpay_mode );

			$this->send_afterpay_debug_mail( $afterpay );

			if ( 0 === $afterpay->order_result->return->resultId ) {
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
	 * Check order status.
	 *
	 * @param  object $order    Woocommerce Order Object.
	 * @return string $status   Order status
	 */
	public function afterpay_check_status( $order ) {

		// Load AfterPay Library.
		require_once __DIR__ . '/vendor/autoload.php';

		// Create AfterPay object.
		$afterpay = new \Afterpay\Afterpay();
		$afterpay->setRest();

		// Set order management action to get the order
		$afterpay->set_ordermanagement( 'get_order' );

		// Set up the additional information
		$aporder['ordernumber'] = $order->get_order_number();

		// Create the order object for B2C or B2B
		$afterpay->set_order( $aporder, 'OM' );

		// Get connection mode.
		$afterpay_mode = $this->get_connection_mode();

		// Set up the AfterPay credentials and sent the request.
		$authorisation['apiKey'] = $this->get_api_key();

		$afterpay->do_request( $authorisation, $afterpay_mode );

		$return = $afterpay->order_result->return->orderDetails->status;

		return $return;
	}

	/**
	 * Get additional platform and plugin data.
	 *
	 * @return array $data Array with platform and plugin data.
	 */
	public function get_additional_data() {
		$plugin_path         = '/afterpay-payment-gateway-for-woocommerce/class-wc-gateway-afterpay.php';
		$woocommerce_path    = '/woocommerce/woocommerce.php';
		$wordpress_version   = get_bloginfo( 'version' );
		$woocommerce_version = $this->get_plugin_version( $woocommerce_path );

		$data['pluginProvider']      = 'Riverty';
		$data['pluginVersion']       = $this->get_plugin_version( $plugin_path );
		$data['shopUrl']             = get_site_url();
		$data['shopPlatform']        = 'WordPress | WooCommerce';
		$data['shopPlatformVersion'] = 'WordPress: ' . $wordpress_version . ' | WooCommerce: ' . $woocommerce_version;

		return $data;
	}

	/**
	 * Get version number of WordPress plugin.
	 *
	 * @param  string $plugin   Path of plugin
	 * @return string $version  Version number of plugin
	 */
	private function get_plugin_version( $plugin ) {

		$plugin = WP_PLUGIN_DIR . $plugin;

		$plugin_data = get_plugin_data( $plugin );

		if ( isset( $plugin_data['Version'] ) ) {
			return $plugin_data['Version'];
		} else {
			return '';
		}
	}
}
