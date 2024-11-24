<?php
/**
 * Activities Class
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Activities {
    public function get_member_stats($member_id) {
        global $wpdb;
        
        $stats = array(
            'total_activities' => 0,
            'total_points' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_activity' => null,
            'activities_by_type' => array()
        );
        
        if (!$member_id) {
            mkwa_log('Invalid member_id provided to get_member_stats');
            return $stats;
        }
        
        try {
            // First verify the member exists
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->prefix}mkwa_members WHERE member_id = %d",
                $member_id
            ));
            
            if (!$user_id) {
                mkwa_log("Member ID {$member_id} not found in members table");
                return $stats;
            }
            
            $results = $wpdb->get_row($wpdb->prepare(
                "SELECT COUNT(*) as total_activities, 
                        COALESCE(SUM(points), 0) as total_points
                 FROM {$wpdb->prefix}mkwa_activity_log
                 WHERE user_id = %d",
                $user_id
            ));
            
            if ($results) {
                $stats['total_activities'] = (int)$results->total_activities;
                $stats['total_points'] = (int)$results->total_points;
                
                // Get last activity
                $last_activity = $wpdb->get_row($wpdb->prepare(
                    "SELECT activity_type, logged_at 
                     FROM {$wpdb->prefix}mkwa_activity_log 
                     WHERE user_id = %d 
                     ORDER BY logged_at DESC 
                     LIMIT 1",
                    $user_id
                ));
                
                if ($last_activity) {
                    $stats['last_activity'] = array(
                        'type' => $last_activity->activity_type,
                        'date' => $last_activity->logged_at
                    );
                }
                
                // Get activities by type
                $activities_by_type = $wpdb->get_results($wpdb->prepare(
                    "SELECT activity_type, COUNT(*) as count, SUM(points) as points
                     FROM {$wpdb->prefix}mkwa_activity_log 
                     WHERE user_id = %d 
                     GROUP BY activity_type",
                    $user_id
                ));
                
                if ($activities_by_type) {
                    foreach ($activities_by_type as $activity) {
                        $stats['activities_by_type'][$activity->activity_type] = array(
                            'count' => (int)$activity->count,
                            'points' => (int)$activity->points
                        );
                    }
                }
            }
            
        } catch (Exception $e) {
            mkwa_log('Error in get_member_stats: ' . $e->getMessage());
            if (defined('WP_DEBUG') && WP_DEBUG) {
                mkwa_log($e->getTraceAsString());
            }
        }
        
        return $stats;
    }
}