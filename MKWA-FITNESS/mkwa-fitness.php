<?php
/**
 * Plugin Name: MKWA Fitness
 * Plugin URI: https://yoursite.com/mkwa-fitness
 * Description: A comprehensive fitness tracking and gamification system
 * Version: 1.0.0
 * Author: LastHopeForRaoha
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mkwa-fitness
 * Domain Path: /languages
 *
 * @package MkwaFitness
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define error logging function first
if (!function_exists('mkwa_log')) {
    function mkwa_log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }
}

// Define plugin constants
define('MKWA_VERSION', '1.0.0');
define('MKWA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MKWA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MKWA_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('MKWA_CURRENT_TIME', '2024-11-23 23:36:34'); // Updated to current time

try {
    // Load required files first to ensure constants are available
    require_once MKWA_PLUGIN_DIR . 'includes/constants.php';
} catch (Exception $e) {
    mkwa_log('Error loading constants.php: ' . $e->getMessage());
}

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    try {
        $prefix = 'MKWA_';
        $base_dir = MKWA_PLUGIN_DIR . 'includes/';
        
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        $relative_class = substr($class, $len);
        $file = $base_dir . 'class-' . str_replace('_', '-', strtolower($relative_class)) . '.php';
        
        if (file_exists($file)) {
            require $file;
            mkwa_log("Successfully loaded class file: $file");
        } else {
            mkwa_log("Class file not found: $file");
        }
    } catch (Exception $e) {
        mkwa_log('Error in autoloader: ' . $e->getMessage());
    }
});

// Load required files
try {
    require_once MKWA_PLUGIN_DIR . 'includes/functions.php';
    require_once MKWA_PLUGIN_DIR . 'includes/points-functions.php';
    require_once MKWA_PLUGIN_DIR . 'includes/badge-system.php';
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-frontend.php';
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-ajax.php';
} catch (Exception $e) {
    mkwa_log('Error loading required files: ' . $e->getMessage());
}

// Initialize frontend and AJAX handlers
new MKWA_Frontend();
new MKWA_Ajax();

/**
 * Main plugin class
 */
final class MKWA_Fitness {
    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        try {
            $this->init_hooks();
            mkwa_log('MKWA_Fitness instance constructed successfully');
        } catch (Exception $e) {
            mkwa_log('Error in MKWA_Fitness constructor: ' . $e->getMessage());
        }
    }
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', array($this, 'init_plugin'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Add admin menu hook
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        if (is_admin()) {
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        }
        mkwa_log('Hooks initialized successfully');
    }

    /**
     * Initialize plugin
     */
    public function init_plugin() {
        try {
            load_plugin_textdomain('mkwa-fitness', false, dirname(MKWA_PLUGIN_BASENAME) . '/languages');
            $this->maybe_init_database();
            mkwa_log('Plugin initialized successfully');
        } catch (Exception $e) {
            mkwa_log('Error initializing plugin: ' . $e->getMessage());
        }
    }

    /**
     * Add admin menu
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

        add_submenu_page(
            'mkwa-fitness',
            __('Leaderboard', 'mkwa-fitness'),
            __('Leaderboard', 'mkwa-fitness'),
            'manage_options',
            'mkwa-leaderboard',
            array($this, 'render_leaderboard_page')
        );

        add_submenu_page(
            'mkwa-fitness',
            __('Badges', 'mkwa-fitness'),
            __('Badges', 'mkwa-fitness'),
            'manage_options',
            'mkwa-badges',
            array($this, 'render_badges_page')
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

    /**
     * Render page methods
     */
    public function render_dashboard_page() {
        require_once MKWA_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }

    public function render_leaderboard_page() {
        require_once MKWA_PLUGIN_DIR . 'admin/templates/leaderboard.php';
    }

    public function render_badges_page() {
        require_once MKWA_PLUGIN_DIR . 'admin/templates/badges.php';
    }

    public function render_settings_page() {
        require_once MKWA_PLUGIN_DIR . 'admin/templates/settings.php';
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        try {
            if (strpos($hook, 'mkwa-fitness') !== false) {
                wp_enqueue_media();
                wp_enqueue_style('mkwa-admin', MKWA_PLUGIN_URL . 'admin/css/admin.css', array(), MKWA_VERSION);
                wp_enqueue_script('mkwa-admin', MKWA_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), MKWA_VERSION, true);
                mkwa_log('Admin scripts enqueued successfully');
            }
        } catch (Exception $e) {
            mkwa_log('Error enqueuing admin scripts: ' . $e->getMessage());
        }
    }
    /**
     * Plugin activation
     */
    public function activate() {
        try {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            mkwa_log('Starting plugin activation...');

            // Create members table
            $sql_members = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_members (
                member_id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                current_level int(11) NOT NULL DEFAULT 1,
                total_points int(11) NOT NULL DEFAULT 0,
                created_at datetime NOT NULL DEFAULT '" . MKWA_CURRENT_TIME . "',
                PRIMARY KEY  (member_id),
                KEY user_id (user_id)
            ) $charset_collate;";

            // Create badges table
            $sql_badges = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_badges (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                title varchar(255) NOT NULL,
                description text NOT NULL,
                icon_url varchar(255) NOT NULL,
                badge_type varchar(50) NOT NULL,
                category varchar(50) NOT NULL,
                points_required int(11) NOT NULL DEFAULT 0,
                activities_required text,
                cultural_requirement text,
                seasonal_requirement text,
                created_at datetime NOT NULL DEFAULT '" . MKWA_CURRENT_TIME . "',
                PRIMARY KEY  (id)
            ) $charset_collate;";

            // Create activity log table
            $sql_activity_log = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_activity_log (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                activity_type varchar(50) NOT NULL,
                points int(11) NOT NULL,
                logged_at datetime NOT NULL DEFAULT '" . MKWA_CURRENT_TIME . "',
                PRIMARY KEY  (id),
                KEY user_id (user_id),
                KEY activity_type (activity_type)
            ) $charset_collate;";

            // Create member metrics table
            $sql_member_metrics = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_member_metrics (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                attendance_rate decimal(5,2) DEFAULT 0.00,
                challenge_completion_rate decimal(5,2) DEFAULT 0.00,
                community_participation_score decimal(5,2) DEFAULT 0.00,
                streak_score decimal(5,2) DEFAULT 0.00,
                point_earning_velocity decimal(8,2) DEFAULT 0.00,
                consistency_factor decimal(5,2) DEFAULT 0.00,
                engagement_depth decimal(5,2) DEFAULT 0.00,
                overall_score decimal(10,2) DEFAULT 0.00,
                last_calculated datetime DEFAULT '" . MKWA_CURRENT_TIME . "',
                PRIMARY KEY (id),
                UNIQUE KEY user_idx (user_id),
                KEY score_idx (overall_score DESC)
            ) $charset_collate;";

            // Create leaderboard current table
            $sql_leaderboard_current = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_leaderboard_current (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                total_points bigint(20) NOT NULL DEFAULT 0,
                monthly_points int(11) NOT NULL DEFAULT 0,
                quarterly_points int(11) NOT NULL DEFAULT 0,
                ranking_score decimal(10,2) NOT NULL DEFAULT 0.00,
                monthly_rank int(11) DEFAULT NULL,
                quarterly_rank int(11) DEFAULT NULL,
                overall_rank int(11) DEFAULT NULL,
                updated_at datetime DEFAULT '" . MKWA_CURRENT_TIME . "',
                PRIMARY KEY (id),
                UNIQUE KEY user_idx (user_id),
                KEY ranking_idx (ranking_score DESC)
            ) $charset_collate;";

            // Create leaderboard history table
            $sql_leaderboard_history = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_leaderboard_history (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                period_type varchar(20) NOT NULL,
                period_start date NOT NULL,
                period_end date NOT NULL,
                points int(11) NOT NULL DEFAULT 0,
                final_rank int(11) NOT NULL,
                ranking_score decimal(10,2) NOT NULL DEFAULT 0.00,
                created_at datetime DEFAULT '" . MKWA_CURRENT_TIME . "',
                PRIMARY KEY (id),
                KEY period_idx (period_type, period_start, period_end),
                KEY user_period_idx (user_id, period_type, period_start)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            
            mkwa_log('Creating database tables...');
            dbDelta($sql_members);
            dbDelta($sql_badges);
            dbDelta($sql_activity_log);
            dbDelta($sql_member_metrics);
            dbDelta($sql_leaderboard_current);
            dbDelta($sql_leaderboard_history);

            // Set default options
            mkwa_log('Setting default options...');
            $default_options = array(
                'mkwa_points_checkin' => MKWA_POINTS_CHECKIN_DEFAULT,
                'mkwa_points_class' => MKWA_POINTS_CLASS_DEFAULT,
                'mkwa_points_cold_plunge' => MKWA_POINTS_COLD_PLUNGE_DEFAULT,
                'mkwa_points_pr' => MKWA_POINTS_PR_DEFAULT,
                'mkwa_points_competition' => MKWA_POINTS_COMPETITION_DEFAULT,
                'mkwa_cache_duration' => MKWA_CACHE_DURATION_DEFAULT,
            );

            foreach ($default_options as $key => $value) {
                add_option($key, $value);
            }

            // Ensure the current user is set up
            $user = wp_get_current_user();
            if ($user->exists()) {
                mkwa_ensure_member($user->ID);
                mkwa_log('Current user setup completed');
            }

            flush_rewrite_rules();
            mkwa_log('Plugin activated successfully');
            
        } catch (Exception $e) {
            mkwa_log('Error during plugin activation: ' . $e->getMessage());
            if (defined('WP_DEBUG') && WP_DEBUG) {
                mkwa_log($e->getTraceAsString());
            }
        }
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        try {
            wp_clear_scheduled_hook('mkwa_daily_streak_check');
            flush_rewrite_rules();
            mkwa_log('Plugin deactivated successfully');
        } catch (Exception $e) {
            mkwa_log('Error during plugin deactivation: ' . $e->getMessage());
        }
    }

    /**
     * Initialize database if needed
     */
    private function maybe_init_database() {
        try {
            $db_version = get_option('mkwa_db_version');
            if ($db_version !== MKWA_VERSION) {
                mkwa_log('Database version mismatch. Current: ' . ($db_version ?: 'none') . ', Required: ' . MKWA_VERSION);
                $this->activate();
                update_option('mkwa_db_version', MKWA_VERSION);
                mkwa_log('Database initialized successfully');
            }
        } catch (Exception $e) {
            mkwa_log('Error initializing database: ' . $e->getMessage());
        }
    }
}

/**
 * Main plugin instance
 */
function mkwa_fitness() {
    try {
        return MKWA_Fitness::instance();
    } catch (Exception $e) {
        mkwa_log('Error getting MKWA_Fitness instance: ' . $e->getMessage());
        return null;
    }
}

// Load required files
try {
    require_once MKWA_PLUGIN_DIR . 'includes/functions.php';
    require_once MKWA_PLUGIN_DIR . 'includes/points-functions.php';
    require_once MKWA_PLUGIN_DIR . 'includes/badge-system.php';
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-frontend.php';
    require_once MKWA_PLUGIN_DIR . 'includes/class-mkwa-ajax.php';
    require_once MKWA_PLUGIN_DIR . 'register.php'; // Add this line
    require_once MKWA_PLUGIN_DIR . 'login.php'; // Add this line
} catch (Exception $e) {
    mkwa_log('Error loading required files: ' . $e->getMessage());
}

// Initialize the plugin
try {
    mkwa_fitness();
    mkwa_log('Plugin initialization completed');
} catch (Exception $e) {
    mkwa_log('Error during plugin initialization: ' . $e->getMessage());
}

// Add Shortcodes for Login and Register Forms
function mkwa_register_form() {
    ob_start();
    ?>
    <form method="post" action="">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Register</button>
    </form>
    <?php
    mkwa_register_user();
    return ob_get_clean();
}
add_shortcode('mkwa_register', 'mkwa_register_form');

function mkwa_login_form() {
    ob_start();
    ?>
    <form method="post" action="">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Login</button>
    </form>
    <?php
    mkwa_login_user();
    return ob_get_clean();
}
add_shortcode('mkwa_login', 'mkwa_login_form');