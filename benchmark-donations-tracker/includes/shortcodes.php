<?php
/**
 * Shortcodes for displaying map and thermometer
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * US Map Shortcode
 * Usage: [benchmark_map]
 */
function bdt_map_shortcode($atts) {
    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => 'auto',
    ), $atts);

    $state_totals = bdt_get_state_totals();
    $theme_color = get_option('bdt_theme_color', '#00c19f');

    if (empty($state_totals)) {
        return '<div class="bdt-map-container"><p>No donation data available yet. Please configure the Google Sheets URL in the admin settings.</p></div>';
    }

    $max_amount = max($state_totals);

    // Get SVG content
    $svg_path = BDT_PLUGIN_DIR . 'assets/images/us-map-sample.svg';
    if (!file_exists($svg_path)) {
        return '<div class="bdt-map-container"><p>Map file not found.</p></div>';
    }

    $svg_content = file_get_contents($svg_path);

    // Inject state colors and data attributes
    foreach ($state_totals as $state => $amount) {
        $color = bdt_get_state_color($state, $amount, $max_amount, $theme_color);
        $formatted_amount = '$' . number_format($amount, 2);

        // Add fill color and data attributes to state path
        $pattern = '/(<path[^>]*id="' . $state . '"[^>]*)(>)/i';
        $replacement = '$1 fill="' . $color . '" data-amount="' . $formatted_amount . '" data-state="' . $state . '" class="bdt-state"$2';
        $svg_content = preg_replace($pattern, $replacement, $svg_content);
    }

    // Add tooltip div
    $output = '<div class="bdt-map-container" style="width: ' . esc_attr($atts['width']) . ';">';
    $output .= '<div class="bdt-map-tooltip" id="bdt-map-tooltip"></div>';
    $output .= $svg_content;
    $output .= '</div>';

    // Add legend
    $output .= '<div class="bdt-map-legend">';
    $output .= '<div class="bdt-legend-title">Donation Amount</div>';
    $output .= '<div class="bdt-legend-scale">';
    $output .= '<span class="bdt-legend-min">$0</span>';
    $output .= '<div class="bdt-legend-gradient" style="background: linear-gradient(to right, #FFFFFF, ' . esc_attr($theme_color) . ');"></div>';
    $output .= '<span class="bdt-legend-max">$' . number_format($max_amount, 0) . '</span>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('benchmark_map', 'bdt_map_shortcode');

/**
 * Thermometer Shortcode
 * Usage: [benchmark_thermometer] or [benchmark_thermometer goal="100000"]
 */
function bdt_thermometer_shortcode($atts) {
    $atts = shortcode_atts(array(
        'goal' => get_option('bdt_goal_amount', 50000),
        'width' => '200px',
        'height' => '400px',
    ), $atts);

    $current = bdt_get_total_donations();
    $goal = floatval($atts['goal']);

    if ($goal <= 0) {
        $goal = 50000;
    }

    $percentage = min(($current / $goal) * 100, 100);
    $theme_color = get_option('bdt_theme_color', '#00c19f');

    $output = '<div class="bdt-thermometer-container" style="width: ' . esc_attr($atts['width']) . '; height: ' . esc_attr($atts['height']) . ';">';
    $output .= '<div class="bdt-thermometer-goal">Goal: $' . number_format($goal, 0) . '</div>';
    $output .= '<div class="bdt-thermometer">';
    $output .= '<div class="bdt-thermometer-bg">';
    $output .= '<div class="bdt-thermometer-fill" style="height: ' . esc_attr($percentage) . '%; background-color: ' . esc_attr($theme_color) . ';">';
    $output .= '<span class="bdt-thermometer-percentage">' . number_format($percentage, 1) . '%</span>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<div class="bdt-thermometer-bulb" style="background-color: ' . esc_attr($theme_color) . ';"></div>';
    $output .= '</div>';
    $output .= '<div class="bdt-thermometer-current">Raised: $' . number_format($current, 2) . '</div>';
    $output .= '</div>';

    return $output;
}
add_shortcode('benchmark_thermometer', 'bdt_thermometer_shortcode');

/**
 * Donation Stats Shortcode (optional - shows quick stats)
 * Usage: [benchmark_stats]
 */
function bdt_stats_shortcode($atts) {
    $state_totals = bdt_get_state_totals();
    $total = bdt_get_total_donations();
    $donations_data = get_option('bdt_donations_data', array());
    $last_fetch = get_option('bdt_last_fetch', '');

    if (empty($state_totals)) {
        return '<div class="bdt-stats"><p>No donation data available yet.</p></div>';
    }

    $top_state = array_key_first($state_totals);
    $top_amount = $state_totals[$top_state];

    $output = '<div class="bdt-stats">';
    $output .= '<div class="bdt-stat-item">';
    $output .= '<span class="bdt-stat-label">Total Donations:</span>';
    $output .= '<span class="bdt-stat-value">$' . number_format($total, 2) . '</span>';
    $output .= '</div>';
    $output .= '<div class="bdt-stat-item">';
    $output .= '<span class="bdt-stat-label">States Contributing:</span>';
    $output .= '<span class="bdt-stat-value">' . count($state_totals) . '</span>';
    $output .= '</div>';
    $output .= '<div class="bdt-stat-item">';
    $output .= '<span class="bdt-stat-label">Top State:</span>';
    $output .= '<span class="bdt-stat-value">' . $top_state . ' ($' . number_format($top_amount, 2) . ')</span>';
    $output .= '</div>';
    $output .= '<div class="bdt-stat-item">';
    $output .= '<span class="bdt-stat-label">Total Donors:</span>';
    $output .= '<span class="bdt-stat-value">' . count($donations_data) . '</span>';
    $output .= '</div>';
    if ($last_fetch) {
        $output .= '<div class="bdt-stat-item">';
        $output .= '<span class="bdt-stat-label">Last Updated:</span>';
        $output .= '<span class="bdt-stat-value">' . date('F j, Y g:i A', strtotime($last_fetch)) . '</span>';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('benchmark_stats', 'bdt_stats_shortcode');
