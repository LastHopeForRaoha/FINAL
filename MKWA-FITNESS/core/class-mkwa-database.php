<?php
/**
 * Database management for Mkwa Fitness Plugin
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Database {
    /**
     * List of plugin tables
     */
    private static $tables = array(
        'members',
        'activities',
        'badges',
        'schedules'
    );
    
    /**
     * Create plugin database tables
     */
    public static function create_tables() {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Members table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_members (
            member_id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            current_level int(11) DEFAULT 1,
            total_points bigint(20) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (member_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Activities table - Updated with meta_data column
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_activities (
            activity_id bigint(20) NOT NULL AUTO_INCREMENT,
            member_id bigint(20) NOT NULL,
            activity_type varchar(50) NOT NULL,
            points_earned int(11) DEFAULT 0,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            meta_data longtext,
            PRIMARY KEY (activity_id),
            KEY member_id (member_id),
            KEY activity_type (activity_type),
            KEY timestamp (timestamp)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Badges table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_badges (
            badge_id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            description text,
            tier varchar(50) NOT NULL,
            points_required int(11) DEFAULT 0,
            auto_award tinyint(1) DEFAULT 1,
            svg_path varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (badge_id),
            KEY tier (tier),
            KEY points_required (points_required)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Schedules table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_schedules (
            schedule_id bigint(20) NOT NULL AUTO_INCREMENT,
            activity_type varchar(50) NOT NULL,
            start_time datetime NOT NULL,
            end_time datetime NOT NULL,
            capacity int(11) DEFAULT 0,
            current_bookings int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (schedule_id),
            KEY activity_type (activity_type),
            KEY start_time (start_time),
            KEY end_time (end_time)
        ) $charset_collate;";
        dbDelta($sql);

        // Member Achievement Stats table (new)
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_member_stats (
            stat_id bigint(20) NOT NULL AUTO_INCREMENT,
            member_id bigint(20) NOT NULL,
            current_streak int(11) DEFAULT 0,
            longest_streak int(11) DEFAULT 0,
            total_activities int(11) DEFAULT 0,
            last_activity_date datetime DEFAULT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (stat_id),
            UNIQUE KEY member_id (member_id),
            KEY current_streak (current_streak),
            KEY longest_streak (longest_streak)
        ) $charset_collate;";
        dbDelta($sql);
    }
    
    /**
     * Get full table name with prefix
     *
     * @param string $table Table name without prefix
     * @return string Full table name with prefix
     */
    public static function get_table_name($table) {
        global $wpdb;
        return $wpdb->prefix . 'mkwa_' . $table;
    }

    /**
     * Check if tables exist
     *
     * @return bool True if all tables exist
     */
    public static function check_tables() {
        global $wpdb;
        
        foreach (self::$tables as $table) {
            $table_name = self::get_table_name($table);
            $query = $wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table_name));
            
            if (!$wpdb->get_var($query)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Drop all plugin tables
     * 
     * @return void
     */
    public static function drop_tables() {
        global $wpdb;
        
        foreach (self::$tables as $table) {
            $table_name = self::get_table_name($table);
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }
    }

    /**
     * Get table status
     *
     * @return array Table status information
     */
    public static function get_tables_status() {
        global $wpdb;
        
        $status = array();
        
        foreach (self::$tables as $table) {
            $table_name = self::get_table_name($table);
            $exists = $wpdb->get_var($wpdb->prepare(
                'SHOW TABLES LIKE %s',
                $wpdb->esc_like($table_name)
            ));
            
            $status[$table] = array(
                'exists' => !empty($exists),
                'rows' => !empty($exists) ? $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}") : 0
            );
        }
        
        return $status;
    }

    /**
     * Initialize default data
     *
     * @return void
     */
    public static function init_default_data() {
        global $wpdb;
        
        // Default badges
        $default_badges = array(
            array(
                'name' => 'Early Bird',
                'description' => 'Complete your first morning workout',
                'tier' => 'bronze',
                'points_required' => 0,
                'auto_award' => 1
            ),
            array(
                'name' => 'Cold Warrior',
                'description' => 'Complete your first cold plunge',
                'tier' => 'silver',
                'points_required' => 20,
                'auto_award' => 1
            ),
            array(
                'name' => 'Dedication Master',
                'description' => 'Maintain a 30-day streak',
                'tier' => 'gold',
                'points_required' => 500,
                'auto_award' => 1
            )
        );
        
        $badges_table = self::get_table_name('badges');
        
        foreach ($default_badges as $badge) {
            $wpdb->insert($badges_table, $badge);
        }
    }
}