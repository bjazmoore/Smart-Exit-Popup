<?php
/*
Plugin Name: Smart Exit Popup
Description: Displays a modal popup when the user intends to exit the page.
Version: 1.0
Author: Bradley Moore
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function smart_exit_log($message) {
    $log_file = plugin_dir_path(__FILE__) . 'logs/logs.txt';
    $date = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$date] $message" . PHP_EOL, FILE_APPEND);
}


// Enqueue frontend scripts and styles
function smart_exit_popup_enqueue_scripts() {
    wp_enqueue_style('smart-exit-style', plugin_dir_url(__FILE__) . 'assets/style.css');
    wp_enqueue_script('smart-exit-script', plugin_dir_url(__FILE__) . 'assets/script.js', array('jquery'), null, true);

    // Get image settings
    $image_id = get_option('smart_exit_image_id');
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

    $popup_settings = array(
        'content'         => get_option('smart_exit_popup_content', ''),
        'headerEnabled'   => (bool)get_option('smart_exit_show_header', 1),
        'headerText'      => get_option('smart_exit_header_text', 'Wait! Don\'t leave yet!'),
        'headerFontSize'  => get_option('smart_exit_header_font_size', 1.8),
        'modalWidth'      => get_option('smart_exit_modal_width_pct', 90),
        'modalHeightMode'    => get_option('smart_exit_modal_height_mode', 'auto'),
        'modalHeightCustom'  => get_option('smart_exit_modal_height_custom', ''),
        'modalRadius'     => get_option('smart_exit_modal_radius', 10),
        'modalBg'         => get_option('smart_exit_modal_background', '#ffffff'),
        'overlayBgColor' => get_option('smart_exit_overlay_bg_color', '#000000'),
        'overlayAlpha'   => get_option('smart_exit_overlay_alpha', 0.7),

    
        'showFooter'      => (bool)get_option('smart_exit_show_footer', false),
        'footerBgColor'   => get_option('smart_exit_footer_bg_color', '#191970'),
        'footerHeightRem' => get_option('smart_exit_footer_height_rem', 3),
        'footerTextColorMode' => get_option('smart_exit_footer_text_color_mode', 'auto'),
        'footerTextColor'     => get_option('smart_exit_footer_text_color', '#ffffff'),
        'enableDismissOption' => (bool)get_option('smart_exit_enable_dismiss_option', false),
    
        'headerHeight'   => get_option('smart_exit_header_height_rem', 5),
        'headerColorMode' => get_option('smart_exit_header_color_mode', 'gradient'),
        'headerColorLeft' => get_option('smart_exit_header_color_left', '#0348df'),
        'headerColorRight'=> get_option('smart_exit_header_color_right', '#44a2e5'),
        'headerTextColorMode' => get_option('smart_exit_header_text_color_mode', 'auto'),
        'headerTextColor'     => get_option('smart_exit_header_text_color', '#ffffff'),
    
        'showImage'       => (bool)get_option('smart_exit_show_image', 0),
        'imageUrl'        => $image_url,
        'imageScale'      => get_option('smart_exit_image_scale', 0.25),
        'imageXPosition'  => get_option('smart_exit_image_x_position', 'center'),
        'imageXCustom'    => get_option('smart_exit_image_x_custom', 0),
        'imageY'          => get_option('smart_exit_image_offset_y', 20),
    
        'pluginUrl'       => plugin_dir_url(__FILE__)
    );
    

    wp_localize_script('smart-exit-script', 'smartExitPopup', $popup_settings);
}
add_action('wp_enqueue_scripts', 'smart_exit_popup_enqueue_scripts');

// Enqueue admin media uploader
function smart_exit_popup_admin_scripts($hook) {
    if ($hook === 'toplevel_page_smart-exit-popup') {
        wp_enqueue_media();
        wp_enqueue_script('smart-exit-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', array('jquery', 'wp-color-picker'), null, true);
        wp_enqueue_style('wp-color-picker'); // Add this
    }
}

add_action('admin_enqueue_scripts', 'smart_exit_popup_admin_scripts');

// Create admin settings page
function smart_exit_popup_create_menu() {
    add_menu_page('Smart Exit Popup', 'Smart Exit Popup', 'manage_options', 'smart-exit-popup', 'smart_exit_popup_settings_page');
}
add_action('admin_menu', 'smart_exit_popup_create_menu');

// Allow extended HTML (e.g., SVGs) in popup content
add_filter('wp_kses_allowed_html', 'smart_exit_custom_allowed_html', 10, 2);

function smart_exit_custom_allowed_html($tags, $context) {
    if ($context === 'smart_exit_popup') {
        $tags['div'] = [
            'class' => true,
            'id' => true,
            'style' => true,
        ];
        $tags['a'] = [
            'href' => true,
            'target' => true,
            'rel' => true,
            'style' => true,
        ];
        $tags['svg'] = [
            'xmlns'    => true,
            'viewBox'  => true,
            'viewbox'  => true,
            'fill'     => true,
            'width'    => true,
            'height'   => true,
            'style'    => true,
            'role'     => true,
            'aria-hidden' => true,
        ];
        
        $tags['path'] = [
            'fill'   => true,
            'd'      => true,
            'style'  => true,
        ];

        $tags['img'] = [
            'src'      => true,
            'alt'      => true,
            'width'    => true,
            'height'   => true,
            'class'    => true,
            'style'    => true,
            'loading'  => true,
            'decoding' => true,
            'title'    => true,
        ];
        
        $tags['title'] = [];

        $tags['p'] = ['style' => true];
        $tags['strong'] = [];

        $tags['figure'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['figcaption'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['picture'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['source'] = [
            'srcset' => true,
            'media'  => true,
            'type'   => true,
        ];
        $tags['form'] = [
            'action'   => true,
            'method'   => true,
            'target'   => true,
            'class'    => true,
            'id'       => true,
            'style'    => true,
        ];
        
        $tags['input'] = [
            'type'     => true,
            'name'     => true,
            'value'    => true,
            'placeholder' => true,
            'required' => true,
            'checked'  => true,
            'class'    => true,
            'id'       => true,
            'style'    => true,
        ];
        
        $tags['button'] = [
            'type'     => true,
            'name'     => true,
            'value'    => true,
            'class'    => true,
            'id'       => true,
            'style'    => true,
        ];
        
        $tags['label'] = [
            'for'      => true,
            'class'    => true,
            'id'       => true,
            'style'    => true,
        ];
        
        $tags['textarea'] = [
            'name'     => true,
            'rows'     => true,
            'cols'     => true,
            'placeholder' => true,
            'required' => true,
            'class'    => true,
            'id'       => true,
            'style'    => true,
        ];
        
        $tags['select'] = [
            'name'     => true,
            'class'    => true,
            'id'       => true,
            'style'    => true,
        ];
        
        $tags['option'] = [
            'value'    => true,
            'selected' => true,
            'style'    => true,
        ];
        $tags['ul'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
    
        $tags['ol'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
    
        $tags['li'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];

        $tags['span'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['table'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
            'border' => true,
            'cellspacing' => true,
            'cellpadding' => true,
        ];
        
        $tags['thead'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['tbody'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['tfoot'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['tr'] = [
            'class' => true,
            'id'    => true,
            'style' => true,
        ];
        
        $tags['td'] = [
            'class'   => true,
            'id'      => true,
            'style'   => true,
            'colspan' => true,
            'rowspan' => true,
        ];
        
        $tags['th'] = [
            'class'   => true,
            'id'      => true,
            'style'   => true,
            'scope'   => true,
            'colspan' => true,
            'rowspan' => true,
        ];
    
        // <br> is a void tag and doesn't take attributes, but it still needs to be whitelisted:
        $tags['br'] = [];        
    }
    return $tags;
}

// Allow additional safe CSS properties
add_filter('safe_style_css', 'smart_exit_allow_flex_display');
function smart_exit_allow_flex_display($styles) {
    $styles[] = 'display';
    $styles[] = 'gap';
    $styles[] = 'justify-content';
    $styles[] = 'align-items';
    $styles[] = 'flex-wrap';
    return $styles;
}

// Register settings
function smart_exit_popup_register_settings() {
    // Content editor — allows safe HTML
    register_setting('smart-exit-settings-group', 'smart_exit_popup_content', [
        'sanitize_callback' => function ($content) {
            $is_power_mode = get_option('smart_exit_power_user_mode', false);
            return $is_power_mode ? wp_kses($content, wp_kses_allowed_html('smart_exit_popup')) : $content;
        }
    ]);

    // Header section
    register_setting('smart-exit-settings-group', 'smart_exit_show_header');
    register_setting('smart-exit-settings-group', 'smart_exit_header_text', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_header_font_size', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_header_height_rem', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_header_color_mode');
    register_setting('smart-exit-settings-group', 'smart_exit_header_color_left', ['sanitize_callback' => 'sanitize_hex_color']);
    register_setting('smart-exit-settings-group', 'smart_exit_header_color_right', ['sanitize_callback' => 'sanitize_hex_color']);


    // Modal styling
    register_setting('smart-exit-settings-group', 'smart_exit_modal_radius', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_modal_background', [
        'sanitize_callback' => 'sanitize_hex_color'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_modal_width_pct', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_modal_height_mode'); // 'auto' or 'custom'
    register_setting('smart-exit-settings-group', 'smart_exit_modal_height_custom', [
        'sanitize_callback' => 'floatval'
    ]);

    // Image options
    register_setting('smart-exit-settings-group', 'smart_exit_show_image');
    register_setting('smart-exit-settings-group', 'smart_exit_image_id', [
        'sanitize_callback' => 'absint'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_image_scale', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_image_x_position', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_image_x_custom', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_image_offset_y', [
        'sanitize_callback' => 'intval'
    ]);

    register_setting('smart-exit-settings-group', 'smart_exit_show_footer');
    register_setting('smart-exit-settings-group', 'smart_exit_footer_bg_color', [
        'sanitize_callback' => 'sanitize_hex_color'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_enable_dismiss_option');

    register_setting('smart-exit-settings-group', 'smart_exit_footer_height_rem', [
        'sanitize_callback' => 'floatval'
    ]);

    register_setting('smart-exit-settings-group', 'smart_exit_header_text_color', [
        'sanitize_callback' => 'sanitize_text_field' // allows 'auto' or a hex
    ]);
    
    register_setting('smart-exit-settings-group', 'smart_exit_footer_text_color', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    
    register_setting('smart-exit-settings-group', 'smart_exit_header_text_color_mode', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_footer_text_color_mode', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_overlay_bg_color', [
        'sanitize_callback' => 'sanitize_hex_color'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_overlay_alpha', [
        'sanitize_callback' => 'floatval'
    ]);
    register_setting('smart-exit-settings-group', 'smart_exit_power_user_mode', [
        'sanitize_callback' => function($val) { return $val ? 1 : 0; }
    ]);
    
}

add_action('admin_init', 'smart_exit_popup_register_settings');


// Settings page layout
function smart_exit_popup_settings_page() {
    ?>
    <div class="wrap">
        <h1>Smart Exit Popup Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('smart-exit-settings-group'); ?>
            <?php do_settings_sections('smart-exit-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Popup Content</th>
                    <td>
                        <?php
                        $power_mode_enabled = get_option('smart_exit_power_user_mode', false);
                        // Fallback to localStorage value using JS, so this PHP default just covers initial load.
                        $content = get_option('smart_exit_popup_content', '<h2>Wait! Don\'t leave yet!</h2><p>Check out our latest offers.</p>');
                        
                        wp_editor(
                            $content,
                            'smart_exit_popup_content',
                            array(
                                'textarea_name' => 'smart_exit_popup_content',
                                'media_buttons' => true,
                                'teeny' => false,
                                'textarea_rows' => 10,
                                'tinymce'       => !$power_mode_enabled, // visual mode if not in power-user
                                'quicktags'     => $power_mode_enabled,  // text/HTML if in power-user
                            )
                        );
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Editor: Power-User Mode</th>
                    <td>
                        <label>
                            <input type="checkbox" name="smart_exit_power_user_mode" id="smart_exit_power_user_mode"
                                value="1" <?php checked(1, get_option('smart_exit_power_user_mode', 0)); ?> />
                            Enable Power User Mode (HTML/SVG allowed)
                        </label>
                        <p class="description">⚠️ Caution: Switching from <b>Power-User Mode</b> back to Visual mode may strip some advanced HTML.  
                        &nbsp;&nbsp;Changes to <b>Power-User Mode</b> take effect <u>after</u> saving settings.</p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Modal Width (% of viewport)</th>
                    <td>
                        <input type="number" name="smart_exit_modal_width_pct" value="<?php echo esc_attr(get_option('smart_exit_modal_width_pct', 90)); ?>" style="width:80px;" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Modal Height</th>
                    <td>
                        <?php $height_mode = get_option('smart_exit_modal_height_mode', 'auto'); ?>
                        <select name="smart_exit_modal_height_mode" id="smart_exit_modal_height_mode">
                            <option value="auto" <?php selected($height_mode, 'auto'); ?>>Auto</option>
                            <option value="custom" <?php selected($height_mode, 'custom'); ?>>Custom (set value below)</option>
                        </select>

                        <div id="smart-exit-modal-height-custom-wrapper" style="<?php echo $height_mode === 'custom' ? '' : 'display:none;'; ?>; margin-top: 8px;">
                            <input type="number" step="0.1" name="smart_exit_modal_height_custom" value="<?php echo esc_attr(get_option('smart_exit_modal_height_custom', '')); ?>" style="width:80px;" />
                            <span class="description">Height in rem (e.g., 30)</span>
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Modal Corner Radius (px)</th>
                    <td><input type="number" name="smart_exit_modal_radius" id="smart_exit_modal_radius" value="<?php echo esc_attr(get_option('smart_exit_modal_radius', 10)); ?>" style="width:80px;" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Modal Dialog Window Background Color</th>
                    <td><input type="text" name="smart_exit_modal_background" id="smart_exit_modal_background" value="<?php echo esc_attr(get_option('smart_exit_modal_background', '#ffffff')); ?>" class="regular-text wp-color-picker" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Overlay Background Color</th>
                    <td>
                        <input type="text" name="smart_exit_overlay_bg_color" id="smart_exit_overlay_bg_color"
                            value="<?php echo esc_attr(get_option('smart_exit_overlay_bg_color', '#000000')); ?>"
                            class="regular-text wp-color-picker" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Overlay Opacity</th>
                    <td>
                        <input type="number" name="smart_exit_overlay_alpha" min="0" max="1" step="0.05"
                            value="<?php echo esc_attr(get_option('smart_exit_overlay_alpha', 0.7)); ?>"
                            style="width: 80px;" />
                        <span class="description">0 = fully transparent, 1 = fully opaque</span>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <fieldset style="border: 1px solid #ccc; padding: 15px; margin-bottom: 20px;">
                            <legend style="font-weight: bold;">Header Options</legend>

                            <p>
                                <label>
                                    <input type="checkbox" name="smart_exit_show_header" value="1" <?php checked(1, get_option('smart_exit_show_header', 1)); ?> />
                                    Enable Header Section
                                </label>
                            </p>

                            <p>
                                <label for="smart_exit_header_text"><strong>Header Banner Text</strong></label><br />
                                <input type="text" name="smart_exit_header_text" id="smart_exit_header_text" value="<?php echo esc_attr(get_option('smart_exit_header_text', 'Wait! Don\'t leave yet!')); ?>" class="regular-text" />
                            </p>

                            <p>
                                <label for="smart_exit_header_font_size"><strong>Font Size (rem)</strong></label><br />
                                <input type="number" step="0.1" name="smart_exit_header_font_size" id="smart_exit_header_font_size" value="<?php echo esc_attr(get_option('smart_exit_header_font_size', 1.8)); ?>" style="width:80px;" />
                            </p>

                            <p>
                                <label for="smart_exit_header_height_rem"><strong>Header Height (rem)</strong></label><br />
                                <input type="number" step="0.1" name="smart_exit_header_height_rem" id="smart_exit_header_height_rem" value="<?php echo esc_attr(get_option('smart_exit_header_height_rem', 5)); ?>" style="width:80px;" />
                            </p>


                            <p>
                                <label for="smart_exit_header_color_mode"><strong>Header Background Type</strong></label><br />
                                <?php $mode = get_option('smart_exit_header_color_mode', 'gradient'); ?>
                                <select name="smart_exit_header_color_mode" id="smart_exit_header_color_mode">
                                    <option value="solid" <?php selected($mode, 'solid'); ?>>Solid Color</option>
                                    <option value="gradient" <?php selected($mode, 'gradient'); ?>>Gradient</option>
                                </select>
                            </p>

                            <p>
                                <label for="smart_exit_header_color_left"><strong><?php echo $mode === 'gradient' ? 'Left Color (Gradient)' : 'Header Color'; ?></strong></label><br />
                                <input type="text" name="smart_exit_header_color_left" id="smart_exit_header_color_left"
                                    value="<?php echo esc_attr(get_option('smart_exit_header_color_left', '#0348df')); ?>"
                                    class="regular-text wp-color-picker" />
                            </p>

                            <p id="header-gradient-right-color" style="<?php echo $mode === 'gradient' ? '' : 'display:none;'; ?>">
                                <label for="smart_exit_header_color_right"><strong>Right Color (Gradient)</strong></label><br />
                                <input type="text" name="smart_exit_header_color_right" id="smart_exit_header_color_right"
                                    value="<?php echo esc_attr(get_option('smart_exit_header_color_right', '#44a2e5')); ?>"
                                    class="regular-text wp-color-picker" />
                            </p>

                            <p>
                                <label for="smart_exit_header_text_color_mode"><strong>Header Text Color Mode</strong></label><br />
                                <select name="smart_exit_header_text_color_mode" id="smart_exit_header_text_color_mode">
                                    <option value="auto" <?php selected(get_option('smart_exit_header_text_color_mode', 'auto'), 'auto'); ?>>Auto (adjusts contrast)</option>
                                    <option value="custom" <?php selected(get_option('smart_exit_header_text_color_mode', 'auto'), 'custom'); ?>>Custom</option>
                                </select>
                            </p>

                            <p id="smart-exit-header-text-color-wrapper" style="<?php echo (get_option('smart_exit_header_text_color_mode', 'auto') === 'custom') ? '' : 'display:none;'; ?>">
                                <label for="smart_exit_header_text_color"><strong>Custom Header Text Color</strong></label><br />
                                <input type="text" name="smart_exit_header_text_color" id="smart_exit_header_text_color"
                                    value="<?php echo esc_attr(get_option('smart_exit_header_text_color', '#ffffff')); ?>" class="regular-text wp-color-picker" />
                            </p>

                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <fieldset style="border: 1px solid #ccc; padding: 15px;">
                            <legend style="font-weight: bold;">Image Options</legend>

                            <p>
                                <label>
                                    <input type="checkbox" name="smart_exit_show_image" id="smart_exit_show_image" value="1" <?php checked(1, get_option('smart_exit_show_image', 0)); ?> />
                                    Enable Image in Popup
                                </label>
                            </p>

                            <p>
                                <label><strong>Select Image</strong></label><br />
                                <?php
                                $image_id = get_option('smart_exit_image_id');
                                $image = $image_id ? wp_get_attachment_image_src($image_id, 'medium') : '';
                                ?>
                                <div id="smart-exit-image-preview">
                                    <?php if ($image): ?>
                                        <img src="<?php echo esc_url($image[0]); ?>" style="max-width:150px;" />
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="smart_exit_image_id" id="smart_exit_image_id" value="<?php echo esc_attr($image_id); ?>" />
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <button type="button" class="button" id="smart-exit-upload-button">Select Image</button>
                                    <button type="button" class="button" id="smart-exit-remove-button">Remove Image</button>
                                </div>

                            </p>

                            <p>
                                <label for="smart_exit_image_scale"><strong>Image Scale</strong></label><br />
                                <input type="number" step="0.01" name="smart_exit_image_scale" value="<?php echo esc_attr(get_option('smart_exit_image_scale', 0.25)); ?>" style="width:80px;" /> (e.g., 0.25 = 25%)
                            </p>

                            <p>
                                <label for="smart_exit_image_offset_y"><strong>Vertical Axis Positioning (px)</strong></label><br />
                                <input type="number" name="smart_exit_image_offset_y" value="<?php echo esc_attr(get_option('smart_exit_image_offset_y', 20)); ?>" style="width:80px;" />
                                <br><span class="description">Use a negative value to move image above the popup.</span>
                            </p>

                            <p>
                                <label for="smart_exit_image_x_position"><strong>Horizontal Axis Positioning</strong></label><br />
                                <?php $x_position = get_option('smart_exit_image_x_position', 'center'); ?>
                                <select name="smart_exit_image_x_position" id="smart_exit_image_x_position">
                                    <option value="center" <?php selected($x_position, 'center'); ?>>Center</option>
                                    <option value="left5" <?php selected($x_position, 'left5'); ?>>Left + 5%</option>
                                    <option value="left25" <?php selected($x_position, 'left25'); ?>>Left + 25%</option>
                                    <option value="right5" <?php selected($x_position, 'right5'); ?>>Right - 5%</option>
                                    <option value="right25" <?php selected($x_position, 'right25'); ?>>Right - 25%</option>
                                    <option value="custom" <?php selected($x_position, 'custom'); ?>>Custom (rem from left)</option>
                                </select>
                            </p>

                            <p id="smart-exit-custom-x-wrapper" style="<?php echo $x_position === 'custom' ? '' : 'display:none;'; ?>">
                                <label for="smart_exit_image_x_custom"><strong>Custom X Offset (rem):</strong></label><br />
                                <input type="number" step="0.1" name="smart_exit_image_x_custom" value="<?php echo esc_attr(get_option('smart_exit_image_x_custom', 0)); ?>" style="width:80px;" />
                            </p>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <fieldset style="border: 1px solid #ccc; padding: 15px;">
                            <legend style="font-weight: bold;">Footer Bar Options</legend>

                            <p>
                                <label>
                                    <input type="checkbox" name="smart_exit_show_footer" id="smart_exit_show_footer" value="1" <?php checked(1, get_option('smart_exit_show_footer', 0)); ?> />
                                    Enable footer bar at the bottom of the popup
                                </label>
                            </p>

                            <p>
                                <label for="smart_exit_footer_bg_color"><strong>Footer Background Color</strong></label><br />
                                <input type="text" name="smart_exit_footer_bg_color" id="smart_exit_footer_bg_color"
                                    value="<?php echo esc_attr(get_option('smart_exit_footer_bg_color', '#191970')); ?>"
                                    class="regular-text wp-color-picker" />
                            </p>

                            <p>
                                <label for="smart_exit_footer_text_color_mode"><strong>Footer Text Color Mode</strong></label><br />
                                <select name="smart_exit_footer_text_color_mode" id="smart_exit_footer_text_color_mode">
                                    <option value="auto" <?php selected(get_option('smart_exit_footer_text_color_mode', 'auto'), 'auto'); ?>>Auto (adjusts contrast)</option>
                                    <option value="custom" <?php selected(get_option('smart_exit_footer_text_color_mode', 'auto'), 'custom'); ?>>Custom</option>
                                </select>
                            </p>

                            <p id="smart-exit-footer-text-color-wrapper" style="<?php echo (get_option('smart_exit_footer_text_color_mode', 'auto') === 'custom') ? '' : 'display:none;'; ?>">
                                <label for="smart_exit_footer_text_color"><strong>Custom Footer Text Color</strong></label><br />
                                <input type="text" name="smart_exit_footer_text_color" id="smart_exit_footer_text_color"
                                    value="<?php echo esc_attr(get_option('smart_exit_footer_text_color', '#ffffff')); ?>" class="regular-text wp-color-picker" />
                            </p>

                            <p>
                                <label for="smart_exit_footer_height_rem"><strong>Footer Height (rem)</strong></label><br />
                                <?php
                                    $footer_height = get_option('smart_exit_footer_height_rem', '');
                                    $footer_height = ($footer_height === '' || $footer_height === null) ? 3 : $footer_height;
                                ?>
                                <input type="number" step="0.1" name="smart_exit_footer_height_rem"
                                    value="<?php echo esc_attr($footer_height); ?>" style="width:80px;" />
                                <span class="description">Default is 3 rem.</span>
                                <span class="description">Leave blank for default (3rem)</span>
                            </p>
                            
                            <br/>
                            <p>
                                <label><strong>Enable Popup Dismissal</strong></label><br/>
                                    <input type="checkbox" name="smart_exit_enable_dismiss_option" id="smart_exit_enable_dismiss_option" value="1" <?php checked(1, get_option('smart_exit_enable_dismiss_option', 0)); ?> />
                                    Show "Don't show this again" checkbox
                                </label>
                            </p>

                            <br/>
                            <p>
                                <label><strong>Reset Popup Dismissal</strong></label><br/>
                                <button type="button" class="button" id="smart-exit-reset-dismissal">Reset “Don’t Show Again”</button>
                                <br/><span class="description">Clears localStorage for this feature in your browser - for testing.</span>
                            </p>
                            
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <div id="smart-exit-privacy-toggle" style="margin-bottom: 10px;">
                            <h4 style="font-weight:bold; font-size: 1.05rem;">Privacy & Compliance</h4>
                            <a href="#" id="toggle-privacy-info" style="text-decoration: none; font-weight: bold;">
                                🔒 Show Privacy & Compliance Info
                            </a>
                        </div>

                        <div id="smart-exit-privacy-details" style="display: none; border: 1px solid #ccc; padding: 15px; background: #f9f9f9;">
                            <p><strong>Smart Exit Popup is privacy-friendly by design.</strong></p>
                            <ul style="margin-left: 20px; list-style-type: disc;">
                                <li>No cookies are used</li>
                                <li>No personal data is collected or stored</li>
                                <li>"Don't show again" is saved using <code>localStorage</code></li>
                                <li>No third-party services or tracking are involved</li>
                                <li>Compliant with GDPR, CCPA, and other major regulations</li>
                            </ul>

                            <p><strong>Optional privacy policy text (for your privacy policy):</strong></p>
                            <textarea readonly rows="4" style="width: 100%; font-family: monospace;">
We use a cookieless popup to display important messages when users attempt to leave the site. If enabled, a non-personal preference is saved to your browser using localStorage to remember your choice not to see the popup again. No personal data is collected or shared.
                            </textarea>
                            <p style="font-size: 11px; color: #555;">You may copy and paste this into your site's Privacy Policy.</p>
                        </div>
                    </td>
                </tr>
                <!--
                <tr style="display:none;"><td><input type="hidden" name="redirect_to" value="admin.php?page=smart-exit-popup"></td></tr>
                -->
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Create assets folder if it doesn't exist
function smart_exit_popup_create_assets_folder() {
    $dir = plugin_dir_path(__FILE__) . 'assets';
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}
register_activation_hook(__FILE__, 'smart_exit_popup_create_assets_folder');
