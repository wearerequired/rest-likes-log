<?php
/**
 * Plugin Name: REST Likes: Log
 * Plugin URI:  https://github.com/wearerequired/rest-likes-log
 * Description: Add-on for REST Likes to log like requests.
 * Version:     1.0.0
 * Author:      required
 * Author URI:  https://required.com
 * Text Domain: rest-likes-log
 * License:     GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace Required\RestLikes\Log;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	include __DIR__ . '/vendor/autoload.php';
}

bootstrap();
