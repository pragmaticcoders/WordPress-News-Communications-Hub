<?php

defined( 'ABSPATH' ) || exit;

/**
 * Enqueues admin styles.
 *
 * @return void
 */
function pc_admin_styles() {
    wp_enqueue_style( 'pc-admin-styles', PLUGIN_URL . 'assets/css/pc-styles.css' );
}
add_action( 'admin_enqueue_scripts', 'pc_admin_styles' );

/**
 * Enqueues admin scripts.
 *
 * @return void
 */
function pc_admin_scripts() {
    wp_enqueue_script( 'pc-admin-scripts', PLUGIN_URL . 'assets/js/pc-scripts.js', array('jquery'), null, true );
}
add_action( 'admin_enqueue_scripts', 'pc_admin_scripts' );

/**
 * Adds a custom menu item in the WordPress admin panel.
 *
 * @return void
 */
function pragmaticcoders_menu_item() {
    add_menu_page(
        'Pragmatic Coders',
        'Pragmatic Coders',
        'manage_options',
        'pragmaticcoders',
        'pragmaticcoders_main_page',
        "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNy4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iV2Fyc3R3YV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDI0IDI0IiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCAyNCAyNDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPg0KPHN0eWxlIHR5cGU9InRleHQvY3NzIj4NCgkuc3Qwe2ZpbGw6IzAwQTU3MTt9DQo8L3N0eWxlPg0KPHBhdGggY2xhc3M9InN0MCIgZD0iTTEwLjEsMjIuM2gtNlYxNGgxLjV2Ni44aDMuMXYtNy45SDQuMVYxLjdoOS4zYzQuMiwwLDcuNiwzLjYsNy42LDhjMCw0LjQtMy40LDgtNy42LDhoLTIuNXYtMS42aDIuNQ0KCWMzLjQsMCw2LjEtMi45LDYuMS02LjRjMC0zLjUtMi44LTYuNC02LjEtNi40SDUuNnY4LjJoM1Y1LjhoNC41YzEuOSwwLDMuNCwxLjYsMy40LDMuNXMtMS41LDMuNS0zLjQsMy41aC0zVjIyLjN6IE0xMC4xLDExLjRoMw0KCWMxLDAsMS45LTAuOSwxLjktMmMwLTEuMS0wLjktMi0xLjktMmgtM1YxMS40eiIvPg0KPC9zdmc+DQo=",
        59
    );

    add_action('admin_init', 'remove_pragmaticcoders_submenu');

}
add_action('admin_menu', 'pragmaticcoders_menu_item');

/**
 * Removes the submenu for the Pragmatic Coders menu first item.
 *
 * @return void
 */
function remove_pragmaticcoders_submenu() {
    remove_submenu_page('pragmaticcoders', 'pragmaticcoders');
}

/**
 * Displays the main page content for the Pragmatic Coders menu item.
 *
 * @return void
 */
function pragmaticcoders_main_page() {
    ?>
    <div class="wrap">
        <h2>Pragmatic Coders</h2>
    </div>
    <?php
}