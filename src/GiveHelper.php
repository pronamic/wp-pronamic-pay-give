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
			__( 'Give donation %s', 'pronamic_ideal' ),
			$donation_id
		);
	}

	/**
	 * Get description.
	 *
	 * @param Gateway $gateway Gateway.
	 * @param int     $donation_id Donation ID.
	 *
	 * @return string
	 */
	public static function get_description( Gateway $gateway, int $donation_id ): string {
		$search = [
			'{donation_id}',
		];

		$replace = [
			(string) $donation_id,
		];

		$description = $gateway->get_transaction_description();

		if ( '' === $description ) {
			$description = self::get_title( $donation_id );
		}

		return str_replace( $search, $replace, $description );
	}

	/**
	 * Get value from array.
	 *
	 * @param array<string, mixed> $data  Array.
	 * @param string                $key   Key.
	 * @return mixed
	 */
	private static function get_value_from_array( array $data, string $key ): mixed {
		if ( ! array_key_exists( $key, $data ) ) {
			return null;
		}

		return $data[ $key ];
	}

	/**
	 * Get customer from user data.
	 *
	 * @param array<string, mixed> $user_info   User info.
	 * @param int                  $donation_id Donation ID.
	 *
	 * @return Customer|null
	 */
	public static function get_customer_from_user_info( array $user_info, int $donation_id ): ?Customer {
		return CustomerHelper::from_array(
			[
				'name'    => self::get_name_from_user_info( $user_info ),
				'email'   => \give_get_payment_user_email( $donation_id ),
				'phone'   => null,
				'user_id' => null,
			]
		);
	}

	/**
	 * Get name from user data.
	 *
	 * @param array<string, mixed> $user_info User info.
	 *
	 * @return ContactName|null
	 */
	public static function get_name_from_user_info( array $user_info ): ?ContactName {
		return ContactNameHelper::from_array(
			[
				'first_name' => self::get_value_from_array( $user_info, 'first_name' ),
				'last_name'  => self::get_value_from_array( $user_info, 'last_name' ),
			]
		);
	}

	/**
	 * Get address from user info.
	 *
	 * @param array<string, mixed> $user_info   User info.
	 * @param int                  $donation_id Donation ID.
	 *
	 * @return Address|null
	 */
	public static function get_address_from_user_info( array $user_info, int $donation_id ): ?Address {
		$address_info = self::get_value_from_array( $user_info, 'address' );

		if ( ! \is_array( $address_info ) ) {
			$address_info = [];
		}

		return AddressHelper::from_array(
			[
				'name'         => self::get_name_from_user_info( $user_info ),
				'line_1'       => self::get_value_from_array( $address_info, 'line1' ),
				'line_2'       => self::get_value_from_array( $address_info, 'line2' ),
				'postal_code'  => self::get_value_from_array( $address_info, 'zip' ),
				'city'         => self::get_value_from_array( $address_info, 'city' ),
				'region'       => null,
				'country_code' => null,
				'email'        => \give_get_payment_user_email( $donation_id ),
				'phone'        => null,
			]
		);
	}
}
