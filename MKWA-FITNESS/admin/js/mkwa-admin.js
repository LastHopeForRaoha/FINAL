(function($) {
    'use strict';

    const MKWA_Admin = {
        init: function() {
            this.initTabs();
            this.initPointsCalculator();
            this.initDataRefresh();
            this.initTooltips();
        },

        initTabs: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                const tab = $(this).attr('href').split('tab=')[1];
                
                // Update URL without reload
                window.history.pushState({}, '', $(this).attr('href'));
                
                // Show active tab
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show tab content
                $('.mkwa-tab-content').hide();
                $(`#mkwa-tab-${tab}`).show();
            });
        },

        initPointsCalculator: function() {
            $('.points-calculator input').on('change', function() {
                const basePoints = parseInt($('#base-points').val()) || 0;
                const streakMultiplier = parseFloat($('#streak-multiplier').val()) || 1;
                const result = basePoints * streakMultiplier;
                
                $('#calculated-points').text(result.toFixed(0));
            });
        },

        initDataRefresh: function() {
            $('#refresh-stats').on('click', function(e) {
                e.preventDefault();
                const $button = $(this);
                const $container = $('.mkwa-stats-container');
                
                $button.prop('disabled', true);
                $container.addClass('mkwa-loading');

                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'mkwa_refresh_stats',
                        nonce: mkwaAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $container.html(response.data.html);
                        }
                    },
                    complete: function() {
                        $button.prop('disabled', false);
                        $container.removeClass('mkwa-loading');
                    }
                });
            });
        },

        initTooltips: function() {
            $('.mkwa-tooltip').tooltip({
                position: { my: "left+15 center", at: "right center" }
            });
        }
    };

    $(document).ready(function() {
        MKWA_Admin.init();
    });

})(jQuery);