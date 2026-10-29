<?php
/**
 * Settings under WooCommerce > Settings > Tax > EU VAT validator.
 *
 * @package WSEVV
 */

defined( 'ABSPATH' ) || exit;

/**
 * Settings.
 */
class WSEVV_Settings {

	/**
	 * Hook in.
	 */
	public static function init() {
		add_filter( 'woocommerce_get_sections_tax', array( __CLASS__, 'section' ) );
		add_filter( 'woocommerce_get_settings_tax', array( __CLASS__, 'settings' ), 10, 2 );
	}

	/**
	 * Get an option.
	 *
	 * @param string $name Option name without prefix.
	 * @return string
	 */
	public static function get( $name ) {
		$defaults = array(
			'api_key'        => '',
			'requester'      => '',
			'reverse_charge' => 'yes',
			'required'       => 'no',
			'on_invalid'     => 'block',
			'on_unavailable' => 'charge',
		);
		return (string) get_option( 'wsevv_' . $name, isset( $defaults[ $name ] ) ? $defaults[ $name ] : '' );
	}

	/**
	 * Add section.
	 *
	 * @param array $sections Sections.
	 * @return array
	 */
	public static function section( $sections ) {
		$sections['wsevv'] = __( 'EU VAT validator', 'w-system-eu-vat-check' );
		return $sections;
	}

	/**
	 * Section settings.
	 *
	 * @param array  $settings Settings.
	 * @param string $current_section Section.
	 * @return array
	 */
	public static function settings( $settings, $current_section ) {
		if ( 'wsevv' !== $current_section ) {
			return $settings;
		}
		return array(
			array(
				'title' => __( 'W System EU VAT Check for WooCommerce', 'w-system-eu-vat-check' ),
				'type'  => 'title',
				'desc'  => __( 'VAT numbers are checked against the official EU VIES service through the W System API. Every feature of this plugin works without an API key: the free tier of the W System API allows 20 checks per day per server IP address, and results are cached for 24 hours. An API key only raises that external service quota.', 'w-system-eu-vat-check' ) . ' ' . sprintf(
					/* translators: %s: URL of the free API key form. */
					__( 'Need more than 20 checks a day? <a href="%s" target="_blank" rel="noopener">Get a free API key</a> (500 units a month, email only, no card) and paste it below.', 'w-system-eu-vat-check' ),
					esc_url( 'https://api.wsystem.dev/account/free-key?ref=woo-plugin' )
				),
				'id'    => 'wsevv_title',
			),
			array(
				'title'    => __( 'API key (optional)', 'w-system-eu-vat-check' ),
				'id'       => 'wsevv_api_key',
				'type'     => 'password',
				'desc_tip' => __( 'Only needed for more than 20 checks per day. Sent to api.wsystem.dev as the X-API-Key header. Leave empty to use the free tier; no plugin feature depends on it.', 'w-system-eu-vat-check' ),
				'desc'     => sprintf(
					/* translators: %s: URL of the free API key form. */
					__( 'Optional. <a href="%s" target="_blank" rel="noopener">Get a free API key</a>: 500 units a month, no card.', 'w-system-eu-vat-check' ),
					esc_url( 'https://api.wsystem.dev/account/free-key?ref=woo-plugin' )
				),
			),
			array(
				'title'    => __( 'Your EU VAT number', 'w-system-eu-vat-check' ),
				'id'       => 'wsevv_requester',
				'type'     => 'text',
				'desc_tip' => __( 'Your own VAT number, so VIES returns a consultation number (proof of the check) that is saved on each order.', 'w-system-eu-vat-check' ),
			),
			array(
				'title'   => __( 'Reverse charge', 'w-system-eu-vat-check' ),
				'id'      => 'wsevv_reverse_charge',
				'type'    => 'checkbox',
				'default' => 'yes',
				'desc'    => __( 'Remove VAT when the VAT number is valid and from an EU country other than your store base country.', 'w-system-eu-vat-check' ),
			),
			array(
				'title'   => __( 'Require VAT number', 'w-system-eu-vat-check' ),
				'id'      => 'wsevv_required',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Invalid VAT number', 'w-system-eu-vat-check' ),
				'id'      => 'wsevv_on_invalid',
				'type'    => 'select',
				'default' => 'block',
				'options' => array(
					'block'  => __( 'Block checkout with an error', 'w-system-eu-vat-check' ),
					'charge' => __( 'Allow checkout and charge VAT', 'w-system-eu-vat-check' ),
				),
			),
			array(
				'title'   => __( 'VIES unavailable', 'w-system-eu-vat-check' ),
				'id'      => 'wsevv_on_unavailable',
				'type'    => 'select',
				'default' => 'charge',
				'desc'    => __( 'When the member state service is down or the daily quota is used up.', 'w-system-eu-vat-check' ),
				'options' => array(
					'charge' => __( 'Allow checkout and charge VAT', 'w-system-eu-vat-check' ),
					'exempt' => __( 'Allow checkout and apply reverse charge (verify later)', 'w-system-eu-vat-check' ),
					'block'  => __( 'Block checkout', 'w-system-eu-vat-check' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wsevv_title',
			),
		);
	}
}
