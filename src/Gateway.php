<?php

/**
 * Title: Give gateway
 * Description:
 * Copyright: Copyright (c) 2005 - 2016
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Pronamic_WP_Pay_Extensions_Give_Gateway {
	/**
	 * The unique ID of this payment gateway
	 *
	 * @var string
	 */
	const ID = 'pronamic_pay';

	/**
	 * The payment method
	 *
	 * @var string
	 */
	protected $payment_method;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an iDEAL gateway
	 */
	public function __construct() {
		$this->name = __( 'Pronamic', 'pronamic_ideal' );

		add_filter( 'give_settings_gateways', array( $this, 'gateway_settings' ) );

		add_action( 'give_gateway_' . self::ID, array( $this, 'process_purchase' ) );

		// Remove CC form
		add_action( 'give_' . self::ID . '_cc_form', '__return_false' );
	}

	/**
	 * Register gateway settings.
	 *
	 * @param   array   $settings
	 * @return  array
	 * @since   1.0.0
	 */
	public function gateway_settings( $settings ) {
		$settings[] = array(
			'name' => $this->name,
			'desc' => '',
			'id'   => sprintf( 'give_title_%s', self::ID ),
			'type' => 'give_title',
		);

		$settings[] = array(
			'name'    => __( 'Configuration', 'pronamic_ideal' ),
			'desc'    => '',
			'id'      => sprintf( 'give_%s_configuration', self::ID ),
			'type'    => 'select',
			'options' => Pronamic_WP_Pay_Plugin::get_config_select_options( $this->payment_method ),
		);

		return $settings;
	}

	/**
	 * Process purchase.
	 *
	 * @since 1.0.0
	 *
	 * @param array $purchase_data Purchase Data
	 *
	 * @return void
	 */
	function process_purchase( $purchase_data, $gateway = null ) {
		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'give-gateway' ) ) {
			wp_die( __( 'Nonce verification has failed', 'give' ), __( 'Error', 'give' ), array( 'response' => 403 ) );
		}

		$form_id = intval( $purchase_data['post_data']['give-form-id'] );

		// Collect payment data
		$payment_data = array(
			'price'           => $purchase_data['price'],
			'give_form_title' => $purchase_data['post_data']['give-form-title'],
			'give_form_id'    => $form_id,
			'date'            => $purchase_data['date'],
			'user_email'      => $purchase_data['user_email'],
			'purchase_key'    => $purchase_data['purchase_key'],
			'currency'        => give_get_currency(),
			'user_info'       => $purchase_data['user_info'],
			'status'          => 'pending',
			'gateway'         => self::ID,
		);

		// Record the pending payment
		$donation_id = give_insert_payment( $payment_data );

		if ( ! $donation_id ) {
			// Record the error
			give_record_gateway_error( __( 'Payment Error', 'give' ), sprintf( __( 'Payment creation failed before sending buyer to payment provider. Payment data: %s', 'pronamic_ideal' ), json_encode( $payment_data ) ), $donation_id );

			// Problems? send back
			give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
		} else {
			if ( null === $gateway ) {
				$gateway = new self();
			} else {
				$gateway = new $gateway();
			}

			$payment_method = $gateway->payment_method;

			$config_id = give_get_option( sprintf( 'give_%s_configuration', $gateway::ID ) );

			$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );

			if ( $gateway ) {
				// Data
				$data = new Pronamic_WP_Pay_Extensions_Give_PaymentData( $donation_id );

				$gateway->set_payment_method( $payment_method );

				$payment = Pronamic_WP_Pay_Plugin::start( $config_id, $gateway, $data, $payment_method );

				$error = $gateway->get_error();

				if ( ! is_wp_error( $error ) ) {
					// Redirect
					$gateway->redirect( $payment );
				}
			}

//			$listener_url = add_query_arg( 'give-listener', 'IPN', home_url( 'index.php' ) );
//
//			// Get the success url
//			$return_url = add_query_arg( array(
//				'payment-confirmation' => 'paypal',
//				'payment-id'           => $payment
//
//			), get_permalink( give_get_option( 'success_page' ) ) );
//
//			// Get the PayPal redirect uri
//			$paypal_redirect = trailingslashit( give_get_paypal_redirect() ) . '?';
//
//			//Item name - pass level name if variable priced
//			$item_name = $purchase_data['post_data']['give-form-title'];
//
//			//Verify has variable prices
//			if ( give_has_variable_prices( $form_id ) && isset( $purchase_data['post_data']['give-price-id'] ) ) {
//
//				$item_price_level_text = give_get_price_option_name( $form_id, $purchase_data['post_data']['give-price-id'] );
//
//				$price_level_amount = give_get_price_option_amount( $form_id, $purchase_data['post_data']['give-price-id'] );
//
//				//Donation given doesn't match selected level (must be a custom amount)
//				if ( $price_level_amount != give_sanitize_amount( $purchase_data['price'] ) ) {
//					$custom_amount_text = get_post_meta( $form_id, '_give_custom_amount_text', true );
//					//user custom amount text if any, fallback to default if not
//					$item_name .= ' - ' . ( ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'give' ) );
//
//				} //Is there any donation level text?
//				elseif ( ! empty( $item_price_level_text ) ) {
//					$item_name .= ' - ' . $item_price_level_text;
//				}
//
//			} //Single donation: Custom Amount
//			elseif ( give_get_form_price( $form_id ) !== give_sanitize_amount( $purchase_data['price'] ) ) {
//				$custom_amount_text = get_post_meta( $form_id, '_give_custom_amount_text', true );
//				//user custom amount text if any, fallback to default if not
//				$item_name .= ' - ' . ( ! empty( $custom_amount_text ) ? $custom_amount_text : __( 'Custom Amount', 'give' ) );
//			}
//
//			// Setup PayPal arguments
//			$paypal_args = array(
//				'business'      => give_get_option( 'paypal_email', false ),
//				'email'         => $purchase_data['user_email'],
//				'invoice'       => $purchase_data['purchase_key'],
//				'amount'        => $purchase_data['price'],
//				// The all important donation amount
//				'item_name'     => $item_name,
//				// "Purpose" field pre-populated with Form Title
//				'no_shipping'   => '1',
//				'shipping'      => '0',
//				'no_note'       => '1',
//				'currency_code' => give_get_currency(),
//				'charset'       => get_bloginfo( 'charset' ),
//				'custom'        => $payment,
//				'rm'            => '2',
//				'return'        => $return_url,
//				'cancel_return' => give_get_failed_transaction_uri( '?payment-id=' . $payment ),
//				'notify_url'    => $listener_url,
//				'page_style'    => give_get_paypal_page_style(),
//				'cbt'           => get_bloginfo( 'name' ),
//				'bn'            => 'givewp_SP'
//			);
//
//			if ( ! empty( $purchase_data['user_info']['address'] ) ) {
//				$paypal_args['address1'] = $purchase_data['user_info']['address']['line1'];
//				$paypal_args['address2'] = $purchase_data['user_info']['address']['line2'];
//				$paypal_args['city']     = $purchase_data['user_info']['address']['city'];
//				$paypal_args['country']  = $purchase_data['user_info']['address']['country'];
//			}
//
//			if ( give_get_option( 'paypal_button_type' ) === 'standard' ) {
//				$paypal_extra_args = array(
//					'cmd' => '_xclick',
//				);
//			} else {
//				$paypal_extra_args = array(
//					'cmd' => '_donations',
//				);
//			}
//
//			$paypal_args = array_merge( $paypal_extra_args, $paypal_args );
//
//
//			$paypal_args = apply_filters( 'give_paypal_redirect_args', $paypal_args, $purchase_data );
//
//			// Build query
//			$paypal_redirect .= http_build_query( $paypal_args );
//
//			// Fix for some sites that encode the entities
//			$paypal_redirect = str_replace( '&amp;', '&', $paypal_redirect );
//
//			// Redirect to PayPal
//			wp_redirect( $paypal_redirect );
//			exit;
		}

	}

	///**
	// * Process donation.
	// *
	// * @param   int                            $donation_id
	// * @param   Charitable_Donation_Processor  $processor
	// * @param   string                         $gateway
	// * @since   1.0.0
	// */
	//public static function process_donation( $donation_id, $processor, $gateway = null ) {
	//	if ( null === $gateway ) {
	//		$gateway = new self();
	//	} else {
	//		$gateway = new $gateway();
	//	}
	//
	//	$payment_method = $gateway->payment_method;
	//
	//	$config_id = $gateway->get_value( 'config_id' );
	//
	//	$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );
	//
	//	if ( $gateway ) {
	//		// Data
	//		$data = new Pronamic_WP_Pay_Extensions_Charitable_PaymentData( $donation_id, $processor );
	//
	//		$gateway->set_payment_method( $payment_method );
	//
	//		$payment = Pronamic_WP_Pay_Plugin::start( $config_id, $gateway, $data, $payment_method );
	//
	//		$error = $gateway->get_error();
	//
	//		if ( ! is_wp_error( $error ) ) {
	//			// Redirect
	//			$gateway->redirect( $payment );
	//		}
	//	}
	//}

	/**
	 * Returns the current gateway's ID.
	 *
	 * @return  string
	 * @access  public
	 * @static
	 * @since   1.0.0
	 */
	public static function get_gateway_id() {
		return self::ID;
	}
}
