<?php
/**
 * Constants for MKWA Fitness Plugin
 * 
 * @package MkwaFitness
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Database version
define('MKWA_DB_VERSION', '1.0.0');

// Activity Types
define('MKWA_ACTIVITY_CHECKIN', 'checkin');
define('MKWA_ACTIVITY_CLASS', 'class');
define('MKWA_ACTIVITY_COLD_PLUNGE', 'cold_plunge');
define('MKWA_ACTIVITY_PR', 'personal_record');
define('MKWA_ACTIVITY_COMPETITION', 'competition');

// Point Values (Default)
define('MKWA_POINTS_CHECKIN_DEFAULT', 3);
define('MKWA_POINTS_CLASS_DEFAULT', 15);
define('MKWA_POINTS_COLD_PLUNGE_DEFAULT', 20);
define('MKWA_POINTS_PR_DEFAULT', 25);
define('MKWA_POINTS_COMPETITION_DEFAULT', 50);

// Cache Settings
define('MKWA_CACHE_GROUP', 'mkwa_fitness');
define('MKWA_CACHE_DURATION_DEFAULT', 3600);

// API Settings
define('MKWA_API_NAMESPACE', 'mkwa/v1');
define('MKWA_API_VERSION', '1');

// Levels Settings
define('MKWA_LEVEL_BASE_POINTS', 100);
define('MKWA_MAX_LEVEL', 100);

// Streaks
define('MKWA_STREAK_BRONZE', 7);
define('MKWA_STREAK_SILVER', 30);
define('MKWA_STREAK_GOLD', 90);

// Admin Settings
define('MKWA_SETTINGS_PAGE', 'mkwa-fitness');
define('MKWA_OPTION_GROUP', 'mkwa_fitness_options');