# Pragmatic Coders News Communications Hub

## Overview
The **Pragmatic Coders News Communications Hub** is a flexible WordPress plugin designed to help you easily manage and display customizable notifications and news updates on your site. With robust admin options, Elementor icons integration, and flexible styling, this plugin provides all the tools you need to keep users informed and engaged.

## Key Features
- **Custom Notifications**: Add, edit, and manage notifications directly from the WordPress admin interface. You can display any type of post or page, including custom post types.
- **Post Type Selection**: Choose which post types (posts, pages, or custom types) you want to feature in your notifications.
- **Custom Dates**: Each custom notification can have specific start and end dates, allowing for time-sensitive announcements. This feature is ideal for promotions, events, or temporary updates.
- **First-Time Visitor Notifications**: Separate custom notifications into those that are visible for first-time visitors and those that appear for subsequent visits, enhancing user experience by presenting relevant information based on user behavior.
- **Icon Support**: Use Elementor's icon library or WordPress Dashicons to select icons for your notifications.
- **Widgets and Shortcodes**: Display notifications easily with the included widget or using the shortcode `[pc-nch]`.
- **Notification Log**: Log important events, errors, and other data to a log file located in `/logs/nch.log` for debugging and tracking purposes.
- **Customizable Styling**: Modify the look and feel of notifications via the CSS files located in the `assets/css` folder, or add your own custom styles.
- **Optimized Code**: The plugin is designed to minimize database queries and improve performance. Efficient queries and caching mechanisms are used to ensure optimal performance, reducing load times and server strain.

## Installation
1. Download or clone the repository.
2. Upload the `PragmaticCoders-News-Communications-Hub` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin via the **Plugins** menu in WordPress.
4. Navigate to **Pragmatic Coder** > **News Communications Hub** to configure your notifications.

## Usage
### Adding New Notifications
After activating the plugin, you can add new notifications through the **News Communications Hub** admin menu:
- **Notification Title**: Set the title that will appear in the notification bar.
- **Post Type Selection**: Choose which post types (e.g., posts, pages, or any custom types) should appear in the notification. You can select multiple post types.
- **Custom Dates**: Specify start and end dates for each notification, allowing for precise control over when notifications are visible to users.
- **First-Time Visitor Options**: Choose whether a notification should be visible only for first-time visitors or for all subsequent visits.
- **Icons**: Select an icon for your notification from the Elementor icon library or use the default Dashicons from WordPress.

### Displaying Notifications
1. **Shortcode**: Use the `[pc-nch]` shortcode to display the notification bar on any page or post.
2. **Widget**: Alternatively, you can place the notification widget in any widgetized area of your theme (e.g., the sidebar or footer).
3. **Automatic Fetching**: The plugin can automatically fetch the latest posts or custom post types and display them as notifications.

### Managing Styles
All styles can be found in the `assets/css` folder. To modify the appearance of notifications, you can:
- Edit the provided CSS files.
- Override the styles in your theme by using custom CSS rules.

### Notification Log
A log file is maintained at `logs/nch.log` to track important events such as errors, notification dispatches, and user interactions. This log can be useful for debugging issues or analyzing how notifications are being processed.

### Optimized Database Queries
The plugin is built with performance in mind, utilizing optimized database queries to minimize the load on the server. By caching results when appropriate and reducing the number of queries executed per page load, the plugin ensures a seamless experience for users while conserving server resources.

## Customization
1. **Icons**: Icons can be selected either from the Elementor icon library or WordPress Dashicons, allowing for rich visual customization.
2. **Post Type Inclusion**: You can include any custom post types in the notifications, offering flexibility to display specific types of content.
3. **Shortcodes and Widgets**: Whether through shortcodes or widgets, the plugin is designed to be easy to use and integrate with any WordPress theme.

## FAQ

#### Can I choose different post types for notifications?
Yes, you can select custom post types directly from the plugin settings to include posts, pages, or any other post type in your notifications.

#### Can I use icons from Elementor?
Yes, the plugin integrates with Elementor's icon library, allowing you to choose icons when creating notifications. You can also use WordPress's native Dashicons.

#### How do I add notifications to a page?
You can use the built-in widget or the provided shortcode `[pc-nch]` to display notifications on any page.

#### Can custom notifications have specific start and end dates?
Yes, you can set specific start and end dates for each custom notification, allowing for time-sensitive announcements.

#### Is there a way to differentiate notifications for first-time visitors?
Yes, you can create notifications that are visible only for first-time visitors, as well as those that appear on subsequent visits.

#### How is the code optimized for database queries?
The plugin utilizes efficient database queries to minimize overhead, caching results when possible, and reducing the number of queries executed per page load for optimal performance.


## Demo

https://pragmaticcoders.com


## Changelog

### Version 1.0
- Initial release with full notification management, post type selection, custom start and end dates, first-time visitor options, shortcode, widget support, and Elementor integration.

## Contributing
We welcome contributions to improve the plugin. To contribute:
1. Fork the repository.
2. Make your changes in a new branch.
3. Create a pull request to have your changes reviewed.

## Authors

- [@Pragmatic Coders](https://www.github.com/Pragmatic Coders)
- [@jan-wegner](https://www.github.com/jan-wegner)

## License

[![GPLv2 License](https://img.shields.io/badge/License-GPL%20v2-yellow.svg)](https://opensource.org/licenses/)

