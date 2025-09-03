<?php
/**
 * Plugin Name: X Card Meta
 * Plugin URI: https://github.com/tararauzumaki/x-card-meta
 * Description: Adds X (Twitter) card meta tags with summary large image to your WordPress site. Optimized for performance and compatible with image renaming plugins.
 * Version: 1.0.0
 * Author: Tanvir Rana Rabbi
 * License: GPL v2 or later
 * Text Domain: x-card-meta
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('XCM_VERSION', '1.0.0');
define('XCM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('XCM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('XCM_PLUGIN_FILE', __FILE__);

/**
 * Main X Card Meta Plugin Class
 */
class XCardMeta {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Plugin options
     */
    private $options;
    
    /**
     * Cache for meta data
     */
    private static $meta_cache = array();
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_options();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
        
        // Frontend hooks
        add_action('wp_head', array($this, 'add_x_card_meta'), 5);
        
        // Plugin activation/deactivation
        register_activation_hook(XCM_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(XCM_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Load text domain
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Load plugin options
     */
    private function load_options() {
        $this->options = get_option('x_card_meta_settings', array(
            'twitter_site' => '',
            'twitter_creator' => '',
            'enable_caching' => true,
            'load_only_single' => true
        ));
    }
    
    /**
     * Add X card meta tags to head
     */
    public function add_x_card_meta() {
        // Performance optimization: only load on single posts/pages if enabled
        if ($this->options['load_only_single'] && !is_singular()) {
            return;
        }
        
        // Get post ID
        $post_id = get_queried_object_id();
        if (!$post_id) {
            return;
        }
        
        // Check cache first
        $cache_key = 'xcm_meta_' . $post_id;
        if ($this->options['enable_caching']) {
            $cached_meta = wp_cache_get($cache_key, 'x_card_meta');
            if ($cached_meta !== false) {
                echo $cached_meta;
                return;
            }
        }
        
        // Generate meta tags
        $meta_output = $this->generate_meta_tags($post_id);
        
        // Cache the output
        if ($this->options['enable_caching']) {
            wp_cache_set($cache_key, $meta_output, 'x_card_meta', 3600); // Cache for 1 hour
        }
        
        echo $meta_output;
    }
    
    /**
     * Generate X card meta tags
     */
    private function generate_meta_tags($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            return '';
        }
        
        // Hook for before meta generation
        do_action('xcm_before_meta_generation', $post_id);
        
        $meta_tags = array();
        
        // Basic X card type
        $card_type = apply_filters('xcm_card_type', 'summary_large_image', $post_id);
        $meta_tags[] = '<meta name="twitter:card" content="' . esc_attr($card_type) . '" />';
        
        // Site and creator
        $twitter_site = apply_filters('xcm_twitter_site', $this->options['twitter_site'], $post_id);
        if (!empty($twitter_site)) {
            $meta_tags[] = '<meta name="twitter:site" content="' . esc_attr($twitter_site) . '" />';
        }
        
        $twitter_creator = apply_filters('xcm_twitter_creator', $this->options['twitter_creator'], $post_id);
        if (!empty($twitter_creator)) {
            $meta_tags[] = '<meta name="twitter:creator" content="' . esc_attr($twitter_creator) . '" />';
        }
        
        // Title - use post title
        $title = get_the_title($post_id);
        $title = apply_filters('xcm_title', $title, $post_id);
        if ($title) {
            // Limit title length for better display
            $title = mb_substr($title, 0, 70);
            $meta_tags[] = '<meta name="twitter:title" content="' . esc_attr($title) . '" />';
        }
        
        // Description - get from og:description or post excerpt
        $description = $this->get_og_description($post_id);
        $description = apply_filters('xcm_description', $description, $post_id);
        if ($description) {
            // Limit description length
            $description = mb_substr($description, 0, 200);
            $meta_tags[] = '<meta name="twitter:description" content="' . esc_attr($description) . '" />';
        }
        
        // Image - get from og:image with renamed file support
        $image_url = $this->get_og_image($post_id);
        $image_url = apply_filters('xcm_image_url', $image_url, $post_id);
        if ($image_url) {
            $meta_tags[] = '<meta name="twitter:image" content="' . esc_url($image_url) . '" />';
            
            // Add image alt text if available
            $image_alt = $this->get_image_alt($image_url, $post_id);
            $image_alt = apply_filters('xcm_image_alt', $image_alt, $post_id, $image_url);
            if ($image_alt) {
                $meta_tags[] = '<meta name="twitter:image:alt" content="' . esc_attr($image_alt) . '" />';
            }
        }
        
        // Allow filtering of all meta tags
        $meta_tags = apply_filters('xcm_meta_tags', $meta_tags, $post_id);
        
        // Wrap in comments for easy identification
        $output = "\n<!-- X Card Meta Plugin -->\n";
        $output .= implode("\n", $meta_tags);
        $output .= "\n<!-- /X Card Meta Plugin -->\n\n";
        
        // Hook for after meta generation
        do_action('xcm_after_meta_generation', $post_id, $output);
        
        return $output;
    }
    
    /**
     * Get OG description or fallback to post excerpt
     */
    private function get_og_description($post_id) {
        // First try to get from existing OG meta
        $og_description = get_post_meta($post_id, '_yoast_wpseo_opengraph-description', true);
        
        // Try other common OG description meta fields
        if (empty($og_description)) {
            $og_description = get_post_meta($post_id, '_aioseop_description', true);
        }
        
        // Fallback to post excerpt
        if (empty($og_description)) {
            $post = get_post($post_id);
            if ($post) {
                if (!empty($post->post_excerpt)) {
                    $og_description = $post->post_excerpt;
                } else {
                    $og_description = wp_trim_words(strip_tags($post->post_content), 30);
                }
            }
        }
        
        return $og_description;
    }
    
    /**
     * Get OG image with support for renamed files
     */
    private function get_og_image($post_id) {
        // First try to get from existing OG meta
        $og_image = get_post_meta($post_id, '_yoast_wpseo_opengraph-image', true);
        
        // Try other common OG image meta fields
        if (empty($og_image)) {
            $og_image = get_post_meta($post_id, '_aioseop_opengraph_settings', true);
            if (is_array($og_image) && isset($og_image['aioseop_opengraph_settings_image'])) {
                $og_image = $og_image['aioseop_opengraph_settings_image'];
            }
        }
        
        // Try featured image
        if (empty($og_image)) {
            $featured_image_id = get_post_thumbnail_id($post_id);
            if ($featured_image_id) {
                $og_image = wp_get_attachment_image_url($featured_image_id, 'large');
            }
        }
        
        // Handle renamed files - check if the original file exists or if it was renamed
        if (!empty($og_image)) {
            $og_image = $this->handle_renamed_image($og_image, $post_id);
        }
        
        return $og_image;
    }
    
    /**
     * Handle renamed image files
     */
    private function handle_renamed_image($image_url, $post_id) {
        // If it's already a full URL, check if it exists
        if (filter_var($image_url, FILTER_VALIDATE_URL)) {
            return $image_url;
        }
        
        // If it's a relative path or attachment ID
        if (is_numeric($image_url)) {
            // It's an attachment ID
            $image_url = wp_get_attachment_url($image_url);
            if ($image_url) {
                return $image_url;
            }
        }
        
        // Check for renamed files by looking for post-specific naming patterns
        // This assumes your rename plugin follows a pattern like post-id-filename or post-slug-filename
        $post = get_post($post_id);
        if ($post && !empty($image_url)) {
            $upload_dir = wp_upload_dir();
            $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
            
            // If original doesn't exist, try to find renamed version
            if (!file_exists($image_path)) {
                $pathinfo = pathinfo($image_path);
                $possible_names = array(
                    $post_id . '-' . $pathinfo['filename'] . '.' . $pathinfo['extension'],
                    $post->post_name . '-' . $pathinfo['filename'] . '.' . $pathinfo['extension'],
                    sanitize_title($post->post_title) . '-' . $pathinfo['filename'] . '.' . $pathinfo['extension']
                );
                
                foreach ($possible_names as $possible_name) {
                    $renamed_path = $pathinfo['dirname'] . '/' . $possible_name;
                    if (file_exists($renamed_path)) {
                        return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $renamed_path);
                    }
                }
            }
        }
        
        return $image_url;
    }
    
    /**
     * Get image alt text
     */
    private function get_image_alt($image_url, $post_id) {
        // Try to get attachment ID from URL
        $attachment_id = attachment_url_to_postid($image_url);
        
        if ($attachment_id) {
            $alt_text = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
            if ($alt_text) {
                return $alt_text;
            }
        }
        
        // Fallback to post title
        return get_the_title($post_id);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        if (!get_option('x_card_meta_settings')) {
            update_option('x_card_meta_settings', array(
                'twitter_site' => '',
                'twitter_creator' => '',
                'enable_caching' => true,
                'load_only_single' => true
            ));
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear cache
        wp_cache_flush();
    }
    
    /**
     * Load text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('x-card-meta', false, dirname(plugin_basename(XCM_PLUGIN_FILE)) . '/languages/');
    }
    
    /**
     * Add admin menu
     */
    public function admin_menu() {
        add_options_page(
            __('X Card Meta Settings', 'x-card-meta'),
            __('X Card Meta', 'x-card-meta'),
            'manage_options',
            'x-card-meta',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Initialize admin settings
     */
    public function admin_init() {
        register_setting('x_card_meta_settings', 'x_card_meta_settings', array($this, 'sanitize_settings'));
        
        // Settings sections
        add_settings_section(
            'x_card_meta_main',
            __('X Card Settings', 'x-card-meta'),
            array($this, 'settings_section_callback'),
            'x-card-meta'
        );
        
        // Settings fields
        add_settings_field(
            'twitter_site',
            __('Twitter Site', 'x-card-meta'),
            array($this, 'twitter_site_callback'),
            'x-card-meta',
            'x_card_meta_main'
        );
        
        add_settings_field(
            'twitter_creator',
            __('Twitter Creator', 'x-card-meta'),
            array($this, 'twitter_creator_callback'),
            'x-card-meta',
            'x_card_meta_main'
        );
        
        add_settings_field(
            'enable_caching',
            __('Enable Caching', 'x-card-meta'),
            array($this, 'enable_caching_callback'),
            'x-card-meta',
            'x_card_meta_main'
        );
        
        add_settings_field(
            'load_only_single',
            __('Load Only on Single Posts/Pages', 'x-card-meta'),
            array($this, 'load_only_single_callback'),
            'x-card-meta',
            'x_card_meta_main'
        );
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        $sanitized['twitter_site'] = sanitize_text_field($input['twitter_site']);
        $sanitized['twitter_creator'] = sanitize_text_field($input['twitter_creator']);
        $sanitized['enable_caching'] = isset($input['enable_caching']) ? true : false;
        $sanitized['load_only_single'] = isset($input['load_only_single']) ? true : false;
        
        // Clear cache when settings are updated
        wp_cache_flush();
        
        return $sanitized;
    }
    
    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __('Configure your X (Twitter) card settings below.', 'x-card-meta') . '</p>';
    }
    
    /**
     * Twitter site field callback
     */
    public function twitter_site_callback() {
        $value = isset($this->options['twitter_site']) ? $this->options['twitter_site'] : '';
        echo '<input type="text" name="x_card_meta_settings[twitter_site]" value="' . esc_attr($value) . '" class="regular-text" placeholder="@yoursite" />';
        echo '<p class="description">' . __('Your site\'s Twitter username (including @).', 'x-card-meta') . '</p>';
    }
    
    /**
     * Twitter creator field callback
     */
    public function twitter_creator_callback() {
        $value = isset($this->options['twitter_creator']) ? $this->options['twitter_creator'] : '';
        echo '<input type="text" name="x_card_meta_settings[twitter_creator]" value="' . esc_attr($value) . '" class="regular-text" placeholder="@yourcreator" />';
        echo '<p class="description">' . __('Default Twitter username for content creator (including @).', 'x-card-meta') . '</p>';
    }
    
    /**
     * Enable caching field callback
     */
    public function enable_caching_callback() {
        $value = isset($this->options['enable_caching']) ? $this->options['enable_caching'] : true;
        echo '<input type="checkbox" name="x_card_meta_settings[enable_caching]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label>' . __('Enable caching to improve performance', 'x-card-meta') . '</label>';
    }
    
    /**
     * Load only single field callback
     */
    public function load_only_single_callback() {
        $value = isset($this->options['load_only_single']) ? $this->options['load_only_single'] : true;
        echo '<input type="checkbox" name="x_card_meta_settings[load_only_single]" value="1" ' . checked($value, true, false) . ' />';
        echo '<label>' . __('Only load X card meta on single posts and pages (recommended)', 'x-card-meta') . '</label>';
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_scripts($hook) {
        if ($hook !== 'settings_page_x-card-meta') {
            return;
        }
        
        wp_enqueue_style('x-card-meta-admin', XCM_PLUGIN_URL . 'assets/admin.css', array(), XCM_VERSION);
    }
    
    /**
     * Admin page HTML
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="xcm-admin-container">
                <div class="xcm-main-content">
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('x_card_meta_settings');
                        do_settings_sections('x-card-meta');
                        submit_button();
                        ?>
                    </form>
                </div>
                
                <div class="xcm-sidebar">
                    <div class="xcm-box">
                        <h3><?php _e('About X Card Meta', 'x-card-meta'); ?></h3>
                        <p><?php _e('This plugin adds X (Twitter) card meta tags to your WordPress site with summary large image format.', 'x-card-meta'); ?></p>
                        
                        <h4><?php _e('Features:', 'x-card-meta'); ?></h4>
                        <ul>
                            <li><?php _e('✓ Summary large image format', 'x-card-meta'); ?></li>
                            <li><?php _e('✓ Uses post title automatically', 'x-card-meta'); ?></li>
                            <li><?php _e('✓ Gets description from og:description', 'x-card-meta'); ?></li>
                            <li><?php _e('✓ Gets image from og:image', 'x-card-meta'); ?></li>
                            <li><?php _e('✓ Supports renamed image files', 'x-card-meta'); ?></li>
                            <li><?php _e('✓ Performance optimized with caching', 'x-card-meta'); ?></li>
                        </ul>
                        
                        <h4><?php _e('Version:', 'x-card-meta'); ?></h4>
                        <p><?php echo XCM_VERSION; ?></p>
                    </div>
                    
                    <div class="xcm-box">
                        <h3><?php _e('How it Works', 'x-card-meta'); ?></h3>
                        <p><?php _e('The plugin automatically:', 'x-card-meta'); ?></p>
                        <ol>
                            <li><?php _e('Uses the post/page title for twitter:title', 'x-card-meta'); ?></li>
                            <li><?php _e('Gets description from existing OG meta or post excerpt', 'x-card-meta'); ?></li>
                            <li><?php _e('Gets image from existing OG meta or featured image', 'x-card-meta'); ?></li>
                            <li><?php _e('Handles renamed image files automatically', 'x-card-meta'); ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
function xcm_init() {
    return XCardMeta::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'xcm_init');
