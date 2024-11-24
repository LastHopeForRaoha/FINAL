<?php
/**
 * AJAX Handler Class
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Ajax {
    
    public function __construct() {
        // Activity log refresh
        add_action('wp_ajax_mkwa_refresh_activity', array($this, 'refresh_activity_log'));
        
        // Progress refresh
        add_action('wp_ajax_mkwa_refresh_progress', array($this, 'refresh_progress'));
        
        // Add activity
        add_action('wp_ajax_mkwa_log_activity', array($this, 'log_activity'));

        // Class registration
        add_action('wp_ajax_mkwa_register_class', array($this, 'register_class'));
        add_action('wp_ajax_mkwa_cancel_class', array($this, 'cancel_class'));
        add_action('wp_ajax_mkwa_refresh_classes', array($this, 'refresh_classes'));
    }

    /**
     * Refresh activity log
     */
    public function refresh_activity_log() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                throw new Exception(__('User not logged in.', 'mkwa-fitness'));
            }

            global $wpdb;
            $activities = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mkwa_activity_log 
                WHERE user_id = %d 
                ORDER BY logged_at DESC 
                LIMIT 10",
                $user_id
            ));

            ob_start();
            include MKWA_PLUGIN_DIR . 'templates/dashboard/activity-log.php';
            $html = ob_get_clean();

            wp_send_json_success(array(
                'html' => $html
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Refresh progress data
     */
    public function refresh_progress() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                throw new Exception(__('User not logged in.', 'mkwa-fitness'));
            }

            $member_id = mkwa_get_member_id($user_id);
            if (!$member_id) {
                throw new Exception(__('Member not found.', 'mkwa-fitness'));
            }

            $member_stats = mkwa_get_member_stats($member_id);

            ob_start();
            include MKWA_PLUGIN_DIR . 'templates/dashboard/progress-tracker.php';
            $html = ob_get_clean();

            wp_send_json_success(array(
                'html' => $html,
                'stats' => array(
                    'total_points' => $member_stats['total_points'],
                    'current_level' => mkwa_calculate_level($member_stats['total_points']),
                    'current_streak' => $member_stats['current_streak'],
                    'total_activities' => $member_stats['total_activities']
                )
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Log new activity
     */
    public function log_activity() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                throw new Exception(__('User not logged in.', 'mkwa-fitness'));
            }

            $activity_type = isset($_POST['activity_type']) ? sanitize_text_field($_POST['activity_type']) : '';
            if (!$activity_type) {
                throw new Exception(__('Activity type is required.', 'mkwa-fitness'));
            }

            $member_id = mkwa_get_member_id($user_id);
            if (!$member_id) {
                throw new Exception(__('Member not found.', 'mkwa-fitness'));
            }

            // Get points for activity type
            $points = $this->get_points_for_activity($activity_type);
            
            // Log the activity
            global $wpdb;
            $result = $wpdb->insert(
                $wpdb->prefix . 'mkwa_activity_log',
                array(
                    'user_id' => $user_id,
                    'activity_type' => $activity_type,
                    'points' => $points,
                    'logged_at' => current_time('mysql')
                ),
                array('%d', '%s', '%d', '%s')
            );

            if ($result === false) {
                throw new Exception(__('Failed to log activity.', 'mkwa-fitness'));
            }

            // Update member points
            mkwa_add_points($member_id, $points, $activity_type);

            // Get updated stats
            $member_stats = mkwa_get_member_stats($member_id);

            do_action('mkwa_activity_logged', $member_id);

            wp_send_json_success(array(
                'message' => __('Activity logged successfully!', 'mkwa-fitness'),
                'points_earned' => $points,
                'total_points' => $member_stats['total_points'],
                'current_level' => mkwa_calculate_level($member_stats['total_points'])
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Register for a class
     */
    public function register_class() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                throw new Exception(__('User not logged in.', 'mkwa-fitness'));
            }

            $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
            if (!$class_id) {
                throw new Exception(__('Invalid class ID.', 'mkwa-fitness'));
            }

            // Check if already registered
            if (mkwa_is_user_registered_for_class($user_id, $class_id)) {
                throw new Exception(__('You are already registered for this class.', 'mkwa-fitness'));
            }

            // Check if class is full
            $class = mkwa_get_class($class_id);
            if (!$class) {
                throw new Exception(__('Class not found.', 'mkwa-fitness'));
            }

            $registered_count = mkwa_get_class_registration_count($class_id);
            if ($registered_count >= $class->capacity) {
                throw new Exception(__('This class is full.', 'mkwa-fitness'));
            }

            // Register user
            $result = mkwa_register_user_for_class($user_id, $class_id);
            if (!$result) {
                throw new Exception(__('Failed to register for class.', 'mkwa-fitness'));
            }

            $spots_left = $class->capacity - ($registered_count + 1);

            wp_send_json_success(array(
                'message' => __('Successfully registered for class!', 'mkwa-fitness'),
                'spots_left' => sprintf(__('%d spots left', 'mkwa-fitness'), $spots_left)
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Cancel class registration
     */
    public function cancel_class() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $user_id = get_current_user_id();
            if (!$user_id) {
                throw new Exception(__('User not logged in.', 'mkwa-fitness'));
            }

            $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
            if (!$class_id) {
                throw new Exception(__('Invalid class ID.', 'mkwa-fitness'));
            }

            // Check if actually registered
            if (!mkwa_is_user_registered_for_class($user_id, $class_id)) {
                throw new Exception(__('You are not registered for this class.', 'mkwa-fitness'));
            }

            // Cancel registration
            $result = mkwa_cancel_class_registration($user_id, $class_id);
            if (!$result) {
                throw new Exception(__('Failed to cancel registration.', 'mkwa-fitness'));
            }

            $class = mkwa_get_class($class_id);
            $registered_count = mkwa_get_class_registration_count($class_id);
            $spots_left = $class->capacity - $registered_count;

            wp_send_json_success(array(
                'message' => __('Class registration cancelled.', 'mkwa-fitness'),
                'spots_left' => sprintf(__('%d spots left', 'mkwa-fitness'), $spots_left)
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Refresh class list
     */
    public function refresh_classes() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            ob_start();
            include MKWA_PLUGIN_DIR . 'templates/dashboard/class-list.php';
            $html = ob_get_clean();

            wp_send_json_success(array(
                'html' => $html
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Get points for activity type
     */
    private function get_points_for_activity($activity_type) {
        switch ($activity_type) {
            case MKWA_ACTIVITY_CHECKIN:
                return get_option('mkwa_points_checkin', MKWA_POINTS_CHECKIN_DEFAULT);
            case MKWA_ACTIVITY_CLASS:
                return get_option('mkwa_points_class', MKWA_POINTS_CLASS_DEFAULT);
            case MKWA_ACTIVITY_COLD_PLUNGE:
                return get_option('mkwa_points_cold_plunge', MKWA_POINTS_COLD_PLUNGE_DEFAULT);
            case MKWA_ACTIVITY_PR:
                return get_option('mkwa_points_pr', MKWA_POINTS_PR_DEFAULT);
            case MKWA_ACTIVITY_COMPETITION:
                return get_option('mkwa_points_competition', MKWA_POINTS_COMPETITION_DEFAULT);
            default:
                return 0;
        }
    }
}