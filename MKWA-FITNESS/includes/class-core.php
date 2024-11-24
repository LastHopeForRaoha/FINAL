<?php
/**
 * Core functionality for MKWA Fitness
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Core {
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
        
        // User related hooks
        add_action('init', array($this, 'register_user_roles'));
        add_action('user_register', array($this, 'setup_new_user'));
        
        // Activity tracking hooks
        add_action('wp', array($this, 'schedule_daily_tasks'));
        add_action('mkwa_daily_streak_check', array($this, 'process_daily_streaks'));
        
        // Frontend hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_mkwa_log_activity', array($this, 'handle_activity_logging'));

        // NEW: Add admin styles hook
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
    }

    /**
     * NEW: Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        // Only load on our plugin pages
        if (strpos($hook, 'mkwa-fitness') !== false) {
            wp_enqueue_style(
                'mkwa-admin',
                MKWA_PLUGIN_URL . 'admin/css/admin.css',
                array(),
                MKWA_VERSION
            );
        }
    }

    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        add_menu_page(
            __('MKWA Fitness', 'mkwa-fitness'),
            __('MKWA Fitness', 'mkwa-fitness'),
            'manage_options',
            'mkwa-fitness',
            array($this, 'render_dashboard_page'),
            'dashicons-universal-access',
            30
        );

        add_submenu_page(
            'mkwa-fitness',
            __('Dashboard', 'mkwa-fitness'),
            __('Dashboard', 'mkwa-fitness'),
            'manage_options',
            'mkwa-fitness',
            array($this, 'render_dashboard_page')
        );

        // NEW: Add Badges submenu
        add_submenu_page(
            'mkwa-fitness',
            __('Badges', 'mkwa-fitness'),
            __('Badges', 'mkwa-fitness'),
            'manage_options',
            'mkwa-fitness-badges',
            array($this, 'render_badges_page')
        );

        // NEW: Add Activities submenu
        add_submenu_page(
            'mkwa-fitness',
            __('Activities', 'mkwa-fitness'),
            __('Activities', 'mkwa-fitness'),
            'manage_options',
            'mkwa-fitness-activities',
            array($this, 'render_activities_page')
        );

        add_submenu_page(
            'mkwa-fitness',
            __('Settings', 'mkwa-fitness'),
            __('Settings', 'mkwa-fitness'),
            'manage_options',
            'mkwa-settings',
            array($this, 'render_settings_page')
        );
    }

    // [Previous code remains unchanged until the render methods]

    /**
     * NEW: Render badges page
     */
    public function render_badges_page() {
        // Get all badges from the database
        global $wpdb;
        $badges = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mkwa_badges ORDER BY points_required ASC");
        
        include MKWA_PLUGIN_DIR . 'admin/templates/badges.php';
    }

    /**
     * NEW: Render activities page
     */
    public function render_activities_page() {
        // Get recent activities from the database
        global $wpdb;
        $activities = $wpdb->get_results(
            "SELECT a.*, u.display_name 
            FROM {$wpdb->prefix}mkwa_activity_log a 
            LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
            ORDER BY a.logged_at DESC 
            LIMIT 50"
        );
        
        include MKWA_PLUGIN_DIR . 'admin/templates/activities.php';
    }

    /**
     * Render dashboard page
     */
    public function render_dashboard_page() {
        // Get summary statistics
        global $wpdb;
        
        $stats = array(
            'total_users' => $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}mkwa_activity_log"),
            'total_activities' => $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_activity_log"),
            'total_points' => $wpdb->get_var("SELECT SUM(points) FROM {$wpdb->prefix}mkwa_activity_log"),
            'recent_activities' => $wpdb->get_results(
                "SELECT a.*, u.display_name 
                FROM {$wpdb->prefix}mkwa_activity_log a 
                LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID 
                ORDER BY a.logged_at DESC 
                LIMIT 10"
            )
        );
        
        include MKWA_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }

    // [Previous code remains unchanged]
}