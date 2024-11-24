<?php
/**
 * Points functions for MKWA Fitness Plugin
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add points to a member
 */
function mkwa_add_points($member_id, $points, $activity_type) {
    try {
        global $wpdb;
        
        // Update points in member table
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mkwa_members SET total_points = total_points + %d WHERE member_id = %d",
            $points, $member_id
        ));
        
        // Log the activity and points
        $wpdb->insert(
            $wpdb->prefix . 'mkwa_activity_log',
            array(
                'user_id' => mkwa_get_user_id($member_id),
                'activity_type' => $activity_type,
                'points' => $points,
                'logged_at' => current_time('mysql')
            ),
            array('%d', '%s', '%d', '%s')
        );
        
        // Clear member cache
        mkwa_clear_member_cache($member_id);
        
        return true;
    } catch (Exception $e) {
        mkwa_log('Error adding points: ' . $e->getMessage());
        return false;
    }
}

/**
 * Subtract points from a member
 */
function mkwa_subtract_points($member_id, $points) {
    try {
        global $wpdb;
        
        // Update points in member table
        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}mkwa_members SET total_points = total_points - %d WHERE member_id = %d",
            $points, $member_id
        ));
        
        // Clear member cache
        mkwa_clear_member_cache($member_id);
        
        return true;
    } catch (Exception $e) {
        mkwa_log('Error subtracting points: ' . $e->getMessage());
        return false;
    }
}

/**
 * Check member points
 */
function mkwa_get_member_points($member_id) {
    try {
        global $wpdb;
        
        $points = $wpdb->get_var($wpdb->prepare(
            "SELECT total_points FROM {$wpdb->prefix}mkwa_members WHERE member_id = %d",
            $member_id
        ));
        
        return $points;
    } catch (Exception $e) {
        mkwa_log('Error getting member points: ' . $e->getMessage());
        return 0;
    }
}