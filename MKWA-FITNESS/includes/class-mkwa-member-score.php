<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

class MKWA_Member_Score {
    private $weights = [
        'attendance' => 0.30,
        'challenge_completion' => 0.20,
        'community_participation' => 0.15,
        'streak_maintenance' => 0.20,
        'point_earning_rate' => 0.15
    ];

    private function calculate_attendance_score($user_id) {
        global $wpdb;
        $thirty_days_ago = date('Y-m-d', strtotime('-30 days'));
        $total_possible_days = 30;
        
        $actual_attendance = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(DISTINCT DATE(logged_at)) 
            FROM {$wpdb->prefix}mkwa_activity_log 
            WHERE user_id = %d 
            AND activity_type = %s
            AND logged_at >= %s",
            $user_id,
            'check_in',
            $thirty_days_ago
        ));

        return ($actual_attendance / $total_possible_days) * 100;
    }

    private function calculate_challenge_score($user_id) {
        global $wpdb;
        $completed_challenges = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
            FROM {$wpdb->prefix}mkwa_activity_log 
            WHERE user_id = %d 
            AND activity_type = %s
            AND logged_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)",
            $user_id,
            'challenge_complete'
        ));

        // Assuming an average of 1 challenge per week as baseline
        $expected_challenges = 12; // 90 days / 7 days per week
        return min(($completed_challenges / $expected_challenges) * 100, 100);
    }

    private function calculate_streak_score($user_id) {
        global $wpdb;
        
        // Get current streak
        $current_streak = $wpdb->get_var($wpdb->prepare(
            "SELECT streak_count 
            FROM {$wpdb->prefix}mkwa_members 
            WHERE user_id = %d",
            $user_id
        ));

        // Calculate score based on streak length
        $base_score = min($current_streak * 5, 100); // 5 points per day, max 100
        
        return $base_score;
    }

    private function calculate_point_velocity($user_id) {
        global $wpdb;
        
        // Get points earned in last 30 days
        $points = $wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(points), 0) 
            FROM {$wpdb->prefix}mkwa_activity_log 
            WHERE user_id = %d 
            AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $user_id
        ));

        // Calculate daily average and normalize to 0-100 scale
        // Assuming 100 points per day is excellent performance
        $daily_average = $points / 30;
        return min(($daily_average / 100) * 100, 100);
    }

    private function calculate_community_score($user_id) {
        global $wpdb;
        
        // Get community engagement metrics
        $social_interactions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) 
            FROM {$wpdb->prefix}mkwa_activity_log 
            WHERE user_id = %d 
            AND activity_type IN ('social_share', 'community_event', 'group_workout')
            AND logged_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $user_id
        ));

        // Normalize to 0-100 scale (assuming 30 interactions per month is excellent)
        return min(($social_interactions / 30) * 100, 100);
    }

    public function calculate_overall_score($user_id) {
        // Gather all metrics
        $metrics = [
            'attendance' => $this->calculate_attendance_score($user_id),
            'challenge_completion' => $this->calculate_challenge_score($user_id),
            'community_participation' => $this->calculate_community_score($user_id),
            'streak_maintenance' => $this->calculate_streak_score($user_id),
            'point_earning_rate' => $this->calculate_point_velocity($user_id)
        ];

        // Calculate weighted score
        $overall_score = 0;
        foreach ($this->weights as $metric => $weight) {
            $overall_score += ($metrics[$metric] * $weight);
        }

        // Update the database
        $this->update_member_metrics($user_id, $metrics, $overall_score);

        return $overall_score;
    }

    private function update_member_metrics($user_id, $metrics, $overall_score) {
        global $wpdb;
        
        $wpdb->replace(
            "{$wpdb->prefix}mkwa_member_metrics",
            [
                'user_id' => $user_id,
                'attendance_rate' => $metrics['attendance'],
                'challenge_completion_rate' => $metrics['challenge_completion'],
                'community_participation_score' => $metrics['community_participation'],
                'streak_score' => $metrics['streak_maintenance'],
                'point_earning_velocity' => $metrics['point_earning_rate'],
                'overall_score' => $overall_score,
                'last_calculated' => current_time('mysql')
            ],
            ['%d', '%f', '%f', '%f', '%f', '%f', '%f', '%s']
        );

        // Update leaderboard
        do_action('mkwa_member_score_updated', $user_id, $overall_score);
    }

    // Hook into point awards to update scores
    public function init() {
        add_action('mkwa_points_awarded', [$this, 'handle_points_awarded'], 10, 3);
    }

    public function handle_points_awarded($user_id, $points, $activity_type) {
        $this->calculate_overall_score($user_id);
    }
}