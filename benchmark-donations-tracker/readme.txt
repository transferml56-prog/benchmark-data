=== Benchmark Donations Tracker ===
Contributors: Benchmark Team
Tags: donations, fundraising, google sheets, map, thermometer, visualization
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track donations by state with interactive US map and thermometer visualization, synced with Google Sheets.

== Description ==

Benchmark Donations Tracker is a powerful WordPress plugin that helps you visualize donation data from Google Sheets with beautiful, interactive displays including:

* **Interactive US Map** - Color-coded by donation amount, darker colors for higher donations
* **Fundraising Thermometer** - Visual progress tracker towards your goal
* **Real-time Statistics** - Display total donations, top states, and more
* **Automatic Syncing** - Daily automatic updates from your Google Sheet
* **Manual Updates** - Fetch latest data any time with one click

Perfect for nonprofits, fundraising campaigns, political campaigns, or any organization tracking donations across the United States.

= Features =

* Google Sheets Integration - Easy "publish to web" URL setup
* Customizable Theme Color - Match your brand
* Three Shortcodes - Map, Thermometer, and Statistics
* Automatic Daily Updates - Schedule fetch time
* Manual Data Fetch - Update on demand
* State-by-State Visualization - See which states contribute most
* Goal Tracking - Set and display fundraising goals
* Dark Theme Optimized - Looks great on dark backgrounds
* BeTheme Compatible - Works with BeTheme and most WordPress themes
* Responsive Design - Mobile-friendly displays
* Hover Tooltips - Interactive state information

= How It Works =

1. Create a Google Sheet with donation data (Timestamp, DonorName, State, Amount columns)
2. Publish your Google Sheet to the web
3. Paste the URL in the plugin settings
4. Customize your theme color and goal amount
5. Add shortcodes to your pages
6. Done! Your donation tracker is live

= Shortcodes =

* `[benchmark_map]` - Display interactive US map
* `[benchmark_thermometer]` - Display fundraising thermometer
* `[benchmark_stats]` - Display donation statistics

= Documentation =

Full documentation is available in the plugin's ReadMe & Directions page in your WordPress admin.

== Installation ==

1. Upload the `benchmark-donations-tracker` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Donations Tracker' in your admin menu
4. Configure your Google Sheets URL and settings
5. Add shortcodes to your pages

== Frequently Asked Questions ==

= What format does my Google Sheet need? =

Your Google Sheet needs four columns with these exact headers:
* Timestamp
* DonorName
* State (2-letter code like CA, NY, TX)
* Amount (numbers only)

= How do I get my Google Sheets URL? =

In Google Sheets, go to File → Share → Publish to web, then copy the URL provided.

= Can I customize the colors? =

Yes! Go to Donations Tracker → Settings and use the color picker to choose your theme color. States will use shades of this color based on donation amounts.

= How often does it update? =

The plugin automatically fetches data once daily at a time you specify. You can also manually update any time from the settings page.

= Does this work with my theme? =

Yes! The plugin is compatible with BeTheme and most modern WordPress themes. The styling is optimized for dark backgrounds but includes light theme support.

= Can I set a fundraising goal? =

Yes! Set your goal amount in the Settings page, and it will be displayed on the thermometer.

== Screenshots ==

1. Interactive US Map with state-by-state donation visualization
2. Fundraising thermometer with goal tracking
3. Admin settings page with color picker and Google Sheets configuration
4. Donation statistics display
5. ReadMe & Directions page with full documentation

== Changelog ==

= 1.0.0 =
* Initial release
* Interactive US map with hover tooltips
* Fundraising thermometer with customizable goal
* Google Sheets integration
* Automatic daily updates
* Manual update option
* Customizable theme color
* Three shortcodes (map, thermometer, stats)
* Dark theme optimized
* Responsive design
* Full admin documentation

== Upgrade Notice ==

= 1.0.0 =
Initial release of Benchmark Donations Tracker.

== Privacy Policy ==

This plugin fetches data from Google Sheets URLs that you provide. No personal data is collected or stored by the plugin itself. The donation data you display is entirely under your control through your Google Sheet.

== Credits ==

* US Map SVG based on work by Simplemaps.com (http://simplemaps.com)
* Built for BeTheme compatibility
* Designed for michaell734.sg-host.com
