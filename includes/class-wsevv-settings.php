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
		$sections['wsevv'] = __( 'EU VAT validator', 'eu-vat-validator-wsystem' );
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
				'title' => __( 'EU VAT Number Validator by W System', 'eu-vat-validator-wsystem' ),
				'type'  => 'title',
				'desc'  => __( 'VAT numbers are checked against the official EU VIES service through the W System API. Without an API key the free tier (20 checks per day) is used; results are cached for 24 hours.', 'eu-vat-validator-wsystem' ),
				'id'    => 'wsevv_title',
			),
			array(
				'title'    => __( 'API key (optional)', 'eu-vat-validator-wsystem' ),
				'id'       => 'wsevv_api_key',
				'type'     => 'password',
				'desc_tip' => __( 'Paid key for higher volume, sent as the X-API-Key header. Leave empty for the free tier.', 'eu-vat-validator-wsystem' ),
			),
			array(
				'title'    => __( 'Your EU VAT number', 'eu-vat-validator-wsystem' ),
				'id'       => 'wsevv_requester',
				'type'     => 'text',
				'desc_tip' => __( 'Your own VAT number, so VIES returns a consultation number (proof of the check) that is saved on each order.', 'eu-vat-validator-wsystem' ),
			),
			array(
				'title'   => __( 'Reverse charge', 'eu-vat-validator-wsystem' ),
				'id'      => 'wsevv_reverse_charge',
				'type'    => 'checkbox',
				'default' => 'yes',
				'desc'    => __( 'Remove VAT when the VAT number is valid and from an EU country other than your store base country.', 'eu-vat-validator-wsystem' ),
			),
			array(
				'title'   => __( 'Require VAT number', 'eu-vat-validator-wsystem' ),
				'id'      => 'wsevv_required',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'title'   => __( 'Invalid VAT number', 'eu-vat-validator-wsystem' ),
				'id'      => 'wsevv_on_invalid',
				'type'    => 'select',
				'default' => 'block',
				'options' => array(
					'block'  => __( 'Block checkout with an error', 'eu-vat-validator-wsystem' ),
					'charge' => __( 'Allow checkout and charge VAT', 'eu-vat-validator-wsystem' ),
				),
			),
			array(
				'title'   => __( 'VIES unavailable', 'eu-vat-validator-wsystem' ),
				'id'      => 'wsevv_on_unavailable',
				'type'    => 'select',
				'default' => 'charge',
				'desc'    => __( 'When the member state service is down or the daily quota is used up.', 'eu-vat-validator-wsystem' ),
				'options' => array(
					'charge' => __( 'Allow checkout and charge VAT', 'eu-vat-validator-wsystem' ),
					'exempt' => __( 'Allow checkout and apply reverse charge (verify later)', 'eu-vat-validator-wsystem' ),
					'block'  => __( 'Block checkout', 'eu-vat-validator-wsystem' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wsevv_title',
			),
		);
	}
}
