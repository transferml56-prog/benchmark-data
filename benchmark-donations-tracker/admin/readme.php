<?php
/**
 * ReadMe and Directions Page
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ReadMe page content
 */
function bdt_readme_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    ?>
    <div class="wrap bdt-readme-wrap">
        <h1>
            <span class="dashicons dashicons-book"></span>
            Benchmark Donations Tracker - ReadMe & Directions
        </h1>

        <div class="bdt-readme-content">
            <div class="bdt-card">
                <h2>Welcome to Benchmark Donations Tracker!</h2>
                <p>
                    This plugin allows you to track donations by state using Google Sheets and display beautiful
                    visualizations on your WordPress site with an interactive US map and thermometer.
                </p>
            </div>

            <div class="bdt-card">
                <h2>📋 Quick Start Guide</h2>

                <h3>Step 1: Prepare Your Google Sheet</h3>
                <ol>
                    <li>Create a Google Sheet with the following columns (case-sensitive):
                        <ul>
                            <li><strong>Timestamp</strong> - Date/time of donation</li>
                            <li><strong>DonorName</strong> - Name of donor</li>
                            <li><strong>State</strong> - Two-letter state code (e.g., CA, NY, TX)</li>
                            <li><strong>Amount</strong> - Donation amount (numbers only, no $ symbol)</li>
                        </ul>
                    </li>
                    <li>Make sure the first row contains these exact column headers</li>
                    <li>Fill in your donation data below the headers</li>
                </ol>

                <h3>Step 2: Publish Your Google Sheet</h3>
                <ol>
                    <li>In your Google Sheet, click <strong>File → Share → Publish to web</strong></li>
                    <li>Click the <strong>Publish</strong> button</li>
                    <li>Copy the URL provided</li>
                    <li>Go to <strong>Donations Tracker → Settings</strong> in your WordPress admin</li>
                    <li>Paste the URL in the <strong>Google Sheets URL</strong> field</li>
                </ol>

                <h3>Step 3: Configure Settings</h3>
                <ol>
                    <li>Set your <strong>Theme Color</strong> - This will be used for the map and thermometer</li>
                    <li>Set your <strong>Goal Amount</strong> - Your fundraising target</li>
                    <li>Set your <strong>Scheduled Fetch Time</strong> - When to automatically update data daily</li>
                    <li>Click <strong>Save Settings</strong></li>
                </ol>

                <h3>Step 4: Fetch Your Data</h3>
                <ol>
                    <li>Click the <strong>Update Donations Info Now</strong> button</li>
                    <li>Wait for the success message</li>
                    <li>Your data is now loaded!</li>
                </ol>

                <h3>Step 5: Display on Your Site</h3>
                <p>Use these shortcodes in any page or post:</p>
                <ul>
                    <li><code>[benchmark_map]</code> - Displays the interactive US map</li>
                    <li><code>[benchmark_thermometer]</code> - Displays the fundraising thermometer</li>
                    <li><code>[benchmark_stats]</code> - Displays donation statistics</li>
                </ul>
            </div>

            <div class="bdt-card">
                <h2>🎨 How the Coloring Works</h2>
                <p>
                    The map uses a gradient coloring system based on donation amounts:
                </p>
                <ul>
                    <li><strong>White</strong> - States with $0 donations</li>
                    <li><strong>Light shade of your theme color</strong> - States with low donations</li>
                    <li><strong>Pure theme color</strong> - State with the highest donations</li>
                    <li><strong>Gradual shading</strong> - All states in between</li>
                </ul>
                <p>
                    Hover over any state on the map to see the exact donation amount!
                </p>
            </div>

            <div class="bdt-card">
                <h2>⏰ Automatic Updates</h2>
                <p>
                    The plugin automatically fetches data from your Google Sheet once per day at the time you specify.
                    This happens in the background using WordPress Cron.
                </p>
                <p>
                    <strong>Note:</strong> WordPress Cron requires site traffic to trigger. For guaranteed execution,
                    consider using a real cron job or a service like UptimeRobot to ping your site regularly.
                </p>
                <p>
                    You can always manually update the data by clicking the <strong>Update Donations Info Now</strong>
                    button in the Settings page.
                </p>
            </div>

            <div class="bdt-card">
                <h2>🔧 Troubleshooting</h2>

                <h3>Data Not Loading?</h3>
                <ul>
                    <li>Make sure your Google Sheet is published to web (File → Share → Publish to web)</li>
                    <li>Verify the URL you pasted is correct</li>
                    <li>Check that your column headers match exactly: Timestamp, DonorName, State, Amount</li>
                    <li>Ensure State column uses 2-letter codes (CA, not California)</li>
                    <li>Make sure Amount column contains numbers only (no $ symbols)</li>
                </ul>

                <h3>Map Not Displaying Correctly?</h3>
                <ul>
                    <li>Clear your browser cache</li>
                    <li>Check that the SVG map file exists in the plugin's assets/images folder</li>
                    <li>Try fetching the data manually again</li>
                </ul>

                <h3>Colors Not Showing?</h3>
                <ul>
                    <li>Make sure you've saved your theme color in Settings</li>
                    <li>Try using a different hex color (some very light colors may not show well)</li>
                    <li>Clear your site's cache if using a caching plugin</li>
                </ul>

                <h3>Automatic Updates Not Working?</h3>
                <ul>
                    <li>WordPress Cron requires site visits to trigger</li>
                    <li>Try changing the scheduled time and saving settings</li>
                    <li>Use the manual update button if needed</li>
                </ul>
            </div>

            <div class="bdt-card">
                <h2>📝 Google Sheet Example</h2>
                <p>Here's what your Google Sheet should look like:</p>
                <table class="bdt-example-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>DonorName</th>
                            <th>State</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-10-25 11:45:00</td>
                            <td>John Doe</td>
                            <td>CA</td>
                            <td>100</td>
                        </tr>
                        <tr>
                            <td>2025-10-25 13:25:00</td>
                            <td>Jane Smith</td>
                            <td>NY</td>
                            <td>250</td>
                        </tr>
                        <tr>
                            <td>2025-10-26 5:37:00</td>
                            <td>Bob Johnson</td>
                            <td>TX</td>
                            <td>500</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="bdt-card">
                <h2>💡 Tips & Best Practices</h2>
                <ul>
                    <li>Keep your Google Sheet organized with consistent formatting</li>
                    <li>Use the same sheet structure - don't change column names</li>
                    <li>Set a reasonable goal amount for the thermometer</li>
                    <li>Choose a theme color that contrasts well with white for better visibility</li>
                    <li>Schedule automatic updates during low-traffic hours (e.g., 3 AM)</li>
                    <li>Test the manual update button after making changes to your Google Sheet</li>
                    <li>For dark backgrounds, use bright, vibrant theme colors</li>
                </ul>
            </div>

            <div class="bdt-card">
                <h2>🎯 Shortcode Options</h2>

                <h3>Map Shortcode</h3>
                <code>[benchmark_map width="100%" height="auto"]</code>
                <p>Customize the width and height as needed.</p>

                <h3>Thermometer Shortcode</h3>
                <code>[benchmark_thermometer goal="50000" width="200px" height="400px"]</code>
                <p>Override the default goal amount or customize dimensions.</p>
            </div>

            <div class="bdt-card">
                <h2>✅ Plugin Requirements</h2>
                <ul>
                    <li>WordPress 6.0 or higher</li>
                    <li>PHP 7.4 or higher</li>
                    <li>A published Google Sheet with donation data</li>
                    <li>Compatible with BeTheme and most modern WordPress themes</li>
                </ul>
            </div>

            <div class="bdt-card">
                <h2>📞 Need Help?</h2>
                <p>
                    If you're still having issues, make sure to:
                </p>
                <ul>
                    <li>Check all settings are saved properly</li>
                    <li>Verify your Google Sheet is publicly accessible</li>
                    <li>Try the manual update button first</li>
                    <li>Check your WordPress error logs for any issues</li>
                </ul>
            </div>

            <div class="bdt-card bdt-success-box">
                <h2>🎉 You're All Set!</h2>
                <p>
                    Your Benchmark Donations Tracker is ready to go. Head over to the
                    <a href="<?php echo admin_url('admin.php?page=benchmark-donations'); ?>">Settings page</a>
                    to configure your tracker or start adding shortcodes to your pages!
                </p>
            </div>
        </div>
    </div>
    <?php
}
