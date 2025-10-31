<?php
/**
 * Plugin Name: Benchmark Donations Tracker
 * Plugin URI: https://michaell734.sg-host.com
 * Description: Track donations by state with interactive US map and thermometer visualization, synced with Google Sheets
 * Version: 1.0.0
 * Author: Benchmark Team
 * Author URI: https://michaell734.sg-host.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: benchmark-donations-tracker
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BDT_VERSION', '1.0.0');
define('BDT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BDT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BDT_PLUGIN_FILE', __FILE__);

// Include required files
require_once BDT_PLUGIN_DIR . 'includes/data-fetcher.php';
require_once BDT_PLUGIN_DIR . 'includes/shortcodes.php';
require_once BDT_PLUGIN_DIR . 'includes/cron.php';

// Include admin files
if (is_admin()) {
    require_once BDT_PLUGIN_DIR . 'admin/settings.php';
    require_once BDT_PLUGIN_DIR . 'admin/readme.php';
}

/**
 * Plugin activation
 */
function bdt_activate() {
    // Set default options
    $defaults = array(
        'theme_color' => '#00c19f',
        'google_sheets_url' => '',
        'goal_amount' => 50000,
        'fetch_time' => '03:00', // 3 AM default
        'last_fetch' => '',
    );

    foreach ($defaults as $key => $value) {
        if (get_option('bdt_' . $key) === false) {
            add_option('bdt_' . $key, $value);
        }
    }

    // Schedule cron event
    if (!wp_next_scheduled('bdt_daily_fetch')) {
        $fetch_time = get_option('bdt_fetch_time', '03:00');
        list($hour, $minute) = explode(':', $fetch_time);
        $timestamp = strtotime("today {$hour}:{$minute}");
        if ($timestamp < time()) {
            $timestamp = strtotime("tomorrow {$hour}:{$minute}");
        }
        wp_schedule_event($timestamp, 'daily', 'bdt_daily_fetch');
    }
}
register_activation_hook(__FILE__, 'bdt_activate');

/**
 * Plugin deactivation
 */
function bdt_deactivate() {
    // Clear scheduled event
    $timestamp = wp_next_scheduled('bdt_daily_fetch');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'bdt_daily_fetch');
    }
}
register_deactivation_hook(__FILE__, 'bdt_deactivate');

/**
 * Enqueue frontend styles and scripts
 */
function bdt_enqueue_frontend_assets() {
    wp_enqueue_style(
        'bdt-frontend-styles',
        BDT_PLUGIN_URL . 'assets/css/frontend-styles.css',
        array(),
        BDT_VERSION
    );

    wp_enqueue_script(
        'bdt-map-handler',
        BDT_PLUGIN_URL . 'assets/js/map-handler.js',
        array('jquery'),
        BDT_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'bdt_enqueue_frontend_assets');

/**
 * Enqueue admin styles
 */
function bdt_enqueue_admin_assets($hook) {
    if (strpos($hook, 'benchmark-donations') === false) {
        return;
    }

    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_style(
        'bdt-admin-styles',
        BDT_PLUGIN_URL . 'admin/admin-styles.css',
        array(),
        BDT_VERSION
    );

    wp_enqueue_script(
        'bdt-admin-script',
        BDT_PLUGIN_URL . 'admin/admin-script.js',
        array('jquery', 'wp-color-picker'),
        BDT_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'bdt_enqueue_admin_assets');
