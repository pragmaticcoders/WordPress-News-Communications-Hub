<?php

defined( 'ABSPATH' ) || exit;

require_once PLUGIN_PATH . 'admin/admin.php';

/**
 * Registers a submenu page for the News Communications Hub under the main menu 'pragmaticcoders'.
 *
 * @return void
 */
function pc_nch_menu() {
    add_submenu_page(
        'pragmaticcoders',
        'News Communications Hub',
        'News Communications Hub',
        'manage_options',
        'pragmaticcoders-nch',
        'pragmaticcoders_nch_page'
    );
}
add_action('admin_menu', 'pc_nch_menu');

/**
 * Enqueues the Dashicons style if it's not already enqueued.
 *
 * @return void
 */
function enqueue_dashicons_if_needed() {
    if (!wp_style_is('dashicons', 'enqueued')) {
        wp_enqueue_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'enqueue_dashicons_if_needed');

/**
 * Renders the main settings page for the News Communications Hub plugin.
 *
 * @return void
 */
function pragmaticcoders_nch_page() {

    if (!current_user_can('manage_options')) {
        return;
    }

    $is_log_enabled = get_option('nch_enable_log', 0);
    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'custom_notifications';

    ?>
    <div class="wrap">
        <h1><?php _e('News Communications Hub', 'pragmaticcoders'); ?> <small><?php echo NCH_VERSION; ?></small></h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=pragmaticcoders-nch&tab=custom_notifications" class="nav-tab <?php echo $active_tab === 'custom_notifications' ? 'nav-tab-active' : ''; ?>"><?php _e('Custom Notifications', 'pragmaticcoders'); ?></a>
            <a href="?page=pragmaticcoders-nch&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>"><?php _e('Settings', 'pragmaticcoders'); ?></a>
            <a href="?page=pragmaticcoders-nch&tab=cron" class="nav-tab <?php echo $active_tab === 'cron' ? 'nav-tab-active' : ''; ?>"><?php _e('CRON Settings', 'pragmaticcoders'); ?></a>
            <a href="?page=pragmaticcoders-nch&tab=transients" class="nav-tab <?php echo $active_tab === 'transients' ? 'nav-tab-active' : ''; ?>"><?php _e('Transients', 'pragmaticcoders'); ?></a>
        <?php if ($is_log_enabled): ?>
            <a href="?page=pragmaticcoders-nch&tab=log" class="nav-tab <?php echo $active_tab === 'log' ? 'nav-tab-active' : ''; ?>"><?php _e('Log', 'pragmaticcoders'); ?></a>
        <?php endif; ?>
            <a href="?page=pragmaticcoders-nch&tab=about" class="nav-tab <?php echo $active_tab === 'about' ? 'nav-tab-active' : ''; ?>"><?php _e('About', 'pragmaticcoders'); ?></a>
       
        </h2>

        <div class="tab-content">
            <?php
            switch ($active_tab) {
                case 'general':
                    echo '<form method="post" action="options.php">';
                    settings_fields('nch_general_group');
                    do_settings_sections('pragmaticcoders-nch-general');
                    submit_button();
                    echo '</form>';
                    break;

                case 'cron':
                    echo '<form method="post" action="options.php">';
                    settings_fields('nch_cron_group');
                    do_settings_sections('pragmaticcoders-nch-cron');
                    submit_button();
                    echo '</form>';
                    break;

                case 'transients':
                    ?>
                    <h3><?php _e('Manage First Visit Transients', 'pragmaticcoders'); ?></h3>

                    <form method="post" action="">
                        <p><?php _e('Regenerate the transient key that stores the last ' . get_option('nch_first_visit_display_limit', 5) . ' notifications for users visiting the site for the first time.', 'pragmaticcoders'); ?></p>
                        <input type="hidden" name="regenerate_transients" value="1" />
                        <?php submit_button(__('Regenerate First Visit Transients', 'pragmaticcoders'), 'primary'); ?>
                    </form>
                  

                    <?php
                    if (!current_user_can('manage_options')) {
                        return;
                    }

                    $transients = [
                        'nch_first_visit_notifications',
                        'timeout_nch_first_visit_notifications',
                    ];

                    if (isset($_POST['regenerate_transients'])) {
                        require_once plugin_dir_path(__FILE__) . '../pragmaticcoders-news-communications-hub.php';
                        
                        pc_error_log("--- * FIRST VISIT TRANSIENTS REGENERATION * ---");
                        
                        foreach ($transients as $transient) {
                            if (get_transient($transient)) {
                                delete_transient($transient);
                                pc_error_log("Old First visit transients removed");
                            }
                        }

                        nch_generate_first_visit_data();
                        pc_error_log("--- * ---");
                        echo '<div class="notice notice-success is-dismissible"><p>First visit transients have been regenerated.</p></div>';
                    }
                    break;


                case 'log':
                    if ($is_log_enabled) {
                        echo '<h2>' . __('Log', 'pragmaticcoders') . '</h2>';
                        nch_display_log();
                    }
                    break;

                case 'about':
                    ?>
                    <?php
                        nch_about_page();
                    break;

                case 'custom_notifications':
                    default:
                    echo '<form method="post" action="options.php">';
                    settings_fields('nch_custom_notifications_group');
                    do_settings_sections('pragmaticcoders-nch-custom_notifications');
                    submit_button();
                    echo '</form>';
                    break;
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Registers settings for different sections including general, custom notifications, CRON settings, and appearance options.
 *
 * @return void
 */
function nch_register_settings() {
    // General group
    register_setting('nch_general_group', 'nch_post_types');
    register_setting('nch_general_group', 'nch_update_types');
    register_setting('nch_general_group', 'nch_display_limit');
    register_setting('nch_general_group', 'nch_first_visit_display_limit');
    register_setting('nch_general_group', 'nch_first_visit_post_types');
    register_setting('nch_general_group', 'nch_localstorage_lifetime');
    register_setting('nch_general_group', 'nch_localstorage_first_visit_lifetime');
    register_setting('nch_general_group', 'nch_transient_key_lifetime');
    register_setting('nch_general_group', 'nch_transient_key_first_visit_lifetime');
    register_setting('nch_general_group', 'nch_post_icons');
    register_setting('nch_general_group', 'nch_list_position');
    register_setting('nch_general_group', 'nch_list_width');
    register_setting('nch_general_group', 'nch_enable_log');
    register_setting('nch_general_group', 'nch_read_icon');
    register_setting('nch_general_group', 'nch_show_logo');
    register_setting('nch_general_group', 'nch_mark_all_as_read');
    register_setting('nch_general_group', 'nch_show_read_items');
    register_setting('nch_general_group', 'nch_hide_read_items');
    register_setting('nch_general_group', 'nch_all_read_message');

    // Custom Notifications group
    register_setting('nch_custom_notifications_group', 'nch_custom_notifications', 'nch_save_custom_notifications');

    // CRON group
    register_setting('nch_cron_group', 'nch_cron_frequency');

    // Section General
    add_settings_section('nch_general_section', __('General Settings', 'pragmaticcoders'), null, 'pragmaticcoders-nch-general');
    add_settings_field('nch_update_types', __('Update types to track', 'pragmaticcoders'), 'nch_update_types_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_post_types', __('Post types to track', 'pragmaticcoders'), 'nch_post_types_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_first_visit_post_types', __('Post types to track for first visit', 'pragmaticcoders'), 'nch_first_visit_post_types_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_display_limit', __('Number of notifications', 'pragmaticcoders'), 'nch_display_limit_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_first_visit_display_limit', __('Number of notifications on first visit', 'pragmaticcoders'), 'nch_first_visit_display_limit_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_localstorage_lifetime', __('LocalStorage Lifetime', 'pragmaticcoders'), 'nch_localstorage_lifetime_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_localstorage_first_visit_lifetime', __('First visit LocalStorage Lifetime', 'pragmaticcoders'), 'nch_localstorage_first_visit_lifetime_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_transient_key_lifetime', __('Transient Key Lifetime', 'pragmaticcoders'), 'nch_transient_key_lifetime_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_transient_key_first_visit_lifetime', __('First visit Transient Key Lifetime', 'pragmaticcoders'), 'nch_transient_key_first_visit_lifetime_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_field('nch_enable_log', __('Enable Log', 'pragmaticcoders'), 'nch_enable_log_callback', 'pragmaticcoders-nch-general', 'nch_general_section');
    add_settings_section('nch_appearance_section', __('Appearance', 'pragmaticcoders'), null, 'pragmaticcoders-nch-general');
    add_settings_field('nch_list_position', __('List position', 'pragmaticcoders'), 'nch_list_position_callback', 'pragmaticcoders-nch-general', 'nch_appearance_section');
    add_settings_field('nch_list_width', __('List width (px)', 'pragmaticcoders'), 'nch_list_width_callback', 'pragmaticcoders-nch-general', 'nch_appearance_section');
    add_settings_section('nch_icons_section', __('Icons', 'pragmaticcoders'), null, 'pragmaticcoders-nch-general');
    add_settings_field('nch_post_icons', __('Post Type Icons', 'pragmaticcoders'), 'nch_post_icons_callback', 'pragmaticcoders-nch-general', 'nch_icons_section');
    add_settings_section( 'nch_messages_section', __('Button Labels and Messages', 'pragmaticcoders'), null, 'pragmaticcoders-nch-general' );
    add_settings_field( 'nch_read_icon', __('Read icon', 'pragmaticcoders'), 'nch_read_icon_callback', 'pragmaticcoders-nch-general', 'nch_messages_section' );
    add_settings_field( 'nch_mark_all_as_read', __('Mark All as Read Button', 'pragmaticcoders'), 'nch_mark_all_as_read_callback', 'pragmaticcoders-nch-general', 'nch_messages_section' );
    add_settings_field( 'nch_show_read_items', __('Show Read Items Button', 'pragmaticcoders'), 'nch_show_read_items_callback', 'pragmaticcoders-nch-general', 'nch_messages_section' );
    add_settings_field( 'nch_hide_read_items', __('Hide Read Items Button', 'pragmaticcoders'), 'nch_hide_read_items_callback', 'pragmaticcoders-nch-general', 'nch_messages_section' );
    add_settings_field( 'nch_all_read_message', __('All notifications are read message', 'pragmaticcoders'), 'nch_all_read_message_callback', 'pragmaticcoders-nch-general', 'nch_messages_section' );
    add_settings_field( 'nch_show_logo', __('Show Pragmatic Coders Logo', 'pragmaticcoders'), 'nch_show_logo_callback', 'pragmaticcoders-nch-general', 'nch_messages_section' );

    // Section Custom Notifications
    add_settings_section('nch_custom_notifications_section', __('Custom Notifications', 'pragmaticcoders'), null, 'pragmaticcoders-nch-custom_notifications');
    add_settings_field('nch_custom_notifications', __('Notifications', 'pragmaticcoders'), 'nch_custom_notifications_callback', 'pragmaticcoders-nch-custom_notifications', 'nch_custom_notifications_section');

    // Section CRON
    add_settings_section('nch_cron_section', __('CRON Settings', 'pragmaticcoders'), null, 'pragmaticcoders-nch-cron');
    add_settings_field('nch_cron_frequency', __('First visit CRON Frequency', 'pragmaticcoders'), 'nch_cron_frequency_callback', 'pragmaticcoders-nch-cron', 'nch_cron_section');

}
add_action('admin_init', 'nch_register_settings');

/**
 * Displays a list of public post types as checkboxes for selection.
 *
 * @return void
 */
function nch_post_types_callback() {
    $post_types = get_option('nch_post_types', array());
    if (!is_array($post_types)) {
        $post_types = array(); 
    }

    $all_post_types = get_post_types(array('public' => true), 'objects');
    ?>
    <fieldset>
        <div class="admin-grid">
        <div class="row">
        <?php foreach ($all_post_types as $post_type): ?>
            <div class="col column-3 column-md-6">
            <label>
                <input type="checkbox" name="nch_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $post_types)); ?>>
                <?php echo esc_html($post_type->label); ?>
            </label>
            </div>
        <?php endforeach; ?>
        </div>
        </div>
    </fieldset>
    <?php
}

/**
 * Displays checkboxes for selecting update types (new and modified posts).
 *
 * @return void
 */
function nch_update_types_callback() {
    $update_types = get_option('nch_update_types', array('new', 'modified'));
    if (!is_array($update_types)) {
        $update_types = array();
    }    ?>
    <fieldset>
        <label>
            <input type="checkbox" name="nch_update_types[]" value="new" <?php checked(in_array('new', $update_types)); ?>>
            <?php _e('New Posts', 'pragmaticcoders'); ?>
        </label><br>
        <label>
            <input type="checkbox" name="nch_update_types[]" value="modified" <?php checked(in_array('modified', $update_types)); ?>>
            <?php _e('Modified Posts', 'pragmaticcoders'); ?>
        </label>
    </fieldset>
    <?php
}

/**
 * Displays a number input field for setting the display limit of notifications.
 *
 * @return void
 */
function nch_display_limit_callback() {
    $display_limit = get_option('nch_display_limit', 5);
    ?>
    <input type="number" name="nch_display_limit" value="<?php echo esc_attr($display_limit); ?>" min="1" max="30" />
    <p class="description"><?php _e('Enter the max. number of notifications to display (1-30).', 'pragmaticcoders'); ?></p>
    <?php
}

/**
 * Displays a number input field for setting the display limit of notifications on first visit.
 *
 * @return void
 */
function nch_first_visit_display_limit_callback() {
    $nch_first_visit_display_limit = get_option('nch_first_visit_display_limit', 5);
    ?>
    <input type="number" name="nch_first_visit_display_limit" value="<?php echo esc_attr($nch_first_visit_display_limit); ?>" min="1" max="30" />
    <p class="description"><?php _e('Enter the max. number of notifications to display on first visit (1-30).', 'pragmaticcoders') ?></p>
    <?php
}

/**
 * Displays a list of public post types as checkboxes for selection on first visit.
 * 
 * @return void
 */
function nch_first_visit_post_types_callback() {
    $first_visit_post_types = get_option('nch_first_visit_post_types', array());
    if (!is_array($first_visit_post_types)) {
        $first_visit_post_types = array();
    }
    $all_post_types = get_post_types(array('public' => true), 'objects');
    ?>
    <fieldset>
    <div class="admin-grid">
    <div class="row">
        <?php foreach ($all_post_types as $post_type): ?>
            <div class="col column-3 column-md-6">
                <label>
                <input type="checkbox" name="nch_first_visit_post_types[]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $first_visit_post_types)); ?>>
                <?php echo esc_html($post_type->label); ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
    </div>
    </fieldset>
    <?php
}

/**
 * Displays a form for managing custom notifications.
 * 
 * @return void
 */
function nch_custom_notifications_callback() {
    $notifications = get_option('nch_custom_notifications', array());
    $icons = is_plugin_active('elementor/elementor.php') ? get_elementor_icons() : array();

    ?>
    <div id="nch-notifications-container">
    <div id="admin-grid">
        <?php
        $custom_notification_index = 0;
        if ($notifications) {
            
            foreach ($notifications as $index => $notification) {
                if (!isset($notification['id'])) {
                    $notification['id'] = uniqid();
                }
                $custom_notification_index++;
                $date_value = isset($notification['date']) ? $notification['date'] : date('Y-m-d\TH:i');
                $date_to_value = isset($notification['date_to']) ? $notification['date_to'] : '';
                $show_on_first_visit_value = isset($notification['show_on_first_visit']) ? $notification['show_on_first_visit'] : '';
                $show_on_top_value = isset($notification['show_on_top']) ? $notification['show_on_top'] : '';
                $icon_value = isset($notification['icon']) ? $notification['icon'] : 'dashicons dashicons-book';
                $tags_value = isset($notification['tags']) ? implode(', ', $notification['tags']) : '';
                $color_value = isset($notification['color']) ? $notification['color'] : '#00a571';

                ?>
                <div class="nch-notification" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ccc;">
                    <input type="hidden" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][id]" value="<?php echo esc_attr($notification['id']); ?>" />
                    
                    <div class="row">
                    <div class="col column-10 column-md-11">
                        <div class="row">

                        <div class="col column-4 column-md-6">
                            <input type="text" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($notification['title']); ?>" placeholder="<?php _e('Title', 'pragmaticcoders') ?>" />
                        </div>
                        <div class="col column-4 column-md-6">
                            <input type="text" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][link]" value="<?php echo esc_url($notification['link']); ?>" placeholder="<?php _e('URL', 'pragmaticcoders'); ?>" />
                        </div>
                        <div class="col column-2 column-md-6">
                            <input type="datetime-local" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][datetime]" value="<?php echo esc_attr(date('Y-m-d\TH:i', strtotime($date_value))); ?>" />
                        </div>
                        <div class="col column-2 column-md-6">
                            <input type="datetime-local" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][date_to]" value="<?php echo esc_attr($date_to_value); ?>" placeholder="Valid Until" />
                        </div>

                        </div>

                        <div class="row">
                        <div class="col column-4 column-md-6">
                        <input type="color" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][color]" value="<?php echo esc_attr($color_value); ?>" style="height: 31px;display: inline-block;vertical-align: bottom; width: 20%;" />
                        <div style="width: calc(80% - 10px); display: inline-block;"><?php display_icon_picker("nch_custom_notifications[{$index}][icon]", $icon_value); ?></div>
                        </div>

                        <div class="col column-4 column-md-6">
                        <input type="text" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][tags]" value="<?php echo esc_attr($tags_value); ?>" placeholder="<?php _e('Tags (comma separated)','pragmaticcoders'); ?>" />
                        </div>
                        <div class="col column-4 column-md-12">
                        <label>
                            <input type="checkbox" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][show_on_first_visit]" value="1" <?php checked($show_on_first_visit_value, '1'); ?> />
                            <?php _e('Show on first visit', 'pragmaticcoders') ?>
                        </label>
                        &nbsp;
                        <label>
                            <input type="checkbox" name="nch_custom_notifications[<?php echo esc_attr($index); ?>][show_on_top]" value="1" <?php checked($show_on_top_value, '1'); ?> />
                            <?php _e('Show on top', 'pragmaticcoders') ?>
                        </label>
                        </div>
                        </div>
                        </div>
                        <div class="col column-2 column-md-1">
                        <button type="button" class="button nch-remove-notification" data-id="<?php echo esc_attr($notification['id']); ?>"><?php _e('Remove', 'pragmaticcoders') ?></button>
                        </div>
                    </div>
                </div>
            <?php }
        } else {
            echo '<em>' . __('No custom notifications', 'pragmaticcoders') . '</em>';
        }
        ?>
    </div>
    </div>
    <br/>
    <button type="button" class="button" id="nch-add-notification"><?php _e('Add Notification', 'pragmaticcoders') ?></button>
    <script>
    jQuery(document).ready(function($) {
        var container = $('#nch-notifications-container');
        var addButton = $('#nch-add-notification');
        
        <?php 
            $index = 0;
            $index = $custom_notification_index; 
        ?>
        
        addButton.on('click', function() {
            var index = container.find('.nch-notification').length;
            var now = new Date();
            var nowLocal = now.toISOString().slice(0, 16);
            var newNotificationId = 'nch_' + Date.now().toString(36) + Math.random().toString(36).substring(2, 15);

            var newNotification = `
                <div class="nch-notification new-notification ` + newNotificationId + `" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ccc;">
                    <input type="hidden" name="nch_custom_notifications[` + index + `][id]" value="` + newNotificationId + `" />
                    <div class="row">
                        <div class="col column-10 column-md-11">
                            <div class="row">
                                <div class="col column-4 column-md-6">
                                    <input type="text" name="nch_custom_notifications[` + index + `][title]" placeholder="<?php _e('Title','pragmaticcoders'); ?>" />
                                </div>
                                <div class="col column-4 column-md-6">
                                    <input type="text" name="nch_custom_notifications[` + index + `][link]" placeholder="<?php _e('URL','pragmaticcoders'); ?>" />
                                </div>
                                <div class="col column-2 column-md-6">
                                    <input type="datetime-local" name="nch_custom_notifications[` + index + `][datetime]" value="` + nowLocal + `" />
                                </div>
                                <div class="col column-2 column-md-6">
                                    <input type="datetime-local" name="nch_custom_notifications[` + index + `][date_to]" placeholder="" />
                                </div>
                            </div>
                            <div class="row">
                                <div class="col column-4 column-md-6">
                                    <input type="color" name="nch_custom_notifications[` + index + `][color]" value="#00a571" style="height: 31px; display: inline-block; vertical-align: bottom; width: 20%;" />
                                    <div style="width: calc(80% - 10px); display: inline-block;">
                                        <?php display_icon_picker("nch_custom_notifications[${index}][icon]", 'dashicons dashicons-book'); ?>
                                    </div>
                                </div>
                                <div class="col column-4 column-md-6">
                                    <input type="text" name="nch_custom_notifications[` + index + `][tags]" placeholder="<?php _e('Tags (comma separated)', 'pragmaticcoders'); ?>" />
                                </div>
                                <div class="col column-4 column-md-12">
                                    <label>
                                        <input type="checkbox" name="nch_custom_notifications[` + index + `][show_on_first_visit]" value="1" />
                                        <?php _e('Show on first visit', 'pragmaticcoders'); ?>
                                    </label>
                                    &nbsp;
                                    <label>
                                        <input type="checkbox" name="nch_custom_notifications[` + index + `][show_on_top]" value="1" />
                                        <?php _e('Show on top', 'pragmaticcoders'); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col column-2 column-md-1">
                            <button type="button" class="button nch-remove-notification" data-id="` + newNotificationId + `"><?php _e('Remove', 'pragmaticcoders'); ?></button>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(newNotification);
            initializeIconPicker( jQuery('.new-notification') );
        });

        container.on('click', '.nch-remove-notification', function() {
            $(this).closest('.nch-notification').remove();
        });
    });

    </script>
    <?php
}

/**
 * Saves custom notifications from the input to the database.
 *
 * @param array $input The input data for custom notifications.
 * @return array An array of sanitized and formatted notifications.
 */
function nch_save_custom_notifications($input) {
    $timezone_string = get_option('timezone_string', 'UTC');
    $timezone = new DateTimeZone($timezone_string);

    $notifications = array();
    if (isset($input) && is_array($input)) {
        foreach ($input as $notification) {
            if (isset($notification['id'], $notification['title'], $notification['link'], $notification['datetime'], $notification['icon'])) {
                $date = new DateTime($notification['datetime'], $timezone);
                $show_on_first_visit = isset($notification['show_on_first_visit']) && $notification['show_on_first_visit'] === '1' ? '1' : '0';
                $show_on_top = isset($notification['show_on_top']) && $notification['show_on_top'] === '1' ? '1' : '0';
                $icon = isset($notification['icon']) ? sanitize_text_field($notification['icon']) : 'eci pragmaticcoders-book-open';
                $color = isset($notification['color']) ? sanitize_text_field($notification['color']) : '#00a571';

                $date_to = !empty($notification['date_to']) ? (new DateTime($notification['date_to'], $timezone))->format('Y-m-d H:i:s') : null;

                if (isset($notification['tags']) && !empty($notification['tags'])) {
                    $tags = array_map('trim', explode(',', $notification['tags']));
                } else {
                    $tags = array();
                }

                $notifications[] = array(
                    'id' => sanitize_text_field($notification['id']),
                    'title' => sanitize_text_field($notification['title']),
                    'link' => esc_url_raw($notification['link']),
                    'date' => $date->format('Y-m-d H:i:s'),
                    'date_to' => $date_to,
                    'show_on_first_visit' => $show_on_first_visit,
                    'show_on_top' => $show_on_top,
                    'tags' => $tags,
                    'icon' => $icon,
                    'color' => $color
                );
            }
        }
    }
    return $notifications;
}

/**
 * Displays a dropdown for selecting the position of the notifications list.
 *
 * @return void
 */
function nch_list_position_callback() {
    $options = get_option('nch_list_position', 'right');
    ?>
    <select name="nch_list_position">
        <option value="left" <?php selected($options, 'left'); ?>><?php _e('Left', 'pragmaticcoders'); ?></option>
        <option value="right" <?php selected($options, 'right'); ?>><?php _e('Right', 'pragmaticcoders'); ?></option>
    </select>
    <p class="description"><?php _e('Choose whether the notifications list is aligned to the left or right of the bell icon.', 'pragmaticcoders'); ?></p>
    <?php
}

/**
 * Displays a number input field for setting the width of the notifications list.
 * 
 */
function nch_list_width_callback() {
    $width = get_option('nch_list_width', '320');
    ?>
    <input type="number" name="nch_list_width" min="200" value="<?php echo esc_attr($width); ?>" /> px
    <p class="description"><?php _e('Set the width of the notifications list in pixels.', 'pragmaticcoders'); ?></p>
    <?php
}

/**
 * Displays a form for managing icons for each public post type.
 *
 * @return void
 */
function nch_post_icons_callback() {
    $post_icons = get_option('nch_post_icons', array());
    $all_post_types = get_post_types(array('public' => true), 'objects');
    ?>
    <fieldset>
        <div class="admin-grid">
            <div class="row">
                <?php foreach ($all_post_types as $post_type): ?>
                    <div class="col column-4">
                        <label>
                            <?php echo esc_html($post_type->label); ?> <?php _e('Icon','pragmaticcoders'); ?>: <br>

                            <?php 
                                $selected_icon = $post_icons[$post_type->name] ?? 'dashicons dashicons-admin-post';
                                display_icon_picker("nch_post_icons[{$post_type->name}]", $selected_icon);
                            ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </fieldset>
    <?php
}

/**
 * Renders the input field for specifying the lifetime of the data stored in localStorage.
 *
 * @return void
 */
function nch_localstorage_lifetime_callback() {
    $localstorage_lifetime = get_option('nch_localstorage_lifetime', 28);
    ?>
    <input type="number" name="nch_localstorage_lifetime" value="<?php echo esc_attr($localstorage_lifetime); ?>" min="1" />
    <p class="description"><?php _e('Specify the lifetime of the data stored in localStorage in days.','pragmaticcoders'); ?></p>
    <?php
}

/**
 * Renders the input field for specifying the lifetime of the database transient key.
 *
 * @return void
 */
function nch_transient_key_lifetime_callback() {
    $transient_key_lifetime = get_option('nch_transient_key_lifetime', 28);
    ?>
    <input type="number" name="nch_transient_key_lifetime" value="<?php echo esc_attr($transient_key_lifetime); ?>" min="1" />
    <p class="description"><?php _e('Specify the lifetime of database transient key in days.','pragmaticcoders'); ?></p>
    <?php
}

/**
 * Renders the input field for specifying the first visit lifetime of the data stored in localStorage.
 *
 * @return void
 */
function nch_localstorage_first_visit_lifetime_callback() {
    $localstorage_first_visit_lifetime = get_option('nch_localstorage_first_visit_lifetime', 1);
    ?>
    <input type="number" name="nch_localstorage_first_visit_lifetime" value="<?php echo esc_attr($localstorage_first_visit_lifetime); ?>" min="1" />
    <p class="description"><?php _e('Specify the first visit lifetime of the data stored in localStorage in days.','pragmaticcoders'); ?></p>
    <?php
}

/**
 * Renders the input field for specifying the first visit lifetime of the database transient key.
 *
 * @return void
 */
function nch_transient_key_first_visit_lifetime_callback() {
    $transient_key_first_visit_lifetime = get_option('nch_transient_key_first_visit_lifetime', 7);
    ?>
    <input type="number" name="nch_transient_key_first_visit_lifetime" value="<?php echo esc_attr($transient_key_first_visit_lifetime); ?>" min="1" />
    <p class="description"><?php _e('Specify the first visit lifetime of database transient key in days.','pragmaticcoders'); ?></p>
    <?php
}

/**
 * Renders the dropdown menu for selecting how often the first visit news CRON job should run.
 *
 * @return void
 */
function nch_cron_frequency_callback() {
    $cron_frequency = get_option('nch_cron_frequency', 'daily');
    $frequencies = array(
        'hourly' => 'Hourly',
        'twicedaily' => 'Twice Daily',
        'daily' => 'Daily',
        'every_two_days' => 'Every 2 Days',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly'
    );
    ?>
    <select name="nch_cron_frequency">
        <?php foreach ($frequencies as $key => $label): ?>
            <option value="<?php echo esc_attr($key); ?>" <?php selected($cron_frequency, $key); ?>>
                <?php echo esc_html($label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <p class="description"><?php _e('Select how often the first visit news CRON job should run.','pragmaticcoders'); ?></p>
    <?php
}

/**
 * Renders the checkbox for enabling or disabling logging.
 *
 * @return void
 */
function nch_enable_log_callback() {
    $enable_log = get_option('nch_enable_log', 0);
    $plugin_dir = PLUGIN_PATH;
    $logs_dir = $plugin_dir . 'logs';
    $log_file = $logs_dir . '/nch.log';

    echo '<input type="checkbox" id="nch_enable_log" name="nch_enable_log" value="1" ' . checked(1, $enable_log, false) . '/>';
    echo '<label for="nch_enable_log"> ' . __('Enable log to display Log tab.', 'pragmaticcoders') . '</label>';

    if ($enable_log) {
        if (!file_exists($logs_dir)) {
            wp_mkdir_p($logs_dir);
        }

        if (!file_exists($log_file)) {
            file_put_contents($log_file, '');
        }

    } else {
        if (file_exists($log_file)) {
            unlink($log_file);
        }
    }
}

/**
 * Renders the icon picker for selecting the icon for read items.
 *
 * @return void
 */
function nch_read_icon_callback() {
    $value = get_option('nch_read_icon', 'dashicons dashicons-yes');
    $icon = $value ?? 'dashicons dashicons-yes';
    ?>
    <div class="nch-read-icon-wrapper">
        <div style="display: inline-block; min-width: 200px;">
            <?php 
            display_icon_picker("nch_read_icon", $icon);
            ?>
        </div>


    </div>
    <?php
}

/**
 * Renders the icon picker and text input for marking all items as read.
 *
 * @return void
 */
function nch_mark_all_as_read_callback() {
    $value = get_option('nch_mark_all_as_read', '<i class="dashicons dashicons-yes"></i> <span>Mark all as read</span>');
    $icon = '';
    $text = '';

    if (preg_match('/<i class="(.+?)"><\/i>\s*<span>(.+?)<\/span>/', $value, $matches)) {
        $icon = trim($matches[1]);
        $text = trim($matches[2]);
    }

    ?>
    <div class="nch-icon-text-wrapper">
        <div style="display: inline-block; min-width: 200px;">
            <?php display_icon_picker('nch_mark_all_as_read_icon', $icon); ?>
        </div>
        <input type="text" class="nch-icon-text" value="<?php echo esc_attr($text); ?>" placeholder="Mark all as read">
        <input type="hidden" class="nch-icon-hidden" name="nch_mark_all_as_read" value="<?php echo esc_attr($value); ?>" data-initial-icon="<?php echo esc_attr($icon); ?>">
    </div>
    <?php
}

/**
 * Renders the icon picker and text input for showing read items.
 *
 * @return void
 */
function nch_show_read_items_callback() {
    $value = get_option('nch_show_read_items', '<i class="dashicons dashicons-visibility"></i> <span>Show read items</span>');
    $icon = '';
    $text = '';

    if (preg_match('/<i class="(.+?)"><\/i>\s*<span>(.+?)<\/span>/', $value, $matches)) {
        $icon = trim($matches[1]);
        $text = trim($matches[2]);
    }
    ?>
    <div class="nch-icon-text-wrapper">
        <div style="display: inline-block; min-width: 200px;">
            <?php display_icon_picker('nch_show_read_items_icon', $icon); ?>
        </div>
        <input type="text" class="nch-icon-text" value="<?php echo esc_attr($text); ?>" placeholder="Show read items">
        <input type="hidden" class="nch-icon-hidden" name="nch_show_read_items" value="<?php echo esc_attr($value); ?>" data-initial-icon="<?php echo esc_attr($icon); ?>">
    </div>
    <?php
}

/**
 * Renders the icon picker and text input for hiding read items.
 *
 * @return void
 */
function nch_hide_read_items_callback() {
    $value = get_option('nch_hide_read_items', '<i class="dashicons dashicons-visibility"></i> <span>Hide read items</span>');
    $icon = '';
    $text = '';

    if (preg_match('/<i class="(.+?)"><\/i>\s*<span>(.+?)<\/span>/', $value, $matches)) {
        $icon = trim($matches[1]);
        $text = trim($matches[2]);
    }
    ?>
    <div class="nch-icon-text-wrapper">
        <div style="display: inline-block; min-width: 200px;">
            <?php display_icon_picker('nch_hide_read_items_icon', $icon); ?>
        </div>
        <input type="text" class="nch-icon-text" value="<?php echo esc_attr($text); ?>" placeholder="Hide read items">
        <input type="hidden" class="nch-icon-hidden" name="nch_hide_read_items" value="<?php echo esc_attr($value); ?>" data-initial-icon="<?php echo esc_attr($icon); ?>">
    </div>
    <?php
}

/**
 * Renders the icon picker and text input for the "all read" message.
 *
 * @return void
 */
function nch_all_read_message_callback() {
    $value = get_option('nch_all_read_message', '<i class="dashicons dashicons-smiley"></i> <span>Congrats, you are up to date! Now you can impress your friends with your cutting-edge knowledge.</span>');
    $icon = '';
    $text = '';

    if (preg_match('/<i class="(.+?)"><\/i>\s*<span>(.+?)<\/span>/', $value, $matches)) {
        $icon = trim($matches[1]);
        $text = trim($matches[2]);
    }
    ?>
    <div class="nch-icon-text-wrapper">
        <div style="display: inline-block; min-width: 200px;">
            <?php display_icon_picker('nch_all_read_message_icon', $icon); ?>
        </div>
        <input type="text" class="nch-icon-text" value="<?php echo esc_attr($text); ?>" placeholder="Congrats, you are up to date!">
        <input type="hidden" class="nch-icon-hidden" name="nch_all_read_message" value="<?php echo esc_attr($value); ?>" data-initial-icon="<?php echo esc_attr($icon); ?>">
    </div>
    <?php
}

/**
 * Renders the checkbox for showing or hiding the Pragmatic Coders logo.
 *
 * @return void
 */
function nch_show_logo_callback() {
    $value = get_option('nch_show_logo', 1);
    echo '<input type="checkbox" name="nch_show_logo" value="1" ' . checked(1, $value, false) . ' /> ' . __('Show Pragmatic Coders Logo', 'pragmaticcoders');
}

/**
 * Displays the contents of the log file, showing the last 1000 lines.
 *
 * @return void
 */
function nch_display_log() {
    $log_file = PLUGIN_PATH . 'logs/nch.log';

    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if (!empty($lines)) {
            $last_100_lines = array_slice($lines, -1000);
            $log_content = implode("\n", $last_100_lines);
        } else {
            $log_content = __('Log is empty.', 'pragmaticcoders');
        }
    } else {
        $log_content = __('Log file not found.', 'pragmaticcoders');
    }
    ?>
    <script>
        jQuery(document).ready(function(){
            var $textarea = jQuery('#nch-log');
            $textarea.scrollTop($textarea[0].scrollHeight);
        });
    </script>
    <?php
    echo '<textarea  id="nch-log" readonly rows="20" style="width:100%;">' . esc_textarea($log_content) . '</textarea>';
}

/**
 * Renders the "About" page for the Pragmatic Coders News Communications Hub.
 * 
 * @return void
 */
function nch_about_page() {
    ?>
    <h2>About Pragmatic Coders News Communications Hub <small>v <?php echo NCH_VERSION; ?></small></h2>

    <hr> 
    <h2>Overview</h2> 
    <p>The <strong>PragmaticCoders News Communications Hub</strong> is a flexible WordPress plugin designed to help you easily manage and display customizable notifications and news updates on your site. With robust admin options, Elementor icons integration, and flexible styling, this plugin provides all the tools you need to keep users informed and engaged. </p>
    <h2>Key Features</h2>
    <ul>
      <li>
        <strong>Custom Notifications</strong>: Add, edit, and manage notifications directly from the WordPress admin interface. You can display any type of post or page, including custom post types.
      </li>
      <li>
        <strong>Post Type Selection</strong>: Choose which post types (posts, pages, or custom types) you want to feature in your notifications.
      </li>
      <li>
        <strong>Custom Dates</strong>: Each custom notification can have specific start and end dates, allowing for time-sensitive announcements. This feature is ideal for promotions, events, or temporary updates.
      </li>
      <li>
        <strong>First-Time Visitor Notifications</strong>: Separate custom notifications into those that are visible for first-time visitors and those that appear for subsequent visits, enhancing user experience by presenting relevant information based on user behavior.
      </li>
      <li>
        <strong>Icon Support</strong>: Use Elementor's icon library or WordPress Dashicons to select icons for your notifications.
      </li>
      <li>
        <strong>Widgets and Shortcodes</strong>: Display notifications easily with the included widget or using the shortcode <code>[pc-nch]</code>.
      </li>
      <li>
        <strong>Notification Log</strong>: Log important events, errors, and other data to a log file located in <code>/logs/nch.log</code> for debugging and tracking purposes.
      </li>
      <li>
        <strong>Customizable Styling</strong>: Modify the look and feel of notifications via the CSS files located in the <code>assets/css</code> folder, or add your own custom styles.
      </li>
      <li>
        <strong>Optimized Code</strong>: The plugin is designed to minimize database queries and improve performance. Efficient queries and caching mechanisms are used to ensure optimal performance, reducing load times and server strain.
      </li>
    </ul>
    <h2>Usage</h2>
    <h3>Adding New Notifications</h3>
    <p>After activating the plugin, you can add new notifications through the <strong>News Communications Hub</strong> admin menu: </p>
    <ul>
      <li>
        <strong>Notification Title</strong>: Set the title that will appear in the notification bar.
      </li>
      <li>
        <strong>Post Type Selection</strong>: Choose which post types (e.g., posts, pages, or any custom types) should appear in the notification. You can select multiple post types.
      </li>
      <li>
        <strong>Custom Dates</strong>: Specify start and end dates for each notification, allowing for precise control over when notifications are visible to users.
      </li>
      <li>
        <strong>First-Time Visitor Options</strong>: Choose whether a notification should be visible only for first-time visitors or for all subsequent visits.
      </li>
      <li>
        <strong>Icons</strong>: Select an icon for your notification from the Elementor icon library or use the default Dashicons from WordPress.
      </li>
    </ul>
    <h3>Displaying Notifications</h3>
    <ol>
      <li>
        <strong>Shortcode</strong>: Use the <code>[pc-nch]</code> shortcode to display the notification bar on any page or post.
      </li>
      <li>
        <strong>Widget</strong>: Alternatively, you can place the notification widget in any widgetized area of your theme (e.g., the sidebar or footer).
      </li>
      <li>
        <strong>Automatic Fetching</strong>: The plugin can automatically fetch the latest posts or custom post types and display them as notifications.
      </li>
    </ol>
    <h3>Managing Styles</h3>
    <p>All styles can be found in the <code>assets/css</code> folder. To modify the appearance of notifications, you can: </p>
    <ul>
      <li>Edit the provided CSS files.</li>
      <li>Override the styles in your theme by using custom CSS rules.</li>
    </ul>
    <h3>Notification Log</h3>
    <p>A log file is maintained at <code>logs/nch.log</code> to track important events such as errors, notification dispatches, and user interactions. This log can be useful for debugging issues or analyzing how notifications are being processed. </p>
    <h3>Optimized Database Queries</h3>
    <p>The plugin is built with performance in mind, utilizing optimized database queries to minimize the load on the server. By caching results when appropriate and reducing the number of queries executed per page load, the plugin ensures a seamless experience for users while conserving server resources.</p>
    <h2>Customization</h2>
    <ol>
      <li>
        <strong>Icons</strong>: Icons can be selected either from the Elementor icon library or WordPress Dashicons, allowing for rich visual customization.
      </li>
      <li>
        <strong>Post Type Inclusion</strong>: You can include any custom post types in the notifications, offering flexibility to display specific types of content.
      </li>
      <li>
        <strong>Shortcodes and Widgets</strong>: Whether through shortcodes or widgets, the plugin is designed to be easy to use and integrate with any WordPress theme.
      </li>
    </ol>
    <h2>FAQ</h2>
    <h4>Can I choose different post types for notifications?</h4>
    <p>Yes, you can select custom post types directly from the plugin settings to include posts, pages, or any other post type in your notifications.</p>
    <h4>Can I use icons from Elementor?</h4>
    <p>Yes, the plugin integrates with Elementor's icon library, allowing you to choose icons when creating notifications. You can also use WordPress's native Dashicons.</p>
    <h4>How do I add notifications to a page?</h4>
    <p>You can use the built-in widget or the provided shortcode <code>[pc-nch]</code> to display notifications on any page. </p>
    <h4>Can custom notifications have specific start and end dates?</h4>
    <p>Yes, you can set specific start and end dates for each custom notification, allowing for time-sensitive announcements.</p>
    <h4>Is there a way to differentiate notifications for first-time visitors?</h4>
    <p>Yes, you can create notifications that are visible only for first-time visitors, as well as those that appear on subsequent visits.</p>
    <h4>How is the code optimized for database queries?</h4>
    <p>The plugin utilizes efficient database queries to minimize overhead, caching results when possible, and reducing the number of queries executed per page load for optimal performance.</p>
    <?php
}