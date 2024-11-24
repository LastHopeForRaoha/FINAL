<?php
/**
 * Badges Template
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$earned_badges = $wpdb->get_results($wpdb->prepare(
    "SELECT b.* 
    FROM {$wpdb->prefix}mkwa_badges b
    JOIN {$wpdb->prefix}mkwa_member_badges mb ON b.id = mb.badge_id
    WHERE mb.member_id = %d
    ORDER BY mb.earned_at DESC",
    $member_id
));

$total_badges = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}mkwa_badges");
?>

<div class="mkwa-badges-container">
    <div class="mkwa-badges-summary">
        <span class="mkwa-badges-count">
            <?php echo esc_html(sprintf(
                __('%d of %d badges earned', 'mkwa-fitness'),
                count($earned_badges),
                $total_badges
            )); ?>
        </span>
    </div>

    <?php if ($earned_badges) : ?>
        <div class="mkwa-badges-grid">
            <?php foreach ($earned_badges as $badge) : ?>
                <div class="mkwa-badge-item" title="<?php echo esc_attr($badge->description); ?>">
                    <img src="<?php echo esc_url($badge->icon_url); ?>" 
                         alt="<?php echo esc_attr($badge->title); ?>"
                         class="mkwa-badge-icon">
                    <span class="mkwa-badge-title"><?php echo esc_html($badge->title); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <p class="mkwa-no-badges">
            <?php esc_html_e('No badges earned yet. Keep working out to earn badges!', 'mkwa-fitness'); ?>
        </p>
    <?php endif; ?>
</div>