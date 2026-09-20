<?php
/**
 * Plugin uninstall entrypoint.
 *
 * @package DarajaMpesa
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$daraja_mpesa_settings = get_option( 'woocommerce_daraja_mpesa_settings', array() );
if ( is_array( $daraja_mpesa_settings ) ) {
	$daraja_mpesa_environment  = isset( $daraja_mpesa_settings['environment'] ) && is_scalar( $daraja_mpesa_settings['environment'] )
		? (string) $daraja_mpesa_settings['environment']
		: '';
	$daraja_mpesa_consumer_key = isset( $daraja_mpesa_settings['consumer_key'] ) && is_scalar( $daraja_mpesa_settings['consumer_key'] )
		? (string) $daraja_mpesa_settings['consumer_key']
		: '';

	if ( '' !== $daraja_mpesa_environment && '' !== $daraja_mpesa_consumer_key ) {
		$daraja_mpesa_identity = $daraja_mpesa_environment . ':' . $daraja_mpesa_consumer_key;
		delete_transient( 'daraja_mpesa_token_' . substr( hash( 'sha256', $daraja_mpesa_identity ), 0, 40 ) );
	}
}

if ( function_exists( 'as_unschedule_all_actions' ) ) {
	as_unschedule_all_actions( 'daraja_mpesa_poll_attempt', array(), 'daraja-mpesa' );
	as_unschedule_all_actions( 'daraja_mpesa_review_attempt', array(), 'daraja-mpesa' );
}

global $wpdb;

if ( $wpdb instanceof wpdb ) {
	// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange -- Uninstall removes only plugin-owned tables.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->query(
		(string) $wpdb->prepare(
			'DROP TABLE IF EXISTS %i, %i',
			$wpdb->prefix . 'daraja_mpesa_attempts',
			$wpdb->prefix . 'daraja_mpesa_manual_audit'
		)
	);
	// phpcs:enable
}

delete_option( 'woocommerce_daraja_mpesa_settings' );
delete_option( 'daraja_mpesa_version' );
delete_option( 'daraja_mpesa_schema_version' );
