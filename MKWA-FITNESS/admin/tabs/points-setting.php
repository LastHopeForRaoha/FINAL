<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<form method="post" action="options.php">
    <?php settings_fields(MKWA_OPTION_GROUP); ?>
    
    <h2><?php _e('Activity Points Settings', 'mkwa-fitness'); ?></h2>
    <p><?php _e('Configure the base points awarded for each type of activity.', 'mkwa-fitness'); ?></p>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('Check-in Points', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_points_checkin" 
                       value="<?php echo esc_attr(get_option('mkwa_points_checkin', MKWA_POINTS_CHECKIN_DEFAULT)); ?>" 
                       min="0" 
                       step="1" />
                <p class="description">
                    <?php _e('Base points awarded for checking in at the gym.', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('Class Attendance Points', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_points_class" 
                       value="<?php echo esc_attr(get_option('mkwa_points_class', MKWA_POINTS_CLASS_DEFAULT)); ?>" 
                       min="0" 
                       step="1" />
                <p class="description">
                    <?php _e('Base points awarded for attending a fitness class.', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('Cold Plunge Points', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_points_cold_plunge" 
                       value="<?php echo esc_attr(get_option('mkwa_points_cold_plunge', MKWA_POINTS_COLD_PLUNGE_DEFAULT)); ?>" 
                       min="0" 
                       step="1" />
                <p class="description">
                    <?php _e('Base points awarded for completing a cold plunge session.', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('Personal Record Points', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_points_pr" 
                       value="<?php echo esc_attr(get_option('mkwa_points_pr', MKWA_POINTS_PR_DEFAULT)); ?>" 
                       min="0" 
                       step="1" />
                <p class="description">
                    <?php _e('Base points awarded for setting a new personal record.', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('Competition Points', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_points_competition" 
                       value="<?php echo esc_attr(get_option('mkwa_points_competition', MKWA_POINTS_COMPETITION_DEFAULT)); ?>" 
                       min="0" 
                       step="1" />
                <p class="description">
                    <?php _e('Base points awarded for participating in a competition.', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
    </table>

    <h2><?php _e('Streak Bonuses', 'mkwa-fitness'); ?></h2>
    <p><?php _e('Configure bonus multipliers for maintaining activity streaks.', 'mkwa-fitness'); ?></p>
    
    <table class="form-table">
        <tr>
            <th scope="row"><?php _e('7-Day Streak Bonus', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_streak_bonus_bronze" 
                       value="<?php echo esc_attr(get_option('mkwa_streak_bonus_bronze', 0.5)); ?>" 
                       min="0" 
                       max="5" 
                       step="0.1" />
                <p class="description">
                    <?php _e('Bonus multiplier for maintaining a 7-day streak (e.g., 0.5 = 50% bonus).', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('30-Day Streak Bonus', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_streak_bonus_silver" 
                       value="<?php echo esc_attr(get_option('mkwa_streak_bonus_silver', 1.0)); ?>" 
                       min="0" 
                       max="5" 
                       step="0.1" />
                <p class="description">
                    <?php _e('Bonus multiplier for maintaining a 30-day streak (e.g., 1.0 = 100% bonus).', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
        
        <tr>
            <th scope="row"><?php _e('90-Day Streak Bonus', 'mkwa-fitness'); ?></th>
            <td>
                <input type="number" 
                       name="mkwa_streak_bonus_gold" 
                       value="<?php echo esc_attr(get_option('mkwa_streak_bonus_gold', 2.0)); ?>" 
                       min="0" 
                       max="5" 
                       step="0.1" />
                <p class="description">
                    <?php _e('Bonus multiplier for maintaining a 90-day streak (e.g., 2.0 = 200% bonus).', 'mkwa-fitness'); ?>
                </p>
            </td>
        </tr>
    </table>
    
    <?php submit_button(__('Save Points Settings', 'mkwa-fitness')); ?>
</form>