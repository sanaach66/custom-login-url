<?php
/**
 * Plugin Name: Custom Login URL
 * Plugin URI: https://devsanaa.com/
 * Description: Change the default WordPress login URL to a custom slug for enhanced security. Configure under Settings → Custom Login URL.
 * Version: 1.0.0
 * Author: Sana Ullah
 * Author URI: https://devsanaa.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-login-url
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CLU_VERSION', '1.0.0');
define('CLU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CLU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CLU_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */

//echo "<h2>Test Text</h2>";
class Custom_Login_URL {
    
    /**
     * Single instance of the class
     *
     * @var Custom_Login_URL
     */
    private static $instance = null;
    
    /**
     * Get single instance
     *
     * @return Custom_Login_URL
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
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    /**
     * Load required files
     */
    private function load_dependencies() {
        require_once CLU_PLUGIN_DIR . 'includes/class-clu-settings.php';
        require_once CLU_PLUGIN_DIR . 'includes/class-clu-handler.php';
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Initialize components
        add_action('plugins_loaded', array($this, 'init'));
        
        // Add settings link on plugins page
        add_filter('plugin_action_links_' . CLU_PLUGIN_BASENAME, array($this, 'add_settings_link'));
    }
    
    /**
     * Initialize plugin components
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('custom-login-url', false, dirname(CLU_PLUGIN_BASENAME) . '/languages');
        
        // Initialize settings page (admin only)
        if (is_admin()) {
            CLU_Settings::get_instance();
        }
        
        // Initialize login handler (always)
        CLU_Handler::get_instance();
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        if (false === get_option('clu_login_slug')) {
            add_option('clu_login_slug', 'login');
        }
        if (false === get_option('clu_redirect_to_home')) {
            add_option('clu_redirect_to_home', '1');
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules to remove custom rules
        flush_rewrite_rules();
    }
    
    /**
     * Add settings link on plugins page
     *
     * @param array $links Existing links
     * @return array Modified links
     */
    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('options-general.php?page=custom-login-url') . '">' . 
                         esc_html__('Settings', 'custom-login-url') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
}

/**
 * Initialize the plugin
 */
function custom_login_url_init() {
    return Custom_Login_URL::get_instance();
}

// Start the plugin
custom_login_url_init();
