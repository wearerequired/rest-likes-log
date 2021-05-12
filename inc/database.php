<?php
/**
 * Database managment.
 *
 * @since 1.0.0
 */

namespace Required\RestLikes\Log\Database;

const SCHEMA_OPTION  = 'rest-likes-schema-version';
const SCHEMA_VERSION = 3;
const TABLE_NAME_LOG = 'rest_likes_log';

/**
 * Bootstrap the database.
 */
function bootstrap(): void {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\register_tables' );
	add_action( 'admin_init', __NAMESPACE__ . '\upgrade_tables' );
}

/**
 * Registers tables to $wpdb.
 */
function register_tables(): void {
	global $wpdb;

	$wpdb->rest_likes_log = $wpdb->prefix . TABLE_NAME_LOG;
}

/**
 * Creates and upgrades tables.
 */
function upgrade_tables(): void {
	$current_schema_version = (int) get_option( SCHEMA_OPTION, 0 );
	if ( SCHEMA_VERSION === $current_schema_version ) {
		return;
	}

	if ( ! function_exists( 'dbDelta' ) ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	dbDelta( tables_schema() );

	update_option( SCHEMA_OPTION, SCHEMA_VERSION );
}

/**
 * Schema of the tables.
 */
function tables_schema(): string {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();

	return "
CREATE TABLE $wpdb->rest_likes_log (
id bigint(20) unsigned NOT NULL auto_increment,
object_id bigint(20) unsigned NOT NULL default '0',
object_type varchar(20) NOT NULL default '',
time datetime(3) NOT NULL default '0000-00-00 00:00:00.00000',
ip varchar(100) NOT NULL default '',
action varchar(50) NOT NULL default '',
PRIMARY KEY  (id)
) $charset_collate;";
}
