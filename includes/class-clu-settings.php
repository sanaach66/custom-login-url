<?php
/**
 * Settings page handler
 *
 * @package Custom_Login_URL
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings class
 */

//echo "<h2>Test text</h2>";
class CLU_Settings {
    
    private static $instance = null;
    private $option_slug = 'clu_login_slug';
    private $option_redirect = 'clu_redirect_to_home';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'maybe_flush_rewrite_rules'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    public function add_settings_page() {
        add_options_page(
            __('Custom Login URL Settings', 'custom-login-url'),
            __('Custom Login URL', 'custom-login-url'),
            'manage_options',
            'custom-login-url',
            array($this, 'render_settings_page')
        );
    }
    
    public function register_settings() {
        register_setting('clu_settings_group', $this->option_slug, array(
            'type' => 'string',
            'sanitize_callback' => array($this, 'sanitize_slug'),
            'default' => 'login'
        ));
        
        register_setting('clu_settings_group', $this->option_redirect, array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '1'
        ));
        
        add_settings_section('clu_main_section', __('Login URL Configuration', 'custom-login-url'),
            array($this, 'render_section_description'), 'custom-login-url');
        
        add_settings_field('clu_login_slug', __('Custom Login Slug', 'custom-login-url'),
            array($this, 'render_slug_field'), 'custom-login-url', 'clu_main_section');
        
        add_settings_field('clu_redirect_to_home', __('Default wp-login.php Behavior', 'custom-login-url'),
            array($this, 'render_redirect_field'), 'custom-login-url', 'clu_main_section');
    }
    
    public function maybe_flush_rewrite_rules() {
        if (get_transient('clu_flush_rewrite_rules')) {
            delete_transient('clu_flush_rewrite_rules');
            flush_rewrite_rules();
        }
    }
    
    public function sanitize_slug($input) {
        $input = sanitize_title($input);
        
        if (empty($input)) {
            add_settings_error($this->option_slug, 'empty_slug',
                __('Login slug cannot be empty. Using default "login".', 'custom-login-url'), 'error');
            return 'login';
        }
        
        $reserved = array('wp-admin', 'wp-content', 'wp-includes', 'admin', 'login', 'wp-login', 'wp-login.php');
        if (in_array($input, $reserved)) {
            add_settings_error($this->option_slug, 'reserved_slug',
                sprintf(__('The slug "%s" is reserved. Please choose a different one.', 'custom-login-url'), $input), 'error');
            return get_option($this->option_slug, 'login');
        }
        
        $old_slug = get_option($this->option_slug);
        if ($old_slug !== $input) {
            add_settings_error($this->option_slug, 'slug_updated',
                __('Login slug updated successfully. Rewrite rules have been flushed. Please test the new login URL.', 'custom-login-url'), 'success');
            // Set a transient to flush rewrite rules after option is saved
            set_transient('clu_flush_rewrite_rules', 1, 60);
        }
        
        return $input;
    }
    
    public function render_section_description() {
        echo '<p>' . esc_html__('Configure your custom login URL settings below. This will hide the default WordPress login page and create a new custom URL.', 'custom-login-url') . '</p>';
    }
    
    public function render_slug_field() {
        $slug = get_option($this->option_slug, 'login');
        $site_url = home_url();
        ?>
        <input type="text" id="clu_login_slug" name="<?php echo esc_attr($this->option_slug); ?>" 
               value="<?php echo esc_attr($slug); ?>" class="regular-text" pattern="[a-z0-9\-]+" required />
        <p class="description">
            <?php printf(esc_html__('Enter your custom login slug (letters, numbers, and hyphens only). Your new login URL will be: %s', 'custom-login-url'),
                '<br><strong class="clu-login-url">' . esc_url($site_url . '/<span id="clu-slug-preview">' . esc_html($slug) . '</span>') . '</strong>'); ?>
        </p>
        <?php
    }
    
    public function render_redirect_field() {
        $redirect = get_option($this->option_redirect, '1');
        ?>
        <fieldset>
            <label><input type="radio" name="<?php echo esc_attr($this->option_redirect); ?>" value="1" <?php checked($redirect, '1'); ?> />
                <?php esc_html_e('Redirect to homepage', 'custom-login-url'); ?></label><br>
            <label><input type="radio" name="<?php echo esc_attr($this->option_redirect); ?>" value="0" <?php checked($redirect, '0'); ?> />
                <?php esc_html_e('Show 404 error page', 'custom-login-url'); ?></label>
            <p class="description"><?php esc_html_e('Choose what happens when someone tries to access /wp-login.php directly.', 'custom-login-url'); ?></p>
        </fieldset>
        <?php
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'custom-login-url'));
        }
        
        $slug = get_option($this->option_slug, 'login');
        $login_url = home_url($slug);
        ?>
        <div class="wrap clu-settings-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="clu-notice-box">
                <h2><?php esc_html_e('⚠️ Important Security Notice', 'custom-login-url'); ?></h2>
                <p><?php esc_html_e('Please bookmark your new login URL and keep it safe. If you forget it, you may need to access your database to recover access.', 'custom-login-url'); ?></p>
                <p class="clu-current-url">
                    <strong><?php esc_html_e('Your current login URL:', 'custom-login-url'); ?></strong><br>
                    <a href="<?php echo esc_url($login_url); ?>" target="_blank" class="clu-login-link"><?php echo esc_url($login_url); ?></a>
                </p>
            </div>
            
            <form method="post" action="options.php" class="clu-settings-form">
                <?php settings_fields('clu_settings_group'); do_settings_sections('custom-login-url'); submit_button(__('Save Settings', 'custom-login-url')); ?>
            </form>
            
            <div class="clu-info-box">
                <h2><?php esc_html_e('Troubleshooting', 'custom-login-url'); ?></h2>
                <p><strong><?php esc_html_e('Getting 404 error on your custom login URL?', 'custom-login-url'); ?></strong></p>
                <p><?php printf(
                    esc_html__('Go to %s and click "Save Changes" to flush rewrite rules.', 'custom-login-url'),
                    '<a href="' . admin_url('options-permalink.php') . '">' . esc_html__('Settings → Permalinks', 'custom-login-url') . '</a>'
                ); ?></p>
                
                <h2><?php esc_html_e('How to Recover Access', 'custom-login-url'); ?></h2>
                <p><?php esc_html_e('If you forget your custom login URL, you can:', 'custom-login-url'); ?></p>
                <ol>
                    <li><?php esc_html_e('Deactivate this plugin via FTP/cPanel (rename the plugin folder)', 'custom-login-url'); ?></li>
                    <li><?php esc_html_e('Access your database and delete the "clu_login_slug" option', 'custom-login-url'); ?></li>
                    <li><?php esc_html_e('Use phpMyAdmin to view the option value in wp_options table', 'custom-login-url'); ?></li>
                </ol>
            </div>
        </div>
        <?php
    }
    
    public function enqueue_admin_assets($hook) {
        if ('settings_page_custom-login-url' !== $hook) {
            return;
        }
        
        wp_enqueue_style('clu-admin-styles', CLU_PLUGIN_URL . 'assets/css/admin.css', array(), CLU_VERSION);
        wp_enqueue_script('clu-admin-scripts', CLU_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), CLU_VERSION, true);
    }
}
