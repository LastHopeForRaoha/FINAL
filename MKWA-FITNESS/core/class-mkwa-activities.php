<?php
/**
 * Activities management for Mkwa Fitness Plugin
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Activities {
    /**
     * Points system instance
     */
    private $points;

    /**
     * Cache duration in seconds
     */
    private $cache_duration;

    /**
     * Constructor
     */
    public function __construct() {
        $this->points = new MKWA_Points();
        $this->cache_duration = get_option('mkwa_cache_duration', 3600);
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'schedule_daily_checks'));
        add_action('mkwa_process_scheduled_activities', array($this, 'process_scheduled_activities'));
    }

    /**
     * Schedule daily checks
     */
    public function schedule_daily_checks() {
        if (!wp_next_scheduled('mkwa_daily_streak_check')) {
            wp_schedule_event(time(), 'daily', 'mkwa_daily_streak_check');
        }
        
        if (!wp_next_scheduled('mkwa_process_scheduled_activities')) {
            wp_schedule_event(time(), 'hourly', 'mkwa_process_scheduled_activities');
        }
    }

    /**
     * Log a new activity
     */
    public function log_activity($member_id, $activity_type, $meta = array()) {
        global $wpdb;
        
        // Validate activity type
        if (!$this->is_valid_activity_type($activity_type)) {
            return new WP_Error(
                'invalid_activity_type',
                'Invalid activity type provided.',
                array('status' => 400)
            );
        }

        // Check if member exists
        if (!$this->member_exists($member_id)) {
            return new WP_Error(
                'invalid_member',
                'Member does not exist.',
                array('status' => 404)
            );
        }

        $activity_data = array(
            'member_id' => $member_id,
            'activity_type' => $activity_type,
            'timestamp' => current_time('mysql'),
            'meta_data' => maybe_serialize($meta)
        );

        $result = $wpdb->insert(
            $wpdb->prefix . 'mkwa_activities',
            $activity_data,
            array('%d', '%s', '%s', '%s')
        );

        if (!$result) {
            return new WP_Error(
                'db_error',
                'Failed to log activity.',
                array('status' => 500)
            );
        }

        $activity_id = $wpdb->insert_id;
        
        // Add activity ID to data for points calculation
        $activity_data['activity_id'] = $activity_id;
        
        // Calculate points based on activity type
        $points = 0;
        switch ($activity_type) {
            case MKWA_ACTIVITY_CHECKIN:
                $points = MKWA_POINTS_CHECKIN_DEFAULT;
                break;
            case MKWA_ACTIVITY_CLASS:
                $points = MKWA_POINTS_CLASS_DEFAULT;
                break;
            case MKWA_ACTIVITY_COLD_PLUNGE:
                $points = MKWA_POINTS_COLD_PLUNGE_DEFAULT;
                break;
            case MKWA_ACTIVITY_PR:
                $points = MKWA_POINTS_PR_DEFAULT;
                break;
            case MKWA_ACTIVITY_COMPETITION:
                $points = MKWA_POINTS_COMPETITION_DEFAULT;
                break;
        }

        // Add points to member
        mkwa_add_points($member_id, $points, $activity_type);
        
        // Clear activity cache for member
        $this->clear_member_cache($member_id);

        // Trigger activity logged action
        do_action('mkwa_activity_logged', $member_id, array(
            'type' => $activity_type,
            'meta' => $meta,
            'activity_id' => $activity_id
        ));

        return array(
            'activity_id' => $activity_id,
            'message' => 'Activity logged successfully'
        );
    }

    /**
     * Get member activities
     */
    public function get_member_activities($member_id, $args = array()) {
        global $wpdb;

        $defaults = array(
            'limit' => 10,
            'offset' => 0,
            'type' => '',
            'start_date' => '',
            'end_date' => '',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);
        
        // Build query
        $query = "SELECT * FROM {$wpdb->prefix}mkwa_activities WHERE member_id = %d";
        $params = array($member_id);

        // Add filters
        if (!empty($args['type'])) {
            $query .= " AND activity_type = %s";
            $params[] = $args['type'];
        }

        if (!empty($args['start_date'])) {
            $query .= " AND timestamp >= %s";
            $params[] = $args['start_date'];
        }

        if (!empty($args['end_date'])) {
            $query .= " AND timestamp <= %s";
            $params[] = $args['end_date'];
        }

        // Add ordering
        $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
        $query .= " ORDER BY timestamp {$order}";

        // Add limit and offset
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $args['limit'];
        $params[] = $args['offset'];

        $activities = $wpdb->get_results(
            $wpdb->prepare($query, $params)
        );

        // Unserialize meta data
        foreach ($activities as &$activity) {
            $activity->meta_data = maybe_unserialize($activity->meta_data);
        }

        return $activities;
    }

    /**
     * Get member statistics
     */
    public function get_member_stats($member_id) {
        global $wpdb;
        
        $cache_key = 'member_stats_' . $member_id;
        $stats = wp_cache_get($cache_key);
        
        if (false === $stats) {
            $stats = array(
                'total_activities' => 0,
                'current_streak' => 0,
                'best_streak' => 0,
                'this_month_activities' => 0,
                'points_breakdown' => array(),
                'recent_achievements' => array()
            );

            // Get total activities
            $stats['total_activities'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_activities WHERE member_id = %d",
                $member_id
            ));

            // Get current streak
            $stats['current_streak'] = $this->points->get_current_streak($member_id);

            // Get this month's activities
            $stats['this_month_activities'] = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_activities 
                WHERE member_id = %d 
                AND MONTH(timestamp) = MONTH(CURRENT_DATE)
                AND YEAR(timestamp) = YEAR(CURRENT_DATE)",
                $member_id
            ));

            // Get points breakdown
            $stats['points_breakdown'] = $wpdb->get_results($wpdb->prepare(
                "SELECT activity_type, 
                        COUNT(*) as count, 
                        SUM(points_earned) as total_points 
                FROM {$wpdb->prefix}mkwa_activities 
                WHERE member_id = %d 
                GROUP BY activity_type",
                $member_id
            ));

            wp_cache_set($cache_key, $stats, '', $this->cache_duration);
        }

        return $stats;
    }

    /**
     * Process scheduled activities
     */
    public function process_scheduled_activities() {
        global $wpdb;

        // Get all scheduled activities that need processing
        $scheduled = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}mkwa_schedules 
            WHERE start_time <= NOW() 
            AND processed = 0"
        );

        foreach ($scheduled as $activity) {
            // Process each scheduled activity
            $this->process_scheduled_activity($activity);
        }
    }

    /**
     * Process a single scheduled activity
     */
    private function process_scheduled_activity($activity) {
        global $wpdb;

        // Mark as processed
        $wpdb->update(
            $wpdb->prefix . 'mkwa_schedules',
            array('processed' => 1),
            array('schedule_id' => $activity->schedule_id)
        );

        // Get all bookings for this activity
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}mkwa_bookings 
            WHERE schedule_id = %d",
            $activity->schedule_id
        ));

        // Log activity for each booking
        foreach ($bookings as $booking) {
            $this->log_activity(
                $booking->member_id,
                $activity->activity_type,
                array(
                    'schedule_id' => $activity->schedule_id,
                    'booking_id' => $booking->booking_id
                )
            );
        }
    }

    /**
     * Clear member cache
     */
    private function clear_member_cache($member_id) {
        wp_cache_delete('member_stats_' . $member_id);
        wp_cache_delete('member_activities_' . $member_id);
    }

    /**
     * Check if activity type is valid
     */
    private function is_valid_activity_type($type) {
        $valid_types = array(
            'checkin',
            'class',
            'cold_plunge',
            'personal_record',
            'competition'
        );
        
        return in_array($type, $valid_types);
    }

    /**
     * Check if member exists
     */
    private function member_exists($member_id) {
        global $wpdb;
        
        return (bool)$wpdb->get_var($wpdb->prepare(
            "SELECT 1 FROM {$wpdb->prefix}mkwa_members WHERE member_id = %d",
            $member_id
        ));
    }
}