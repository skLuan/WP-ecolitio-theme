jQuery(document).ready(function($) {
    'use strict';

    // Handle pagination clicks
    $(document).on('click', '.pagination-link', function(e) {
        e.preventDefault();

        const page = $(this).data('page');
        const grid = $('#products-grid');
        const pagination = $('#products-pagination');

        // Add loading state
        grid.addClass('loading');
        pagination.addClass('loading');

        // Disable pagination during load
        $('.pagination-link').prop('disabled', true);

        // AJAX request
        $.ajax({
            url: ecolitio_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_products_page',
                page: page,
                nonce: ecolitio_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Animate out current products
                    grid.addClass('sliding-out');

                    setTimeout(function() {
                        // Replace content
                        grid.html(response.data.html);
                        pagination.html(response.data.pagination);

                        // Animate in new products
                        grid.removeClass('sliding-out loading').addClass('sliding-in');
                        pagination.removeClass('loading');

                        // Re-enable pagination
                        $('.pagination-link').prop('disabled', false);

                        setTimeout(function() {
                            grid.removeClass('sliding-in');
                        }, 600);

                        // Update URL hash for bookmarking (optional)
                        if (history.pushState) {
                            history.pushState(null, null, '#page-' + page);
                        }
                    }, 300);
                } else {
                    console.error('AJAX error:', response.data);
                    // Re-enable on error
                    grid.removeClass('loading');
                    pagination.removeClass('loading');
                    $('.pagination-link').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', status, error);
                // Re-enable on error
                grid.removeClass('loading');
                pagination.removeClass('loading');
                $('.pagination-link').prop('disabled', false);
            }
        });
    });

    // Handle add to cart via AJAX (if WooCommerce AJAX add to cart is enabled)
    $(document).on('click', '.add_to_cart_button', function(e) {
        e.preventDefault();

        const button = $(this);
        const productId = button.data('product_id');
        const quantity = button.data('quantity') || 1;

        // Add loading state
        button.addClass('loading').prop('disabled', true);

        $.ajax({
            url: ecolitio_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'woocommerce_ajax_add_to_cart',
                product_id: productId,
                quantity: quantity,
                nonce: ecolitio_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update cart fragments if available
                    if (typeof wc_cart_fragments_params !== 'undefined') {
                        $(document.body).trigger('wc_fragment_refresh');
                    }

                    // Show success message
                    showNotification('Producto añadido al carrito', 'success');
                } else {
                    showNotification('Error al añadir producto al carrito', 'error');
                }
            },
            error: function() {
                showNotification('Error de conexión', 'error');
            },
            complete: function() {
                button.removeClass('loading').prop('disabled', false);
            }
        });
    });

    // Simple notification function
    function showNotification(message, type) {
        // Create notification element
        const notification = $('<div class="notification ' + type + '">' + message + '</div>');
        $('body').append(notification);

        // Style and animate
        notification.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#10b981' : '#ef4444',
            color: 'white',
            padding: '12px 20px',
            borderRadius: '8px',
            zIndex: 9999,
            opacity: 0,
            transform: 'translateY(-20px)'
        }).animate({
            opacity: 1,
            transform: 'translateY(0)'
        }, 300);

        // Remove after 3 seconds
        setTimeout(function() {
            notification.animate({
                opacity: 0,
                transform: 'translateY(-20px)'
            }, 300, function() {
                notification.remove();
            });
        }, 3000);
    }
});