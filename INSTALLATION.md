# Benchmark Donations Tracker - Installation Guide

## Quick Installation

### Step 1: Upload Plugin
1. Download or copy the `benchmark-donations-tracker` folder
2. Upload it to your WordPress site's `/wp-content/plugins/` directory
3. You can use FTP, cPanel File Manager, or WordPress plugin upload

### Step 2: Activate Plugin
1. Log in to your WordPress admin dashboard
2. Go to **Plugins** → **Installed Plugins**
3. Find "Benchmark Donations Tracker"
4. Click **Activate**

### Step 3: Configure Settings
1. In your WordPress admin, go to **Donations Tracker** → **Settings**
2. Configure the following:
   - **Theme Color**: Choose a hex color (default: #00c19f)
   - **Google Sheets URL**: Paste your published Google Sheet URL
   - **Goal Amount**: Set your fundraising goal
   - **Scheduled Fetch Time**: Set when to auto-update (default: 3:00 AM)
3. Click **Save Settings**

### Step 4: Prepare Your Google Sheet
Your Google Sheet must have these columns (case-sensitive):
- **Timestamp**: Date/time of donation
- **DonorName**: Donor's name
- **State**: Two-letter state code (CA, NY, TX, etc.)
- **Amount**: Donation amount (numbers only, no $ symbol)

Example:
```
Timestamp               | DonorName    | State | Amount
2025-10-25 11:45:00    | John Doe     | CA    | 100
2025-10-25 13:25:00    | Jane Smith   | NY    | 250
```

### Step 5: Publish Your Google Sheet
1. Open your Google Sheet
2. Go to **File** → **Share** → **Publish to web**
3. Click **Publish**
4. Copy the URL provided
5. Paste it in the plugin settings (Step 3)

### Step 6: Fetch Data
1. In **Donations Tracker** → **Settings**
2. Click **Update Donations Info Now**
3. Wait for success message
4. Your data is now loaded!

### Step 7: Add Shortcodes to Your Pages
Use these shortcodes in any page or post:

#### Display US Map
```
[benchmark_map]
```

#### Display Thermometer
```
[benchmark_thermometer]
```

#### Display Statistics
```
[benchmark_stats]
```

## Shortcode Options

### Map Shortcode
```
[benchmark_map width="100%" height="auto"]
```

### Thermometer Shortcode
```
[benchmark_thermometer goal="50000" width="200px" height="400px"]
```

## Automatic Updates

The plugin automatically fetches data from Google Sheets once per day at your specified time. You can change this in the settings.

**Note**: WordPress Cron requires site traffic. For guaranteed execution, consider:
- Using a real server cron job
- Using a monitoring service like UptimeRobot
- Or simply use the manual update button daily

## Troubleshooting

### Data Not Loading?
- Verify your Google Sheet is published to web
- Check that column headers match exactly
- Ensure State uses 2-letter codes
- Make sure Amount contains numbers only

### Map Not Displaying?
- Clear browser cache
- Check that SVG file exists in `assets/images/`
- Try fetching data manually again

### Colors Not Showing?
- Save your theme color in settings
- Try a different hex color
- Clear site cache if using caching plugin

## Full Documentation

For complete documentation, go to **Donations Tracker** → **ReadMe & Directions** in your WordPress admin.

## File Structure

```
benchmark-donations-tracker/
├── benchmark-donations-tracker.php  (main plugin file)
├── admin/
│   ├── settings.php                 (settings page)
│   ├── readme.php                   (documentation page)
│   ├── admin-styles.css             (admin styling)
│   └── admin-script.js              (admin JavaScript)
├── includes/
│   ├── data-fetcher.php             (Google Sheets integration)
│   ├── shortcodes.php               (shortcode handlers)
│   └── cron.php                     (scheduled tasks)
├── assets/
│   ├── css/
│   │   └── frontend-styles.css      (frontend styling)
│   ├── js/
│   │   └── map-handler.js           (map interactions)
│   └── images/
│       └── us-map-sample.svg        (US map SVG)
└── readme.txt                        (plugin information)
```

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- A published Google Sheet with donation data
- Compatible with BeTheme and most modern WordPress themes

## Support

For issues or questions:
1. Check the **ReadMe & Directions** page in the plugin
2. Review the troubleshooting section above
3. Verify all settings are correct
4. Check WordPress error logs

## License

GPL v2 or later - Free for commercial use

---

**Built for michaell734.sg-host.com**
