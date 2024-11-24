<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap mkwa-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="mkwa-badges-grid">
        <?php if ($badges): ?>
            <?php foreach ($badges as $badge): ?>
                <div class="mkwa-badge-box">
                    <img src="<?php echo esc_url($badge->icon_url); ?>" alt="<?php echo esc_attr($badge->title); ?>">
                    <h3><?php echo esc_html($badge->title); ?></h3>
                    <p><?php echo esc_html($badge->description); ?></p>
                    <p class="points-required"><?php printf(__('Points Required: %d', 'mkwa-fitness'), $badge->points_required); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p><?php _e('No badges found.', 'mkwa-fitness'); ?></p>
        <?php endif; ?>
    </div>
</div>