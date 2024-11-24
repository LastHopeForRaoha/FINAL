<?php
/**
 * Core functionality for MKWA Fitness plugin
 *
 * @package MkwaFitness
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Core {
    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_ajax_mkwa_log_activity', array($this, 'handle_activity_logging'));
        
        // API endpoints
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        
        // Only load on our plugin's admin pages
        if ($screen && strpos($screen->id, 'mkwa-fitness') !== false) {
            wp_enqueue_style(
                'mkwa-admin-styles',
                MKWA_PLUGIN_URL . 'admin/css/mkwa-admin.css',
                array(),
                MKWA_VERSION
            );

            wp_enqueue_script(
                'mkwa-admin-script',
                MKWA_PLUGIN_URL . 'admin/js/mkwa-admin.js',
                array('jquery', 'jquery-ui-tooltip'),
                MKWA_VERSION,
                true
            );

            wp_localize_script('mkwa-admin-script', 'mkwaAdmin', array(
                'nonce' => wp_create_nonce('mkwa-admin-nonce'),
                'ajaxurl' => admin_url('admin-ajax.php')
            ));
        }
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_assets() {
        // Only enqueue on our plugin pages
        if (!is_page(['fitness-dashboard', 'fitness-profile'])) {
            return;
        }

        // Enqueue Chart.js
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '3.7.0',
            true
        );

        // Enqueue our styles
        wp_enqueue_style(
            'mkwa-fitness-styles',
            MKWA_PLUGIN_URL . 'assets/css/mkwa-fitness.css',
            array(),
            MKWA_VERSION
        );

        // Enqueue our scripts
        wp_enqueue_script(
            'mkwa-fitness-script',
            MKWA_PLUGIN_URL . 'assets/js/mkwa-fitness.js',
            array('jquery', 'chartjs'),
            MKWA_VERSION,
            true
        );

        // Localize script
        wp_localize_script('mkwa-fitness-script', 'mkwaFitness', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mkwa-fitness-nonce'),
            'strings' => array(
                'error' => __('An error occurred. Please try again.', 'mkwa-fitness'),
                'success' => __('Activity logged successfully!', 'mkwa-fitness'),
                'confirm' => __('Are you sure?', 'mkwa-fitness')
            ),
            'user' => array(
                'id' => get_current_user_id(),
                'name' => wp_get_current_user()->display_name
            )
        ));
    }

    /**
     * Register admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('MKWA Fitness', 'mkwa-fitness'),
            __('MKWA Fitness', 'mkwa-fitness'),
            'manage_options',
            'mkwa-fitness',
            array($this, 'render_admin_page'),
            'dashicons-universal-access',
            30
        );
    }

    /**
     * Register plugin settings
     */
    public function register_settings() {
        // General settings
        register_setting('mkwa_fitness_options', 'mkwa_cache_duration');
        
        // Points settings
        register_setting('mkwa_fitness_options', 'mkwa_points_checkin');
        register_setting('mkwa_fitness_options', 'mkwa_points_class');
        register_setting('mkwa_fitness_options', 'mkwa_points_cold_plunge');
        register_setting('mkwa_fitness_options', 'mkwa_points_pr');
        register_setting('mkwa_fitness_options', 'mkwa_points_competition');
        
        // Streak settings
        register_setting('mkwa_fitness_options', 'mkwa_streak_bonus_bronze');
        register_setting('mkwa_fitness_options', 'mkwa_streak_bonus_silver');
        register_setting('mkwa_fitness_options', 'mkwa_streak_bonus_gold');
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        require_once MKWA_PLUGIN_DIR . 'admin/admin-page.php';
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('mkwa_dashboard', array($this, 'render_dashboard'));
        add_shortcode('mkwa_profile', array($this, 'render_profile'));
    }

    /**
     * Render dashboard shortcode
     */
    public function render_dashboard($atts) {
        if (!is_user_logged_in()) {
            return __('Please log in to view your fitness dashboard.', 'mkwa-fitness');
        }

        ob_start();
        require MKWA_PLUGIN_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }

    /**
     * Render profile shortcode
     */
    public function render_profile($atts) {
        if (!is_user_logged_in()) {
            return __('Please log in to view your fitness profile.', 'mkwa-fitness');
        }

        ob_start();
        require MKWA_PLUGIN_DIR . 'templates/profile.php';
        return ob_get_clean();
    }

    /**
     * Handle activity logging via AJAX
     */
    public function handle_activity_logging() {
        check_ajax_referer('mkwa-fitness-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('Not logged in');
        }

        $activity_type = sanitize_text_field($_POST['activity_type']);
        $member_id = mkwa_get_member_id(get_current_user_id());

        $activities = new MKWA_Activities();
        $result = $activities->log_activity($member_id, $activity_type);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success(array(
            'points' => $result['points'],
            'message' => __('Activity logged successfully!', 'mkwa-fitness')
        ));
    }

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route('mkwa/v1', '/activities', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_activities_endpoint'),
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ));

        register_rest_route('mkwa/v1', '/stats', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_stats_endpoint'),
            'permission_callback' => function() {
                return is_user_logged_in();
            }
        ));
    }

    /**
     * REST API endpoint for activities
     */
    public function get_activities_endpoint(WP_REST_Request $request) {
        $member_id = mkwa_get_member_id(get_current_user_id());
        $activities = new MKWA_Activities();
        return $activities->get_member_activities($member_id);
    }

    /**
     * REST API endpoint for stats
     */
    public function get_stats_endpoint(WP_REST_Request $request) {
        $member_id = mkwa_get_member_id(get_current_user_id());
        return mkwa_get_member_stats($member_id);
    }
}