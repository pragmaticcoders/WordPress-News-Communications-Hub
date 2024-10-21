/**
 * @type {number} Lifetime of the local storage.
 */
var lifetimeLocalStorage;

/**
 * @type {number} Lifetime of the local storage for first visit.
 */
var lifetimeLocalStorageFirstVisit;

/**
 * Messages from the localized script data.
 */
var markAllAsReadMessage;
var showReadItemsMessage;
var hideReadItemsMessage;
var allReadMessage;
var iconRead;

var pluginURL;
var showPClogo;

jQuery(document).ready(function($) {

    lifetimeLocalStorage = nch_data.localstorage_lifetime;
    lifetimeLocalStorageFirstVisit = nch_data.localstorage_first_visit_lifetime;

    if (!localStorage.getItem('nch_message_mark_all_as_read')) {
        setLocalStorage('nch_message_mark_all_as_read', nch_data.message_mark_all_as_read, 14);
    }

    if (!localStorage.getItem('nch_message_show_read_items')) {
        setLocalStorage('nch_message_show_read_items', nch_data.message_show_read_items, 14);
    }
    if (!localStorage.getItem('nch_message_hide_read_items')) {
        setLocalStorage('nch_message_hide_read_items', nch_data.message_hide_read_items, 14);
    }

    if (!localStorage.getItem('nch_message_all_read')) {
        setLocalStorage('nch_message_all_read', nch_data.message_all_read, 14);
    }

    if (!localStorage.getItem('nch_icon_read')) {
        setLocalStorage('nch_icon_read', nch_data.icon_read, 14);
    }

    markAllAsReadMessage = getLocalStorage('message_mark_all_as_read') || nch_data.message_mark_all_as_read;
    showReadItemsMessage = getLocalStorage('message_show_read_items') || nch_data.message_show_read_items;
    hideReadItemsMessage = getLocalStorage('message_hide_read_items') || nch_data.message_hide_read_items;
    allReadMessage = getLocalStorage('message_all_read') || nch_data.message_all_read;
    iconRead = getLocalStorage('icon_read') || nch_data.icon_read;

    pluginURL = nch_data.plugin_url;
    showPClogo = nch_data.show_pc_logo;

    var hiddenVisibility = getLocalStorage('nch_visibility_state') || 'hide';
    var $button = $('#show-hide');

    /**
     * Checks if the user agent is a bot.
     * @returns {boolean} True if the user agent matches bot patterns, otherwise false.
     */
    function isBot() {
        var userAgent = navigator.userAgent;
        return /bot|crawl|slurp|spider/i.test(userAgent);
    }

    if (hiddenVisibility === 'show') {
        $button.data('action', 'show').attr('data-action', 'show')
            .html(showReadItemsMessage);
        $('.notification-item.read').addClass('hidden');
    } else {
        $button.data('action', 'hide').attr('data-action', 'hide')
            .html(hideReadItemsMessage);
    }

    /**
     * Gets a localStorage value by name.
     * @param {string} name - The name of the localStorage item.
     * @returns {string|null} The value of the localStorage item, or null if not found.
     */
    function getLocalStorage(name) {
        var storedItem = localStorage.getItem(name);
        if (!storedItem) return null;

        var parsedItem = JSON.parse(storedItem);
        if (new Date().getTime() > parsedItem.expiry) {
            localStorage.removeItem(name);
            return null;
        }
        return parsedItem.value;
    }

    /**
     * Sets a localStorage item with a specified name, value, and expiration days.
     * @param {string} name - The name of the localStorage item.
     * @param {string} value - The value of the localStorage item.
     * @param {number} days - The number of days until the item expires.
     */
    function setLocalStorage(name, value, days) {
        var now = new Date();
        var expiry = now.getTime() + (days * 24 * 60 * 60 * 1000);
        localStorage.setItem(name, JSON.stringify({ value: value, expiry: expiry }));
    }

    /**
     * Deletes a localStorage item by name.
     * @param {string} name - The name of the localStorage item to delete.
     */
    function deleteLocalStorage(name) {
        localStorage.removeItem(name);
    }

    /**
     * Formats a date object into a string "YYYY-MM-DD".
     * @param {Date} date - The date object to format.
     * @returns {string} The formatted date string.
     */
    function formatDate(date) {
        return date.getFullYear() + '-' +
               ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
               ('0' + date.getDate()).slice(-2) + ' ';
    }

    /**
     * Formats a date string into a localized string representation.
     * @param {string} dateStr - The date string to format.
     * @returns {string} The formatted date string.
     */
    function formatNotificationDate(dateStr) {
        var date = new Date(dateStr);
        var options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        };
        return date.toLocaleDateString('en-US', options);
    }

    /**
     * Retrieves clicked notifications from localStorage.
     * @returns {Array<number>} An array of clicked notification IDs.
     */
    function getClickedNotifications() {
        var clicked = getLocalStorage('nch_read_notifications');
        return clicked ? JSON.parse(clicked) : [];
    }

    /**
     * Sets clicked notifications in localStorage.
     * @param {Array<number>} clicked - An array of clicked notification IDs.
     */
    function setClickedNotifications(clicked) {
        setLocalStorage('nch_read_notifications', JSON.stringify(clicked), lifetimeLocalStorage);
    }

    /**
     * Cleans up clicked notifications by removing IDs that are no longer valid.
     * @param {Array<Object>} items - The items to compare against clicked notifications.
     * @returns {Array<number>} The updated list of clicked notification IDs.
     */
    function cleanUpClickedNotifications(items) {
        var clickedNotifications = getClickedNotifications();
        var updatedClickedNotifications = clickedNotifications.filter(function(clickedId) {
            return items.some(function(item) {
                return item.id === clickedId;
            });
        });
        if (updatedClickedNotifications.length !== clickedNotifications.length) {
            setClickedNotifications(updatedClickedNotifications);
        }
        return updatedClickedNotifications;
    }

    /**
     * Displays notification items on the page.
     * @param {Array<Object>} items - The notification items to display.
     * @param {boolean} [isFirstVisit=false] - Indicates if this is the user's first visit.
     */
    function displayItems(items, isFirstVisit = false) {
        // Clean up clicked notifications and get their current state
        var clickedNotifications = cleanUpClickedNotifications(items);

        // Get visibility state from localStorage, default to 'hide'
        var hiddenVisibility = getLocalStorage('nch_visibility_state');

        var html = '<ul>';
        var unreadItems = [];
        var readItems = [];

        // Separate unread and read notification items
        items.forEach(function(item) {
            var isRead = clickedNotifications.includes(item.id);
            if (isRead) {
                readItems.push(item);
            } else {
                unreadItems.push(item);
            }
        });

        // Separate unread top notifications and sort them by date (newest first)
        var topUnreadItems = unreadItems.filter(function(item) {
            return item.show_on_top == 1;
        }).sort(function(a, b) {
            return new Date(b.date) - new Date(a.date); // Sort by date (newest to oldest)
        });

        // Separate read top notifications and sort them by date (newest first)
        var topReadItems = readItems.filter(function(item) {
            return item.show_on_top == 1;
        }).sort(function(a, b) {
            return new Date(b.date) - new Date(a.date); // Sort by date (newest to oldest)
        });

        // Separate the rest of the notifications (those without show_on_top=1)
        var normalUnreadItems = unreadItems.filter(function(item) {
            return item.show_on_top != 1;
        });

        var normalReadItems = readItems.filter(function(item) {
            return item.show_on_top != 1;
        });

        /**
         * Generates HTML for a single notification item.
         * @param {Object} item - The notification item.
         * @returns {string} The HTML string for the notification item.
         */
        function generateItemHTML(item) {
            var date = item.date ? formatNotificationDate(item.date) : '';
            var tag = item.tag ? item.tag : '';
            var post_type_name = item.post_type_name ? item.post_type_name : '';
            var link = item.link ? item.link : '#';
            var id = item.id ? item.id : ''; 
            var icon = item.icon ? item.icon : ''; 
            var color = item.color ? item.color : ''; 
            var tags = Array.isArray(item.tags) ? item.tags : [];

            var readClass = clickedNotifications.includes(item.id) ? 'read' : '';
            var hiddenClass = clickedNotifications.includes(item.id) && hiddenVisibility === 'hide' ? 'hidden' : '';

            var itemHTML = '<li class="notification-item ' + readClass + ' ' + hiddenClass + '" data-id="' + id + '" style="--item-color: ' + color + ';">';
            itemHTML += '<a href="' + link + '" class="notification-link" data-id="' + id + '">' + item.title + '</a>';
            itemHTML += '<div class="item-top">';
            itemHTML += '<div class="notification-icon"><i class="' + icon + ' nch-icon"> </i></div>';
            itemHTML += '<span class="notification-title">' + item.title + '</span>';
            itemHTML += '<span class="mark-read-btn" data-id="' + id + '" title="Mark as read"><i class="' + iconRead + '"></i></span>';
            itemHTML += '</div>';

            itemHTML += '<div class="notification-meta">';
            if (post_type_name) {
                itemHTML += ' <span class="post-type">' + post_type_name + '</span> ';
            }
            if (tag) {
                itemHTML += ' <span class="tag">' + tag + '</span> ';
            }
            if (tags) {
                tags.forEach(function(tag) {
                    if (tag) {
                        itemHTML += ' <span class="tag">' + tag + '</span> ';
                    }
                });
            }
            if (date) {
                itemHTML += ' <span class="date">' + date + '</span>';
            }
            itemHTML += '</div>';
            itemHTML += '</li>';
            
            return itemHTML;
        }

        // Render unread top notifications first
        topUnreadItems.forEach(function(item) {
            html += generateItemHTML(item);
        });

        // Then render normal unread items
        normalUnreadItems.forEach(function(item) {
            html += generateItemHTML(item);
        });

        // Then render normal read items
        normalReadItems.forEach(function(item) {
            html += generateItemHTML(item);
        });

        // Finally, render read top notifications
        topReadItems.forEach(function(item) {
            html += generateItemHTML(item);
        });

        html += '<div class="nch-info">' + allReadMessage + '</div>';
        html += '</ul>';

        if (showPClogo == 1) {
            html += '<div class="pragmaticcoders-logo-badge"><div class="badge-content"><a href="https://www.pragmaticcoders.com" target="_blank">Notifications Hub by <img src="' + pluginURL + 'assets/img/pragmaticcoders-logo-black.svg"></a></div></div>';
        }

        html += '<div class="notification-tools">';
        html += '<div class="action" id="mark-all-read">' + markAllAsReadMessage + '</div>';
        html += '<div class="action" data-action="' + (hiddenVisibility === 'hide' ? 'show' : 'hide') + '" id="show-hide">' 
        + (hiddenVisibility === 'hide' ? showReadItemsMessage : hideReadItemsMessage) + '</div>';
        html += '</div>';

        $('#nch-notifications').html(html);
        var count = unreadItems.length;
        if (count > 0) {
            $('#nch-notification-count').text(count).show();
        } else {
            $('#nch-notification-count').hide();
        }
    }


    /**
     * Calls the API to fetch notifications and posts.
     */
    function fetchNotifications() {
        var lastVisitStr = getLocalStorage('nch_last_visit');
        var lastVisit;

        var localStorageKey = 'nch_notifications';
        var storedData = JSON.parse(localStorage.getItem(localStorageKey));
        var now = new Date().getTime();

        if (!lastVisitStr) {
            lastVisit = new Date();
            var lastVisitTime = formatDate(lastVisit);
            setLocalStorage('nch_first_visit', lastVisitTime, 1);
            setLocalStorage('nch_visibility_state', 'hide', 365);

            setTimeout(function() {
                setLocalStorage('nch_last_visit', lastVisitTime, lifetimeLocalStorage);
            }, 500);
        } else {
            lastVisit = new Date(lastVisitStr);
            if (isNaN(lastVisit.getTime())) {
                lastVisit = new Date();
            }
        }

        if (storedData && now - storedData.timestamp < lifetimeLocalStorage * 24 * 60 * 60 * 1000) {
            displayItems(storedData.items);
            return;
        }

        var apiUrl = '/wp-json/nch/v1/notifications';
        var params = new URLSearchParams();

        if (lastVisitStr) {
            params.append('nch_last_visit', formatDate(lastVisit));
        }

        fetch(apiUrl + '?' + params.toString(), {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var items = data.items;
                if (items.length > 0) {
                    displayItems(items);

                    localStorage.setItem(localStorageKey, JSON.stringify({
                        timestamp: now,
                        items: items
                    }));
                } else {
                    console.warn('No new notifications to display.');
                }
            } else {
                console.error('Error in response:', data.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
    }

    /**
     * Event handlers for click actions.
     * 
     */

    $(document).on('mousedown', '#nch-notifications .notification-link', function(e) {
        if (e.which === 1 || e.which === 2 || e.ctrlKey || e.shiftKey) {
            var clickedNotifications = getClickedNotifications();
            var notificationId = $(this).data('id');
            var hiddenVisibility = getLocalStorage('nch_visibility_state');

            if (notificationId && !clickedNotifications.includes(notificationId)) {
                clickedNotifications.push(notificationId);
                setClickedNotifications(clickedNotifications);
            }

            var currentCount = parseInt($('#nch-notification-count').text());
            $('#nch-notification-count').text(currentCount - 1);

            $(this).closest('.notification-item').addClass('read');

            if (hiddenVisibility === 'hide') {
                var $notificationItem = $(this).closest('.notification-item');
                setTimeout(function() {
                    $notificationItem.addClass('hidden');
                }, 100);
            }

            var now = new Date();
            var nowFormatted = formatDate(now);

            if (currentCount - 1 <= 0) {
                setLocalStorage('nch_last_visit', nowFormatted);
                deleteLocalStorage('nch_read_notifications');
                deleteLocalStorage('nch_first_visit');
                $('.pragmaticcoders-nch').removeClass('has-notifications');
            }

            var linkHostname = this.hostname;
            var currentHostname = location.hostname;
            if (linkHostname && linkHostname !== currentHostname) {
                $(this).attr('target', '_blank');
            }

            setTimeout(function() {
                //$(this).css('pointer-events', 'none');
            }.bind(this), 10);
        }
    });

    $(document).on('click', '.mark-read-btn', function() {
        var $notificationItem = $(this).closest('.notification-item');
        var hiddenVisibility = getLocalStorage('nch_visibility_state');

        if (hiddenVisibility === 'hide') {
            $notificationItem.addClass('animate');

            setTimeout(function() {
                $notificationItem.addClass('hidden');
                $notificationItem.removeClass('animate');
            }, 500);
        }

        var notificationId = $(this).data('id');
        var clickedNotifications = getClickedNotifications();

        if (!clickedNotifications.includes(notificationId)) {
            clickedNotifications.push(notificationId);
            setClickedNotifications(clickedNotifications);
        }

        $notificationItem.addClass('read');
        var currentCount = parseInt($('#nch-notification-count').text());
        $('#nch-notification-count').text(currentCount - 1);
    });

    $(document).on('click', '#show-hide', function() {
        var $this = $(this);
        var action = $this.data('action');
        var clickedNotifications = getClickedNotifications();

        if (action === 'show') {
            $('.notification-item.read').removeClass('hidden');
            $this.data('action', 'hide').attr('data-action', 'hide')
                .html(hideReadItemsMessage);
            setLocalStorage('nch_visibility_state', 'show', 365);
        } else {
            clickedNotifications.forEach(function(id) {
                $('[data-id="' + id + '"]').addClass('hidden');
            });
            $this.data('action', 'show').attr('data-action', 'show')
                .html(showReadItemsMessage);
            setLocalStorage('nch_visibility_state', 'hide', 365);
        }

    });

    $(document).on('click', '#mark-all-read', function() {
        var clickedNotifications = getClickedNotifications();
        var hiddenVisibility = getLocalStorage('nch_visibility_state');

        $('.notification-item').each(function() {
            var notificationId = $(this).data('id');
            if (!clickedNotifications.includes(notificationId)) {
                clickedNotifications.push(notificationId);
            }
            $(this).addClass('read');

            if (hiddenVisibility === 'hide') {
                $(this).addClass('hidden');
            }
        });

        setClickedNotifications(clickedNotifications);

        $('#nch-notification-count').text(0).hide();
        $('#nch-notifications .nch-info').show();
    });

    $('#nch-bell-button').on('click', function(e) {
        e.preventDefault();

        $('#nch-notifications').toggle();
        $('.pragmaticcoders-nch').toggleClass('open');
    });

$(document).on('click', function(event) {
    var $target = $(event.target);
    if ($('.pragmaticcoders-nch').hasClass('open') 
        && !$target.closest('.pragmaticcoders-nch').length 
        && !$target.closest('#show-hide').length) {
        $('.pragmaticcoders-nch').removeClass('open');
        $('#nch-notifications').hide();
    }
});

    jQuery(window).on("load", function () {
        if (!isBot()) {
            fetchNotifications();
        }

        var currentCount = parseInt($('#nch-notification-count').text());
        if (currentCount > 0) {
            $('.pragmaticcoders-nch').addClass('has-notifications');
        }
    });

});
