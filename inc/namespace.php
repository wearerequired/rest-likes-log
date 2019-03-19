<?php
/**
 * Main file for plugin.
 *
 * @since 1.0.0
 */

namespace Required\RestLikes\Log;

use DateTime;
use DateTimeZone;

/**
 * Bootstrap the plugin.
 */
function bootstrap() {
	Database\bootstrap();

	add_action( 'rest_likes.update_likes', __NAMESPACE__ . '\log_update', 10, 6 );
	add_action( 'rest_likes.request_rejected', __NAMESPACE__ . '\log_reject', 10, 3 );
}

/**
 * Determines the user's actual IP address.
 *
 * @return string The address on success or empty string on failure.
 */
function get_client_ip() {
	$client_ip = '';

	$address_headers = array(
		'HTTP_CLIENT_IP',
		'HTTP_X_FORWARDED_FOR',
		'HTTP_X_FORWARDED',
		'HTTP_X_CLUSTER_CLIENT_IP',
		'HTTP_FORWARDED_FOR',
		'HTTP_FORWARDED',
		'REMOTE_ADDR',
	);

	foreach ( $address_headers as $header ) {
		if ( array_key_exists( $header, $_SERVER ) ) {
			/*
			 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
			 * addresses. The first one is the original client. It can't be
			 * trusted for authenticity, but we don't need to for this purpose.
			 */
			$address_chain = explode( ',', $_SERVER[ $header ] );
			$client_ip     = trim( $address_chain[0] );

			break;
		}
	}

	return $client_ip;
}

/**
 * Logs when the like count is updated for an object.
 *
 * @since 1.0.0
 *
 * @param string $object_type The object type.
 * @param int    $object_id   Object ID.
 * @param int    $likes       The like count.
 * @param int    $likes_i18n  The formatted like count.
 * @param int    $old_likes   The old like count.
 * @param bool   $remove      Whether to increment or decrement the counter.
 */
function log_update( $object_type, $object_id, $likes, $likes_i18n, $old_likes, $remove ) {
	$ip_address = get_client_ip();
	$time       = ( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( 'Y-m-d H:i:s.u' );
	$action     = $remove ? 'unlike' : 'like';

	log_to_table( $object_id, $object_type, $time, $ip_address, $action );
}

/**
 * Logs when the like request was rejected.
 *
 * @since 1.0.0
 *
 * @param \WP_Error $result      Permission result.
 * @param int       $object_id   Object ID.
 * @param string    $object_type Object type.
 */
function log_reject( $result, $object_id, $object_type ) {
	$ip_address = get_client_ip();
	$time       = ( new DateTime( 'now', new DateTimeZone( 'UTC' ) ) )->format( 'Y-m-d H:i:s.u' );
	$action     = 'rejected';

	log_to_table( $object_id, $object_type, $time, $ip_address, $action );
}

/**
 * Logs data to the table.
 *
 * @since 1.0.0
 *
 * @param int    $object_id   The object type.
 * @param string $object_type Object ID.
 * @param string $time        MySQL datetime string.
 * @param string $ip_address  Client IP address.
 * @param string $action      Action of log entry.
 */
function log_to_table( int $object_id, string $object_type, string $time, string $ip_address, string $action ) {
	global $wpdb;

	$wpdb->insert(
		$wpdb->rest_likes_log,
		[
			'object_id'   => $object_id,
			'object_type' => $object_type,
			'time'        => $time,
			'ip'          => $ip_address,
			'action'      => $action,
		],
		[
			'%d',
			'%s',
			'%s',
			'%s',
			'%s',
		]
	);
}
