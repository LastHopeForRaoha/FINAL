<?php
/**
 * Cultural Activities Handler
 *
 * @package MkwaFitness
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Cultural_Activities {
    /**
     * Calendar instance
     *
     * @var MKWA_Cultural_Calendar
     */
    private $calendar;

    /**
     * Constructor
     */
    public function __construct() {
        $this->calendar = new MKWA_Cultural_Calendar();
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', [$this, 'register_cultural_activity_post_type']);
        add_action('mkwa_activity_logged', [$this, 'process_cultural_activity'], 10, 2);
        add_filter('mkwa_points_multiplier', [$this, 'apply_cultural_multiplier'], 10, 3);
    }

    /**
     * Register cultural activity post type
     */
    public function register_cultural_activity_post_type() {
        $labels = [
            'name' => _x('Cultural Activities', 'Post Type General Name', 'mkwa-fitness'),
            'singular_name' => _x('Cultural Activity', 'Post Type Singular Name', 'mkwa-fitness'),
            'menu_name' => __('Cultural Activities', 'mkwa-fitness'),
            'all_items' => __('All Activities', 'mkwa-fitness'),
            'add_new' => __('Add New', 'mkwa-fitness'),
            'add_new_item' => __('Add New Cultural Activity', 'mkwa-fitness'),
            'edit_item' => __('Edit Cultural Activity', 'mkwa-fitness'),
            'new_item' => __('New Cultural Activity', 'mkwa-fitness'),
            'view_item' => __('View Cultural Activity', 'mkwa-fitness'),
            'search_items' => __('Search Cultural Activities', 'mkwa-fitness'),
            'not_found' => __('No cultural activities found', 'mkwa-fitness'),
            'not_found_in_trash' => __('No cultural activities found in Trash', 'mkwa-fitness')
        ];

        $args = [
            'label' => __('Cultural Activity', 'mkwa-fitness'),
            'description' => __('Cultural activities and traditions', 'mkwa-fitness'),
            'labels' => $labels