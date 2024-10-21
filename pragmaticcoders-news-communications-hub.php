<?php
/*
Plugin Name: Pragmatic Coders News Communications Hub
Description: Flexible WordPress plugin that allows you to display news notifications and updates on your website
Version: 1.0
Author: <a href="https://www.pragmaticcoders.com/">Pragmatic Coders</a>, <a href="https://www.elsero.pl">ELSERO</a>
Requires PHP: 7.0
*/

defined("ABSPATH") || exit();

define("NCH_VERSION", "1.0");
if (!defined("PLUGIN_URL")) {
    define("PLUGIN_URL", plugin_dir_url(__FILE__));
}

if (!defined("PLUGIN_PATH")) {
    define("PLUGIN_PATH", plugin_dir_path(__FILE__));
}

require_once PLUGIN_PATH . "admin/nch.php";
require_once PLUGIN_PATH . "includes/functions.php";

$lifetime_localstorage = get_option("nch_localstorage_lifetime", 28);
$lifetime_transient_key = get_option("nch_transient_key_lifetime", 28);
$lifetime_localstorage_first_visit = get_option(
    "nch_localstorage_first_visit_lifetime",
    1
);
$lifetime_transient_key_first_visit = get_option(
    "nch_transient_key_first_visit_lifetime",
    7
);


/**
 * Enqueues the JavaScript required for the plugin.
 *
 * This function adds the JavaScript file and localizes necessary data
 * for use within that script.
 *
 * @return void
 */
function nch_enqueue_scripts()
{
    global $lifetime_localstorage,
        $lifetime_transient_key,
        $lifetime_localstorage_first_visit,
        $lifetime_transient_key_first_visit;

    $message_mark_all_as_read = get_option(
        "nch_mark_all_as_read",
        '<i class="dashicons dashicons-yes"></i> <span>Mark all as read</span>'
    );
    $message_show_read_items = get_option(
        "nch_show_read_items",
        '<i class="dashicons dashicons-visibility"></i> <span>Show read items</span>'
    );
    $message_hide_read_items = get_option(
        "nch_hide_read_items",
        '<i class="dashicons dashicons-visibility"></i> <span>Hide read items</span>'
    );
    $message_all_read = get_option(
        "nch_all_read_message",
        '<i class="dashicons dashicons-smiley"></i> <span>Congrats, you are up to date!<span>'
    );
    $icon_read = get_option(
        "nch_read_icon",
        '<i class="dashicons dashicons-yes"></i>'
    );

    $show_pc_logo = get_option("nch_show_logo", 1);

    if (!wp_script_is("jquery", "enqueued")) {
        wp_enqueue_script("jquery");
    }

    wp_enqueue_script(
        "nch-script",
        PLUGIN_URL . "assets/js/nch-script.js",
        ["jquery"],
        "1.0.2",
        true
    );
    wp_localize_script("nch-script", "nch_data", [
        "localstorage_lifetime" => $lifetime_localstorage,
        "localstorage_first_visit_lifetime" => $lifetime_localstorage_first_visit,

        "message_mark_all_as_read" => $message_mark_all_as_read,
        "message_show_read_items" => $message_show_read_items,
        "message_hide_read_items" => $message_hide_read_items,
        "message_all_read" => $message_all_read,
        "icon_read" => $icon_read,

        "show_pc_logo" => $show_pc_logo,
        "plugin_url" => PLUGIN_URL,
    ]);
    wp_enqueue_style(
        "nch-style",
        PLUGIN_URL . "assets/css/nch-style.css",
        [],
        "1.0.0"
    );
}
add_action("wp_enqueue_scripts", "nch_enqueue_scripts");

/**
 * Logs error messages to a log file within the plugin's logs directory.
 *
 * This function creates the logs directory and log file if they do not exist,
 * and logs messages with the current timestamp.
 *
 * @param string $message The message to log.
 * @return void
 */
function pc_error_log($message)
{
    $plugin_dir = PLUGIN_PATH;
    $logs_dir = $plugin_dir . "logs";
    $log_file = $logs_dir . "/nch.log";
    $is_log_enabled = get_option("nch_enable_log", 0);

    if ($is_log_enabled) {
        $current_time = current_time("mysql");
        error_log("[$current_time] $message" . PHP_EOL, 3, $log_file);
    }
}

/**
 * Fetches posts and notifications based on the provided request.
 *
 * This function handles fetching posts and notifications,
 * caching them using WordPress transients and responding
 * to REST API requests.
 *
 * @param WP_REST_Request|null $request The REST API request object.
 * @return WP_REST_Response The response containing the fetched items.
 */
function nch_fetch_posts_and_notifications(WP_REST_Request $request = null)
{
    global $lifetime_localstorage,
        $lifetime_transient_key,
        $lifetime_localstorage_first_visit,
        $lifetime_transient_key_first_visit;

    pc_error_log("-------------");

    $is_first_visit =
        is_null($request) || !$request->get_param("nch_last_visit");
    pc_error_log("Is first visit: " . ($is_first_visit ? "Yes" : "No"));

    $transient_key = $is_first_visit
        ? "nch_first_visit_notifications"
        : "nch_notifications_" . md5($request->get_param("nch_last_visit"));
    pc_error_log("Transient key: " . $transient_key);

    $cached_data = get_transient($transient_key);
    if ($cached_data) {
        pc_error_log("Returning cached data.");
        return new WP_REST_Response(
            [
                "success" => true,
                "items" => $cached_data,
            ],
            200
        );
    }

    pc_error_log("No cached data found, proceeding to fetch new data.");

    $display_limit = $is_first_visit
        ? get_option("nch_first_visit_display_limit", 5)
        : get_option("nch_display_limit", 5);
    $post_types = $is_first_visit
        ? get_option("nch_first_visit_post_types", ["post"])
        : get_option("nch_post_types", ["post"]);
    $update_types = get_option("nch_update_types", ["new"]);

    $args = [
        "post_type" => $post_types,
        "posts_per_page" => $display_limit,
        "post_status" => "publish",
        "orderby" => "date",
        "order" => "DESC",
    ];

    if (!$is_first_visit) {
        $last_visit = sanitize_text_field(
            $request->get_param("nch_last_visit")
        );
        pc_error_log("Last visit: " . $last_visit);

        $last_visit_date = DateTime::createFromFormat("Y-m-d", $last_visit);
        if (!$last_visit_date) {
            pc_error_log("Invalid last visit date.");
            return new WP_REST_Response(
                [
                    "success" => false,
                    "message" => "Invalid last visit date.",
                ],
                400
            );
        }

        $args["date_query"] = [
            [
                "after" => $last_visit_date->format("Y-m-d"),
                "inclusive" => true,
            ],
        ];

        if (in_array("modified", $update_types)) {
            $args["date_query"][] = [
                "column" => "post_modified",
                "after" => $last_visit_date->format("Y-m-d"),
                "inclusive" => true,
            ];
        }
    }

    pc_error_log("Query arguments: " . print_r($args, true));

    $query = new WP_Query($args);
    $posts = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = [
                "id" => get_the_ID(),
                "title" => get_the_title(),
                "link" => get_permalink(),
                "tag" => nch_get_main_tag(get_the_ID()),
                "post_type_name" => get_post_type_object(get_post_type())
                    ->labels->name,
                "date" => get_the_date("Y-m-d H:i:s"),
                "icon" => nch_get_post_icon(get_post_type()),
            ];
        }
        pc_error_log("Fetched " . count($posts) . " posts.");
    } else {
        pc_error_log("No posts found.");
    }
    wp_reset_postdata();

    $notifications = get_option("nch_custom_notifications", []);
    pc_error_log("Fetched " . count($notifications) . " notifications.");

    $filtered_notifications = [];
    foreach ($notifications as $notification) {
        $notification["id"] = $notification["id"] ?? uniqid();
        $notification["date"] = $notification["date"] ?? date("Y-m-d H:i:s");
        $notification["link"] = $notification["link"] ?? "#";
        $notification["icon"] =
            $notification["icon"] ?? "eci pragmaticcoders-book-open";
        $notification["color"] = $notification["color"] ?? null;
        $notification["date_to"] = $notification["date_to"] ?? null;

        if ($notification["date_to"]) {
            $date_to = DateTime::createFromFormat(
                "Y-m-d H:i:s",
                $notification["date_to"]
            );
            if (!$date_to) {
                pc_error_log(
                    "Invalid date_to for notification ID " . $notification["id"]
                );
                continue;
            }

            if ($date_to < new DateTime()) {
                continue;
            }
        }

        if ($is_first_visit) {
            if (
                isset($notification["show_on_first_visit"]) &&
                $notification["show_on_first_visit"] == 1
            ) {
                $filtered_notifications[] = $notification;
            }
        } else {
            $notification_date = DateTime::createFromFormat(
                "Y-m-d H:i:s",
                $notification["date"]
            );
            if ($notification_date && $notification_date >= $last_visit_date) {
                $filtered_notifications[] = $notification;
            }
        }
    }

    if ($is_first_visit) {
        $filtered_notifications = array_slice(
            $filtered_notifications,
            0,
            $display_limit
        );
        pc_error_log(
            "Filtered down to " .
                count($filtered_notifications) .
                " notifications for first visit."
        );
    }

    $combined = array_merge($posts, $filtered_notifications);
    pc_error_log("Combined total items: " . count($combined));

    usort($combined, function ($a, $b) {
        return strtotime($b["date"]) - strtotime($a["date"]);
    });

    $combined = array_slice($combined, 0, $display_limit);
    pc_error_log("Final items to return: " . count($combined));

    set_transient(
        $transient_key,
        $combined,
        $is_first_visit
            ? $lifetime_transient_key_first_visit * DAY_IN_SECONDS
            : $lifetime_transient_key * DAY_IN_SECONDS
    );

    return new WP_REST_Response(
        [
            "success" => true,
            "items" => $combined,
        ],
        200
    );
}

/**
 * Initializes the REST API route for fetching notifications.
 *
 * This function registers a new REST route that allows clients to
 * fetch notifications through a GET request.
 *
 * @return void
 */
add_action("rest_api_init", function () {
    register_rest_route("nch/v1", "/notifications", [
        "methods" => "GET",
        "callback" => "nch_fetch_posts_and_notifications",
        "permission_callback" => "__return_true",
    ]);
});

/**
 * Generates data for the first visit.
 *
 * This function calls `nch_fetch_posts_and_notifications` to retrieve
 * posts and notifications for the first visit.
 *
 * @return void
 */
function nch_generate_first_visit_data()
{
    nch_fetch_posts_and_notifications();
}
add_action("nch_generate_first_visit_data", "nch_generate_first_visit_data");

/**
 * Registers the CRON job for generating first visit data.
 *
 * This function schedules the `nch_generate_first_visit_data`
 * event based on the selected frequency from the options.
 *
 * @return void
 */
function nch_register_cron_job()
{
    $timezone = get_option("timezone_string");
    $current_time = new DateTime("now", new DateTimeZone($timezone));

    if ($current_time->format("H") >= 9) {
        do_action("nch_generate_first_visit_data");
        $cron_job_frequency = get_option("nch_cron_frequency", "daily");

        if (!wp_next_scheduled("nch_generate_first_visit_data")) {
            wp_schedule_event(
                time() + 60,
                $cron_job_frequency,
                "nch_generate_first_visit_data"
            );
        }
    } else {
        $next_run = new DateTime("09:00", new DateTimeZone($timezone));

        if ($current_time > $next_run) {
            $next_run->modify("+1 day");
        }

        if (!wp_next_scheduled("nch_generate_first_visit_data")) {
            wp_schedule_event(
                $next_run->getTimestamp(),
                get_option("nch_cron_frequency", "daily"),
                "nch_generate_first_visit_data"
            );
        }
    }
}
add_action("wp", "nch_register_cron_job");

/**
 * Removes the scheduled CRON job on plugin deactivation.
 *
 * This function unschedules the `nch_generate_first_visit_data` event
 * if it exists.
 *
 * @return void
 */
function nch_remove_cron_job()
{
    $timestamp = wp_next_scheduled("nch_generate_first_visit_data");
    wp_unschedule_event($timestamp, "nch_generate_first_visit_data");
}
register_deactivation_hook(__FILE__, "nch_remove_cron_job");

/**
 * Retrieves the main tag for a given post.
 *
 * This function fetches the main tag for a post based on its ID.
 * It checks if the post type is 'post' or 'blog', and returns the
 * main blog tag if available. Otherwise, it returns the first
 * term of the custom taxonomy associated with the post.
 *
 * @param int $post_id The ID of the post.
 * @return string|null The name of the main tag or null if not found.
 */
function nch_get_main_tag($post_id)
{
    $post_type = get_post_type($post_id);

    if ($post_type == "post" || $post_type == "blog") {
        $main_tag_id = get_post_meta($post_id, "main_blog_tag", true);
        return $main_tag_id
            ? get_term_by("id", $main_tag_id, "blog_tag")->name
            : null;
    }

    $terms = get_the_terms($post_id, $post_type . "_tag");
    return !is_wp_error($terms) && !empty($terms) ? $terms[0]->name : null;
}

/**
 * Retrieves the icon associated with a specific post type.
 *
 * This function gets the icon class name for a given post type
 * from the options stored in the database. If no icon is found,
 * a default icon is returned.
 *
 * @param string $post_type The post type to retrieve the icon for.
 * @return string The icon class name.
 */
function nch_get_post_icon($post_type)
{
    $icons = get_option("nch_post_icons", []);
    return $icons[$post_type] ?? "eci pragmaticcoders-book-open";
}

/**
 * Generates the HTML output for the bell icon shortcode.
 *
 * This function defines a shortcode that displays a bell icon with
 * a notification count. The color of the icon can be customized
 * via shortcode attributes.
 *
 * @param array $atts Shortcode attributes, including 'color'.
 * @return string The HTML output for the shortcode.
 */
function nch_bell_icon_shortcode($atts)
{
    $atts = shortcode_atts(
        [
            "color" => "#00a571",
        ],
        $atts,
        "nch_bell_icon"
    );

    $color = esc_attr($atts["color"]);
    $list_position = get_option("nch_list_position", "left");
    $list_width = get_option("nch_list_width", "320");

    ob_start();
    ?>
    <div class="pragmaticcoders-nch">
        <div id="nch-bell-icon">
            <div id="nch-bell-button">
                <div class="icon" style="color: <?php echo $color; ?>;">
                    <?php echo file_get_contents(
                        PLUGIN_URL . "assets/img/bell.svg"
                    ); ?>
                </div>
                <span id="nch-notification-count" style="display: none;"></span>
            </div>
        </div>
        <div id="nch-notifications" style="display: none; min-width: <?php echo $list_width; ?>px; <?php echo $list_position === "left" ? "right" : "left"; ?>: -10px;">
            <div class="nch-info" style="display: none;"></div>
            <div class="notification-tools">
                <div class="action" id="mark-all-read"></div>
                <div class="action" data-action="" id="show-hide"></div>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
}
add_shortcode("pc-nch", "nch_bell_icon_shortcode");

/**
 * Registers the Pragmatic Coders NCH widget.
 *
 * This function registers a custom widget that displays the bell icon
 * and its associated notifications.
 *
 * @return void
 */
function pragmaticcoders_nch_register_widget()
{
    register_widget("PragmaticCoders_NCH_Widget");
}
add_action("widgets_init", "pragmaticcoders_nch_register_widget");

/**
 * Class representing the Pragmatic Coders widget.
 *
 * This class extends the WP_Widget class to create a custom widget
 * that shows the news communications hub bell icon.
 */
class PragmaticCoders_NCH_Widget extends WP_Widget
{
    /**
     * Constructor for the PragmaticCoders_NCH_Widget class.
     *
     * This initializes the widget with its ID, name, and description.
     */
    function __construct()
    {
        parent::__construct(
            "pragmaticcoders_nch_widget",
            __("Pragmatic Coders NCH", "pragmaticcoders"),
            [
                "description" => __(
                    "Show News Communications Hub bell icon",
                    "pragmaticcoders"
                ),
            ]
        );
    }

    /**
     * Outputs the widget settings form in the admin panel.
     *
     * This function displays the form for configuring the widget's title
     * and color in the WordPress admin.
     *
     * @param array $instance Current settings for the widget.
     * @return void
     */
    public function form($instance)
    {
        $title = !empty($instance["title"]) ? $instance["title"] : "";
        $color = !empty($instance["color"]) ? $instance["color"] : "#00a571";
        // Default color
        ?>
        <p>
            <label for="<?php echo $this->get_field_id(
                "title"
            ); ?>"><?php _e("Title:"); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id(
                "title"
            ); ?>" name="<?php echo $this->get_field_name("title"); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id(
                "color"
            ); ?>"><?php _e("Color (hex):"); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id(
                "color"
            ); ?>" name="<?php echo $this->get_field_name("color"); ?>" type="text" value="<?php echo esc_attr($color); ?>" placeholder="#ffffff">
        </p>
        <?php
    }

    /**
     * Processes the widget's options to be saved.
     *
     * This function sanitizes and saves the widget's title and color.
     *
     * @param array $new_instance New settings for the widget instance.
     * @param array $old_instance Old settings for the widget instance.
     * @return array Updated settings for the widget instance.
     */
    public function update($new_instance, $old_instance)
    {
        $instance = [];
        $instance["title"] = !empty($new_instance["title"])
            ? sanitize_text_field($new_instance["title"])
            : "";
        $instance["color"] = !empty($new_instance["color"])
            ? sanitize_text_field($new_instance["color"])
            : "#ffffff";
        return $instance;
    }

    /**
     * Outputs the content of the widget on the front end.
     *
     * This function displays the widget title and the bell icon
     * with the specified color.
     *
     * @param array $args Widget arguments.
     * @param array $instance Settings for the widget instance.
     * @return void
     */
    public function widget($args, $instance)
    {
        $title = apply_filters("widget_title", $instance["title"]);
        $color = !empty($instance["color"]) ? $instance["color"] : "#00a571"; // Default color

        echo $args["before_widget"];
        if (!empty($title)) {
            echo $args["before_title"] . $title . $args["after_title"];
        }

        echo '<div class="nch-widget">';
        echo do_shortcode('[pc-nch color="' . esc_attr($color) . '"]');
        echo "</div>";

        echo $args["after_widget"];
    }
}

/**
 * Displays a welcome message after the plugin is installed.
 *
 * @return void
 */
function nch_welcome_message()
{
    $logo_url = PLUGIN_URL . "assets/img/pragmaticcoders-logo.svg"; ?>
    <div class="notice notice-success is-dismissible">
        <div>
            <img src="<?php echo esc_url(
                $logo_url
            ); ?>" alt="Pragmatic Coders Logo" style="max-width: 32px; vertical-align: middle; margin-right: 10px;" />
            <h4 style="display: inline-block;"><?php _e(
                "Thank you for installing our plugin! We appreciate your support and hope you find it helpful.",
                "pragmaticcoders"
            ); ?></h4>
        </div>
        <p>
            <?php _e("Get started by", "pragmaticcoders"); ?>
            <a href="<?php echo admin_url(
                "admin.php?page=pragmaticcoders-nch&tab=general"
            ); ?>"><?php _e("changing settings", "pragmaticcoders"); ?></a>
            <?php _e("and", "pragmaticcoders"); ?> 
            <a href="<?php echo admin_url(
                "admin.php?page=pragmaticcoders-nch"
            ); ?>"><?php _e(
    "adding your first notification",
    "pragmaticcoders"
); ?></a>!
        </p>
    </div>
    <?php
}

/**
 * Activation hook to set the welcome message option to true.
 * This ensures the welcome message is displayed after plugin activation.
 *
 * @return void
 */
function nch_activation_hook()
{
    update_option("nch_show_welcome_message", true);
}
register_activation_hook(__FILE__, "nch_activation_hook");

/**
 * Checks if the welcome message should be displayed.
 * If the welcome message option is set, it adds the admin notice to display it,
 * and then deletes the option to prevent the message from being shown again.
 *
 * @return void
 */
function nch_check_welcome_message()
{
    if (get_option("nch_show_welcome_message")) {
        add_action("admin_notices", "nch_welcome_message");
        delete_option("nch_show_welcome_message");
    }
}
add_action("admin_init", "nch_check_welcome_message");
