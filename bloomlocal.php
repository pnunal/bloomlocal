<?php
/**
 * Plugin Name: Bloomlocal
 * Plugin URI: https://bloomlocal.net/
 * Description: Bloomlocal
 * Version: 0.1.4
 * Author: Randolph Roble
 * Author URI: https://github.com/rroble
 * Text Domain: bloomlocal
 *
 * @package Bloomlocal
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/admin_filter_orders_by_delivery_date.php';
require_once __DIR__ . '/email_format_delivery_phone.php';
require_once __DIR__ . '/wp_autoupdate.php';

add_action('init', function() {
    new wp_autoupdate(
        '0.1.4',
        'https://arcanys:FvoneOJHEO@bloomlocal.net/dev/update.php',
        plugin_basename(__FILE__),
        'rroble',
        'MIT'
    );
});
