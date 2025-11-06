<?php
/**
 * Uninstall script
 * 
 * Fired when the plugin is uninstalled.
 *
 * @package Custom_Login_URL
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete plugin options
 */
function clu_uninstall() {
    delete_option('clu_login_slug');
    delete_option('clu_redirect_to_home');
    
    // For multisite
    if (is_multisite()) {
        global $wpdb;
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            delete_option('clu_login_slug');
            delete_option('clu_redirect_to_home');
            restore_current_blog();
        }
    }
    
    flush_rewrite_rules();
}

clu_uninstall();
