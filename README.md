# Custom Login URL

A production-ready WordPress plugin that allows administrators to change the default `/wp-login.php` URL to a custom slug for enhanced security.

## Features

- ✅ **Custom Login Slug**: Change `/wp-login.php` to any custom URL (e.g., `/secure-login`)
- ✅ **Admin Settings Page**: Easy-to-use interface under Settings → Custom Login URL
- ✅ **Flexible Blocking**: Choose to redirect to homepage or show 404 for default login URL
- ✅ **Security Focused**: Hides the default WordPress login page from bots and attackers
- ✅ **Clean Code**: Modular, object-oriented architecture following WordPress coding standards
- ✅ **No Inline Styles/Scripts**: All CSS and JavaScript in separate files
- ✅ **Translation Ready**: Fully internationalized with text domain
- ✅ **Multisite Compatible**: Works with WordPress multisite installations

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings → Custom Login URL to configure
4. Enter your desired login slug and save settings
5. **Important**: Bookmark your new login URL immediately!

## Configuration

1. Navigate to **Settings → Custom Login URL**
2. Enter your desired login slug (e.g., `secure-login`, `admin-access`)
3. Choose what happens when someone visits `/wp-login.php`:
   - **Redirect to homepage** (recommended)
   - **Show 404 error page**
4. Click "Save Settings"

### Slug Requirements

- Only lowercase letters, numbers, and hyphens allowed
- Cannot be empty
- Cannot use reserved slugs: `wp-admin`, `wp-content`, `wp-includes`, `admin`, `login`, `wp-login`

## Usage

After configuration, access your login page at:
```
https://yoursite.com/your-custom-slug
```

All WordPress login-related URLs will automatically update:
- Login: `https://yoursite.com/your-custom-slug`
- Logout: `https://yoursite.com/your-custom-slug?action=logout`
- Lost Password: `https://yoursite.com/your-custom-slug?action=lostpassword`
- Register: `https://yoursite.com/your-custom-slug?action=register`

## Recovery Methods

If you forget your custom login URL:

### Method 1: Deactivate Plugin via FTP
1. Connect to your site via FTP or File Manager
2. Navigate to `/wp-content/plugins/`
3. Rename the `sanaloginhide` folder to `sanaloginhide-disabled`
4. Access `/wp-login.php` normally

### Method 2: Database Access
1. Access phpMyAdmin or your database management tool
2. Open your WordPress database
3. Find the `wp_options` table
4. Search for `clu_login_slug` option to view your custom slug

## File Structure

```
sanaloginhide/
├── custom-login-url.php          # Main plugin file
├── uninstall.php                 # Uninstall cleanup script
├── includes/
│   ├── class-clu-settings.php    # Settings page handler
│   └── class-clu-handler.php     # Login URL handler
└── assets/
    ├── css/
    │   └── admin.css             # Admin styles
    └── js/
        └── admin.js              # Admin JavaScript
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Apache with mod_rewrite or Nginx with proper configuration

## Troubleshooting

### Issue: 404 Error on Custom Login URL

**Solution**: Go to Settings → Permalinks and click "Save Changes" to flush rewrite rules.

### Issue: Still Can Access wp-login.php

**Solution**: 
1. Deactivate and reactivate the plugin
2. Clear your browser cache
3. Clear server/plugin cache if using caching

### Issue: Locked Out of Admin

**Solution**: Use one of the recovery methods listed above.

## Best Practices

1. **Bookmark Your Login URL**: Save it in a password manager
2. **Test Before Production**: Try on staging site first
3. **Inform Team Members**: Share the new URL with all admins
4. **Use Strong Slug**: Choose something unique but memorable
5. **Regular Backups**: Always maintain site backups

## Security Note

This plugin enhances security through obscurity but should not be your only security measure. Always use strong passwords, keep WordPress updated, and implement additional security measures.

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, feature requests, or bug reports, please contact your administrator.
