/**
 * Map Handler JavaScript
 * Handles interactive tooltip for US map
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize map tooltips
        initMapTooltips();
    });

    function initMapTooltips() {
        var $tooltip = $('#bdt-map-tooltip');
        var $states = $('.bdt-state');

        if ($states.length === 0 || $tooltip.length === 0) {
            return;
        }

        $states.on('mouseenter', function(e) {
            var $state = $(this);
            var stateName = $state.data('state');
            var amount = $state.data('amount');

            if (stateName && amount) {
                var tooltipContent = '<strong>' + stateName + '</strong>' + amount;
                $tooltip.html(tooltipContent);
                $tooltip.addClass('active');
            }
        });

        $states.on('mousemove', function(e) {
            var offset = $('.bdt-map-container').offset();
            var x = e.pageX - offset.left + 15;
            var y = e.pageY - offset.top + 15;

            $tooltip.css({
                left: x + 'px',
                top: y + 'px'
            });
        });

        $states.on('mouseleave', function() {
            $tooltip.removeClass('active');
        });
    }

})(jQuery);
