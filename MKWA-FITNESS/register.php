<?php
if (!defined('ABSPATH')) {
    exit;
}

function mkwa_register_user() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mkwa_register_nonce']) && wp_verify_nonce($_POST['mkwa_register_nonce'], 'mkwa_register_action')) {
        $username = sanitize_user($_POST['username']);
        $password = sanitize_text_field($_POST['password']);
        $email = sanitize_email($_POST['email']);

        $errors = register_new_user($username, $email);
        if (!is_wp_error($errors)) {
            wp_set_password($password, $errors);
            echo '<p>Registration successful! You can now <a href="' . wp_login_url() . '">log in</a>.</p>';
        } else {
            echo '<p>Error: ' . $errors->get_error_message() . '</p>';
        }
    }
}