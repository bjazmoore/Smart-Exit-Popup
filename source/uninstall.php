<?php
// Exit if accessed directly.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Clean up all plugin settings.
$option_keys = [
    'smart_exit_popup_content',
    'smart_exit_header_text',
    'smart_exit_header_font_size',
    'smart_exit_header_height_rem',
    'smart_exit_header_gradient',
    'smart_exit_modal_radius',
    'smart_exit_modal_bg',
    'smart_exit_modal_width_pct',
    'smart_exit_modal_height_rem',
    'smart_exit_show_header',
    'smart_exit_enable_dismiss_option',

    // Image-related
    'smart_exit_show_image',
    'smart_exit_image_id',
    'smart_exit_image_scale',
    'smart_exit_image_x_position',
    'smart_exit_image_x_custom',
    'smart_exit_image_offset_y'
];

foreach ($option_keys as $key) {
    delete_option($key);
}

// If your plugin ever supports multisite:
if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        foreach ($option_keys as $key) {
            delete_option($key);
        }
        restore_current_blog();
    }
}
