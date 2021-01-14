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
use Pronamic\WordPress\Pay\AddressHelper;
use Pronamic\WordPress\Pay\ContactName;
use Pronamic\WordPress\Pay\ContactNameHelper;
use Pronamic\WordPress\Pay\Customer;
use Pronamic\WordPress\Pay\CustomerHelper;

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
		return CustomerHelper::from_array( array(
			'name'    => self::get_name_from_user_info( $user_info ),
			'email'   => \give_get_payment_user_email( $donation_id ),
			'phone'   => null,
			'user_id' => null,
		) );
	}

	/**
	 * Get name from user data.
	 *
	 * @return ContactName|null
	 */
	public static function get_name_from_user_info( $user_info ) {
		return ContactNameHelper::from_array( array(
			'first_name' => self::get_value_from_array( $user_info, 'first_name' ),
			'last_name'  => self::get_value_from_array( $user_info, 'last_name' ),
		) );
	}

	/**
	 * Get address from user info.
	 *
	 * @return Address|null
	 */
	public static function get_address_from_user_info( $user_info, $donation_id ) {
		$address_info = self::get_value_from_array( $user_info, 'address' );

		return AddressHelper::from_array( array(
			'name'         => self::get_name_from_user_info( $user_info ),
			'line_1'       => self::get_value_from_array( $address_info, 'line1' ),
			'line_2'       => self::get_value_from_array( $address_info, 'line2' ),
			'postal_code'  => self::get_value_from_array( $address_info, 'zip' ),
			'city'         => self::get_value_from_array( $address_info, 'city' ),
			'region'       => null,
			'country_code' => null,
			'email'        => \give_get_payment_user_email( $donation_id ),
			'phone'        => null,
		) );
	}
}
