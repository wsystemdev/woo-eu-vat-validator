<?php
/**
 * Uninstall: remove plugin options. Order meta is kept (tax records).
 *
 * @package WSEVV
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

foreach ( array( 'api_key', 'requester', 'reverse_charge', 'required', 'on_invalid', 'on_unavailable' ) as $wsevv_option ) {
	delete_option( 'wsevv_' . $wsevv_option );
}
