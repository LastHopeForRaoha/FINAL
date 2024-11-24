<?php
/**
 * Admin page for MKWA Fitness Plugin
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <nav class="nav-tab-wrapper">
        <a href="?page=<?php echo MKWA_SETTINGS_PAGE; ?>&tab=general" class="nav-tab <?php echo empty($_GET['tab']) || $_GET['tab'] === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('General Settings', 'mkwa-fitness'); ?>
        </a>
        <a href="?page=<?php echo MKWA_SETTINGS_PAGE; ?>&tab=points" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'points' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Points Settings', 'mkwa-fitness'); ?>
        </a>
        <a href="?page=<?php echo MKWA_SETTINGS_PAGE; ?>&tab=stats" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'stats' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Statistics', 'mkwa-fitness'); ?>
        </a>
    </nav>
    
    <div class="tab-content">
        <?php
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        
        switch ($tab) {
            case 'points':
                include MKWA_PLUGIN_DIR . 'admin/tabs/points-settings.php';
                break;
                
            case 'stats':
                include MKWA_PLUGIN_DIR . 'admin/tabs/statistics.php';
                break;
                
            default:
                include MKWA_PLUGIN_DIR . 'admin/tabs/general-settings.php';
                break;
        }
        ?>
    </div>
</div>