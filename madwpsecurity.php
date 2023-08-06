<?php

/*
Plugin Name: MadWPSecurity
Description: Restricts wp-admin access to specific IP addresses
Version: 1.0
Author: Arthur Silva
*/

// Hook into the 'login_init' action to check the IP address.
add_action('login_init', 'mad_wp_security_custom_ip_login_check');

function mad_wp_security_custom_ip_login_check() {
    $options = get_option('mad_wp_security_custom_ip_login_settings');
    $allowed_ips = isset($options['allowed_ips']) ? $options['allowed_ips'] : array();
    $client_ip = $_SERVER['REMOTE_ADDR'];

    if (!in_array($client_ip, $allowed_ips)) {
        wp_die('Access denied.');
    }
}


// Add a new menu item under the "Settings" menu.
add_action('admin_menu', 'mad_wp_security_custom_ip_login_admin_menu');

function mad_wp_security_custom_ip_login_admin_menu() {
    add_options_page(
        'MadWPSecurity Settings',
        'MadWPSecurity',
        'manage_options',
        'madwpsecurity-custom-ip-login-settings',
        'mad_wp_security_custom_ip_login_settings_page'
    );
}

// Callback function to display the admin page.
function mad_wp_security_custom_ip_login_settings_page() {
    // Check user capabilities.
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mad_wp_security_custom_ip_login');
            do_settings_sections('mad_wp_security_custom_ip_login');
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

// Register the settings and fields.
add_action('admin_init', 'custom_mad_wp_security_ip_login_admin_init');

function custom_mad_wp_security_ip_login_admin_init() {
    register_setting(
        'mad_wp_security_custom_ip_login',
        'mad_wp_security_custom_ip_login_settings',
        'mad_wp_security_custom_ip_login_sanitize_settings'
    );

    add_settings_section(
        'mad_wp_security_custom_ip_login_section',
        'Allowed IP Addresses',
        'mad_wp_security_custom_ip_login_section_callback',
        'mad_wp_security_custom_ip_login'
    );

    add_settings_field(
        'allowed_ips',
        'Enter Allowed IP Addresses',
        'mad_wp_security_custom_ip_login_allowed_ips_callback',
        'mad_wp_security_custom_ip_login',
        'mad_wp_security_custom_ip_login_section'
    );
}

// Sanitize and validate the input.
function mad_wp_security_custom_ip_login_sanitize_settings($input) {
    $sanitized_input = array();

    if (isset($input['allowed_ips'])) {
        if (is_array($input['allowed_ips'])) {
            $input['allowed_ips'] = implode("\n", $input['allowed_ips']);
        }
        $ips = explode("\n", $input['allowed_ips']);
        $sanitized_ips = array();
        foreach ($ips as $ip) {
            $sanitized_ips[] = sanitize_text_field($ip);
        }
        $sanitized_input['allowed_ips'] = $sanitized_ips;
    }

    return $sanitized_input;
}

// Display the IP address fields.
function mad_wp_security_custom_ip_login_allowed_ips_callback() {
    $options = get_option('mad_wp_security_custom_ip_login_settings');
    $ips = isset($options['allowed_ips']) ? $options['allowed_ips'] : array();
    $allowed_ips = implode("\n", $ips);
    echo '<textarea name="mad_wp_security_custom_ip_login_settings[allowed_ips]" rows="5" cols="50">' . esc_textarea($allowed_ips) . '</textarea>';
}

// Display the settings section header.
function mad_wp_security_custom_ip_login_section_callback() {
    echo '<p>Enter the IP addresses that are allowed to access the wp-admin area.</p>';
}

