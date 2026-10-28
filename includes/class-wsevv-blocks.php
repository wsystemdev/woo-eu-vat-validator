<?php
/**
 * Block-based Checkout support (WooCommerce Store API, additional checkout fields).
 *
 * @package WSEVV
 */

defined( 'ABSPATH' ) || exit;

/**
 * Block checkout integration.
 *
 * Registers the EU VAT number as an additional address field, applies the
 * reverse charge when the customer address is updated through the Store API,
 * blocks invalid numbers according to the settings, and stores the VIES result
 * on the order with the same meta keys as the classic checkout.
 */
class WSEVV_Blocks {

	const FIELD = 'wsevv/vat-number';

	/**
	 * Hook in.
	 */
	public static function init() {
		add_action( 'woocommerce_init', array( __CLASS__, 'register_field' ) );
		add_action( 'woocommerce_blocks_validate_location_address_fields', array( __CLASS__, 'validate_address' ), 10, 3 );
		add_action( 'woocommerce_store_api_cart_update_customer_from_request', array( __CLASS__, 'apply_exemption' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_update_customer_from_request', array( __CLASS__, 'apply_exemption' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'save_order_meta' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_order_processed', array( __CLASS__, 'add_note' ) );
	}

	/**
	 * Register the field (WooCommerce 8.9+).
	 */
	public static function register_field() {
		if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
			return;
		}
		woocommerce_register_additional_checkout_field(
			array(
				'id'                => self::FIELD,
				'label'             => __( 'EU VAT number', 'w-system-eu-vat-check' ),
				'optionalLabel'     => __( 'EU VAT number (optional, businesses)', 'w-system-eu-vat-check' ),
				'location'          => 'address',
				'type'              => 'text',
				'required'          => false,
				'attributes'        => array(
					'autocomplete' => 'off',
					'title'        => __( 'Businesses in another EU country: enter a valid VAT number to buy without VAT (reverse charge).', 'w-system-eu-vat-check' ),
				),
				'sanitize_callback' => array( 'WSEVV_API', 'normalize' ),
			)
		);
	}

	/**
	 * Read the VAT number and country from a Store API address array.
	 *
	 * @param mixed $address Address data.
	 * @return array{0:string,1:string}
	 */
	private static function from_address( $address ) {
		if ( ! is_array( $address ) ) {
			return array( '', '' );
		}
		$vat     = isset( $address[ self::FIELD ] ) ? (string) $address[ self::FIELD ] : '';
		$country = isset( $address['country'] ) ? (string) $address['country'] : '';
		return array( sanitize_text_field( $vat ), sanitize_text_field( $country ) );
	}

	/**
	 * Validate the billing address group.
	 *
	 * @param WP_Error $errors Errors.
	 * @param array    $fields Address fields of the group.
	 * @param string   $group  billing or shipping.
	 */
	public static function validate_address( $errors, $fields, $group ) {
		if ( 'billing' !== $group ) {
			return;
		}
		list( $vat, $country ) = self::from_address( $fields );
		if ( '' === trim( $vat ) ) {
			if ( 'yes' === WSEVV_Settings::get( 'required' ) ) {
				$errors->add( 'wsevv_required', __( 'Please enter your EU VAT number.', 'w-system-eu-vat-check' ) );
			}
			return;
		}
		$d = WSEVV_Checkout::decide( $vat, $country );
		if ( $d['error'] ) {
			$errors->add( 'wsevv_invalid', $d['error'] );
		}
	}

	/**
	 * Apply or remove the VAT exemption from the billing address in a Store API request.
	 *
	 * @param WC_Customer     $customer Customer.
	 * @param WP_REST_Request $request  Request.
	 */
	public static function apply_exemption( $customer, $request ) {
		$billing = $request['billing_address'];
		if ( null === $billing ) {
			return;
		}
		list( $vat, $country ) = self::from_address( $billing );
		$d                     = WSEVV_Checkout::decide( $vat, $country );
		$exempt                = $d['exempt'] && '' === $d['error'];
		$customer->set_is_vat_exempt( $exempt );
		if ( WC()->customer && WC()->customer !== $customer ) {
			WC()->customer->set_is_vat_exempt( $exempt );
		}
	}

	/**
	 * Store the VIES result on the order.
	 *
	 * @param WC_Order        $order   Order.
	 * @param WP_REST_Request $request Request.
	 */
	public static function save_order_meta( $order, $request ) {
		list( $vat, $country ) = self::from_address( $request['billing_address'] );
		if ( '' === trim( $vat ) ) {
			return;
		}
		WSEVV_Checkout::write_meta( $order, WSEVV_Checkout::decide( $vat, $country ? $country : $order->get_billing_country() ) );
	}

	/**
	 * Add the order note once the order is placed.
	 *
	 * @param WC_Order $order Order.
	 */
	public static function add_note( $order ) {
		WSEVV_Checkout::add_note( $order->get_id(), array(), $order );
	}
}
