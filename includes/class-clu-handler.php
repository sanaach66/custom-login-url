<?php
/**
 * Login URL handler
 *
 * @package Custom_Login_URL
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handler class for login URL redirection
 */
class CLU_Handler {
    
    private static $instance = null;
    private $login_slug;
    private $redirect_to_home;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->login_slug = get_option('clu_login_slug', 'login');
        $this->redirect_to_home = false; // Always use 404 instead of redirecting to home
        $this->init_hooks();
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('parse_request', array($this, 'parse_login_request'));
        add_action('init', array($this, 'block_default_login'), 1);
        add_filter('site_url', array($this, 'filter_site_url'), 10, 4);
        add_filter('network_site_url', array($this, 'filter_site_url'), 10, 4);
        add_filter('wp_redirect', array($this, 'filter_wp_redirect'), 10, 2);
        add_filter('login_url', array($this, 'filter_login_url'), 10, 3);
        add_filter('logout_url', array($this, 'filter_logout_url'), 10, 2);
        add_filter('lostpassword_url', array($this, 'filter_lostpassword_url'), 10, 2);
        add_filter('register_url', array($this, 'filter_register_url'));
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule('^' . $this->login_slug . '/?$', 'index.php?clu_login=1', 'top');
        add_rewrite_tag('%clu_login%', '([^&]+)');
    }
    
    public function parse_login_request($wp) {
        if (isset($wp->query_vars['clu_login']) && $wp->query_vars['clu_login'] == '1') {
            $this->handle_custom_login();
        }
    }
    
    private function handle_custom_login() {
        global $pagenow;
        $pagenow = 'wp-login.php';
        
        // Initialize variables that wp-login.php expects
        if (!isset($_GET['action'])) {
            $_GET['action'] = 'login';
        }
        
        // Prevent undefined variable warnings
        $error = '';
        $user_login = '';
        
        nocache_headers();
        require_once ABSPATH . 'wp-login.php';
        exit;
    }
    
    public function block_default_login() {
        global $pagenow, $wp;
        
        // Skip if it's an AJAX request or a whitelisted action
        if ((defined('DOING_AJAX') && DOING_AJAX) || $this->is_whitelisted_action()) {
            return;
        }
        
        // Allow logout action when user is logged in
        if (is_user_logged_in() && $pagenow === 'wp-login.php' && isset($_GET['action']) && $_GET['action'] === 'logout') {
            return;
        }
        
        // Get the current URL path
        $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $is_wp_admin = strpos($request_uri, '/wp-admin/') !== false;
        $is_wp_login = strpos($request_uri, 'wp-login.php') !== false;
        
        // Block direct access to wp-login.php
        if ($is_wp_login && !$this->is_whitelisted_action()) {
            $this->handle_blocked_access();
        }
        
        // Block access to wp-admin for non-logged-in users
        if ($is_wp_admin && !is_user_logged_in() && !$this->is_rest_request()) {
            // Check if it's an admin-ajax.php request (needed for some plugins)
            if (strpos($request_uri, 'admin-ajax.php') === false) {
                $this->handle_blocked_access();
            }
            return;
        }
        
        // Block access to wp-login.php through any other URL
        if (isset($request_uri) && strpos($request_uri, '/' . $this->login_slug) === false && 
            $pagenow === 'wp-login.php' && !$this->is_whitelisted_action()) {
            $this->handle_blocked_access();
        }
    }
    
    private function is_whitelisted_action() {
        $whitelisted = array('postpass', 'logout', 'lostpassword', 'retrievepassword', 'resetpass', 'rp', 'register');
        
        // Check for whitelisted actions
        if (isset($_GET['action']) && in_array($_GET['action'], $whitelisted, true)) {
            return true;
        }
        
        // Allow admin-ajax.php requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return true;
        }
        
        // Allow REST API requests
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if the current request is a REST API request
     */
    private function is_rest_request() {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }
        
        if (empty($_SERVER['REQUEST_URI'])) {
            return false;
        }
        
        $rest_prefix = trailingslashit(rest_get_url_prefix());
        $is_rest = (strpos($_SERVER['REQUEST_URI'], $rest_prefix) !== false);
        
        return $is_rest;
    }
    
    private function handle_blocked_access() {
        // Always show 404 for blocked login attempts, regardless of redirect setting
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        
        if (file_exists(get_template_directory() . '/404.php')) {
            include get_template_directory() . '/404.php';
        } else {
            wp_die(__('Page not found', 'custom-login-url'), '404 - Not Found', array('response' => 404));
        }
        exit;
    }
    
    public function filter_site_url($url, $path, $scheme, $blog_id = null) {
        return $this->replace_login_url($url);
    }
    
    public function filter_wp_redirect($location, $status) {
        return $this->replace_login_url($location);
    }
    
    private function replace_login_url($url) {
        if (strpos($url, 'wp-login.php') !== false) {
            $parsed = parse_url($url);
            $new_url = home_url($this->login_slug);
            
            if (isset($parsed['query'])) {
                $new_url .= '?' . $parsed['query'];
            }
            
            if (isset($parsed['fragment'])) {
                $new_url .= '#' . $parsed['fragment'];
            }
            
            return $new_url;
        }
        
        return $url;
    }
    
    public function filter_login_url($login_url, $redirect = '', $force_reauth = false) {
        $new_url = home_url($this->login_slug);
        
        if (!empty($redirect)) {
            $new_url = add_query_arg('redirect_to', urlencode($redirect), $new_url);
        }
        
        if ($force_reauth) {
            $new_url = add_query_arg('reauth', '1', $new_url);
        }
        
        return $new_url;
    }
    
    public function filter_logout_url($logout_url, $redirect = '') {
        $args = array('action' => 'logout');
        
        if (!empty($redirect)) {
            $args['redirect_to'] = urlencode($redirect);
        }
        
        $logout_url = add_query_arg($args, home_url($this->login_slug));
        $logout_url = wp_nonce_url($logout_url, 'log-out');
        
        return $logout_url;
    }
    
    public function filter_lostpassword_url($lostpassword_url, $redirect = '') {
        $args = array('action' => 'lostpassword');
        
        if (!empty($redirect)) {
            $args['redirect_to'] = urlencode($redirect);
        }
        
        return add_query_arg($args, home_url($this->login_slug));
    }
    
    public function filter_register_url($register_url) {
        return add_query_arg('action', 'register', home_url($this->login_slug));
    }
}
