<?php
/**
 * Leaderboard Template
 *
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get initial leaderboard data
$type = isset($atts['type']) ? $atts['type'] : 'overall';
$limit = isset($atts['limit']) ? intval($atts['limit']) : 10;
$leaders = (new MKWA_Leaderboard())->get_leaderboard_data($type, $limit);
?>

<div class="mkwa-leaderboard">
    <!-- Leaderboard Header -->
    <div class="mkwa-leaderboard-header">
        <h3><?php esc_html_e('Community Rankings', 'mkwa-fitness'); ?></h3>
        <p class="mkwa-leaderboard-description">
            <?php esc_html_e('See how you stack up against other members in our fitness journey.', 'mkwa-fitness'); ?>
        </p>
    </div>

    <!-- Leaderboard Filters -->
    <div class="mkwa-leaderboard-filters">
        <button class="mkwa-filter-btn <?php echo $type === 'overall' ? 'active' : ''; ?>" data-type="overall">
            <?php esc_html_e('Overall', 'mkwa-fitness'); ?>
        </button>
        <button class="mkwa-filter-btn <?php echo $type === 'weekly' ? 'active' : ''; ?>" data-type="weekly">
            <?php esc_html_e('This Week', 'mkwa-fitness'); ?>
        </button>
        <button class="mkwa-filter-btn <?php echo $type === 'monthly' ? 'active' : ''; ?>" data-type="monthly">
            <?php esc_html_e('This Month', 'mkwa-fitness'); ?>
        </button>
        <button class="mkwa-filter-btn <?php