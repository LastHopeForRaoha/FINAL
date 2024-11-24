<?php
if (!defined('ABSPATH')) {
    exit;
}

function mkwa_login_user() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mkwa_login_nonce']) && wp_verify_nonce($_POST['mkwa_login_nonce'], 'mkwa_login_action')) {
        $creds = array(
            'user_login'    => sanitize_user($_POST['username']),
            'user_password' => sanitize_text_field($_POST['password']),
            'remember'      => true
        );

        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            echo '<p>Error: ' . $user->get_error_message() . '</p>';
        } else {
            wp_redirect(home_url());
            exit;
        }
    }
}