# X Card Meta Plugin - Installation Guide

## Quick Start

1. **Upload**: Copy the entire `X card data` folder to your WordPress `/wp-content/plugins/` directory
2. **Rename**: Rename the folder from `X card data` to `x-card-meta` for consistency
3. **Activate**: Go to WordPress Admin > Plugins and activate "X Card Meta"
4. **Configure**: Go to Settings > X Card Meta to set up your Twitter handles

## Detailed Installation Steps

### Method 1: Direct Upload (Recommended)

1. **Prepare the files**:
   ```
   Rename "X card data" folder to "x-card-meta"
   ```

2. **Upload via FTP/cPanel**:
   - Upload the `x-card-meta` folder to `/wp-content/plugins/`
   - Final path should be: `/wp-content/plugins/x-card-meta/`

3. **Set permissions** (if needed):
   - Folders: 755
   - Files: 644

4. **Activate**:
   - Log into WordPress Admin
   - Go to Plugins > Installed Plugins
   - Find "X Card Meta" and click "Activate"

### Method 2: ZIP Installation

1. **Create ZIP**:
   - Compress the entire folder into `x-card-meta.zip`
   
2. **Upload via WordPress**:
   - Go to Plugins > Add New > Upload Plugin
   - Choose the ZIP file and click "Install Now"
   - Click "Activate Plugin"

## Initial Configuration

After activation, configure the plugin:

1. **Go to Settings**: Admin Menu > Settings > X Card Meta

2. **Configure Basic Settings**:
   - **Twitter Site**: Enter your website's Twitter handle (e.g., `@yoursite`)
   - **Twitter Creator**: Enter the default creator handle (e.g., `@yourcreator`)

3. **Performance Settings** (Recommended):
   - ✅ Enable Caching: Keep checked for better performance
   - ✅ Load Only on Single Posts/Pages: Keep checked to avoid loading on archive pages

4. **Click "Save Changes"**

## Verification

To verify the plugin is working:

1. **View Page Source**:
   - Go to any post or page on your site
   - Right-click and select "View Page Source"
   - Search for "X Card Meta Plugin" or "twitter:card"

2. **Expected Output**:
   ```html
   <!-- X Card Meta Plugin -->
   <meta name="twitter:card" content="summary_large_image" />
   <meta name="twitter:site" content="@yoursite" />
   <meta name="twitter:creator" content="@yourcreator" />
   <meta name="twitter:title" content="Your Post Title" />
   <meta name="twitter:description" content="Your description..." />
   <meta name="twitter:image" content="https://yoursite.com/image.jpg" />
   <!-- /X Card Meta Plugin -->
   ```

3. **Test with Twitter Card Validator**:
   - Go to: https://cards-dev.twitter.com/validator
   - Enter your post URL to test the card preview

## File Structure After Installation

```
/wp-content/plugins/x-card-meta/
├── x-card-meta.php          # Main plugin file
├── uninstall.php            # Cleanup script
├── assets/
│   └── admin.css            # Admin styling
├── includes/
│   └── config.php           # Configuration file
├── languages/
│   └── x-card-meta.pot      # Translation template
└── README.md               # Documentation
```

## Compatibility Check

### WordPress Requirements
- WordPress 4.7+ ✅
- PHP 7.0+ ✅ 
- MySQL 5.6+ ✅

### Common SEO Plugins
- ✅ Yoast SEO
- ✅ All in One SEO Pack
- ✅ Rank Math
- ✅ The SEO Framework

### Theme Compatibility
The plugin works with any theme that properly calls `wp_head()` in the `<head>` section.

## Troubleshooting

### Plugin Not Showing in Admin
- Check folder name is exactly `x-card-meta`
- Verify all files uploaded correctly
- Check file permissions

### Meta Tags Not Appearing
- Verify plugin is activated
- Check if viewing single post/page (if single-only option enabled)
- Ensure theme calls `wp_head()` properly

### Performance Issues
- Enable caching in plugin settings
- Enable "single posts only" option
- Check for plugin conflicts

## Getting Help

1. **Check README.md** for detailed documentation
2. **Settings Page** has built-in help in the About section
3. **WordPress.org Support** forums for community help

## Next Steps

After installation:
1. Test on different post types
2. Verify images are loading correctly
3. Check compatibility with your image rename plugin
4. Monitor site performance
5. Test social sharing to ensure cards display properly

---

**Important**: After any configuration changes, clear any caching plugins you may have installed to see the changes immediately.
