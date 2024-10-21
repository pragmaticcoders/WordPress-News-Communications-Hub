/**
 * Initializes all icon pickers on the page.
 * Loops through each icon picker wrapper and sets up the icon picker functionality.
 */
function setupIconPickers() {
    jQuery('.icon-picker-select-wrapper').each(function() {
        var $wrapper = jQuery(this);
        initializeIconPicker($wrapper);
    });
}

/**
 * Initializes the icon picker for a given wrapper.
 * @param {jQuery} $wrapper - jQuery object representing the wrapper containing the icon picker.
 */
function initializeIconPicker($wrapper) {
    var $trigger = $wrapper.find('.icon-picker-select-trigger');
    var $options = $wrapper.find('.icon-picker-options');
    var $hiddenInput = $wrapper.find('.hidden-select');
    
    $trigger.on('click', function() {
        $options.toggle();
    });

    $options.find('.icon-picker-option').on('click', function() {
        var value = jQuery(this).data('value');
        var label = jQuery(this).data('label');
        
        $hiddenInput.val(value);
        $trigger.find('.icon-preview i').attr('class', value);
        $trigger.find('.selected-icon-label').text(label);
        
        $options.hide();
    });

    $wrapper.find('.icon-picker-search input').on('keyup', function() {
        var searchTerm = jQuery(this).val().toLowerCase();
        $options.find('.icon-picker-option').each(function() {
            var iconLabel = jQuery(this).data('label').toLowerCase();
            jQuery(this).toggle(iconLabel.includes(searchTerm));
        });
    });

    jQuery(document).on('click', function(e) {
        if (!jQuery(e.target).closest($wrapper).length) {
            $options.hide();
        }
    });
}

jQuery(document).ready(function($) {
    setupIconPickers();

    /**
     * Updates the hidden input field with the selected icon and entered text.
     * @param {jQuery} wrapper - jQuery object representing the icon text wrapper.
     */
    function updateHiddenField(wrapper) {
        var selectedIcon = wrapper.find('.icon-picker-selected').data('value');
        var text = wrapper.find('.nch-icon-text').val();
        var combinedValue = '<i class="' + selectedIcon + '"></i> <span>' + text + '</span>';
        wrapper.find('.nch-icon-hidden').val(combinedValue);
    }

    $('.icon-picker-option').on('click', function() {
        var wrapper = $(this).closest('.nch-icon-text-wrapper');
        wrapper.find('.icon-picker-selected').removeClass('icon-picker-selected');
        $(this).addClass('icon-picker-selected');
        updateHiddenField(wrapper);
    });

    $('.nch-icon-text').on('input', function() {
        var wrapper = $(this).closest('.nch-icon-text-wrapper');
        updateHiddenField(wrapper);
    });

    $('.nch-icon-text-wrapper').each(function() {
        var initialIcon = $(this).find('.nch-icon-hidden').data('initial-icon');
        $(this).find('.icon-picker-option[data-value="' + initialIcon + '"]').addClass('icon-picker-selected');
    });
});
