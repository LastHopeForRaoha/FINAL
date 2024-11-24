<?php
/**
 * Leaderboard Content Template
 * 
 * @package MkwaFitness
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<table class="mkwa-leaderboard-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Rank', 'mkwa-fitness'); ?></th>
            <th><?php esc_html_e('Member', 'mkwa-fitness'); ?></th>
            <th>
                <?php 
                if ($type === 'streaks') {
                    esc_html_e('Current Streak', 'mkwa-fitness');
                } else {
                    esc_html_e('Points', 'mkwa-fitness');
                }
                ?>
            </th>
            <th><?php esc_html_e('Level', 'mkwa-fitness'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php 
        if (!empty($leaders)):
            foreach($leaders as $rank => $leader): 
                $level = mkwa_calculate_level($leader->total_points);
                $is_current_user = $leader->is_current_user;
        ?>
            <tr class="<?php echo $is_current_user ? 'current-user' : ''; ?>">
                <td><?php echo esc_html($rank + 1); ?></td>
                <td><?php echo esc_html($leader->display_name); ?></td>
                <td>
                    <?php 
                    if ($type === 'streaks') {
                        printf(
                            esc_html__('%d days', 'mkwa-fitness'),
                            $leader->current_streak
                        );
                    } else {
                        echo number_format($leader->total_points);
                    }
                    ?>
                </td>
                <td>
                    <div class="mkwa-level-badge level-<?php echo esc_attr($level); ?>">
                        <?php echo esc_html($level); ?>
                    </div>
                </td>
            </tr>
        <?php 
            endforeach;
        else:
        ?>
            <tr>
                <td colspan="4" class="no-results">
                    <?php esc_html_e('No leaderboard data available', 'mkwa-fitness'); ?>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>