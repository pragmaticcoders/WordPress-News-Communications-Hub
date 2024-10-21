<?php 
/**
 * Checks if a specified plugin is active.
 *
 * This function verifies whether a given plugin is active by checking
 * its file path. If the `is_plugin_active` function is not available, 
 * it includes the necessary file from the WordPress admin.
 *
 * @param string $plugin_file The file path of the plugin to check (e.g., 'plugin-directory/plugin-file.php').
 * @return bool True if the plugin is active, false otherwise.
 */
function is_plugin_enabled($plugin_file) {
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    return is_plugin_active($plugin_file);
}

/**
 * Retrieves the available icons from Elementor.
 *
 * This function first checks if Elementor is loaded. If so, it enqueues
 * the necessary Font Awesome stylesheets. Then, it retrieves the icon
 * options from Elementor's controls manager.
 *
 * @return array An array of available icons for use in the icon picker.
 */
function get_elementor_icons() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        return [];
    } else {
        wp_enqueue_style(
            'elementor-font-awesome-solid',
            plugins_url( 'elementor/assets/lib/font-awesome/css/solid.min.css', WP_PLUGIN_DIR . '/elementor/'),
            array('elementor-font-awesome'),
            '6.6.1'
        );
        wp_enqueue_style(
            'elementor-font-awesome-regular',
            plugins_url( 'elementor/assets/lib/font-awesome/css/regular.min.css', WP_PLUGIN_DIR . '/elementor/'),
            array('elementor-font-awesome'),
            '6.6.1'
        );
        wp_enqueue_style(
            'elementor-font-awesome-brands',
            plugins_url( 'elementor/assets/lib/font-awesome/css/brands.min.css', WP_PLUGIN_DIR . '/elementor/'),
            array('elementor-font-awesome'),
            '6.6.1'
        );
        wp_enqueue_style(
            'elementor-font-awesome',
            plugins_url( 'elementor/assets/lib/font-awesome/css/fontawesome.min.css', WP_PLUGIN_DIR . '/elementor/'),
            array(),
            '6.6.1'
        );
    }

    $controls_manager = \Elementor\Plugin::$instance->controls_manager;
    $icons = $controls_manager->get_control( 'icon' )->get_settings( 'options' );
    return $icons;
}


/**
 * Displays an icon picker interface for selecting icons.
 *
 * @param string $name The name attribute for the hidden input field that stores the selected icon.
 * @param string $selected_icon The currently selected icon class (default is 'dashicons dashicons-info-outline').
 * @return void
 */
function display_icon_picker($name, $selected_icon) {
    $icons = is_plugin_enabled('elementor/elementor.php') ? get_elementor_icons() : array();
    $dashicons = apply_filters('nch_dashicons', array(
        'dashicons-menu', 'dashicons-dashboard', 'dashicons-admin-site', 'dashicons-admin-media',
        'dashicons-admin-page', 'dashicons-admin-comments', 'dashicons-admin-appearance',
        'dashicons-admin-plugins', 'dashicons-admin-users', 'dashicons-admin-tools',
        'dashicons-admin-settings', 'dashicons-admin-network', 'dashicons-admin-generic',
        'dashicons-admin-home', 'dashicons-admin-collapse', 'dashicons-admin-links',
        'dashicons-admin-post', 'dashicons-format-standard', 'dashicons-format-image',
        'dashicons-format-gallery', 'dashicons-format-audio', 'dashicons-format-video',
        'dashicons-format-links', 'dashicons-format-chat', 'dashicons-format-status',
        'dashicons-format-aside', 'dashicons-format-quote', 'dashicons-welcome-write-blog',
        'dashicons-welcome-edit-page', 'dashicons-welcome-add-page', 'dashicons-welcome-view-site',
        'dashicons-welcome-widgets-menus', 'dashicons-welcome-comments', 'dashicons-welcome-learn-more',
        'dashicons-image-crop', 'dashicons-image-rotate-left', 'dashicons-image-rotate-right',
        'dashicons-image-flip-vertical', 'dashicons-image-flip-horizontal', 'dashicons-undo',
        'dashicons-redo', 'dashicons-editor-bold', 'dashicons-editor-italic', 'dashicons-editor-ul',
        'dashicons-editor-ol', 'dashicons-editor-quote', 'dashicons-editor-alignleft',
        'dashicons-editor-aligncenter', 'dashicons-editor-alignright', 'dashicons-editor-insertmore',
        'dashicons-editor-spellcheck', 'dashicons-editor-distractionfree', 'dashicons-editor-expand',
        'dashicons-editor-contract', 'dashicons-editor-kitchensink', 'dashicons-editor-underline',
        'dashicons-editor-justify', 'dashicons-editor-textcolor', 'dashicons-editor-paste-word',
        'dashicons-editor-paste-text', 'dashicons-editor-removeformatting', 'dashicons-editor-video',
        'dashicons-editor-customchar', 'dashicons-editor-outdent', 'dashicons-editor-indent',
        'dashicons-editor-help', 'dashicons-editor-strikethrough', 'dashicons-editor-unlink',
        'dashicons-editor-rtl', 'dashicons-editor-break', 'dashicons-editor-code',
        'dashicons-editor-paragraph', 'dashicons-align-left', 'dashicons-align-right',
        'dashicons-align-center', 'dashicons-align-none', 'dashicons-lock', 'dashicons-calendar',
        'dashicons-visibility', 'dashicons-post-status', 'dashicons-edit', 'dashicons-post-trash',
        'dashicons-trash', 'dashicons-external', 'dashicons-arrow-up', 'dashicons-arrow-down',
        'dashicons-arrow-left', 'dashicons-arrow-right', 'dashicons-arrow-up-alt',
        'dashicons-arrow-down-alt', 'dashicons-arrow-left-alt', 'dashicons-arrow-right-alt',
        'dashicons-arrow-up-alt2', 'dashicons-arrow-down-alt2', 'dashicons-arrow-left-alt2',
        'dashicons-arrow-right-alt2', 'dashicons-leftright', 'dashicons-sort', 'dashicons-randomize',
        'dashicons-list-view', 'dashicons-exerpt-view', 'dashicons-hammer', 'dashicons-art',
        'dashicons-migrate', 'dashicons-performance', 'dashicons-universal-access',
        'dashicons-universal-access-alt', 'dashicons-tickets', 'dashicons-nametag', 'dashicons-clipboard',
        'dashicons-heart', 'dashicons-megaphone', 'dashicons-schedule', 'dashicons-wordpress',
        'dashicons-wordpress-alt', 'dashicons-pressthis', 'dashicons-update', 'dashicons-screenoptions',
        'dashicons-info', 'dashicons-cart', 'dashicons-feedback', 'dashicons-cloud',
        'dashicons-translation', 'dashicons-tag', 'dashicons-category', 'dashicons-archive',
        'dashicons-tagcloud', 'dashicons-text', 'dashicons-media-archive', 'dashicons-media-audio',
        'dashicons-media-code', 'dashicons-media-default', 'dashicons-media-document',
        'dashicons-media-interactive', 'dashicons-media-spreadsheet', 'dashicons-media-text',
        'dashicons-media-video', 'dashicons-playlist-audio', 'dashicons-playlist-video', 'dashicons-yes',
        'dashicons-no', 'dashicons-no-alt', 'dashicons-plus', 'dashicons-plus-alt', 'dashicons-minus',
        'dashicons-dismiss', 'dashicons-marker', 'dashicons-star-filled', 'dashicons-star-half',
        'dashicons-star-empty', 'dashicons-flag', 'dashicons-share', 'dashicons-share1',
        'dashicons-share-alt', 'dashicons-share-alt2', 'dashicons-twitter', 'dashicons-rss',
        'dashicons-email', 'dashicons-email-alt', 'dashicons-facebook', 'dashicons-facebook-alt',
        'dashicons-networking', 'dashicons-googleplus', 'dashicons-location', 'dashicons-location-alt',
        'dashicons-camera', 'dashicons-images-alt', 'dashicons-images-alt2', 'dashicons-video-alt',
        'dashicons-video-alt2', 'dashicons-video-alt3', 'dashicons-vault', 'dashicons-shield',
        'dashicons-shield-alt', 'dashicons-sos', 'dashicons-search', 'dashicons-slides', 'dashicons-analytics',
        'dashicons-chart-pie', 'dashicons-chart-bar', 'dashicons-chart-line', 'dashicons-chart-area',
        'dashicons-groups', 'dashicons-businessman', 'dashicons-id', 'dashicons-id-alt', 'dashicons-products',
        'dashicons-awards', 'dashicons-forms', 'dashicons-testimonial', 'dashicons-portfolio', 'dashicons-book',
        'dashicons-book-alt', 'dashicons-download', 'dashicons-upload', 'dashicons-backup', 'dashicons-clock',
        'dashicons-lightbulb', 'dashicons-microphone', 'dashicons-desktop', 'dashicons-tablet',
        'dashicons-smartphone', 'dashicons-smiley'
    ));

    if (empty($selected_icon)) {
        $selected_icon = 'dashicons dashicons-info-outline';
    }

    $selected_label = isset($icons[$selected_icon]) ? $icons[$selected_icon] : '';
    if (strpos($selected_icon, 'dashicons') !== false) {
        $selected_label = str_replace('dashicons ', '', $selected_icon);
    }

    ?>
<div class="icon-picker-select-wrapper">
    <div class="icon-picker-select">
        <div class="icon-picker-select-trigger">
            <span class="icon-preview"><i class="<?php echo esc_attr($selected_icon); ?>"></i></span>
            <span class="selected-icon-label">
                <?php echo esc_html($selected_label); ?>
            </span>
        </div>

        <div class="icon-picker-options" style="display: none;">
            <div class="icon-picker-search">
                <input type="text" placeholder="Search for icon..." />
            </div>

            <?php if (is_plugin_active('elementor/elementor.php')): ?>
                <h4>Elementor</h4>
                <?php foreach ($icons as $icon_key => $icon_label): ?>
                    <span class="icon-picker-option" data-value="<?php echo esc_attr($icon_key); ?>" data-label="<?php echo esc_attr($icon_label); ?>">
                        <i class="<?php echo esc_attr($icon_key); ?>"></i>
                        <?php echo esc_html($icon_label); ?>
                    </span>
                <?php endforeach; ?>
            <?php endif; ?>

            <h4>WordPress</h4>
            <?php foreach ($dashicons as $icon_key): ?>
                <span class="icon-picker-option" data-value="dashicons <?php echo esc_attr($icon_key); ?>" data-label="<?php echo esc_attr(ucfirst(str_replace('dashicons-', '', $icon_key))); ?>">
                    <i class="dashicons <?php echo esc_attr($icon_key); ?>"></i>
                    <?php echo esc_html(ucfirst(str_replace('dashicons-', '', $icon_key))); ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($selected_icon); ?>" class="hidden-select">
</div>
    <?php
}
