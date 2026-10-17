<?php
/**
 * Plugin Name:       EU VAT Number Validator by W System
 * Plugin URI:        https://github.com/zifengw/woo-eu-vat-validator
 * Description:       Validates EU VAT numbers at WooCommerce checkout against the official VIES service and applies B2B reverse charge (VAT exemption) for valid cross-border EU customers. Stores the VIES consultation number on the order.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            W System
 * Author URI:        https://wsystem.dev
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       eu-vat-validator-wsystem
 * Requires Plugins:  woocommerce
 * WC requires at least: 8.0
 * WC tested up to:   11.2
 *
 * @package WSEVV
 */

defined( 'ABSPATH' ) || exit;

define( 'WSEVV_VERSION', '1.0.0' );
define( 'WSEVV_FILE', __FILE__ );
define( 'WSEVV_DIR', plugin_dir_path( __FILE__ ) );

require_once WSEVV_DIR . 'includes/class-wsevv-api.php';
require_once WSEVV_DIR . 'includes/class-wsevv-settings.php';
require_once WSEVV_DIR . 'includes/class-wsevv-checkout.php';

add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

add_action(
	'plugins_loaded',
	function () {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		WSEVV_Settings::init();
		WSEVV_Checkout::init();
	}
);

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=tax&section=wsevv' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'eu-vat-validator-wsystem' ) . '</a>' );
		return $links;
	}
);
