<?php
/**
 * Helper functions for MKWA Fitness Plugin
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get member ID from user ID
 */
function mkwa_get_member_id($user_id) {
    try {
        global $wpdb;
        $member_id = $wpdb->get_var($wpdb->prepare(
            "SELECT member_id FROM {$wpdb->prefix}mkwa_members WHERE user_id = %d",
            $user_id
        ));
        if ($wpdb->last_error) {
            mkwa_log('Database error in mkwa_get_member_id: ' . $wpdb->last_error);
            return false;
        }
        return $member_id;
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_get_member_id: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user ID from member ID
 */
function mkwa_get_user_id($member_id) {
    try {
        global $wpdb;
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->prefix}mkwa_members WHERE member_id = %d",
            $member_id
        ));
        if ($wpdb->last_error) {
            mkwa_log('Database error in mkwa_get_user_id: ' . $wpdb->last_error);
            return false;
        }
        return $user_id;
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_get_user_id: ' . $e->getMessage());
        return false;
    }
}

/**
 * Calculate level from points
 */
function mkwa_calculate_level($points) {
    try {
        if (!defined('MKWA_MAX_LEVEL') || !defined('MKWA_LEVEL_BASE_POINTS')) {
            mkwa_log('Required constants not defined in mkwa_calculate_level');
            return 1;
        }
        return min(
            MKWA_MAX_LEVEL,
            floor(sqrt($points / MKWA_LEVEL_BASE_POINTS)) + 1
        );
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_calculate_level: ' . $e->getMessage());
        return 1;
    }
}

/**
 * Get points needed for next level
 */
function mkwa_points_for_next_level($current_level) {
    try {
        if (!defined('MKWA_LEVEL_BASE_POINTS')) {
            mkwa_log('MKWA_LEVEL_BASE_POINTS constant not defined');
            return 100;
        }
        return pow($current_level, 2) * MKWA_LEVEL_BASE_POINTS;
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_points_for_next_level: ' . $e->getMessage());
        return 100;
    }
}

/**
 * Format points number
 */
function mkwa_format_points($points) {
    try {
        if ($points >= 1000000) {
            return round($points / 1000000, 1) . 'M';
        } elseif ($points >= 1000) {
            return round($points / 1000, 1) . 'K';
        }
        return number_format($points);
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_format_points: ' . $e->getMessage());
        return '0';
    }
}

/**
 * Get activity type label
 */
function mkwa_get_activity_label($type) {
    try {
        if (!defined('MKWA_ACTIVITY_CHECKIN')) {
            mkwa_log('MKWA activity constants not defined');
            return $type;
        }

        $labels = array(
            MKWA_ACTIVITY_CHECKIN => __('Check-in', 'mkwa-fitness'),
            MKWA_ACTIVITY_CLASS => __('Class', 'mkwa-fitness'),
            MKWA_ACTIVITY_COLD_PLUNGE => __('Cold Plunge', 'mkwa-fitness'),
            MKWA_ACTIVITY_PR => __('Personal Record', 'mkwa-fitness'),
            MKWA_ACTIVITY_COMPETITION => __('Competition', 'mkwa-fitness')
        );
        
        return isset($labels[$type]) ? $labels[$type] : $type;
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_get_activity_label: ' . $e->getMessage());
        return $type;
    }
}

/**
 * Check if user is member
 */
function mkwa_is_member($user_id = null) {
    try {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        return (bool)mkwa_get_member_id($user_id);
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_is_member: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create member if not exists
 */
function mkwa_ensure_member($user_id) {
    try {
        if (!mkwa_is_member($user_id)) {
            global $wpdb;
            $result = $wpdb->insert(
                $wpdb->prefix . 'mkwa_members',
                array(
                    'user_id' => $user_id,
                    'current_level' => 1,
                    'total_points' => 0,
                    'created_at' => current_time('mysql')
                ),
                array('%d', '%d', '%d', '%s')
            );
            
            if ($result === false) {
                mkwa_log('Error inserting new member: ' . $wpdb->last_error);
                return false;
            }
            
            return $wpdb->insert_id;
        }
        return mkwa_get_member_id($user_id);
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_ensure_member: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get member stats
 */
function mkwa_get_member_stats($member_id) {
    try {
        if (!class_exists('MKWA_Activities')) {
            mkwa_log('MKWA_Activities class not found');
            return array(
                'total_activities' => 0,
                'total_points' => 0,
                'current_streak' => 0,
                'longest_streak' => 0,
                'last_activity' => null,
                'activities_by_type' => array()
            );
        }
        
        $activities = new MKWA_Activities();
        return $activities->get_member_stats($member_id);
    } catch (Exception $e) {
        mkwa_log('Error getting member stats: ' . $e->getMessage());
        return array(
            'total_activities' => 0,
            'total_points' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_activity' => null,
            'activities_by_type' => array()
        );
    }
}

/**
 * Format duration
 */
function mkwa_format_duration($seconds) {
    try {
        if ($seconds < 60) {
            return sprintf(_n('%d second', '%d seconds', $seconds, 'mkwa-fitness'), $seconds);
        }
        
        $minutes = floor($seconds / 60);
        if ($minutes < 60) {
            return sprintf(_n('%d minute', '%d minutes', $minutes, 'mkwa-fitness'), $minutes);
        }
        
        $hours = floor($minutes / 60);
        $remaining_minutes = $minutes % 60;
        
        if ($remaining_minutes == 0) {
            return sprintf(_n('%d hour', '%d hours', $hours, 'mkwa-fitness'), $hours);
        }
        
        return sprintf(
            __('%d hours %d minutes', 'mkwa-fitness'),
            $hours,
            $remaining_minutes
        );
    } catch (Exception $e) {
        mkwa_log('Error in mkwa_format_duration: ' . $e->getMessage());
        return sprintf(__('%d seconds', 'mkwa-fitness'), $seconds);
    }
}