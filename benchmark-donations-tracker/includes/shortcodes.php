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
 * Usage: [benchmark_map] or [benchmark_map method="external"] or [benchmark_map method="table"]
 */
function bdt_map_shortcode($atts) {
    $atts = shortcode_atts(array(
        'width' => '100%',
        'height' => 'auto',
        'method' => 'external', // 'inline', 'external', 'table', 'javascript'
    ), $atts);

    $state_totals = bdt_get_state_totals();
    $theme_color = get_option('bdt_theme_color', '#00c19f');

    if (empty($state_totals)) {
        return '<div class="bdt-map-container"><p>No donation data available yet. Please configure the Google Sheets URL in the admin settings.</p></div>';
    }

    $max_amount = max($state_totals);

    // Choose rendering method
    switch ($atts['method']) {
        case 'javascript':
            return bdt_render_map_javascript($state_totals, $max_amount, $theme_color, $atts);
        case 'table':
            return bdt_render_map_table($state_totals, $max_amount, $theme_color, $atts);
        case 'external':
            return bdt_render_map_external($state_totals, $max_amount, $theme_color, $atts);
        case 'inline':
        default:
            return bdt_render_map_inline($state_totals, $max_amount, $theme_color, $atts);
    }
}
add_shortcode('benchmark_map', 'bdt_map_shortcode');

/**
 * Render map using external SVG file (bypasses WordPress content filters)
 */
function bdt_render_map_external($state_totals, $max_amount, $theme_color, $atts) {
    $svg_url = BDT_PLUGIN_URL . 'assets/images/us-map-sample.svg';

    // Pass data to JavaScript
    $map_data = array();
    foreach ($state_totals as $state => $amount) {
        $color = bdt_get_state_color($state, $amount, $max_amount, $theme_color);
        $map_data[$state] = array(
            'amount' => $amount,
            'formatted' => '$' . number_format($amount, 2),
            'color' => $color
        );
    }

    $map_id = 'bdt-map-' . uniqid();

    $output = '<div class="bdt-map-container bdt-map-external" style="width: ' . esc_attr($atts['width']) . ';">';
    $output .= '<div class="bdt-map-tooltip" id="bdt-map-tooltip-' . $map_id . '"></div>';
    $output .= '<object id="' . $map_id . '" data="' . esc_url($svg_url) . '" type="image/svg+xml" class="bdt-svg-object"></object>';
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

    // Add inline script to color the SVG after it loads
    $output .= '<script type="text/javascript">
    (function() {
        var mapData = ' . json_encode($map_data) . ';
        var mapObject = document.getElementById("' . $map_id . '");
        var tooltip = document.getElementById("bdt-map-tooltip-' . $map_id . '");

        mapObject.addEventListener("load", function() {
            try {
                var svgDoc = mapObject.contentDocument;
                if (!svgDoc) return;

                for (var state in mapData) {
                    var stateEl = svgDoc.getElementById(state);
                    if (stateEl) {
                        stateEl.style.fill = mapData[state].color;
                        stateEl.setAttribute("data-amount", mapData[state].formatted);
                        stateEl.setAttribute("data-state", state);
                        stateEl.classList.add("bdt-state");

                        // Add hover events
                        stateEl.addEventListener("mouseenter", function(e) {
                            var state = this.getAttribute("data-state");
                            var amount = this.getAttribute("data-amount");
                            tooltip.innerHTML = "<strong>" + state + "</strong>" + amount;
                            tooltip.classList.add("active");
                        });

                        stateEl.addEventListener("mousemove", function(e) {
                            var rect = mapObject.getBoundingClientRect();
                            tooltip.style.left = (e.clientX - rect.left + 15) + "px";
                            tooltip.style.top = (e.clientY - rect.top + 15) + "px";
                        });

                        stateEl.addEventListener("mouseleave", function() {
                            tooltip.classList.remove("active");
                        });
                    }
                }
            } catch(e) {
                console.error("Error loading SVG map:", e);
            }
        });
    })();
    </script>';

    return $output;
}

/**
 * Render map using inline SVG (may be truncated by WordPress)
 */
function bdt_render_map_inline($state_totals, $max_amount, $theme_color, $atts) {
    // Get SVG content
    $svg_path = BDT_PLUGIN_DIR . 'assets/images/us-map-sample.svg';
    if (!file_exists($svg_path)) {
        return '<div class="bdt-map-container"><p>Map file not found. Try using [benchmark_map method="external"] or [benchmark_map method="table"]</p></div>';
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

/**
 * Render map using JavaScript to dynamically load and color SVG
 */
function bdt_render_map_javascript($state_totals, $max_amount, $theme_color, $atts) {
    $svg_url = BDT_PLUGIN_URL . 'assets/images/us-map-sample.svg';

    // Pass data to JavaScript
    $map_data = array();
    foreach ($state_totals as $state => $amount) {
        $color = bdt_get_state_color($state, $amount, $max_amount, $theme_color);
        $map_data[$state] = array(
            'amount' => $amount,
            'formatted' => '$' . number_format($amount, 2),
            'color' => $color
        );
    }

    $map_id = 'bdt-map-' . uniqid();

    $output = '<div class="bdt-map-container bdt-map-js" style="width: ' . esc_attr($atts['width']) . ';">';
    $output .= '<div class="bdt-map-tooltip" id="bdt-map-tooltip-' . $map_id . '"></div>';
    $output .= '<div id="' . $map_id . '" class="bdt-svg-container"></div>';
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

    // Add inline script to load and color the SVG
    $output .= '<script type="text/javascript">
    (function() {
        var mapData = ' . json_encode($map_data) . ';
        var container = document.getElementById("' . $map_id . '");
        var tooltip = document.getElementById("bdt-map-tooltip-' . $map_id . '");

        fetch("' . esc_url($svg_url) . '")
            .then(response => response.text())
            .then(svgText => {
                container.innerHTML = svgText;
                var svg = container.querySelector("svg");
                if (!svg) return;

                for (var state in mapData) {
                    var stateEl = svg.getElementById(state);
                    if (stateEl) {
                        stateEl.style.fill = mapData[state].color;
                        stateEl.setAttribute("data-amount", mapData[state].formatted);
                        stateEl.setAttribute("data-state", state);
                        stateEl.classList.add("bdt-state");

                        // Add hover events
                        stateEl.addEventListener("mouseenter", function(e) {
                            var state = this.getAttribute("data-state");
                            var amount = this.getAttribute("data-amount");
                            tooltip.innerHTML = "<strong>" + state + "</strong>" + amount;
                            tooltip.classList.add("active");
                        });

                        stateEl.addEventListener("mousemove", function(e) {
                            var rect = container.getBoundingClientRect();
                            tooltip.style.left = (e.clientX - rect.left + 15) + "px";
                            tooltip.style.top = (e.clientY - rect.top + 15) + "px";
                        });

                        stateEl.addEventListener("mouseleave", function() {
                            tooltip.classList.remove("active");
                        });
                    }
                }
            })
            .catch(err => {
                container.innerHTML = "<p>Error loading map. Please try the table view: [benchmark_map method=\"table\"]</p>";
            });
    })();
    </script>';

    return $output;
}

/**
 * Render map as a table (fallback when SVG doesn't work)
 */
function bdt_render_map_table($state_totals, $max_amount, $theme_color, $atts) {
    $us_states = array(
        'AL' => 'Alabama', 'AK' => 'Alaska', 'AZ' => 'Arizona', 'AR' => 'Arkansas',
        'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut', 'DE' => 'Delaware',
        'FL' => 'Florida', 'GA' => 'Georgia', 'HI' => 'Hawaii', 'ID' => 'Idaho',
        'IL' => 'Illinois', 'IN' => 'Indiana', 'IA' => 'Iowa', 'KS' => 'Kansas',
        'KY' => 'Kentucky', 'LA' => 'Louisiana', 'ME' => 'Maine', 'MD' => 'Maryland',
        'MA' => 'Massachusetts', 'MI' => 'Michigan', 'MN' => 'Minnesota', 'MS' => 'Mississippi',
        'MO' => 'Missouri', 'MT' => 'Montana', 'NE' => 'Nebraska', 'NV' => 'Nevada',
        'NH' => 'New Hampshire', 'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NY' => 'New York',
        'NC' => 'North Carolina', 'ND' => 'North Dakota', 'OH' => 'Ohio', 'OK' => 'Oklahoma',
        'OR' => 'Oregon', 'PA' => 'Pennsylvania', 'RI' => 'Rhode Island', 'SC' => 'South Carolina',
        'SD' => 'South Dakota', 'TN' => 'Tennessee', 'TX' => 'Texas', 'UT' => 'Utah',
        'VT' => 'Vermont', 'VA' => 'Virginia', 'WA' => 'Washington', 'WV' => 'West Virginia',
        'WI' => 'Wisconsin', 'WY' => 'Wyoming', 'DC' => 'Washington DC'
    );

    $output = '<div class="bdt-map-container bdt-map-table" style="width: ' . esc_attr($atts['width']) . ';">';
    $output .= '<h3>Donations by State</h3>';
    $output .= '<table class="bdt-states-table">';
    $output .= '<thead><tr><th>State</th><th>Total Donations</th><th>Visual</th></tr></thead>';
    $output .= '<tbody>';

    foreach ($us_states as $code => $name) {
        $amount = isset($state_totals[$code]) ? $state_totals[$code] : 0;
        $color = bdt_get_state_color($code, $amount, $max_amount, $theme_color);
        $percentage = $max_amount > 0 ? ($amount / $max_amount) * 100 : 0;

        $output .= '<tr>';
        $output .= '<td><strong>' . esc_html($code) . '</strong> ' . esc_html($name) . '</td>';
        $output .= '<td>$' . number_format($amount, 2) . '</td>';
        $output .= '<td><div class="bdt-bar-container"><div class="bdt-bar" style="width: ' . $percentage . '%; background-color: ' . esc_attr($color) . ';"></div></div></td>';
        $output .= '</tr>';
    }

    $output .= '</tbody>';
    $output .= '</table>';
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
