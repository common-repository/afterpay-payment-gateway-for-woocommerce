<?php
/**
 * WooCommerce AfterPay Gateway
 *
 * @package    WC_Payment_Gateway
 * @author     arvato Finance B.V.
 * @copyright  since 2011 arvato Finance B.V.
 *
 * Uninstall - removes all AfterPay options from DB when user deletes the plugin via WordPress backend.
 *
 * @since 0.3
 **/

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option( 'woocommerce_afterpay_settings' );
