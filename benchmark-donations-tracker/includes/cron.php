<?php
/**
 * Cron Jobs for Scheduled Data Fetching
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hook for daily data fetch
 */
function bdt_scheduled_fetch() {
    bdt_fetch_google_sheets_data();

    // Log the fetch
    $log = get_option('bdt_fetch_log', array());
    $log[] = array(
        'timestamp' => current_time('mysql'),
        'status' => 'success',
    );

    // Keep only last 30 entries
    if (count($log) > 30) {
        $log = array_slice($log, -30);
    }

    update_option('bdt_fetch_log', $log);
}
add_action('bdt_daily_fetch', 'bdt_scheduled_fetch');

/**
 * Reschedule cron when time changes
 */
function bdt_reschedule_cron() {
    // Clear existing schedule
    $timestamp = wp_next_scheduled('bdt_daily_fetch');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'bdt_daily_fetch');
    }

    // Schedule new event
    $fetch_time = get_option('bdt_fetch_time', '03:00');
    list($hour, $minute) = explode(':', $fetch_time);
    $timestamp = strtotime("today {$hour}:{$minute}");
    if ($timestamp < time()) {
        $timestamp = strtotime("tomorrow {$hour}:{$minute}");
    }
    wp_schedule_event($timestamp, 'daily', 'bdt_daily_fetch');
}
