/**
 * Taller Sabway Role JavaScript Functionality
 *
 * Handles client-side interactions for Taller Sabway user role
 * including product filtering and UI enhancements.
 */

(function($) {
    'use strict';

    // Initialize Taller Sabway functionality
    const TallerSabway = {
        
        // Initialize all functionality
        init: function() {
            this.setupProductFiltering();
            this.setupNotifications();
            this.setupAjaxHandlers();
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
            // Handle AJAX actions
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
            $('body').prepend(messageHtml);

            // Auto-hide after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(function() {
                    $('.taller-sabway-message').fadeOut();
                }, 5000);
            }
        }
    };

    // Document ready initialization
    $(document).ready(function() {
        if (taller_sabway_ajax && taller_sabway_ajax.is_taller_sabway) {
            TallerSabway.init();
        }
    });

    // Export for external use if needed
    window.TallerSabway = TallerSabway;

})(jQuery);

// Add CSS animations via JavaScript
const style = document.createElement('style');
style.textContent = `
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
