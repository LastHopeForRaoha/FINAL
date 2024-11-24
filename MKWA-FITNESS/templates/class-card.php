<?php
/**
 * Template for individual class card
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="mkwa-class-card <?php echo $is_registered ? 'registered' : ''; ?>" 
     data-class-id="<?php echo esc_attr($class_id); ?>">
    
    <h3 class="mkwa-class-title"><?php the_title(); ?></h3>
    
    <div class="mkwa-class-meta">
        <p class="mkwa-class-instructor">
            <span class="label"><?php esc_html_e('Instructor:', 'mkwa-fitness'); ?></span>
            <?php echo esc_html(get_post_meta($class_id, '_mkwa_instructor', true)); ?>
        </p>
        
        <p class="mkwa-class-datetime">
            <?php
            $date = get_post_meta($class_id, '_mkwa_class_date', true);
            $time = get_post_meta($class_id, '_mkwa_class_time', true);
            echo esc_html(date_i18n(get_option('date_format'), strtotime($date)) . ' ' . 
                         date_i18n(get_option('time_format'), strtotime($time)));
            ?>
        </p>
        
        <p class="mkwa-class-duration">
            <span class="label"><?php esc_html_e('Duration:', 'mkwa-fitness'); ?></span>
            <?php 
            $duration = get_post_meta($class_id, '_mkwa_duration', true);
            printf(esc_html__('%d minutes', 'mkwa-fitness'), $duration);
            ?>
        </p>
        
        <p class="mkwa-class-spots">
            <span class="label"><?php esc_html_e('Available Spots:', 'mkwa-fitness'); ?></span>
            <?php printf(esc_html__('%d/%d', 'mkwa-fitness'), count($attendees), $capacity); ?>
        </p>
    </div>

    <div class="mkwa-class-actions">
        <?php if ($is_registered): ?>
            <button class="mkwa-btn mkwa-btn-cancel" data-class-id="<?php echo esc_attr($class_id); ?>">
                <?php esc_html_e('Cancel Registration', 'mkwa-fitness'); ?>
            </button>
        <?php elseif ($is_full): ?>
            <button class="mkwa-btn mkwa-btn-full" disabled>
                <?php esc_html_e('Class Full', 'mkwa-fitness'); ?>
            </button>
        <?php else: ?>
            <button class="mkwa-btn mkwa-btn-register" data-class-id="<?php echo esc_attr($class_id); ?>">
                <?php esc_html_e('Register Now', 'mkwa-fitness'); ?>
            </button>
        <?php endif; ?>
    </div>
</div>