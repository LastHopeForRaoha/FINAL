<?php
// core/class-mkwa-api.php

class MKWA_API {
    private $namespace = 'mkwa/v1';

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        // Member endpoints
        register_rest_route($this->namespace, '/members/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_member'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Activity endpoints
        register_rest_route($this->namespace, '/activities', array(
            'methods' => 'POST',
            'callback' => array($this, 'log_activity'),
            'permission_callback' => array($this, 'check_permission'),
        ));

        // Badge endpoints
        register_rest_route($this->namespace, '/badges', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_badges'),
            'permission_callback' => '__return_true',
        ));

        // Schedule endpoints
        register_rest_route($this->namespace, '/schedules', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_schedules'),
            'permission_callback' => '__return_true',
        ));
    }

    public function check_permission() {
        return current_user_can('read');
    }

    public function get_member($request) {
        $member_id = $request['id'];
        global $wpdb;
        
        $member = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}mkwa_members WHERE member_id = %d",
                $member_id
            )
        );

        if (!$member) {
            return new WP_Error(
                'not_found',
                'Member not found',
                array('status' => 404)
            );
        }

        return new WP_REST_Response($member, 200);
    }

    public function log_activity($request) {
        $params = $request->get_params();
        
        if (!isset($params['member_id']) || !isset($params['activity_type'])) {
            return new WP_Error(
                'missing_params',
                'Required parameters are missing',
                array('status' => 400)
            );
        }

        global $wpdb;
        $result = $wpdb->insert(
            $wpdb->prefix . 'mkwa_activities',
            array(
                'member_id' => $params['member_id'],
                'activity_type' => $params['activity_type'],
                'points_earned' => $this->calculate_points($params['activity_type'])
            ),
            array('%d', '%s', '%d')
        );

        if (!$result) {
            return new WP_Error(
                'db_error',
                'Could not log activity',
                array('status' => 500)
            );
        }

        return new WP_REST_Response(
            array('message' => 'Activity logged successfully'),
            201
        );
    }

    private function calculate_points($activity_type) {
        $points_map = array(
            'checkin' => get_option('mkwa_points_checkin', 3),
            'class' => get_option('mkwa_points_class', 15),
            'cold_plunge' => get_option('mkwa_points_cold_plunge', 20)
        );

        return isset($points_map[$activity_type]) ? $points_map[$activity_type] : 0;
    }

    public function get_badges() {
        global $wpdb;
        $badges = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}mkwa_badges"
        );

        return new WP_REST_Response($badges, 200);
    }

    public function get_schedules() {
        global $wpdb;
        $schedules = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}mkwa_schedules 
             WHERE start_time >= NOW() 
             ORDER BY start_time ASC 
             LIMIT 50"
        );

        return new WP_REST_Response($schedules, 200);
    }
}