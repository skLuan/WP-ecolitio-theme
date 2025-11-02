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
// =============================================================================
// TALLER SABWAY ROLE AJAX HANDLERS
// =============================================================================

/**
 * AJAX handler for Taller Sabway role product filtering
 */
add_action('wp_ajax_taller_sabway_filter_products', 'taller_sabway_filter_products');
add_action('wp_ajax_nopriv_taller_sabway_filter_products', 'taller_sabway_filter_products');
function taller_sabway_filter_products() {
    // Security: Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'taller_sabway_nonce')) {
        wp_send_json_error(__('Verificación de seguridad fallida', 'ecolitio-theme'));
        return;
    }

    // Check if user has Taller Sabway role
    if (!current_user_can('taller_sabway')) {
        wp_send_json_error(__('No tienes permisos para esta acción', 'ecolitio-theme'));
        return;
    }

    // Get search term
    $search_term = sanitize_text_field($_POST['search_term'] ?? '');

    // Build query arguments
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        's'              => $search_term,
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_tag',
                'field'    => 'slug',
                'terms'    => 'sabway',
                'operator' => 'IN'
            )
        )
    );

    $products_query = new WP_Query($args);

    // Generate HTML content using templates
    ob_start();
    set_query_var('products_query', $products_query);
    set_query_var('show_pagination', false);
    get_template_part('templates/products-grid');
    $html = ob_get_clean();
    wp_reset_postdata();

    wp_send_json_success(array(
        'html' => $html,
        'count' => $products_query->found_posts
    ));
}

/**
 * AJAX handler for getting Taller Sabway statistics
 */
add_action('wp_ajax_taller_sabway_get_stats', 'taller_sabway_get_stats');
function taller_sabway_get_stats() {
    // Security: Verify nonce
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'taller_sabway_nonce')) {
        wp_send_json_error(__('Verificación de seguridad fallida', 'ecolitio-theme'));
        return;
    }

    // Check if user has Taller Sabway role
    if (!current_user_can('taller_sabway')) {
        wp_send_json_error(__('No tienes permisos para esta acción', 'ecolitio-theme'));
        return;
    }

    $user_id = get_current_user_id();

    // Get Sabway products count
    $sabway_products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
        'return' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => 'sabway'
            )
        )
    ));

    // Get customer orders count
    $customer_orders = wc_get_orders(array(
        'customer' => $user_id,
        'limit' => -1,
        'return' => 'ids'
    ));

    // Get pending orders count
    $pending_orders = wc_get_orders(array(
        'customer' => $user_id,
        'status' => array('pending', 'processing'),
        'limit' => -1,
        'return' => 'ids'
    ));

    $stats = array(
        'available_products' => count($sabway_products),
        'total_orders' => count($customer_orders),
        'pending_orders' => count($pending_orders)
    );

    wp_send_json_success($stats);
}
// =============================================================================
// TALLER SABWAY FORM SUBMISSION HANDLER
// =============================================================================

/**
 * AJAX handler for Sabway form submission
 * Enhanced with comprehensive cookie/session validation
 */
add_action('wp_ajax_sabway_submit_form', 'ecolitio_sabway_submit_form');
add_action('wp_ajax_nopriv_sabway_submit_form', 'ecolitio_sabway_submit_form');
function ecolitio_sabway_submit_form() {
    // Enhanced logging for debugging
    error_log('Ecolitio Sabway Form Submission Started');
    
    // 1. Enhanced Nonce Validation
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'ecolitio_sabway_form_nonce')) {
        error_log('Ecolitio Sabway: Nonce verification failed');
        wp_send_json_error(array(
            'message' => __('Verificación de seguridad fallida (Nonce)', 'ecolitio-theme'),
            'code' => 'nonce_failed'
        ));
        return;
    }

    // 2. Enhanced Cookie/Session Validation
    $session_validation = validate_user_session();
    if (!$session_validation['valid']) {
        error_log('Ecolitio Sabway: Session validation failed - ' . $session_validation['reason']);
        wp_send_json_error(array(
            'message' => __('Sesión inválida o expirada', 'ecolitio-theme'),
            'code' => 'session_failed'
        ));
        return;
    }

    // 3. Validate User Permissions (if logged in)
    if (is_user_logged_in()) {
        // Check if user has proper role or capabilities
        $user = wp_get_current_user();
        if (!$user || (!$user->has_cap('read') && !$user->has_cap('taller_sabway'))) {
            error_log('Ecolitio Sabway: User permission check failed for user ID: ' . $user->ID);
            wp_send_json_error(array(
                'message' => __('Permisos insuficientes', 'ecolitio-theme'),
                'code' => 'permission_failed'
            ));
            return;
        }
    }

    // 4. Sanitize and Validate Form Data
    $form_data = array();
    try {
        // Electrical specifications
        $form_data['voltage'] = sanitize_text_field($_POST['voltage'] ?? '');
        $form_data['amperage'] = sanitize_text_field($_POST['amperage'] ?? '');
        $form_data['distance_range_km'] = intval($_POST['distance_range_km'] ?? 0);
        
        // Physical dimensions
        $form_data['height_cm'] = floatval($_POST['height_cm'] ?? 0);
        $form_data['width_cm'] = floatval($_POST['width_cm'] ?? 0);
        $form_data['length_cm'] = floatval($_POST['length_cm'] ?? 0);
        
        // Other specifications
        $form_data['scooter_model'] = sanitize_text_field($_POST['scooter_model'] ?? '');
        $form_data['battery_location'] = sanitize_text_field($_POST['battery_location'] ?? '');
        $form_data['connector_type'] = sanitize_text_field($_POST['connector_type'] ?? '');
        $form_data['product_id'] = intval($_POST['product_id'] ?? 0);
        
        // Validate required fields
        $validation_errors = validate_sabway_form_data($form_data);
        if (!empty($validation_errors)) {
            error_log('Ecolitio Sabway: Form validation failed - ' . implode(', ', $validation_errors));
            wp_send_json_error(array(
                'message' => __('Datos del formulario inválidos', 'ecolitio-theme'),
                'errors' => $validation_errors,
                'code' => 'validation_failed'
            ));
            return;
        }
        
    } catch (Exception $e) {
        error_log('Ecolitio Sabway: Data processing error - ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => __('Error procesando datos del formulario', 'ecolitio-theme'),
            'code' => 'processing_failed'
        ));
        return;
    }

    // 5. Create WooCommerce Order
    try {
        // Initialize WooCommerce session if not already done
        if (function_exists('WC') && WC()->session) {
            WC()->session->init_session();
        }
        
        // Create the order
        $order = wc_create_order();
        
        if (is_wp_error($order)) {
            error_log('Ecolitio Sabway: Failed to create order - ' . $order->get_error_message());
            wp_send_json_error(array(
                'message' => __('Error creando la orden', 'ecolitio-theme'),
                'code' => 'order_creation_failed'
            ));
            return;
        }
        
        // Add product to order
        $product = wc_get_product($form_data['product_id']);
        if (!$product || !$product->is_purchasable()) {
            $order->delete(true);
            error_log('Ecolitio Sabway: Product not purchasable - ID: ' . $form_data['product_id']);
            wp_send_json_error(array(
                'message' => __('Producto no disponible', 'ecolitio-theme'),
                'code' => 'product_unavailable'
            ));
            return;
        }
        
        // Add the product to order
        $order->add_product($product, 1, array(
            'subtotal' => $product->get_price(),
            'total' => $product->get_price(),
        ));
        
        // Add custom meta data to order
        $order_meta = array(
            '_sabway_electrical_specs' => array(
                'voltage' => $form_data['voltage'],
                'amperage' => $form_data['amperage'],
                'distance_range_km' => $form_data['distance_range_km'],
            ),
            '_sabway_physical_dimensions' => array(
                'height_cm' => $form_data['height_cm'],
                'width_cm' => $form_data['width_cm'],
                'length_cm' => $form_data['length_cm'],
            ),
            '_sabway_specifications' => array(
                'scooter_model' => $form_data['scooter_model'],
                'battery_location' => $form_data['battery_location'],
                'connector_type' => $form_data['connector_type'],
            ),
            '_sabway_order_type' => 'battery_customization',
            '_sabway_order_source' => 'taller_sabway_form',
        );
        
        foreach ($order_meta as $key => $value) {
            if (is_array($value)) {
                $order->update_meta_data($key, wp_json_encode($value));
            } else {
                $order->update_meta_data($key, $value);
            }
        }
        
        // Set order status
        $order->set_status('pending');
        $order->set_payment_method('bacs');
        $order->set_payment_method_title('Transferencia Bancaria');
        $order->set_payment_method('bacs');
        
        // Set customer details (use defaults or form data)
        $order->set_billing_first_name('Sabway');
        $order->set_billing_last_name('Company');
        $order->set_billing_company('Sabway Company');
        $order->set_billing_email('sabway@company.com');
        
        // Calculate totals
        $order->calculate_totals();
        
        // Save the order
        $order->save();
        
        error_log('Ecolitio Sabway: Order created successfully - ID: ' . $order->get_id());
        
        // Return success response
        wp_send_json_success(array(
            'message' => __('Pedido creado exitosamente', 'ecolitio-theme'),
            'order_id' => $order->get_id(),
            'order_key' => $order->get_order_key(),
            'redirect_url' => $order->get_checkout_order_received_url(),
        ));
        
    } catch (Exception $e) {
        error_log('Ecolitio Sabway: Order creation exception - ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => __('Error creando la orden', 'ecolitio-theme'),
            'code' => 'order_exception'
        ));
        return;
    }
}

/**
 * Validate user session and cookies
 * Enhanced session validation to prevent cookie check failures
 */
function validate_user_session() {
    $result = array(
        'valid' => false,
        'reason' => '',
        'session_data' => array()
    );
    
    // Check if session is initialized
    if (function_exists('WC') && WC()->session) {
        WC()->session->init_session();
        
        // Get session data for validation
        $session_data = WC()->session->get_session_data();
        $result['session_data'] = $session_data;
        
        // Validate session exists
        if (empty($session_data)) {
            $result['reason'] = 'Empty session data';
            return $result;
        }
    } else {
        // For non-WooCommerce sessions, check WordPress session
        $session_data = WP_Session_Tokens::get_instance(get_current_user_id());
        if ($session_data) {
            $result['session_data'] = $session_data->get_all();
        }
    }
    
    // Check for active user session if logged in
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        if (!$user_id) {
            $result['reason'] = 'No valid user ID found';
            return $result;
        }
        
        // Validate user exists and is not blocked
        $user = get_userdata($user_id);
        if (!$user) {
            $result['reason'] = 'User data not found';
            return $result;
        }
    }
    
    // Check cookie validation for anonymous users
    if (!is_user_logged_in()) {
        // Validate session cookies are set
        $cookie_keys = array('wp_cart_tracking', 'woocommerce_cart_hash', 'wp_woocommerce_session_');
        $cookie_found = false;
        
        foreach ($_COOKIE as $cookie_name => $cookie_value) {
            foreach ($cookie_keys as $key) {
                if (strpos($cookie_name, $key) !== false) {
                    $cookie_found = true;
                    break 2;
                }
            }
        }
        
        if (!$cookie_found) {
            $result['reason'] = 'Required cookies not found';
            return $result;
        }
    }
    
    // Session validation passed
    $result['valid'] = true;
    return $result;
}

/**
 * Validate Sabway form data
 */
function validate_sabway_form_data($data) {
    $errors = array();
    
    // Required field validations
    $required_fields = array(
        'voltage' => __('Voltaje', 'ecolitio-theme'),
        'amperage' => __('Amperaje', 'ecolitio-theme'),
        'distance_range_km' => __('Rango de distancia', 'ecolitio-theme'),
        'height_cm' => __('Altura', 'ecolitio-theme'),
        'width_cm' => __('Ancho', 'ecolitio-theme'),
        'length_cm' => __('Largo', 'ecolitio-theme'),
        'scooter_model' => __('Modelo de patinete', 'ecolitio-theme'),
        'battery_location' => __('Ubicación de batería', 'ecolitio-theme'),
        'connector_type' => __('Tipo de conector', 'ecolitio-theme'),
        'product_id' => __('ID de producto', 'ecolitio-theme'),
    );
    
    foreach ($required_fields as $field => $label) {
        if (empty($data[$field])) {
            $errors[] = sprintf(__('%s es requerido', 'ecolitio-theme'), $label);
        }
    }
    
    // Numeric validations
    if ($data['distance_range_km'] < 10 || $data['distance_range_km'] > 100) {
        $errors[] = __('Rango de distancia debe estar entre 10 y 100 km', 'ecolitio-theme');
    }
    
    if ($data['height_cm'] <= 0 || $data['width_cm'] <= 0 || $data['length_cm'] <= 0) {
        $errors[] = __('Las dimensiones deben ser valores positivos', 'ecolitio-theme');
    }
    
    // Product validation
    $product = wc_get_product($data['product_id']);
    if (!$product || !$product->exists()) {
        $errors[] = __('Producto inválido', 'ecolitio-theme');
    }
    
    // Validate product has sabway tag for Taller Sabway users
    if (current_user_can('taller_sabway')) {
        $product_tags = wp_get_post_terms($data['product_id'], 'product_tag', array('fields' => 'slugs'));
        if (!in_array('sabway', $product_tags)) {
            $errors[] = __('Este producto no está disponible para Taller Sabway', 'ecolitio-theme');
        }
    }
    
    return $errors;
}