<?php
/**
 * Workout Classes Management
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Classes {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_class_meta_boxes'));
        add_action('save_post_mkwa_class', array($this, 'save_class_meta'));
        add_action('wp_ajax_mkwa_register_for_class', array($this, 'register_for_class'));
        add_action('wp_ajax_mkwa_unregister_from_class', array($this, 'unregister_from_class'));
    }

    /**
     * Register workout class post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Workout Classes', 'mkwa-fitness'),
            'singular_name'      => __('Workout Class', 'mkwa-fitness'),
            'menu_name'          => __('Workout Classes', 'mkwa-fitness'),
            'add_new'           => __('Add New Class', 'mkwa-fitness'),
            'add_new_item'      => __('Add New Workout Class', 'mkwa-fitness'),
            'edit_item'         => __('Edit Workout Class', 'mkwa-fitness'),
            'new_item'          => __('New Workout Class', 'mkwa-fitness'),
            'view_item'         => __('View Workout Class', 'mkwa-fitness'),
            'search_items'      => __('Search Workout Classes', 'mkwa-fitness'),
            'not_found'         => __('No workout classes found', 'mkwa-fitness'),
            'not_found_in_trash'=> __('No workout classes found in trash', 'mkwa-fitness')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'workout-class'),
            'capability_type'   => 'post',
            'has_archive'       => true,
            'hierarchical'      => false,
            'menu_position'     => 5,
            'menu_icon'         => 'dashicons-calendar-alt',
            'supports'          => array('title', 'editor', 'thumbnail')
        );

        register_post_type('mkwa_class', $args);
    }

    /**
     * Add meta boxes for class details
     */
    public function add_class_meta_boxes() {
        add_meta_box(
            'mkwa_class_details',
            __('Class Details', 'mkwa-fitness'),
            array($this, 'render_class_details_meta_box'),
            'mkwa_class',
            'normal',
            'high'
        );

        add_meta_box(
            'mkwa_class_attendees',
            __('Registered Attendees', 'mkwa-fitness'),
            array($this, 'render_class_attendees_meta_box'),
            'mkwa_class',
            'side',
            'default'
        );
    }

    /**
     * Render class details meta box
     */
    public function render_class_details_meta_box($post) {
        wp_nonce_field('mkwa_class_details', 'mkwa_class_details_nonce');

        $instructor = get_post_meta($post->ID, '_mkwa_instructor', true);
        $date = get_post_meta($post->ID, '_mkwa_class_date', true);
        $time = get_post_meta($post->ID, '_mkwa_class_time', true);
        $duration = get_post_meta($post->ID, '_mkwa_duration', true);
        $capacity = get_post_meta($post->ID, '_mkwa_capacity', true);
        $points = get_post_meta($post->ID, '_mkwa_points', true);
        ?>
        <div class="mkwa-meta-row">
            <label for="mkwa_instructor"><?php _e('Instructor:', 'mkwa-fitness'); ?></label>
            <input type="text" id="mkwa_instructor" name="mkwa_instructor" value="<?php echo esc_attr($instructor); ?>">
        </div>

        <div class="mkwa-meta-row">
            <label for="mkwa_class_date"><?php _e('Date:', 'mkwa-fitness'); ?></label>
            <input type="date" id="mkwa_class_date" name="mkwa_class_date" value="<?php echo esc_attr($date); ?>">
        </div>

        <div class="mkwa-meta-row">
            <label for="mkwa_class_time"><?php _e('Time:', 'mkwa-fitness'); ?></label>
            <input type="time" id="mkwa_class_time" name="mkwa_class_time" value="<?php echo esc_attr($time); ?>">
        </div>

        <div class="mkwa-meta-row">
            <label for="mkwa_duration"><?php _e('Duration (minutes):', 'mkwa-fitness'); ?></label>
            <input type="number" id="mkwa_duration" name="mkwa_duration" value="<?php echo esc_attr($duration); ?>" min="0">
        </div>

        <div class="mkwa-meta-row">
            <label for="mkwa_capacity"><?php _e('Capacity:', 'mkwa-fitness'); ?></label>
            <input type="number" id="mkwa_capacity" name="mkwa_capacity" value="<?php echo esc_attr($capacity); ?>" min="1">
        </div>

        <div class="mkwa-meta-row">
            <label for="mkwa_points"><?php _e('Points:', 'mkwa-fitness'); ?></label>
            <input type="number" id="mkwa_points" name="mkwa_points" value="<?php echo esc_attr($points); ?>" min="0">
        </div>
        <?php
    }

    /**
     * Render class attendees meta box
     */
    public function render_class_attendees_meta_box($post) {
        $attendees = $this->get_class_attendees($post->ID);
        if (empty($attendees)) {
            echo '<p>' . __('No attendees registered yet.', 'mkwa-fitness') . '</p>';
            return;
        }

        echo '<ul class="mkwa-attendees-list">';
        foreach ($attendees as $attendee) {
            $user = get_userdata($attendee);
            if ($user) {
                echo '<li>' . esc_html($user->display_name) . '</li>';
            }
        }
        echo '</ul>';
    }

    /**
     * Save class meta data
     */
    public function save_class_meta($post_id) {
        if (!isset($_POST['mkwa_class_details_nonce']) || 
            !wp_verify_nonce($_POST['mkwa_class_details_nonce'], 'mkwa_class_details')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'mkwa_instructor',
            'mkwa_class_date',
            'mkwa_class_time',
            'mkwa_duration',
            'mkwa_capacity',
            'mkwa_points'
        );

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta(
                    $post_id,
                    '_' . $field,
                    sanitize_text_field($_POST[$field])
                );
            }
        }
    }

    /**
     * Get class attendees
     */
    private function get_class_attendees($class_id) {
        return get_post_meta($class_id, '_mkwa_attendees', true) ?: array();
    }

    /**
     * Register user for class
     */
    public function register_for_class() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
            $user_id = get_current_user_id();

            if (!$class_id || !$user_id) {
                throw new Exception(__('Invalid request.', 'mkwa-fitness'));
            }

            $attendees = $this->get_class_attendees($class_id);
            $capacity = get_post_meta($class_id, '_mkwa_capacity', true);

            if (count($attendees) >= $capacity) {
                throw new Exception(__('Class is full.', 'mkwa-fitness'));
            }

            if (in_array($user_id, $attendees)) {
                throw new Exception(__('You are already registered for this class.', 'mkwa-fitness'));
            }

            $attendees[] = $user_id;
            update_post_meta($class_id, '_mkwa_attendees', $attendees);

            wp_send_json_success(array(
                'message' => __('Successfully registered for class!', 'mkwa-fitness')
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Unregister user from class
     */
    public function unregister_from_class() {
        try {
            if (!check_ajax_referer('mkwa-frontend-nonce', 'nonce', false)) {
                throw new Exception(__('Invalid security token.', 'mkwa-fitness'));
            }

            $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
            $user_id = get_current_user_id();

            if (!$class_id || !$user_id) {
                throw new Exception(__('Invalid request.', 'mkwa-fitness'));
            }

            $attendees = $this->get_class_attendees($class_id);
            $key = array_search($user_id, $attendees);

            if ($key === false) {
                throw new Exception(__('You are not registered for this class.', 'mkwa-fitness'));
            }

            unset($attendees[$key]);
            update_post_meta($class_id, '_mkwa_attendees', array_values($attendees));

            wp_send_json_success(array(
                'message' => __('Successfully unregistered from class!', 'mkwa-fitness')
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
}