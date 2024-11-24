<?php
/**
 * Badge management admin interface
 */

if (!defined('ABSPATH')) {
    exit;
}

class MKWA_Admin_Badges {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('admin_menu', array($this, 'add_badges_menu'));
        add_action('admin_init', array($this, 'register_badge_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_badges_menu() {
        add_submenu_page(
            'mkwa-fitness',
            __('Manage Badges', 'mkwa-fitness'),
            __('Badges', 'mkwa-fitness'),
            'manage_options',
            'mkwa-badges',
            array($this, 'render_badges_page')
        );
    }

    public function register_badge_settings() {
        register_setting('mkwa_badges', 'mkwa_badge_settings');
    }

    public function enqueue_admin_scripts($hook) {
        if ('mkwa-fitness_page_mkwa-badges' !== $hook) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_script(
            'mkwa-admin-badges',
            MKWA_PLUGIN_URL . 'admin/js/mkwa-admin-badges.js',
            array('jquery'),
            MKWA_VERSION,
            true
        );
    }

    public function render_badges_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $badge_id = isset($_GET['badge']) ? intval($_GET['badge']) : 0;

        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Manage Badges', 'mkwa-fitness'); ?></h1>
            <a href="?page=mkwa-badges&action=new" class="page-title-action"><?php _e('Add New', 'mkwa-fitness'); ?></a>
            <hr class="wp-header-end">

            <?php
            switch ($action) {
                case 'new':
                case 'edit':
                    $this->render_badge_form($badge_id);
                    break;
                default:
                    $this->render_badges_list();
                    break;
            }
            ?>
        </div>
        <?php
    }

    private function render_badge_form($badge_id = 0) {
        $badge = $badge_id ? MKWA_Badges::get_instance()->get_badge($badge_id) : array(
            'title' => '',
            'description' => '',
            'icon_url' => '',
            'badge_type' => 'standard',
            'category' => 'fitness',
            'points_required' => 0,
            'activities_required' => '',
            'cultural_requirement' => '',
            'seasonal_requirement' => ''
        );

        ?>
        <form method="post" action="">
            <?php wp_nonce_field('mkwa_save_badge', 'mkwa_badge_nonce'); ?>
            <input type="hidden" name="badge_id" value="<?php echo esc_attr($badge_id); ?>">

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="badge_title"><?php _e('Title', 'mkwa-fitness'); ?></label></th>
                    <td>
                        <input name="badge_title" type="text" id="badge_title" 
                               value="<?php echo esc_attr($badge['title']); ?>" class="regular-text">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="badge_description"><?php _e('Description', 'mkwa-fitness'); ?></label></th>
                    <td>
                        <textarea name="badge_description" id="badge_description" rows="5" 
                                  class="large-text"><?php echo esc_textarea($badge['description']); ?></textarea>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="badge_icon"><?php _e('Icon', 'mkwa-fitness'); ?></label></th>
                    <td>
                        <div class="mkwa-badge-icon-preview">
                            <?php if ($badge['icon_url']) : ?>
                                <img src="<?php echo esc_url($badge['icon_url']); ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="badge_icon_url" id="badge_icon_url" 
                               value="<?php echo esc_attr($badge['icon_url']); ?>">
                        <button type="button" class="button mkwa-upload-badge-icon">
                            <?php _e('Upload Icon', 'mkwa-fitness'); ?>
                        </button>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="badge_type"><?php _e('Type', 'mkwa-fitness'); ?></label></th>
                    <td>
                        <select name="badge_type" id="badge_type">
                            <option value="standard" <?php selected($badge['badge_type'], 'standard'); ?>>
                                <?php _e('Standard', 'mkwa-fitness'); ?>
                            </option>
                            <option value="cultural" <?php selected($badge['badge_type'], 'cultural'); ?>>
                                <?php _e('Cultural', 'mkwa-fitness'); ?>
                            </option>
                            <option value="seasonal" <?php selected($badge['badge_type'], 'seasonal'); ?>>
                                <?php _e('Seasonal', 'mkwa-fitness'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="badge_category"><?php _e('Category', 'mkwa-fitness'); ?></label></th>
                    <td>
                        <select name="badge_category" id="badge_category">
                            <option value="fitness" <?php selected($badge['category'], 'fitness'); ?>>
                                <?php _e('Fitness', 'mkwa-fitness'); ?>
                            </option>
                            <option value="cultural" <?php selected($badge['category'], 'cultural'); ?>>
                                <?php _e('Cultural', 'mkwa-fitness'); ?>
                            </option>
                            <option value="community" <?php selected($badge['category'], 'community'); ?>>
                                <?php _e('Community', 'mkwa-fitness'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="badge_points_required"><?php _e('Points Required', 'mkwa-fitness'); ?></label>
                    </th>
                    <td>
                        <input name="badge_points_required" type="number" id="badge_points_required" 
                               value="<?php echo esc_attr($badge['points_required']); ?>" class="small-text">
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
        <?php
    }

    private function render_badges_list() {
        $badges = MKWA_Badges::get_instance()->get_all_badges();
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col"><?php _e('Icon', 'mkwa-fitness'); ?></th>
                    <th scope="col"><?php _e('Title', 'mkwa-fitness'); ?></th>
                    <th scope="col"><?php _e('Type', 'mkwa-fitness'); ?></th>
                    <th scope="col"><?php _e('Category', 'mkwa-fitness'); ?></th>
                    <th scope="col"><?php _e('Points Required', 'mkwa-fitness'); ?></th>
                    <th scope="col"><?php _e('Actions', 'mkwa-fitness'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($badges)) : ?>
                    <tr>
                        <td colspan="6"><?php _e('No badges found.', 'mkwa-fitness'); ?></td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($badges as $badge) : ?>
                        <tr>
                            <td>
                                <?php if ($badge['icon_url']) : ?>
                                    <img src="<?php echo esc_url($badge['icon_url']); ?>" 
                                         alt="" style="width: 40px; height: 40px;">
                                <?php endif; ?>
                            </td>
                            <td><?php echo esc_html($badge['title']); ?></td>
                            <td><?php echo esc_html($badge['badge_type']); ?></td>
                            <td><?php echo esc_html($badge['category']); ?></td>
                            <td><?php echo esc_html($badge['points_required']); ?></td>
                            <td>
                                <a href="?page=mkwa-badges&action=edit&badge=<?php echo $badge['id']; ?>" 
                                   class="button button-small">
                                    <?php _e('Edit', 'mkwa-fitness'); ?>
                                </a>
                                <a href="?page=mkwa-badges&action=delete&badge=<?php echo $badge['id']; ?>&_wpnonce=<?php echo wp_create_nonce('delete_badge'); ?>" 
                                   class="button button-small button-link-delete" 
                                   onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this badge?', 'mkwa-fitness'); ?>')">
                                    <?php _e('Delete', 'mkwa-fitness'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }
}