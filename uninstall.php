<?php
/**
 * X Card Meta Uninstall Script
 * 
 * This file is executed when the plugin is uninstalled (not just deactivated)
 * It cleans up all plugin data from the database
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('x_card_meta_settings');

// Clear any cached data
wp_cache_flush();

// Remove any transients (if we add any in future versions)
global $wpdb;

$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'xcm_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_xcm_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_xcm_%'");

// Clean up any site options for multisite
if (is_multisite()) {
    delete_site_option('x_card_meta_settings');
}
