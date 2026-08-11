<?php
/**
 * Plugin Name: Pronamic Pay Give Add-On
 * Plugin URI: https://www.pronamic.eu/plugins/pronamic-pay-give/
 * Description: Extend the Pronamic Pay plugin with Give support to receive payments through a variety of payment providers.
 *
 * Version: 4.4.0-dev
 * Requires at least: 4.7
 * Requires PHP: 8.1
 *
 * Author: Pronamic
 * Author URI: https://www.pronamic.eu/
 *
 * Text Domain: pronamic-pay-give
 * Domain Path: /languages/
 *
 * License: GPL-2.0-or-later
 *
 * Requires Plugins: pronamic-ideal, give
 * Depends: wp-pay/core
 *
 * GitHub URI: https://github.com/pronamic/wp-pronamic-pay-give
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2026 Pronamic
 * @license   GPL-2.0-or-later
 * @package   Pronamic\WordPress\Pay\Extensions\Give
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload.
 */
$autoload_path = __DIR__ . '/vendor/autoload_packages.php';

if ( file_exists( $autoload_path ) ) {
	require_once $autoload_path;
}

/**
 * Bootstrap.
 */
add_filter(
	'pronamic_pay_plugin_integrations',
	function ( $integrations ) {
		foreach ( $integrations as $integration ) {
			if ( $integration instanceof \Pronamic\WordPress\Pay\Extensions\Give\Extension ) {
				return $integrations;
			}
		}

		$integrations[] = new \Pronamic\WordPress\Pay\Extensions\Give\Extension();

		return $integrations;
	}
);
