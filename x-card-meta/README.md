# X Card Meta - WordPress Plugin

A lightweight, performance-optimized WordPress plugin that adds X (Twitter) card meta tags with summary large image format to your WordPress site.

## Features

- ✅ **Summary Large Image Format**: Uses the recommended Twitter card format for maximum visual impact
- ✅ **Automatic Data Extraction**: 
  - Title from post title
  - Description from og:description meta or post excerpt
  - Image from og:image meta or featured image
- ✅ **Renamed File Support**: Compatible with plugins that rename image files upon publishing
- ✅ **Performance Optimized**: 
  - Caching system to prevent repeated processing
  - Optional loading only on single posts/pages
  - Minimal resource usage
- ✅ **Easy Configuration**: Simple admin interface with Twitter site and creator fields
- ✅ **SEO Plugin Compatible**: Works with Yoast SEO, All in One SEO, and other popular SEO plugins

## Installation

1. Upload the `x-card-meta` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > X Card Meta to configure your Twitter handles
4. The plugin will automatically start adding X card meta tags to your posts and pages

## Configuration

### Settings Page

Navigate to **Settings > X Card Meta** in your WordPress admin to configure:

- **Twitter Site**: Your website's Twitter handle (e.g., @yoursite)
- **Twitter Creator**: Default creator Twitter handle (e.g., @yourcreator)  
- **Enable Caching**: Improves performance by caching generated meta tags
- **Load Only on Single Posts/Pages**: Recommended for better performance

### How It Works

The plugin automatically:

1. **Gets Title**: Uses the post/page title for `twitter:title`
2. **Gets Description**: 
   - First tries existing `og:description` meta tags
   - Falls back to post excerpt
   - Finally uses trimmed post content (30 words)
3. **Gets Image**:
   - First tries existing `og:image` meta tags
   - Falls back to featured image
   - Handles renamed image files automatically
4. **Handles Renamed Files**: Looks for common renaming patterns like:
   - `{post-id}-{filename}.{ext}`
   - `{post-slug}-{filename}.{ext}`
   - `{sanitized-title}-{filename}.{ext}`

## Generated Meta Tags

The plugin generates the following meta tags:

```html
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:site" content="@yoursite" />
<meta name="twitter:creator" content="@yourcreator" />
<meta name="twitter:title" content="Your Post Title" />
<meta name="twitter:description" content="Your post description..." />
<meta name="twitter:image" content="https://yoursite.com/image.jpg" />
<meta name="twitter:image:alt" content="Image alt text" />
```

## Performance Optimizations

### Caching System
- Meta tags are cached for 1 hour to prevent regeneration on every page load
- Cache is automatically cleared when plugin settings are updated
- Uses WordPress object cache for optimal performance

### Conditional Loading
- Option to load only on single posts/pages (recommended)
- Reduces unnecessary processing on archive pages
- Minimal memory footprint

### Efficient Code
- Single instance pattern to prevent multiple initializations
- Lazy loading of options and meta data
- Optimized database queries

## Compatibility

### SEO Plugins
- **Yoast SEO**: Automatically uses `_yoast_wpseo_opengraph-description` and `_yoast_wpseo_opengraph-image`
- **All in One SEO**: Uses `_aioseop_description` and related meta fields
- **Other SEO plugins**: Falls back to featured images and post excerpts

### Image Rename Plugins
The plugin is designed to work with plugins that rename images when posts are published. It checks for common renaming patterns and automatically finds the renamed files.

### WordPress Versions
- Requires WordPress 4.7 or higher
- Tested up to WordPress 6.4
- PHP 7.0 or higher recommended

## File Structure

```
x-card-meta/
├── x-card-meta.php          # Main plugin file
├── assets/
│   └── admin.css            # Admin panel styling
├── languages/               # Translation files (if needed)
└── README.md               # This file
```

## Hooks and Filters

The plugin provides several hooks for developers:

### Filters

```php
// Modify the generated meta tags array before output
apply_filters('xcm_meta_tags', $meta_tags, $post_id);

// Modify the image URL before processing
apply_filters('xcm_image_url', $image_url, $post_id);

// Modify the description before output
apply_filters('xcm_description', $description, $post_id);
```

### Actions

```php
// Fires before meta tags are output
do_action('xcm_before_meta_output', $post_id);

// Fires after meta tags are output
do_action('xcm_after_meta_output', $post_id);
```

## Troubleshooting

### Meta Tags Not Showing
1. Check if the plugin is activated
2. Ensure you're viewing a single post or page (if the single-only option is enabled)
3. Check your theme's `wp_head()` function is properly called

### Images Not Loading
1. Verify the image exists in your media library
2. Check if your image rename plugin is working correctly
3. Ensure proper file permissions on uploaded images

### Performance Issues
1. Enable caching in plugin settings
2. Enable "Load Only on Single Posts/Pages" option
3. Check for conflicts with other plugins

## Changelog

### Version 1.0.0
- Initial release
- Summary large image card support
- Automatic content extraction
- Renamed file support
- Performance optimizations
- Admin settings panel

## Support

For support, please check:
1. This README file
2. WordPress plugin repository
3. Plugin settings page (About section)

## License

This plugin is licensed under the GPL v2 or later.

---

**Note**: This plugin is designed to be lightweight and fast. It only adds the necessary X card meta tags without bloating your site with unnecessary features.
