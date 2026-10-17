<?php
/**
 * Checkout field, validation, reverse charge and order meta.
 *
 * @package WSEVV
 */

defined( 'ABSPATH' ) || exit;

/**
 * Checkout integration (classic checkout).
 */
class WSEVV_Checkout {

	const FIELD = 'billing_eu_vat_number';

	/**
	 * Hook in.
	 */
	public static function init() {
		add_filter( 'woocommerce_billing_fields', array( __CLASS__, 'add_field' ) );
		add_action( 'woocommerce_checkout_update_order_review', array( __CLASS__, 'update_order_review' ) );
		add_action( 'woocommerce_after_checkout_validation', array( __CLASS__, 'validate' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'save_order_meta' ), 10, 2 );
		add_action( 'woocommerce_checkout_order_processed', array( __CLASS__, 'add_note' ), 10, 3 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( __CLASS__, 'admin_display' ) );
		add_action( 'woocommerce_email_after_order_table', array( __CLASS__, 'email_display' ), 10, 2 );
		add_action( 'woocommerce_order_details_after_order_table', array( __CLASS__, 'admin_display' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'script' ) );
	}

	/**
	 * Add the VAT number field to billing fields.
	 *
	 * @param array $fields Fields.
	 * @return array
	 */
	public static function add_field( $fields ) {
		$fields[ self::FIELD ] = array(
			'label'       => __( 'EU VAT number', 'w-system-vies-check' ),
			'placeholder' => __( 'e.g. DE123456789', 'w-system-vies-check' ),
			'required'    => 'yes' === WSEVV_Settings::get( 'required' ),
			'class'       => array( 'form-row-wide', 'update_totals_on_change' ),
			'priority'    => 35,
			'description' => __( 'Businesses in another EU country: enter a valid VAT number to buy without VAT (reverse charge).', 'w-system-vies-check' ),
		);
		return $fields;
	}

	/**
	 * Refresh totals when the field changes.
	 */
	public static function script() {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		wp_add_inline_script( 'wc-checkout', "jQuery(function($){ $(document.body).on('change', '#billing_eu_vat_number', function(){ $(document.body).trigger('update_checkout'); }); });" );
	}

	/**
	 * Decide what to do for a VAT number and billing country.
	 *
	 * @param string $vat     VAT number.
	 * @param string $country Billing country.
	 * @return array{exempt:bool,error:string,result:array,vat:string}
	 */
	public static function decide( $vat, $country ) {
		$vat = WSEVV_API::normalize( $vat );
		$out = array(
			'exempt' => false,
			'error'  => '',
			'result' => array(),
			'vat'    => $vat,
		);
		if ( '' === $vat ) {
			return $out;
		}
		$vat_country = WSEVV_API::country_of( $vat );
		if ( '' === $vat_country ) {
			$out['error'] = __( 'Please enter an EU VAT number starting with the country code (for example DE123456789).', 'w-system-vies-check' );
			return $out;
		}
		if ( $country && $vat_country !== $country ) {
			$out['error'] = __( 'The VAT number country does not match your billing country.', 'w-system-vies-check' );
			return $out;
		}
		$result        = WSEVV_API::verify( $vat );
		$out['result'] = $result;
		$cross_border  = WC()->countries->get_base_country() !== $vat_country;
		$reverse       = 'yes' === WSEVV_Settings::get( 'reverse_charge' );
		if ( true === $result['valid'] ) {
			$out['exempt'] = $reverse && $cross_border;
		} elseif ( false === $result['valid'] ) {
			if ( 'block' === WSEVV_Settings::get( 'on_invalid' ) ) {
				$out['error'] = __( 'The EU VAT number is not valid according to VIES. Please check it, or remove it to buy with VAT.', 'w-system-vies-check' );
			}
		} else {
			$mode = WSEVV_Settings::get( 'on_unavailable' );
			if ( 'block' === $mode ) {
				$out['error'] = __( 'The EU VAT validation service is temporarily unavailable. Please try again in a few minutes.', 'w-system-vies-check' );
			} elseif ( 'exempt' === $mode ) {
				$out['exempt'] = $reverse && $cross_border;
			}
		}
		return $out;
	}

	/**
	 * Apply exemption while the order review is refreshed.
	 *
	 * @param string $post_data Serialized checkout form.
	 */
	public static function update_order_review( $post_data ) {
		parse_str( (string) $post_data, $data );
		$vat     = isset( $data[ self::FIELD ] ) ? sanitize_text_field( wp_unslash( $data[ self::FIELD ] ) ) : '';
		$country = isset( $data['billing_country'] ) ? sanitize_text_field( wp_unslash( $data['billing_country'] ) ) : '';
		$d       = self::decide( $vat, $country );
		WC()->customer->set_is_vat_exempt( $d['exempt'] );
	}

	/**
	 * Validate on place order.
	 *
	 * @param array    $data   Posted data.
	 * @param WP_Error $errors Errors.
	 */
	public static function validate( $data, $errors ) {
		$vat = isset( $data[ self::FIELD ] ) ? (string) $data[ self::FIELD ] : '';
		$d   = self::decide( $vat, isset( $data['billing_country'] ) ? (string) $data['billing_country'] : '' );
		if ( $d['error'] ) {
			$errors->add( 'wsevv', $d['error'], array( 'id' => self::FIELD ) );
		}
		if ( WC()->customer->get_is_vat_exempt() !== $d['exempt'] ) {
			WC()->customer->set_is_vat_exempt( $d['exempt'] );
			WC()->cart->calculate_totals();
		}
	}

	/**
	 * Store validation result on the order.
	 *
	 * @param WC_Order $order Order.
	 * @param array    $data  Posted data.
	 */
	public static function save_order_meta( $order, $data ) {
		$vat = isset( $data[ self::FIELD ] ) ? (string) $data[ self::FIELD ] : '';
		if ( '' === trim( $vat ) ) {
			return;
		}
		$d = self::decide( $vat, $order->get_billing_country() );
		$r = $d['result'];
		$order->update_meta_data( '_wsevv_vat_number', $d['vat'] );
		$order->update_meta_data( '_wsevv_status', isset( $r['status'] ) ? $r['status'] : '' );
		$order->update_meta_data( '_wsevv_consultation_number', isset( $r['consultation_number'] ) ? $r['consultation_number'] : '' );
		$order->update_meta_data( '_wsevv_company_name', isset( $r['name'] ) ? $r['name'] : '' );
		$order->update_meta_data( '_wsevv_checked_at', isset( $r['request_date'] ) && $r['request_date'] ? $r['request_date'] : gmdate( 'c' ) );
		$order->update_meta_data( '_wsevv_reverse_charge', $d['exempt'] ? 'yes' : 'no' );
		if ( $d['exempt'] ) {
			$order->update_meta_data( 'is_vat_exempt', 'yes' );
		}
	}

	/**
	 * Add an order note with the VIES result once the order has an ID.
	 *
	 * @param int      $order_id    Order ID.
	 * @param array    $posted_data Posted data.
	 * @param WC_Order $order       Order.
	 */
	public static function add_note( $order_id, $posted_data, $order ) {
		unset( $order_id, $posted_data );
		$vat = $order->get_meta( '_wsevv_vat_number' );
		if ( ! $vat ) {
			return;
		}
		$cn = $order->get_meta( '_wsevv_consultation_number' );
		$order->add_order_note(
			sprintf(
				/* translators: 1: VAT number, 2: VIES status, 3: consultation number, 4: yes/no */
				__( 'EU VAT number %1$s checked in VIES: %2$s. Consultation number: %3$s. Reverse charge: %4$s.', 'w-system-vies-check' ),
				$vat,
				$order->get_meta( '_wsevv_status' ) ? $order->get_meta( '_wsevv_status' ) : '-',
				$cn ? $cn : '-',
				'yes' === $order->get_meta( '_wsevv_reverse_charge' ) ? __( 'yes', 'w-system-vies-check' ) : __( 'no', 'w-system-vies-check' )
			)
		);
	}

	/**
	 * Show VAT details on the admin order screen and order details page.
	 *
	 * @param WC_Order $order Order.
	 */
	public static function admin_display( $order ) {
		$vat = $order->get_meta( '_wsevv_vat_number' );
		if ( ! $vat ) {
			return;
		}
		echo '<p><strong>' . esc_html__( 'EU VAT number', 'w-system-vies-check' ) . ':</strong> ' . esc_html( $vat );
		echo ' (' . esc_html( $order->get_meta( '_wsevv_status' ) ) . ')';
		$cn = $order->get_meta( '_wsevv_consultation_number' );
		if ( $cn ) {
			echo '<br>' . esc_html__( 'VIES consultation number', 'w-system-vies-check' ) . ': ' . esc_html( $cn );
		}
		if ( 'yes' === $order->get_meta( '_wsevv_reverse_charge' ) ) {
			echo '<br>' . esc_html__( 'Reverse charge: VAT to be accounted for by the recipient (Art. 196 Council Directive 2006/112/EC).', 'w-system-vies-check' );
		}
		echo '</p>';
	}

	/**
	 * Add the reverse-charge notice to emails.
	 *
	 * @param WC_Order $order         Order.
	 * @param bool     $sent_to_admin Admin email.
	 */
	public static function email_display( $order, $sent_to_admin ) {
		unset( $sent_to_admin );
		self::admin_display( $order );
	}
}
