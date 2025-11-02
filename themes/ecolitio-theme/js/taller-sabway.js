/**
 * Taller Sabway Role JavaScript Functionality
 *
 * Handles client-side interactions for Taller Sabway user role
 * including dashboard features, product filtering, and UI enhancements.
 */

(function($) {
    'use strict';

    // Initialize Taller Sabway functionality
    const TallerSabway = {
        
        // Initialize all functionality
        init: function() {
            this.setupDashboard();
            this.setupProductFiltering();
            this.setupNotifications();
            this.setupAjaxHandlers();
            this.setupStatistics();
        },

        /**
         * Setup dashboard functionality
         */
        setupDashboard: function() {
            // Add loading states to dashboard buttons
            $('.taller-sabway-actions .button').on('click', function(e) {
                const $button = $(this);
                $button.addClass('taller-sabway-loading');
                
                // Remove loading state after a delay (server will handle navigation)
                setTimeout(function() {
                    $button.removeClass('taller-sabway-loading');
                }, 2000);
            });

            // Add hover effects to stat boxes
            $('.stat-box').hover(
                function() {
                    $(this).find('.stat-number').addClass('pulse');
                },
                function() {
                    $(this).find('.stat-number').removeClass('pulse');
                }
            );
        },

        /**
         * Setup product filtering for Taller Sabway users
         */
        setupProductFiltering: function() {
            // Add notice on shop pages
            if ($('.woocommerce').length && $('.shop').length) {
                this.showFilterNotice();
            }

            // Handle product search for filtered results
            $('.woocommerce-product-search input[type="search"]').on('input', function() {
                const searchTerm = $(this).val();
                if (searchTerm.length >= 3) {
                    TallerSabway.filterProducts(searchTerm);
                }
            });
        },

        /**
         * Show filtering notice for Taller Sabway users
         */
        showFilterNotice: function() {
            if ($('.sabway-filter-notice').length === 0) {
                const noticeHtml = `
                    <div class="sabway-filter-notice">
                        <h4>🔒 Zona Taller Sabway</h4>
                        <p>Solo puedes ver productos etiquetados como "sabway". Esto garantiza acceso exclusivo a productos específicos de tu rol.</p>
                    </div>
                `;
                
                $('.woocommerce .woocommerce-result-count').after(noticeHtml);
            }
        },

        /**
         * Filter products by search term (AJAX)
         */
        filterProducts: function(searchTerm) {
            $.ajax({
                url: taller_sabway_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'taller_sabway_filter_products',
                    nonce: taller_sabway_ajax.nonce,
                    search_term: searchTerm
                },
                success: function(response) {
                    if (response.success) {
                        $('.products').html(response.data.html);
                    }
                },
                error: function() {
                    console.log('Error filtering products');
                }
            });
        },

        /**
         * Setup notifications system
         */
        setupNotifications: function() {
            // Add click handler for closing notifications
            $(document).on('click', '.taller-sabway-notice .close', function() {
                $(this).closest('.taller-sabway-notice').fadeOut();
            });

            // Auto-hide success messages after 5 seconds
            setTimeout(function() {
                $('.taller-sabway-notice').not('.error').fadeOut();
            }, 5000);
        },

        /**
         * Setup AJAX handlers
         */
        setupAjaxHandlers: function() {
            // Handle dashboard AJAX actions
            $(document).on('click', '[data-action]', function(e) {
                const action = $(this).data('action');
                const target = $(this).data('target');
                
                e.preventDefault();
                TallerSabway.executeAction(action, target, $(this));
            });
        },

        /**
         * Execute AJAX actions
         */
        executeAction: function(action, target, $element) {
            $element.addClass('taller-sabway-loading');

            $.ajax({
                url: taller_sabway_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'taller_sabway_' + action,
                    nonce: taller_sabway_ajax.nonce,
                    target: target
                },
                success: function(response) {
                    if (response.success) {
                        TallerSabway.showMessage(response.data.message, 'success');
                        if (response.data.refresh) {
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        TallerSabway.showMessage(response.data || 'Error occurred', 'error');
                    }
                },
                error: function() {
                    TallerSabway.showMessage('Error de conexión', 'error');
                },
                complete: function() {
                    $element.removeClass('taller-sabway-loading');
                }
            });
        },

        /**
         * Setup statistics animations and updates
         */
        setupStatistics: function() {
            // Animate numbers on page load
            $('.stat-number').each(function() {
                const $this = $(this);
                const target = parseInt($this.text());
                
                if (target > 0) {
                    $this.text('0');
                    
                    $({count: 0}).animate({count: target}, {
                        duration: 1000,
                        step: function() {
                            $this.text(Math.ceil(this.count));
                        },
                        complete: function() {
                            $this.text(target);
                        }
                    });
                }
            });
        },

        /**
         * Show messages to user
         */
        showMessage: function(message, type) {
            const messageClass = 'taller-sabway-message ' + (type || 'info');
            const messageHtml = `
                <div class="${messageClass}">
                    ${message}
                    <button class="close" style="float: right; background: none; border: none; cursor: pointer;">&times;</button>
                </div>
            `;

            // Remove existing messages
            $('.taller-sabway-message').remove();

            // Add new message
            $('.taller-sabway-dashboard').prepend(messageHtml);

            // Auto-hide after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(function() {
                    $('.taller-sabway-message').fadeOut();
                }, 5000);
            }
        },

        /**
         * Refresh statistics
         */
        refreshStatistics: function() {
            $.ajax({
                url: taller_sabway_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'taller_sabway_get_stats',
                    nonce: taller_sabway_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $.each(response.data, function(key, value) {
                            const $element = $('.stat-box[data-stat="' + key + '"] .stat-number');
                            if ($element.length) {
                                $element.text(value);
                            }
                        });
                    }
                }
            });
        }
    };

    // Document ready initialization
    $(document).ready(function() {
        if (taller_sabway_ajax && taller_sabway_ajax.is_taller_sabway) {
            TallerSabway.init();
        }
    });

    // Handle page visibility changes (for statistics updates)
    $(document).on('visibilitychange', function() {
        if (!document.hidden && taller_sabway_ajax && taller_sabway_ajax.is_taller_sabway) {
            TallerSabway.refreshStatistics();
        }
    });

    // Export for external use if needed
    window.TallerSabway = TallerSabway;

})(jQuery);

// Add CSS animations via JavaScript
const style = document.createElement('style');
style.textContent = `
    .stat-number {
        transition: all 0.3s ease;
    }
    
    .stat-number.pulse {
        transform: scale(1.1);
        color: #d02024;
    }
    
    .taller-sabway-loading {
        position: relative;
        opacity: 0.7;
        pointer-events: none;
    }
    
    .taller-sabway-loading::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid #d02024;
        border-radius: 50%;
        border-top-color: transparent;
        animation: taller-sabway-spin 1s linear infinite;
    }
    
    @keyframes taller-sabway-spin {
        to {
            transform: rotate(360deg);
        }
    }
`;
document.head.appendChild(style);