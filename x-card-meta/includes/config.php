<?php
/**
 * X Card Meta Configuration
 * 
 * This file contains configuration constants and default values
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Configuration Class
 */
class XCM_Config {
    
    /**
     * Default plugin settings
     */
    const DEFAULT_SETTINGS = array(
        'twitter_site' => '',
        'twitter_creator' => '',
        'enable_caching' => true,
        'load_only_single' => true,
        'cache_duration' => 3600, // 1 hour
        'title_max_length' => 70,
        'description_max_length' => 200,
        'excerpt_word_limit' => 30
    );
    
    /**
     * SEO plugin meta field mappings
     */
    const SEO_META_FIELDS = array(
        'description' => array(
            '_yoast_wpseo_opengraph-description',
            '_yoast_wpseo_metadesc',
            '_aioseop_description',
            '_genesis_description',
            'meta_description'
        ),
        'image' => array(
            '_yoast_wpseo_opengraph-image',
            '_yoast_wpseo_opengraph-image-id',
            '_aioseop_opengraph_settings',
            '_genesis_canonical_uri'
        )
    );
    
    /**
     * Image file naming patterns for renamed files
     */
    const RENAME_PATTERNS = array(
        '{post_id}-{filename}.{ext}',
        '{post_slug}-{filename}.{ext}',
        '{post_title_sanitized}-{filename}.{ext}',
        '{date}-{filename}.{ext}'
    );
    
    /**
     * Supported image formats
     */
    const SUPPORTED_IMAGE_FORMATS = array(
        'jpg', 'jpeg', 'png', 'gif', 'webp'
    );
    
    /**
     * Performance settings
     */
    const PERFORMANCE = array(
        'cache_group' => 'x_card_meta',
        'max_cache_size' => 1000, // Maximum number of cached entries
        'enable_object_cache' => true,
        'enable_transient_cache' => true
    );
    
    /**
     * Get default setting value
     */
    public static function get_default($key) {
        return isset(self::DEFAULT_SETTINGS[$key]) ? self::DEFAULT_SETTINGS[$key] : null;
    }
    
    /**
     * Get all default settings
     */
    public static function get_defaults() {
        return self::DEFAULT_SETTINGS;
    }
    
    /**
     * Get SEO meta fields for a specific type
     */
    public static function get_seo_fields($type) {
        return isset(self::SEO_META_FIELDS[$type]) ? self::SEO_META_FIELDS[$type] : array();
    }
}
