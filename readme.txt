=== Pragmatic Coders News Communications Hub ===
Contributors: Pragmatic Coders, ELSERO
Tags: news, notifications, communications, widget, shortcode, Elementor
Requires at least: 5.0
Tested up to: 6.3
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
The Pragmatic Coders News Communications Hub is a flexible WordPress plugin that allows you to display news notifications and updates on your website. With full integration for custom post types, Elementor support for icons, and customizable notification types, it's the perfect tool to keep your users informed and engaged.

**Key Features:**
- Add, edit, and delete notifications from the WordPress admin interface.
- Full support for selecting custom post types to display as notifications.
- Custom notifications can have specific start and end dates, allowing for time-sensitive announcements.
- Options to separate custom notifications into those visible for first-time visitors and subsequent visits.
- Integration with Elementor and WordPress icons for enhanced visual styling.
- Shortcodes and widgets for easy placement of notifications on any page.
- Option to log notification activity in a log file for debugging.
- Fully responsive design with customizable styles via CSS.
- Optimized code to minimize database queries and improve performance.

== Installation ==
1. Upload the `PragmaticCoders-News-Communications-Hub` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the settings and start adding notifications from the **Pragmatic Coders** > **News Communications Hub** menu in the WordPress admin area.

**Important**: If you have a caching plugin installed with JavaScript minification or file concatenation enabled, make sure to add `nch-script.js` to the exclusion list. This ensures that the script functions correctly without interference from caching.

== Frequently Asked Questions ==

= Can I choose different post types for notifications? =
Yes, you can select custom post types directly from the plugin settings to include posts, pages, or any other post type in your notifications.

= Can I use icons from Elementor? =
Yes, the plugin integrates with Elementor's icon library, allowing you to choose icons when creating notifications. You can also use WordPress's native Dashicons.

= How do I add notifications to a page? =
You can use the built-in widget or the provided shortcode `[pc-nch]` to display notifications on any page.

= Can custom notifications have specific start and end dates? =
Yes, you can set specific start and end dates for each custom notification, allowing for time-sensitive announcements.

= Is there a way to differentiate notifications for first-time visitors? =
Yes, you can create notifications that are visible only for first-time visitors, as well as those that appear on subsequent visits.

= How is the code optimized for database queries? =
The plugin utilizes efficient database queries to minimize overhead, caching results when possible, and reducing the number of queries executed per page load for optimal performance.

== Screenshots ==
1. Admin panel for adding and managing notifications.
2. Notification widget on the front-end of a website.
3. Icon selection from Elementor integration.

== Changelog ==

= 1.0 =
* Initial release with full notification management, widget and shortcode support, and Elementor integration.

== Upgrade Notice ==
None.
