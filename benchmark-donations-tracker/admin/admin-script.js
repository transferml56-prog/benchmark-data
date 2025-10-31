/**
 * Admin JavaScript
 * Handles color picker and admin interactions
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize WordPress color picker
        if ($('.bdt-color-picker').length) {
            $('.bdt-color-picker').wpColorPicker();
        }
    });

})(jQuery);
