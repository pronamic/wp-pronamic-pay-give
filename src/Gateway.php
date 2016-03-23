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
	 * The payment method
	 *
	 * @var string
	 */
	protected $payment_method;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize a gateway
	 */
	public function __construct() {
		if ( __CLASS__ === get_class( $this ) ) {
			$this->id = 'pronamic_pay';

			$this->name = __( 'Pronamic', 'pronamic_ideal' );
		}

		// Add filters and actions
		add_filter( 'give_settings_gateways', array( $this, 'gateway_settings' ) );

		add_action( 'give_gateway_' . $this->id, array( $this, 'process_purchase' ) );

		add_action( 'give_purchase_form_after_user_info', array( $this, 'info_fields' ) );

		add_action( 'give_' . $this->id . '_cc_form', '__return_false' );
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
			'id'   => sprintf( 'give_title_%s', $this->id ),
			'type' => 'give_title',
		);

		$settings[] = array(
			'name'    => __( 'Configuration', 'pronamic_ideal' ),
			'desc'    => '',
			'id'      => sprintf( 'give_%s_configuration', $this->id ),
			'type'    => 'select',
			'options' => Pronamic_WP_Pay_Plugin::get_config_select_options( $this->payment_method ),
			'default' => get_option( 'pronamic_pay_config_id' ),
		);

		return $settings;
	}

	function info_fields( $form_id ) {
		$payment_mode = give_get_chosen_gateway( $form_id );

		if ( $this->id === $payment_mode ) {
			$config_id = give_get_option( sprintf( 'give_%s_configuration', $this->id ) );

			$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );

			if ( $gateway ) {
				$gateway->set_payment_method( $this->payment_method );

				echo $gateway->get_input_html();
			}
		}
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
	function process_purchase( $purchase_data ) {
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
			'gateway'         => $this->id,
		);

		// Record the pending payment
		$donation_id = give_insert_payment( $payment_data );

		if ( ! $donation_id ) {
			// Record the error
			give_record_gateway_error( __( 'Payment Error', 'give' ), sprintf( __( 'Payment creation failed before sending buyer to payment provider. Payment data: %s', 'pronamic_ideal' ), json_encode( $payment_data ) ), $donation_id );

			// Problems? send back
			give_send_back_to_checkout( '?payment-mode=' . $purchase_data['post_data']['give-gateway'] );
		} else {
			$config_id = give_get_option( sprintf( 'give_%s_configuration', $this->id ) );

			$gateway = Pronamic_WP_Pay_Plugin::get_gateway( $config_id );

			if ( $gateway ) {
				// Data
				$data = new Pronamic_WP_Pay_Extensions_Give_PaymentData( $donation_id );

				$gateway->set_payment_method( $this->payment_method );

				$payment = Pronamic_WP_Pay_Plugin::start( $config_id, $gateway, $data, $this->payment_method );

				$error = $gateway->get_error();

				if ( ! is_wp_error( $error ) ) {
					// Redirect
					$gateway->redirect( $payment );
				}
			}
		}
	}
}
