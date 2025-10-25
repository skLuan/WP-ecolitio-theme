<?php
/**
 * Ecolitio Theme AJAX Handlers
 *
 * This file contains all AJAX handlers for the Ecolitio theme.
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// AJAX SCRIPTS ENQUEUE
// =============================================================================

/**
 * Enqueue products-specific AJAX scripts
 */
function ecolitio_enqueue_products_scripts() {
    wp_enqueue_script(
        'ecolitio-products-ajax',
        get_stylesheet_directory_uri() . '/src/products-ajax.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize script with AJAX data
    wp_localize_script('ecolitio-products-ajax', 'ecolitio_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ecolitio_products_nonce'),
        'strings'  => array(
            'loading' => __('Cargando...', 'ecolitio-theme'),
            'error'   => __('Error al cargar productos', 'ecolitio-theme'),
        ),
    ));
}

// =============================================================================
// AJAX HANDLERS
// =============================================================================

/**
 * AJAX handler for loading products pages
 * Handles pagination requests with security validation
 */
add_action('wp_ajax_load_products_page', 'ecolitio_load_products_page');
add_action('wp_ajax_nopriv_load_products_page', 'ecolitio_load_products_page');
function ecolitio_load_products_page() {
    // Security: Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ecolitio_products_nonce')) {
        wp_send_json_error(__('Verificación de seguridad fallida', 'ecolitio-theme'));
        return;
    }

    // Check WooCommerce availability
    if (!ecolitio_is_woocommerce_active()) {
        wp_send_json_error(__('WooCommerce no está activo', 'ecolitio-theme'));
        return;
    }

    // Sanitize and validate input
    $page = intval($_POST['page'] ?? 1);
    $per_page = apply_filters('ecolitio_products_per_page', 9);

    if ($page < 1) {
        $page = 1;
    }

    $offset = ($page - 1) * $per_page;

    // Build query arguments
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $per_page,
        'offset'         => $offset,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    // Allow filtering of product query
    $args = apply_filters('ecolitio_products_query_args', $args, $page);

    $products_query = new WP_Query($args);
    $total_products = wp_count_posts('product')->publish;
    $total_pages = ceil($total_products / $per_page);

    // Generate HTML content using templates
    ob_start();
    set_query_var('products_query', $products_query);
    set_query_var('current_page', $page);
    set_query_var('total_pages', $total_pages);
    set_query_var('show_pagination', false); // Don't show pagination in AJAX response
    get_template_part('templates/products-grid');
    $html = ob_get_clean();
    wp_reset_postdata();

    // Generate pagination HTML
    ob_start();
    set_query_var('current_page', $page);
    set_query_var('total_pages', $total_pages);
    get_template_part('templates/pagination');
    $pagination_html = ob_get_clean();

    wp_send_json_success(array(
        'html'        => $html,
        'pagination'  => $pagination_html,
        'total_pages' => $total_pages,
        'current_page' => $page,
    ));
}