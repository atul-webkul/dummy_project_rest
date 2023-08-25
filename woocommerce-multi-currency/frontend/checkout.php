<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WOOMULTI_CURRENCY_Frontend_Checkout
 */
class WOOMULTI_CURRENCY_Frontend_Checkout {

	public $settings;

	function __construct() {

//		$this->settings = new WOOMULTI_CURRENCY_Data();
		$this->settings = WOOMULTI_CURRENCY_Data::get_ins();
		if ( $this->settings->get_enable() ) {
			add_action( 'woocommerce_checkout_process', array( $this, 'woocommerce_checkout_process' ) );
			add_action( 'woocommerce_checkout_update_order_review', array(
				$this,
				'woocommerce_checkout_update_order_review'
			) );

			add_filter( 'woocommerce_available_payment_gateways', array( $this, 'control_payment_methods' ), 12 );
		}
	}

	/**
	 * @param $methods
	 */
	public function control_payment_methods( $payment_methods ) {
		if ( is_admin() ) {
			return $payment_methods;
		}
		$current_currency = $this->settings->get_current_currency();
		if ( $this->settings->get_enable_multi_payment() ) {
			$payments = $this->settings->get_payments_by_currency( $current_currency );
			if ( is_array( $payments ) && count( $payments ) ) {
				foreach ( $payment_methods as $key => $payment_method ) {
					if ( ! in_array( $key, $payments ) ) {
						unset( $payment_methods[ $key ] );
					}
				}
			}
		}

		return $payment_methods;
	}

	/**
	 * Update checkout page with one currency
	 */
	public function woocommerce_checkout_update_order_review() {
		$allow_multi = $this->settings->get_enable_multi_payment();
		if ( $allow_multi ) {
			$checkout_currency_args = $this->settings->get_checkout_currency_args();
			$current_currency       = $this->settings->get_current_currency();
			$checkout_currency      = $this->settings->get_checkout_currency();
			if ( $checkout_currency && ! in_array( $current_currency, $checkout_currency_args ) ) {
				$this->settings->set_current_currency( $checkout_currency, false );
				$messages = '';
				// Get order review fragment
				ob_start();
				woocommerce_order_review();
				$woocommerce_order_review = ob_get_clean();

				// Get checkout payment fragment
				ob_start();
				woocommerce_checkout_payment();
				$woocommerce_checkout_payment = ob_get_clean();
				wp_send_json(
					array(
						'result'    => 'failure',
						'messages'  => $messages,
						'reload'    => true,
						'fragments' => apply_filters(
							'woocommerce_update_order_review_fragments', array(
								'.woocommerce-checkout-review-order-table' => $woocommerce_order_review,
								'.woocommerce-checkout-payment'            => $woocommerce_checkout_payment,
							)
						),
					)
				);
			}
		}
	}

	/**
	 * Compare currency on checkout page
	 */
	public function woocommerce_checkout_process() {
		$allow_multi = $this->settings->get_enable_multi_payment();
		if ( $allow_multi ) {
			$checkout_currency_args = $this->settings->get_checkout_currency_args();
			$current_currency       = $this->settings->get_current_currency();
			$checkout_currency      = $this->settings->get_checkout_currency();
			if ( $checkout_currency && ! in_array( $current_currency, $checkout_currency_args ) ) {
				$this->settings->set_current_currency( $checkout_currency, false );
				$this->send_ajax_failure_response();
			}
		}
	}

	/**
	 * If checkout failed during an AJAX call, send failure response.
	 */
	protected function send_ajax_failure_response() {
		if ( is_ajax() ) {
			// only print notices if not reloading the checkout, otherwise they're lost in the page reload
			if ( ! isset( WC()->session->reload_checkout ) ) {
				ob_start();
				wc_print_notices();
				$messages = ob_get_clean();
			}

			$response = array(
				'result'   => 'failure',
				'messages' => isset( $messages ) ? $messages : '',
				'refresh'  => isset( WC()->session->refresh_totals ),
				'reload'   => isset( WC()->session->reload_checkout ),
			);

			unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

			wp_send_json( $response );
		}
	}
}
