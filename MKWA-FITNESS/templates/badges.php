<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$badges = MKWA_Badges::get_instance()->get_user_badges($user_id);
?>

<div class="mkwa-badges-container">
    <h2><?php _e('Your Achievements', 'mkwa-fitness'); ?></h2>
    
    <?php if (empty($badges)) : ?>
        <p><?php _e('Start your fitness journey to earn badges!', 'mkwa-fitness'); ?></p>
    <?php else : ?>
        <div class="mkwa-badges-grid">
            <?php foreach ($badges as $badge) : ?>
                <div class="mkwa-badge-item <?php echo esc_attr($badge['category']); ?>">
                    <div class="mkwa-badge-icon">
                        <img src="<?php echo esc_url($badge['icon_url']); ?>" 
                             alt="<?php echo esc_attr($badge['title']); ?>">
                    </div>
                    <h3><?php echo esc_html($badge['title']); ?></h3>
                    <p><?php echo esc_html($badge['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>