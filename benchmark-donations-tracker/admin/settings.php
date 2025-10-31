<?php
/**
 * Admin Settings Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add admin menu
 */
function bdt_add_admin_menu() {
    add_menu_page(
        'Benchmark Donations Tracker',
        'Donations Tracker',
        'manage_options',
        'benchmark-donations',
        'bdt_settings_page',
        'dashicons-chart-line',
        30
    );

    add_submenu_page(
        'benchmark-donations',
        'Settings',
        'Settings',
        'manage_options',
        'benchmark-donations',
        'bdt_settings_page'
    );

    add_submenu_page(
        'benchmark-donations',
        'ReadMe & Directions',
        'ReadMe & Directions',
        'manage_options',
        'benchmark-donations-readme',
        'bdt_readme_page'
    );
}
add_action('admin_menu', 'bdt_add_admin_menu');

/**
 * Register settings
 */
function bdt_register_settings() {
    register_setting('bdt_settings', 'bdt_theme_color');
    register_setting('bdt_settings', 'bdt_google_sheets_url');
    register_setting('bdt_settings', 'bdt_goal_amount');
    register_setting('bdt_settings', 'bdt_fetch_time');
}
add_action('admin_init', 'bdt_register_settings');

/**
 * Settings page content
 */
function bdt_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    // Handle manual fetch
    if (isset($_POST['bdt_manual_fetch']) && check_admin_referer('bdt_manual_fetch_action')) {
        $result = bdt_fetch_google_sheets_data();
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error"><p>Error: ' . esc_html($result->get_error_message()) . '</p></div>';
        } else {
            $total = bdt_get_total_donations();
            echo '<div class="notice notice-success"><p>Data fetched successfully! Total: $' . number_format($total, 2) . '</p></div>';
        }
    }

    // Handle settings save
    if (isset($_POST['bdt_save_settings']) && check_admin_referer('bdt_settings_action')) {
        update_option('bdt_theme_color', sanitize_hex_color($_POST['bdt_theme_color']));
        update_option('bdt_google_sheets_url', esc_url_raw($_POST['bdt_google_sheets_url']));
        update_option('bdt_goal_amount', floatval($_POST['bdt_goal_amount']));
        update_option('bdt_fetch_time', sanitize_text_field($_POST['bdt_fetch_time']));

        // Reschedule cron with new time
        bdt_reschedule_cron();

        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    // Get current values
    $theme_color = get_option('bdt_theme_color', '#00c19f');
    $google_sheets_url = get_option('bdt_google_sheets_url', '');
    $goal_amount = get_option('bdt_goal_amount', 50000);
    $fetch_time = get_option('bdt_fetch_time', '03:00');
    $last_fetch = get_option('bdt_last_fetch', 'Never');
    $total_donations = bdt_get_total_donations();
    $state_totals = bdt_get_state_totals();
    $next_scheduled = wp_next_scheduled('bdt_daily_fetch');

    ?>
    <div class="wrap bdt-admin-wrap">
        <h1>
            <span class="dashicons dashicons-chart-line"></span>
            Benchmark Donations Tracker Settings
        </h1>

        <div class="bdt-admin-grid">
            <!-- Main Settings -->
            <div class="bdt-admin-section">
                <div class="bdt-card">
                    <h2>Configuration</h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('bdt_settings_action'); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="bdt_theme_color">Theme Color</label>
                                </th>
                                <td>
                                    <input type="text" id="bdt_theme_color" name="bdt_theme_color"
                                           value="<?php echo esc_attr($theme_color); ?>"
                                           class="bdt-color-picker" />
                                    <p class="description">
                                        This color will be used for the map states and thermometer.
                                        States with the most donations will show the purest form of this color.
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="bdt_google_sheets_url">Google Sheets URL</label>
                                </th>
                                <td>
                                    <input type="url" id="bdt_google_sheets_url" name="bdt_google_sheets_url"
                                           value="<?php echo esc_attr($google_sheets_url); ?>"
                                           class="regular-text" placeholder="https://docs.google.com/spreadsheets/..." />
                                    <p class="description">
                                        Paste the "Publish to web" URL from your Google Sheet.
                                        <br>In Google Sheets: File → Share → Publish to web → Copy the link
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="bdt_goal_amount">Goal Amount ($)</label>
                                </th>
                                <td>
                                    <input type="number" id="bdt_goal_amount" name="bdt_goal_amount"
                                           value="<?php echo esc_attr($goal_amount); ?>"
                                           min="0" step="0.01" class="regular-text" />
                                    <p class="description">
                                        The fundraising goal for the thermometer display.
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label for="bdt_fetch_time">Scheduled Fetch Time</label>
                                </th>
                                <td>
                                    <input type="time" id="bdt_fetch_time" name="bdt_fetch_time"
                                           value="<?php echo esc_attr($fetch_time); ?>" />
                                    <p class="description">
                                        Time of day to automatically fetch data from Google Sheets (24-hour format).
                                        <br>Current: <?php echo esc_html($fetch_time); ?>
                                        <?php if ($next_scheduled): ?>
                                            <br>Next scheduled: <?php echo date('F j, Y g:i A', $next_scheduled); ?>
                                        <?php endif; ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" name="bdt_save_settings" class="button button-primary">
                                Save Settings
                            </button>
                        </p>
                    </form>
                </div>

                <!-- Manual Fetch -->
                <div class="bdt-card">
                    <h2>Data Management</h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('bdt_manual_fetch_action'); ?>
                        <p>
                            <strong>Last Fetch:</strong>
                            <?php echo $last_fetch === 'Never' ? 'Never' : date('F j, Y g:i A', strtotime($last_fetch)); ?>
                        </p>
                        <p class="submit">
                            <button type="submit" name="bdt_manual_fetch" class="button button-secondary">
                                <span class="dashicons dashicons-update"></span>
                                Update Donations Info Now
                            </button>
                        </p>
                        <p class="description">
                            Click this button to manually fetch the latest data from your Google Sheet right now.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Stats Sidebar -->
            <div class="bdt-admin-sidebar">
                <div class="bdt-card">
                    <h2>Current Statistics</h2>
                    <div class="bdt-stat-box">
                        <div class="bdt-stat-number"><?php echo number_format($total_donations, 2); ?></div>
                        <div class="bdt-stat-label">Total Donations</div>
                    </div>
                    <div class="bdt-stat-box">
                        <div class="bdt-stat-number"><?php echo count($state_totals); ?></div>
                        <div class="bdt-stat-label">States Contributing</div>
                    </div>
                    <?php if (!empty($state_totals)):
                        $top_state = array_key_first($state_totals);
                        $top_amount = $state_totals[$top_state];
                    ?>
                    <div class="bdt-stat-box">
                        <div class="bdt-stat-number"><?php echo $top_state; ?></div>
                        <div class="bdt-stat-label">Top State ($<?php echo number_format($top_amount, 2); ?>)</div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="bdt-card">
                    <h2>Shortcodes</h2>
                    <p><strong>Display the US Map:</strong></p>
                    <code>[benchmark_map]</code>

                    <p><strong>Display the Thermometer:</strong></p>
                    <code>[benchmark_thermometer]</code>

                    <p><strong>Display Statistics:</strong></p>
                    <code>[benchmark_stats]</code>

                    <p class="description" style="margin-top: 15px;">
                        Copy and paste these shortcodes into any page or post to display the donation tracker elements.
                    </p>
                </div>

                <div class="bdt-card">
                    <h2>Quick Links</h2>
                    <ul class="bdt-quick-links">
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=benchmark-donations-readme'); ?>">
                                <span class="dashicons dashicons-book"></span>
                                View Instructions
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('.bdt-color-picker').wpColorPicker();
    });
    </script>
    <?php
}
