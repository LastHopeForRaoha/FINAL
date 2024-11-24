/**
 * Create badges tables
 */
private function create_badges_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Badges table
    $sql_badges = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_badges (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        title varchar(100) NOT NULL,
        description text NOT NULL,
        icon_url varchar(255) NOT NULL,
        badge_type varchar(50) NOT NULL,
        category varchar(50) NOT NULL,
        points_required int(11) DEFAULT 0,
        activities_required text,
        cultural_requirement text,
        seasonal_requirement varchar(50),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    // User badges table
    $sql_user_badges = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_user_badges (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        badge_id bigint(20) NOT NULL,
        earned_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY user_badge (user_id,badge_id)
    ) $charset_collate;";

    // Achievement criteria table
    $sql_achievement_criteria = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}mkwa_achievement_criteria (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        badge_id bigint(20) NOT NULL,
        criteria_type varchar(50) NOT NULL,
        requirement_value text NOT NULL,
        progress_type varchar(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_badges);
    dbDelta($sql_user_badges);
    dbDelta($sql_achievement_criteria);
}