<?php
/**
 * Google Sheets Data Fetcher
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fetch data from Google Sheets
 */
function bdt_fetch_google_sheets_data() {
    $sheets_url = get_option('bdt_google_sheets_url', '');

    if (empty($sheets_url)) {
        return new WP_Error('no_url', 'Google Sheets URL not configured');
    }

    // Convert published HTML URL to CSV export URL
    $csv_url = bdt_convert_to_csv_url($sheets_url);

    if (!$csv_url) {
        return new WP_Error('invalid_url', 'Invalid Google Sheets URL');
    }

    // Fetch the CSV data
    $response = wp_remote_get($csv_url, array(
        'timeout' => 30,
        'sslverify' => true,
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $body = wp_remote_retrieve_body($response);

    if (empty($body)) {
        return new WP_Error('empty_response', 'No data received from Google Sheets');
    }

    // Parse CSV data
    $donations = bdt_parse_csv_data($body);

    if (is_wp_error($donations)) {
        return $donations;
    }

    // Calculate totals by state
    $state_totals = bdt_calculate_state_totals($donations);

    // Save to database
    update_option('bdt_donations_data', $donations);
    update_option('bdt_state_totals', $state_totals);
    update_option('bdt_last_fetch', current_time('mysql'));
    update_option('bdt_total_donations', array_sum($state_totals));

    return $state_totals;
}

/**
 * Convert Google Sheets URL to CSV export URL
 */
function bdt_convert_to_csv_url($url) {
    // Handle different Google Sheets URL formats

    // Format 1: /d/e/XXXXX/pubhtml
    if (preg_match('/\/d\/e\/([a-zA-Z0-9_-]+)\/pubhtml/', $url, $matches)) {
        $sheet_id = $matches[1];
        return "https://docs.google.com/spreadsheets/d/e/{$sheet_id}/pub?output=csv";
    }

    // Format 2: /d/XXXXX/edit
    if (preg_match('/\/d\/([a-zA-Z0-9_-]+)\/edit/', $url, $matches)) {
        $sheet_id = $matches[1];
        return "https://docs.google.com/spreadsheets/d/{$sheet_id}/export?format=csv";
    }

    // Format 3: Already a CSV export URL
    if (strpos($url, 'output=csv') !== false || strpos($url, 'export?format=csv') !== false) {
        return $url;
    }

    return false;
}

/**
 * Parse CSV data
 */
function bdt_parse_csv_data($csv_string) {
    $lines = explode("\n", $csv_string);
    $donations = array();
    $header = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $data = str_getcsv($line);

        if ($header === null) {
            // First row is header
            $header = $data;
            continue;
        }

        // Map data to header
        $row = array();
        foreach ($header as $index => $column) {
            $row[$column] = isset($data[$index]) ? $data[$index] : '';
        }

        // Validate required fields
        if (empty($row['State']) || empty($row['Amount'])) {
            continue;
        }

        // Clean and validate state code
        $state = strtoupper(trim($row['State']));
        if (strlen($state) !== 2) {
            continue;
        }

        // Clean amount (remove currency symbols, commas, etc.)
        $amount = preg_replace('/[^0-9.]/', '', $row['Amount']);
        $amount = floatval($amount);

        if ($amount <= 0) {
            continue;
        }

        $donations[] = array(
            'timestamp' => isset($row['Timestamp']) ? $row['Timestamp'] : '',
            'donor_name' => isset($row['DonorName']) ? $row['DonorName'] : '',
            'state' => $state,
            'amount' => $amount,
        );
    }

    if (empty($donations)) {
        return new WP_Error('no_data', 'No valid donation data found');
    }

    return $donations;
}

/**
 * Calculate total donations by state
 */
function bdt_calculate_state_totals($donations) {
    $state_totals = array();

    foreach ($donations as $donation) {
        $state = $donation['state'];
        if (!isset($state_totals[$state])) {
            $state_totals[$state] = 0;
        }
        $state_totals[$state] += $donation['amount'];
    }

    // Sort by total (descending)
    arsort($state_totals);

    return $state_totals;
}

/**
 * Get state totals
 */
function bdt_get_state_totals() {
    $state_totals = get_option('bdt_state_totals', array());

    if (empty($state_totals)) {
        // Try to fetch if not available
        $result = bdt_fetch_google_sheets_data();
        if (!is_wp_error($result)) {
            $state_totals = $result;
        }
    }

    return $state_totals;
}

/**
 * Get total donations amount
 */
function bdt_get_total_donations() {
    $total = get_option('bdt_total_donations', 0);

    if ($total == 0) {
        $state_totals = bdt_get_state_totals();
        $total = array_sum($state_totals);
    }

    return $total;
}

/**
 * Get color intensity for state based on donation amount
 */
function bdt_get_state_color($state, $amount, $max_amount, $theme_color) {
    if ($amount == 0) {
        return '#FFFFFF'; // White for no donations
    }

    // Calculate intensity (0 to 1)
    $intensity = $amount / $max_amount;

    // Convert hex color to RGB
    $hex = str_replace('#', '', $theme_color);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    // Interpolate between white (255,255,255) and theme color
    $new_r = round(255 - ($intensity * (255 - $r)));
    $new_g = round(255 - ($intensity * (255 - $g)));
    $new_b = round(255 - ($intensity * (255 - $b)));

    return sprintf('#%02x%02x%02x', $new_r, $new_g, $new_b);
}

/**
 * Manual fetch AJAX handler
 */
function bdt_ajax_manual_fetch() {
    check_ajax_referer('bdt_manual_fetch', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }

    $result = bdt_fetch_google_sheets_data();

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    $total = bdt_get_total_donations();
    $last_fetch = get_option('bdt_last_fetch', '');

    wp_send_json_success(array(
        'message' => 'Data fetched successfully!',
        'total' => number_format($total, 2),
        'last_fetch' => $last_fetch,
        'states_count' => count($result),
    ));
}
add_action('wp_ajax_bdt_manual_fetch', 'bdt_ajax_manual_fetch');
