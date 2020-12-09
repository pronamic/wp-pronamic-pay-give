<?php
/**
 * Give Helper
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2020 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

namespace Pronamic\WordPress\Pay\Extensions\Give;

use Pronamic\WordPress\Pay\Address;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\Customer;

/**
 * Give Helper
 *
 * @version 2.2.0
 * @since   2.2.0
 */
class GiveHelper {
	/**
	 * Get title.
	 *
	 * @param int $donation_id Donation ID.
	 * @return string
	 */
	public static function get_title( $donation_id ) {
		return \sprintf(
			/* translators: %s: Give donation ID */
			__( 'Give donation %s', 'pronamic_ideal' ),
			$donation_id
		);
	}

	/**
	 * Get description.
	 *
	 * @return string
	 */
	public static function get_description( $gateway, $donation_id ) {
		$search = array(
			'{donation_id}',
		);

		$replace = array(
			$donation_id,
		);

		$description = $gateway->get_transaction_description();

		if ( '' === $description ) {
			$description = self::get_title( $donation_id );
		}

		return str_replace( $search, $replace, $description );
	}

	/**
	 * Get value from array.
	 *
	 * @param array  $array Array.
	 * @param string $key   Key.
	 * @return string|null
	 */
	private static function get_value_from_array( $array, $key ) {
		if ( ! array_key_exists( $key, $array ) ) {
			return null;
		}

		return $array[ $key ];
	}

	/**
	 * Get customer from user data.
	 */
	public static function get_customer_from_user_info( $user_info, $donation_id ) {
		$name    = self::get_name_from_user_info( $user_info );
		$email   = \give_get_payment_user_email( $donation_id );
		$phone   = null;
		$user_id = null;

		$customer_data = array(
			$name,
			$email,
			$phone,
			$user_id,
		);

		$customer_data = \array_filter( $customer_data );

		if ( empty( $customer_data ) ) {
			return null;
		}

		$customer = new Customer();

		$customer->set_name( $name );

		if ( ! empty( $email ) ) {
			$customer->set_email( $email );
		}

		if ( ! empty( $phone ) ) {
			$customer->set_phone( $phone );
		}

		if ( ! empty( $user_id ) ) {
			$customer->set_user_id( \intval( $user_id ) );
		}

		return $customer;
	}

	/**
	 * Get name from user data.
	 */
	public static function get_name_from_user_info( $user_info ) {
		$first_name = self::get_value_from_array( $user_info, 'first_name' );
		$last_name  = self::get_value_from_array( $user_info, 'last_name' );

		$name_data = array(
			$first_name,
			$last_name,
		);

		$name_data = \array_filter( $name_data );

		if ( empty( $name_data ) ) {
			return null;
		}

		$name = new ContactName();

		if ( ! empty( $first_name ) ) {
			$name->set_first_name( $first_name );
		}

		if ( ! empty( $last_name ) ) {
			$name->set_last_name( $last_name );
		}
		
		return $name;
	}

	/**
	 * Get address from user info.
	 */
	public static function get_address_from_user_info( $user_info, $donation_id ) {
		$address_info = self::get_value_from_array( $user_info, 'address' );

		$name         = self::get_name_from_user_info( $user_info );
		$line_1       = self::get_value_from_array( $address_info, 'line1' );
		$line_2       = self::get_value_from_array( $address_info, 'line2' );
		$postal_code  = self::get_value_from_array( $address_info, 'zip' );
		$city         = self::get_value_from_array( $address_info, 'city' );
		$state        = null;
		$country_code = null;
		$email        = \give_get_payment_user_email( $donation_id );
		$phone        = null;

		$address_data = array(
			$name,
			$line_1,
			$line_2,
			$postal_code,
			$city,
			$state,
			$country_code,
			$email,
			$phone,
		);

		$address_data = \array_filter( $address_data );

		if ( empty( $address_data ) ) {
			return;
		}

		$address = new Address();

		if ( ! empty( $name ) ) {
			$address->set_name( $name );
		}

		if ( ! empty( $line_1 ) ) {
			$address->set_line_1( $line_1 );
		}

		if ( ! empty( $line_2 ) ) {
			$address->set_line_2( $line_2 );
		}

		if ( ! empty( $postal_code ) ) {
			$address->set_postal_code( $postal_code );
		}

		if ( ! empty( $city ) ) {
			$address->set_city( $city );
		}

		if ( ! empty( $state ) ) {
			$address->set_region( $state );
		}

		if ( ! empty( $country_code ) ) {
			$address->set_country_code( $country_code );
		}

		if ( ! empty( $email ) ) {
			$address->set_email( $email );
		}

		if ( ! empty( $phone ) ) {
			$address->set_phone( $phone );
		}

		return $address;
	}
}
