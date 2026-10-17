<?php
/**
 * Client for the W System VAT validation API (VIES).
 *
 * @package WSEVV
 */

defined( 'ABSPATH' ) || exit;

/**
 * VAT API client.
 */
class WSEVV_API {

	const ENDPOINT = 'https://api.wsystem.dev/verify';

	/**
	 * EU member state VAT prefixes mapped to WooCommerce country codes.
	 *
	 * @var array<string,string>
	 */
	const PREFIXES = array(
		'AT' => 'AT',
		'BE' => 'BE',
		'BG' => 'BG',
		'CY' => 'CY',
		'CZ' => 'CZ',
		'DE' => 'DE',
		'DK' => 'DK',
		'EE' => 'EE',
		'EL' => 'GR',
		'ES' => 'ES',
		'FI' => 'FI',
		'FR' => 'FR',
		'HR' => 'HR',
		'HU' => 'HU',
		'IE' => 'IE',
		'IT' => 'IT',
		'LT' => 'LT',
		'LU' => 'LU',
		'LV' => 'LV',
		'MT' => 'MT',
		'NL' => 'NL',
		'PL' => 'PL',
		'PT' => 'PT',
		'RO' => 'RO',
		'SE' => 'SE',
		'SI' => 'SI',
		'SK' => 'SK',
		'XI' => 'GB',
	);

	/**
	 * Normalise a VAT number: uppercase, strip spaces, dots and dashes; GR -> EL.
	 *
	 * @param string $vat Raw input.
	 * @return string
	 */
	public static function normalize( $vat ) {
		$vat = strtoupper( preg_replace( '/[\s.\-]/', '', (string) $vat ) );
		if ( 0 === strpos( $vat, 'GR' ) ) {
			$vat = 'EL' . substr( $vat, 2 );
		}
		return $vat;
	}

	/**
	 * WooCommerce country code for a VAT number, or '' if not an EU/XI prefix.
	 *
	 * @param string $vat Normalised VAT number.
	 * @return string
	 */
	public static function country_of( $vat ) {
		$prefix = substr( $vat, 0, 2 );
		return isset( self::PREFIXES[ $prefix ] ) ? self::PREFIXES[ $prefix ] : '';
	}

	/**
	 * Validate a VAT number. Results are cached for 24 hours.
	 *
	 * @param string $vat VAT number.
	 * @return array{status:string,valid:bool|null,name?:string,address?:string,consultation_number?:string,request_date?:string,error?:string}
	 */
	public static function verify( $vat ) {
		$vat = self::normalize( $vat );
		if ( ! preg_match( '/^[A-Z]{2}[0-9A-Z+*]{2,13}$/', $vat ) || '' === self::country_of( $vat ) ) {
			return array(
				'status' => 'invalid_format',
				'valid'  => false,
			);
		}
		$key    = 'wsevv_' . md5( $vat );
		$cached = get_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		$args = array( 'vat' => $vat );
		$req  = WSEVV_Settings::get( 'requester' );
		if ( $req ) {
			$args['requester'] = self::normalize( $req );
		}
		$headers = array( 'Accept' => 'application/json' );
		$api_key = WSEVV_Settings::get( 'api_key' );
		if ( $api_key ) {
			$headers['X-API-Key'] = $api_key;
		}
		$response = wp_remote_get(
			add_query_arg( $args, apply_filters( 'wsevv_api_endpoint', self::ENDPOINT ) ),
			array(
				'timeout' => 15,
				'headers' => $headers,
			)
		);
		if ( is_wp_error( $response ) ) {
			return array(
				'status' => 'error',
				'valid'  => null,
				'error'  => $response->get_error_message(),
			);
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( 429 === $code || 401 === $code || 402 === $code ) {
			return array(
				'status' => 'quota',
				'valid'  => null,
				'error'  => 401 === $code ? 'invalid_api_key' : 'quota_exhausted',
			);
		}
		if ( 200 !== $code || ! is_array( $body ) || ! isset( $body['status'] ) ) {
			return array(
				'status' => 'error',
				'valid'  => null,
				'error'  => 'http_' . $code,
			);
		}
		$result = array(
			'status'              => sanitize_key( $body['status'] ),
			'valid'               => isset( $body['valid'] ) ? $body['valid'] : null,
			'name'                => isset( $body['name'] ) ? sanitize_text_field( $body['name'] ) : '',
			'address'             => isset( $body['address'] ) ? sanitize_text_field( $body['address'] ) : '',
			'consultation_number' => isset( $body['consultation_number'] ) ? sanitize_text_field( $body['consultation_number'] ) : '',
			'request_date'        => isset( $body['request_date'] ) ? sanitize_text_field( $body['request_date'] ) : '',
		);
		if ( in_array( $result['status'], array( 'valid', 'invalid' ), true ) ) {
			set_transient( $key, $result, DAY_IN_SECONDS );
		}
		return $result;
	}
}
