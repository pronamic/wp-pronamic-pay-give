<?php
/**
 * Give Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Give\Donations\Models\Donation;
use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\AddressHelper;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\ContactNameHelper;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\CustomerHelper;

/**
 * Give Helper class
 */
class GiveHelper {
	/**
	 * Get title.
	 *
	 * @param int $donation_id Donation ID.
	 * @return string
	 */
	public static function get_title( int $donation_id ): string {
		return \sprintf(
			/* translators: %s: Give donation ID */
			\__( 'Give donation %s', 'pronamic_ideal' ),
			$donation_id
		);
	}

	/**
	 * Get description.
	 *
	 * @param Gateway  $gateway  Gateway.
	 * @param Donation $donation Donation.
	 * @return string
	 */
	public static function get_description( Gateway $gateway, Donation $donation ): string {
		$description = $gateway->get_transaction_description();

		if ( '' === $description ) {
			$description = self::get_title( $donation->id );
		}

		return \str_replace( '{donation_id}', (string) $donation->id, $description );
	}

	/**
	 * Get customer from donation.
	 *
	 * @param Donation $donation Donation.
	 * @return Customer|null
	 */
	public static function get_customer_from_donation( Donation $donation ): ?Customer {
		return CustomerHelper::from_array(
			[
				'name'    => self::get_name_from_donation( $donation ),
				'email'   => $donation->email,
				'phone'   => '' === $donation->phone ? null : $donation->phone,
				'user_id' => null,
			]
		);
	}

	/**
	 * Get contact name from donation.
	 *
	 * @param Donation $donation Donation.
	 * @return ContactName|null
	 */
	public static function get_name_from_donation( Donation $donation ): ?ContactName {
		return ContactNameHelper::from_array(
			[
				'first_name' => $donation->firstName, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
				'last_name'  => $donation->lastName, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			]
		);
	}

	/**
	 * Get address from donation.
	 *
	 * @param Donation $donation Donation.
	 * @return Address|null
	 */
	public static function get_address_from_donation( Donation $donation ): ?Address {
		$billing_address = $donation->billingAddress; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

		return AddressHelper::from_array(
			[
				'name'         => self::get_name_from_donation( $donation ),
				'line_1'       => $billing_address->address1,
				'line_2'       => $billing_address->address2,
				'postal_code'  => $billing_address->zip,
				'city'         => $billing_address->city,
				'region'       => $billing_address->state,
				'country_code' => $billing_address->country,
				'email'        => $donation->email,
				'phone'        => '' === $donation->phone ? null : $donation->phone,
			]
		);
	}
}
