<?php
/**
 * Gateway
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2022 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Give\Helpers\Form\Utils as FormUtils;
use Pronamic\WordPress\Money\Currency;
use Pronamic\WordPress\Money\Money;
use Pronamic\WordPress\Pay\Core\PaymentMethods;
use Pronamic\WordPress\Pay\Plugin;
use Pronamic\WordPress\Pay\Payments\Payment;

/**
 * Title: Give gateway
 * Description:
 * Copyright: 2005-2022 Pronamic
 * Company: Pronamic
 *
 * @author  Reüel van der Steege
 * @version 2.0.4
 * @since   1.0.0
 */
class Gateway {
	/**
	 * The payment method
	 *
	 * @var string
	 */
	protected $payment_method;

	/**
	 * Unique identifier.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Constructs and initialize a gateway.
	 *
	 * @param string $id             Gateway ID.
	 * @param string $name           Gateway name.
	 * @param string $payment_method Gateway payment method.
	 */
	public function __construct( $id = 'pronamic_pay', $payment_method = null ) {
		$this->id             = $id;
		$this->payment_method = $payment_method;

		// Add filters and actions.
		add_filter( 'give_get_settings_gateways', array( $this, 'gateway_settings' ) );
		add_filter( 'give_get_sections_gateways', array( $this, 'gateways_sections' ) );

		add_action( 'give_gateway_' . $this->id, array( $this, 'process_purchase' ) );

		if ( defined( 'GIVE_VERSION' ) && version_compare( GIVE_VERSION, '1.7', '>=' ) ) {
			add_action( 'give_donation_form_before_submit', array( $this, 'before_submit_input_fields' ) );
		} else {
			add_action( 'give_purchase_form_before_submit', array( $this, 'before_submit_input_fields' ) );
		}

		add_action( 'give_' . $this->id . '_cc_form', array( $this, 'payment_fields' ) );
	}

	/**
	 * Add gateways section.
	 *
	 * @param array $sections Gateways sections.
	 *
	 * @return  array
	 * @since   2.0.3
	 */
	public function gateways_sections( $sections ) {
		// Section title.
		$title = \__( 'Pronamic', 'pronamic_ideal' );

		if ( null !== $this->payment_method ) {
			$title = \sprintf(
				'%s - %s',
				$title,
				PaymentMethods::get_name( $this->payment_method, __( 'Pronamic', 'pronamic_ideal' ) )
			);
		}

		// Set section.
		$sections[ $this->id ] = $title;

		return $sections;
	}

	/**
	 * Register gateway settings.
	 *
	 * @param   array $settings Gateway settings.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function gateway_settings( $settings ) {
		$current_section = give_get_current_setting_section();

		// Check if current section is the gateway ID.
		if ( $this->id !== $current_section ) {
			return $settings;
		}

		$description = '';

		if ( 'pronamic_pay' === $this->id ) {
			$description = __( "This payment method does not use a predefined payment method for the payment. Some payment providers list all activated payment methods for your account to choose from. Use payment method specific gateways (such as 'iDEAL') to let customers choose their desired payment method at checkout.", 'pronamic_ideal' );
		}

		$settings[] = array(
			'desc' => $description,
			'id'   => sprintf( 'give_title_%s', $this->id ),
			'type' => 'title',
		);

		$settings[] = array(
			'name'    => __( 'Configuration', 'pronamic_ideal' ),
			'desc'    => '',
			'id'      => sprintf( 'give_%s_configuration', $this->id ),
			'type'    => 'select',
			'options' => Plugin::get_config_select_options( $this->payment_method ),
			'default' => $this->get_config_id(),
		);

		$settings[] = array(
			'name'    => __( 'Transaction description', 'pronamic_ideal' ),
			'desc'    => sprintf(
				/* translators: %s: <code>{tag}</code> */
				__( 'Available tags: %s', 'pronamic_ideal' ),
				sprintf( '<code>%s</code>', '{donation_id}' )
			),
			'id'      => sprintf( 'give_%s_transaction_description', $this->id ),
			'type'    => 'text',
			'default' => __( 'Give donation {donation_id}', 'pronamic_ideal' ),
		);

		$settings[] = array(
			'id'   => sprintf( 'give_title_gateway_settings_%s', $this->id ),
			'type' => 'sectionend',
		);

		return $settings;
	}

	/**
	 * Info fields.
	 *
	 * @param int $form_id Form ID.
	 */
	public function input_fields( $form_id ) {
		$payment_mode = give_get_chosen_gateway( $form_id );

		if ( $this->id !== $payment_mode ) {
			return;
		}

		// Errors.
		if ( filter_has_var( INPUT_GET, 'payment-error' ) ) {
			printf(
				'<div class="give_error">%s</div>',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				Plugin::get_default_error_message()
			);
		}

		// Gateway.
		$config_id = $this->get_config_id();

		try {
			$gateway = Plugin::get_gateway( $config_id );

			if ( null === $gateway ) {
				return;
			}

			$gateway->set_payment_method( $this->payment_method );

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $gateway->get_input_html();
		} catch ( \Exception $e ) {
			printf(
				'<div class="give_error">%s<br /><br />%s</div>',
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				Plugin::get_default_error_message(),
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				sprintf( '%s: %s', $e->getCode(), $e->getMessage() )
			);
		}
	}

	/**
	 * Legacy input fields.
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 */
	public function before_submit_input_fields( $form_id ) {
		if ( \class_exists( 'Give\Helpers\Form\Utils' ) && ! FormUtils::isLegacyForm( $form_id ) ) {
			return;
		}

		$this->input_fields( $form_id );
	}

	/**
	 * Input fields.
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 */
	public function payment_fields( $form_id ) {
		if ( ! \class_exists( 'Give\Helpers\Form\Utils' ) ) {
			return;
		}

		if ( FormUtils::isLegacyForm( $form_id ) ) {
			return;
		}

		$this->input_fields( $form_id );
	}

	/**
	 * Process purchase.
	 *
	 * @since 1.0.0
	 *
	 * @param array $purchase_data Purchase Data.
	 *
	 * @return void
	 */
	public function process_purchase( $purchase_data ) {
		if ( ! wp_verify_nonce( $purchase_data['gateway_nonce'], 'give-gateway' ) ) {
			wp_die( esc_html__( 'Nonce verification has failed', 'pronamic_ideal' ), esc_html__( 'Error', 'pronamic_ideal' ), array( 'response' => 403 ) );
		}

		$form_id = intval( $purchase_data['post_data']['give-form-id'] );

		// Collect payment data.
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

		// Record the pending payment.
		$donation_id = give_insert_payment( $payment_data );

		if ( false === $donation_id ) {
			/*
			 * Record the error.
			 * /wp-admin/edit.php?post_type=give_forms&page=give-reports&tab=logs&view=gateway_errors
			 * @link https://github.com/WordImpress/Give/blob/1.3.6/includes/gateways/functions.php#L267-L285
			 */
			give_record_gateway_error(
				__( 'Payment Error', 'pronamic_ideal' ),
				sprintf(
					/* translators: %s: payment data as JSON */
					__( 'Payment creation failed before sending buyer to payment provider. Payment data: %s', 'pronamic_ideal' ),
					(string) wp_json_encode( $payment_data )
				)
			);

			/*
			 * Problems? Send back.
			 * @link https://github.com/WordImpress/Give/blob/1.3.6/includes/forms/functions.php#L150-L184
			 */
			give_send_back_to_checkout(
				array(
					'payment-error' => true,
					'payment-mode'  => $purchase_data['post_data']['give-gateway'],
				)
			);

			return;
		}

		$config_id = $this->get_config_id();

		$gateway = Plugin::get_gateway( $config_id );

		if ( null === $gateway ) {
			return;
		}

		$gateway->set_payment_method( $this->payment_method );

		$user_info = \give_get_payment_meta_user_info( $donation_id );

		/**
		 * Build payment.
		 */
		$payment = new Payment();

		$payment->source    = 'give';
		$payment->source_id = $donation_id;
		$payment->order_id  = $donation_id;

		$payment->set_description( GiveHelper::get_description( $this, $donation_id ) );

		$payment->title = GiveHelper::get_title( $donation_id );

		// Customer.
		$payment->set_customer( GiveHelper::get_customer_from_user_info( $user_info, $donation_id ) );

		// Address.
		$payment->set_billing_address( GiveHelper::get_address_from_user_info( $user_info, $donation_id ) );

		// Currency.
		$currency = Currency::get_instance( \give_get_payment_currency_code( $donation_id ) );

		// Amount.
		$payment->set_total_amount( new Money( \give_donation_amount( $donation_id ), $currency ) );

		// Payment method.
		$payment->set_payment_method( $this->payment_method );

		// Configuration.
		$payment->config_id = $config_id;

		// Start.
		try {
			$payment = Plugin::start_payment( $payment );

			// Redirect.
			\wp_safe_redirect( $payment->get_pay_redirect_url(), 303 );

			exit;
		} catch ( \Exception $e ) {
			/*
			 * Record the error.
			 * /wp-admin/edit.php?post_type=give_forms&page=give-reports&tab=logs&view=gateway_errors
			 * @link https://github.com/WordImpress/Give/blob/1.3.6/includes/gateways/functions.php#L267-L285
			 */
			give_record_gateway_error(
				__( 'Payment Error', 'pronamic_ideal' ),
				$e->getMessage(),
				$donation_id
			);

			/*
			 * Problems? Send back.
			 * @link https://github.com/WordImpress/Give/blob/1.3.6/includes/forms/functions.php#L150-L184
			 */
			give_send_back_to_checkout(
				array(
					'payment-error' => true,
					'payment-mode'  => $purchase_data['post_data']['give-gateway'],
				)
			);
		}
	}

	/**
	 * Get transaction description setting.
	 *
	 * @since 1.0.3
	 * @return string
	 */
	public function get_transaction_description() {
		return give_get_option( sprintf( 'give_%s_transaction_description', $this->id ), '' );
	}

	/**
	 * Get config ID.
	 *
	 * @return mixed
	 */
	protected function get_config_id() {
		$config_id = give_get_option( sprintf( 'give_%s_configuration', $this->id ) );

		if ( empty( $config_id ) ) {
			// Use default gateway if no configuration has been set.
			$config_id = get_option( 'pronamic_pay_config_id' );
		}

		return $config_id;
	}
}
