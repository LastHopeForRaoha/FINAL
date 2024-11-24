<?php
/**
 * Class List Template
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

$classes = mkwa_get_upcoming_classes();
?>

<div class="mkwa-classes-container">
    <div class="mkwa-classes-header">
        <h3><?php esc_html_e('Upcoming Classes', 'mkwa-fitness'); ?></h3>
        <?php if (current_user_can('edit_posts')) : ?>
            <button class="mkwa-add-class-btn">
                <?php esc_html_e('Add New Class', 'mkwa-fitness'); ?>
            </button>
        <?php endif; ?>
    </div>

    <?php if (!empty($classes)) : ?>
        <div class="mkwa-classes-grid">
            <?php foreach ($classes as $class) : ?>
                <div class="mkwa-class-card" data-class-id="<?php echo esc_attr($class->id); ?>">
                    <div class="mkwa-class-header">
                        <h4><?php echo esc_html($class->title); ?></h4>
                        <?php if (current_user_can('edit_posts')) : ?>
                            <div class="mkwa-class-actions">
                                <button class="mkwa-edit-class-btn" data-class-id="<?php echo esc_attr($class->id); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button class="mkwa-delete-class-btn" data-class-id="<?php echo esc_attr($class->id); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mkwa-class-details">
                        <div class="mkwa-class-info">
                            <span class="mkwa-class-date">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php echo esc_html(date_i18n('F j, Y', strtotime($class->class_date))); ?>
                            </span>
                            <span class="mkwa-class-time">
                                <span class="dashicons dashicons-clock"></span>
                                <?php echo esc_html(date_i18n('g:i a', strtotime($class->start_time))); ?> - 
                                <?php echo esc_html(date_i18n('g:i a', strtotime($class->end_time))); ?>
                            </span>
                            <span class="mkwa-class-instructor">
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php echo esc_html($class->instructor_name); ?>
                            </span>
                            <span class="mkwa-class-spots">
                                <span class="dashicons dashicons-groups"></span>
                                <?php 
                                $spots_left = $class->capacity - $class->enrolled;
                                echo esc_html(sprintf(
                                    __('%d spots left', 'mkwa-fitness'),
                                    $spots_left
                                )); 
                                ?>
                            </span>
                        </div>

                        <div class="mkwa-class-description">
                            <?php echo wp_kses_post($class->description); ?>
                        </div>

                        <div class="mkwa-class-footer">
                            <?php if ($class->enrolled >= $class->capacity) : ?>
                                <button class="mkwa-class-btn mkwa-class-full" disabled>
                                    <?php esc_html_e('Class Full', 'mkwa-fitness'); ?>
                                </button>
                            <?php elseif (mkwa_user_enrolled_in_class($class->id)) : ?>
                                <button class="mkwa-class-btn mkwa-unenroll-btn" data-class-id="<?php echo esc_attr($class->id); ?>">
                                    <?php esc_html_e('Cancel Enrollment', 'mkwa-fitness'); ?>
                                </button>
                            <?php else : ?>
                                <button class="mkwa-class-btn mkwa-enroll-btn" data-class-id="<?php echo esc_attr($class->id); ?>">
                                    <?php esc_html_e('Enroll Now', 'mkwa-fitness'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <div class="mkwa-no-classes">
            <?php esc_html_e('No upcoming classes scheduled.', 'mkwa-fitness'); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Class Form Modal -->
<div class="mkwa-modal" id="mkwaClassModal" style="display: none;">
    <div class="mkwa-modal-content">
        <span class="mkwa-modal-close">&times;</span>
        <h3 class="mkwa-modal-title"><?php esc_html_e('Add New Class', 'mkwa-fitness'); ?></h3>
        
        <form id="mkwaClassForm">
            <div class="mkwa-form-group">
                <label for="classTitle"><?php esc_html_e('Class Title', 'mkwa-fitness'); ?></label>
                <input type="text" id="classTitle" name="title" required>
            </div>

            <div class="mkwa-form-group">
                <label for="classDescription"><?php esc_html_e('Description', 'mkwa-fitness'); ?></label>
                <textarea id="classDescription" name="description" required></textarea>
            </div>

            <div class="mkwa-form-row">
                <div class="mkwa-form-group">
                    <label for="classDate"><?php esc_html_e('Date', 'mkwa-fitness'); ?></label>
                    <input type="date" id="classDate" name="class_date" required>
                </div>

                <div class="mkwa-form-group">
                    <label for="startTime"><?php esc_html_e('Start Time', 'mkwa-fitness'); ?></label>
                    <input type="time" id="startTime" name="start_time" required>
                </div>

                <div class="mkwa-form-group">
                    <label for="endTime"><?php esc_html_e('End Time', 'mkwa-fitness'); ?></label>
                    <input type="time" id="endTime" name="end_time" required>
                </div>
            </div>

            <div class="mkwa-form-row">
                <div class="mkwa-form-group">
                    <label for="instructorName"><?php esc_html_e('Instructor', 'mkwa-fitness'); ?></label>
                    <input type="text" id="instructorName" name="instructor_name" required>
                </div>

                <div class="mkwa-form-group">
                    <label for="classCapacity"><?php esc_html_e('Capacity', 'mkwa-fitness'); ?></label>
                    <input type="number" id="classCapacity" name="capacity" min="1" required>
                </div>
            </div>

            <input type="hidden" id="classId" name="class_id" value="">
            <?php wp_nonce_field('mkwa_class_nonce', 'mkwa_class_nonce'); ?>

            <div class="mkwa-form-actions">
                <button type="submit" class="mkwa-submit-btn">
                    <?php esc_html_e('Save Class', 'mkwa-fitness'); ?>
                </button>
                <button type="button" class="mkwa-cancel-btn">
                    <?php esc_html_e('Cancel', 'mkwa-fitness'); ?>
                </button>
            </div>
        </form>
    </div>
</div>