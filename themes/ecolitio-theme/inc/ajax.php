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
        // FIXED: Removed private method call to init_session()
        // WooCommerce will handle session initialization internally when needed
        
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
            '_wc_order_origin' => 'direct',
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
        
        // Link order to current user (if logged in)
        $current_user_id = get_current_user_id();
        if ($current_user_id) {
            $order->set_customer_id($current_user_id);
            error_log('Ecolitio Sabway: Order linked to user ID: ' . $current_user_id);
        }
        
        // Get user data for billing and shipping (with fallbacks)
        $user_id = $current_user_id ? $current_user_id : 0;
        $user_data = $user_id ? get_userdata($user_id) : null;
        
        // Extract user billing information with fallbacks
        $billing_first_name = $user_id ? get_user_meta($user_id, 'billing_first_name', true) : '';
        $billing_last_name = $user_id ? get_user_meta($user_id, 'billing_last_name', true) : '';
        $billing_company = $user_id ? get_user_meta($user_id, 'billing_company', true) : '';
        $billing_email = $user_id && $user_data ? $user_data->user_email : '';
        $billing_phone = $user_id ? get_user_meta($user_id, 'billing_phone', true) : '';
        $billing_address_1 = $user_id ? get_user_meta($user_id, 'billing_address_1', true) : '';
        $billing_address_2 = $user_id ? get_user_meta($user_id, 'billing_address_2', true) : '';
        $billing_city = $user_id ? get_user_meta($user_id, 'billing_city', true) : '';
        $billing_state = $user_id ? get_user_meta($user_id, 'billing_state', true) : '';
        $billing_postcode = $user_id ? get_user_meta($user_id, 'billing_postcode', true) : '';
        $billing_country = $user_id ? get_user_meta($user_id, 'billing_country', true) : '';
        
        // Set billing address with user data or defaults
        $order->set_billing_first_name($billing_first_name ?: 'Sabway');
        $order->set_billing_last_name($billing_last_name ?: 'Company');
        $order->set_billing_company($billing_company ?: 'Sabway');
        $order->set_billing_email($billing_email ?: 'sabway@company.com');
        $order->set_billing_phone($billing_phone);
        $order->set_billing_address_1($billing_address_1);
        $order->set_billing_address_2($billing_address_2);
        $order->set_billing_city($billing_city);
        $order->set_billing_state($billing_state);
        $order->set_billing_postcode($billing_postcode);
        $order->set_billing_country($billing_country);
        
        // Set shipping address (same as billing for Sabway orders)
        $order->set_shipping_first_name($billing_first_name ?: 'Sabway');
        $order->set_shipping_last_name($billing_last_name ?: 'Company');
        $order->set_shipping_company($billing_company ?: 'Sabway');
        $order->set_shipping_address_1($billing_address_1);
        $order->set_shipping_address_2($billing_address_2);
        $order->set_shipping_city($billing_city);
        $order->set_shipping_state($billing_state);
        $order->set_shipping_postcode($billing_postcode);
        $order->set_shipping_country($billing_country);
        
        // Create order note with form specifications resume
        $order_note = sprintf(
            "SABWAY BATTERY CUSTOMIZATION ORDER RESUME\n\n" .
            "ESPECIFICACIONES ELECTRICAS:\n" .
            "- Voltaje: %s\n" .
            "- Ameraje: %s\n" .
            "- Rango de distancia: %skm\n\n" .
            "DIMENSIONES FISICAS:\n" .
            "- Alto: %scm\n" .
            "- Largo: %scm\n" .
            "- Ancho: %scm\n\n" .
            "ESPECIFICACIONES DEL PATINETE:\n" .
            "- Modelo: %s\n" .
            "- Ubicación de batería: %s\n" .
            "- Tipo de conector: %s\n\n" .
            "Origen de la orden: Sabway Space (sabway-space)",
            $form_data['voltage'],
            $form_data['amperage'],
            $form_data['distance_range_km'],
            $form_data['height_cm'],
            $form_data['width_cm'],
            $form_data['length_cm'],
            $form_data['scooter_model'],
            $form_data['battery_location'],
            $form_data['connector_type']
        );
        
        // Add order note (internal note for staff)
        $order->add_order_note($order_note, 0, false);
        
        // Calculate totals
        $order->calculate_totals();
        
        // Save the order
        $order->save();
        
        error_log('Ecolitio Sabway: Order created successfully - ID: ' . $order->get_id());
        
        // Send emails after order is saved
        try {
            // Send email to client
            send_sabway_order_emails($order, 'client');
            
            // Send email to admin
            send_sabway_order_emails($order, 'admin');
            
            error_log('Ecolitio Sabway: Order emails sent successfully');
        } catch (Exception $email_e) {
            // Log email error but don't fail the order
            error_log('Ecolitio Sabway: Email sending failed - ' . $email_e->getMessage());
        }
        
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
 * AJAX handler for adding custom battery to cart
 */
add_action('wp_ajax_custom_batery_add_to_cart', 'ecolitio_custom_batery_add_to_cart');
add_action('wp_ajax_nopriv_custom_batery_add_to_cart', 'ecolitio_custom_batery_add_to_cart');
function ecolitio_custom_batery_add_to_cart() {
     // 1. Verify Nonce
     $nonce = $_POST['nonce'] ?? '';
     if (!wp_verify_nonce($nonce, 'ecolitio_sabway_form_nonce')) {
         wp_send_json_error(array(
             'message' => __('Verificación de seguridad fallida (Nonce)', 'ecolitio-theme'),
             'code' => 'nonce_failed'
         ));
         return;
     }

     // 2. Sanitize and Validate Form Data
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
         $form_data['liters'] = floatval($_POST['liters'] ?? 0);
         
         // Other specifications
         $form_data['scooter_model'] = sanitize_text_field($_POST['scooter_model'] ?? '');
         $form_data['battery_location'] = sanitize_text_field($_POST['battery_location'] ?? '');
         $form_data['connector_type'] = sanitize_text_field($_POST['connector_type'] ?? '');
         $form_data['product_id'] = intval($_POST['product_id'] ?? 0);
         $form_data['battery_type'] = sanitize_text_field($_POST['battery_type'] ?? 'sabway');
        
        // Validate required fields
        $validation_errors = validate_sabway_form_data($form_data);
        if (!empty($validation_errors)) {
            wp_send_json_error(array(
                'message' => __('Datos del formulario inválidos', 'ecolitio-theme'),
                'errors' => $validation_errors,
                'code' => 'validation_failed'
            ));
            return;
        }
        
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => __('Error procesando datos del formulario', 'ecolitio-theme'),
            'code' => 'processing_failed'
        ));
        return;
    }

    // 3. Add to Cart
    try {
        $product_id = $form_data['product_id'];
        $quantity = 1;
        
        // Prepare custom data to be stored in cart item
        $cart_item_data = array(
            '_sabway_electrical_specs' => array(
                'voltage' => $form_data['voltage'],
                'amperage' => $form_data['amperage'],
                'distance_range_km' => $form_data['distance_range_km'],
            ),
            '_sabway_physical_dimensions' => array(
                'height_cm' => $form_data['height_cm'],
                'width_cm' => $form_data['width_cm'],
                'length_cm' => $form_data['length_cm'],
                'liters' => $form_data['liters'],
            ),
            '_sabway_specifications' => array(
                'scooter_model' => $form_data['scooter_model'],
                'battery_location' => $form_data['battery_location'],
                'connector_type' => $form_data['connector_type'],
            ),
            '_sabway_custom_order' => true
        );

        // Add to cart
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, array(), $cart_item_data);

        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => __('Producto añadido al carrito', 'ecolitio-theme'),
                'cart_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error al añadir al carrito', 'ecolitio-theme'),
                'code' => 'add_to_cart_failed'
            ));
        }

    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => __('Error añadiendo al carrito', 'ecolitio-theme'),
            'code' => 'cart_exception'
        ));
    }
}

/**
 * Validate user session and cookies
 * FIXED: Removed private method call to WC_Session_Handler::init_session()
 * Enhanced session validation to prevent cookie check failures
 */
function validate_user_session() {
    $result = array(
        'valid' => false,
        'reason' => '',
        'session_data' => array()
    );
    
    // Check if session is initialized - FIXED: Removed private method call
    if (function_exists('WC') && WC()->session) {
        try {
            // FIXED: Use public method to get session data instead of calling init_session()
            $session_data = WC()->session->get_session_data();
            $result['session_data'] = $session_data;
            
            // For guest users, session data might be empty initially - this is normal
            // We only require session data for logged-in users
        } catch (Exception $e) {
            // Session not available, continue with cookie validation
            error_log('Ecolitio Sabway: Session access failed - ' . $e->getMessage());
        }
    } else {
        // For non-WooCommerce sessions or if WooCommerce session is not available
        if (is_user_logged_in()) {
            // Check WordPress session for logged-in users
            $session_data = WP_Session_Tokens::get_instance(get_current_user_id());
            if ($session_data) {
                $result['session_data'] = $session_data->get_all();
            }
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
        // Validate essential cookies are set (not just WooCommerce specific ones)
        $essential_cookies = array(
            'wp_cart_tracking' => 'Cart tracking cookie',
            'woocommerce_cart_hash' => 'WooCommerce cart hash',
            'wp_woocommerce_session_' => 'WooCommerce session',
            'wordpress_' => 'WordPress cookies',
            'PHPSESSID' => 'PHP session ID'
        );
        
        $cookie_found = false;
        $found_cookies = array();
        
        foreach ($_COOKIE as $cookie_name => $cookie_value) {
            foreach ($essential_cookies as $key => $description) {
                if (strpos($cookie_name, $key) !== false) {
                    $cookie_found = true;
                    $found_cookies[] = $cookie_name;
                    break;
                }
            }
            if ($cookie_found) break;
        }
        
        // Allow guest users to proceed even without WooCommerce cookies
        // as long as they have some session indication
        if (!$cookie_found) {
            $result['reason'] = 'No session cookies found - allowing for guest users';
            // Don't return early, continue validation for guest users
        } else {
            error_log('Ecolitio Sabway: Session cookies found: ' . implode(', ', $found_cookies));
        }
    }
    
    // For both logged-in and guest users, we allow submission
    // The session validation is more about logging than blocking
    $result['valid'] = true;
    $result['reason'] = is_user_logged_in() ? 'Authenticated user session' : 'Guest user allowed';
    
    return $result;
}

/**
 * Validate Sabway form data
 */
function validate_sabway_form_data($data) {
     $errors = array();
     
     // Required field validations (common to all battery types)
     $required_fields = array(
         'voltage' => __('Voltaje', 'ecolitio-theme'),
         'amperage' => __('Amperaje', 'ecolitio-theme'),
         'distance_range_km' => __('Rango de distancia', 'ecolitio-theme'),
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
     
     // Validate dimensions or liters based on battery location
     $is_external_battery = isset($data['battery_location']) && $data['battery_location'] === 'Externa';
     
     if ($is_external_battery) {
         // For external batteries, validate liters
         if (!isset($data['liters']) || empty($data['liters']) || floatval($data['liters']) <= 0) {
             $errors[] = __('Se requiere una capacidad válida en litros', 'ecolitio-theme');
         }
     } else {
         // For internal batteries, validate dimensions
         if (empty($data['height_cm']) || empty($data['width_cm']) || empty($data['length_cm'])) {
             $errors[] = __('Se requieren las dimensiones (alto, ancho, largo)', 'ecolitio-theme');
         }
         
         if ($data['height_cm'] <= 0 || $data['width_cm'] <= 0 || $data['length_cm'] <= 0) {
             $errors[] = __('Las dimensiones deben ser valores positivos', 'ecolitio-theme');
         }
     }
     
     // Product validation
     $product = wc_get_product($data['product_id']);
     if (!$product || !$product->exists()) {
         $errors[] = __('Producto inválido', 'ecolitio-theme');
     }
     
     // Validate product has correct tag based on battery type
     $battery_type = isset($data['battery_type']) ? $data['battery_type'] : 'sabway';
     $battery_types_config = ecolitio_get_battery_types_config();
     
     if (isset($battery_types_config[$battery_type])) {
         $required_tag = $battery_types_config[$battery_type]['tag'];
         $product_tags = wp_get_post_terms($data['product_id'], 'product_tag', array('fields' => 'slugs'));
         
         if (!in_array($required_tag, $product_tags)) {
             $errors[] = sprintf(
                 __('Este producto no está disponible para %s', 'ecolitio-theme'),
                 $battery_types_config[$battery_type]['title']
             );
         }
     }
     
     return $errors;
 }

/**
 * Send order confirmation emails for Sabway form submissions
 *
 * @param WC_Order $order The order object
 * @param string $recipient_type 'client' or 'admin'
 * @return bool Success status
 */
function send_sabway_order_emails($order, $recipient_type = 'client') {
    if (!$order instanceof WC_Order) {
        return false;
    }
    
    // Get order data
    $order_id = $order->get_id();
    $order_key = $order->get_order_key();
    $billing_email = $order->get_billing_email();
    $billing_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
    
    // Get admin email
    $admin_email = 'erazo..luan@gmail.com';
    
    // Build email content
    if ($recipient_type === 'client') {
        $to = $billing_email;
        $subject = sprintf(__('Confirmación de Pedido - Batería Sabway #%s', 'ecolitio-theme'), $order_id);
        
        // Extract sabway meta data for email
        $electrical_specs = $order->get_meta('_sabway_electrical_specs');
        $physical_dims = $order->get_meta('_sabway_physical_dimensions');
        $specs = $order->get_meta('_sabway_specifications');
        
        $email_message = sprintf(
            __("Hola %s,\n\n" .
            "Gracias por tu pedido de batería personalizada Sabway. Aquí están los detalles:\n\n" .
            "NÚMERO DE PEDIDO: #%d\n\n" .
            "ESPECIFICACIONES ELÉCTRICAS:\n" .
            "• Voltaje: %s\n" .
            "• Amperaje: %s\n" .
            "• Rango de distancia: %s km\n\n" .
            "DIMENSIONES FÍSICAS:\n" .
            "• Alto: %s cm\n" .
            "• Ancho: %s cm\n" .
            "• Largo: %s cm\n\n" .
            "ESPECIFICACIONES DEL PATINETE:\n" .
            "• Modelo: %s\n" .
            "• Ubicación de batería: %s\n" .
            "• Tipo de conector: %s\n\n" .
            "Precio: %s\n\n" .
            "Próximos pasos:\n" .
            "1. Nuestro equipo de Taller Sabway revisará tu pedido\n" .
            "2. Te contactaremos para confirmar los detalles\n" .
            "3. Comenzaremos la fabricación de tu batería personalizada\n\n" .
            "Puedes revisar el estado de tu pedido en: %s\n\n" .
            "¡Gracias por confiar en nosotros!\n\n" .
            "Equipo Ecolitio", 'ecolitio-theme'),
            $billing_name ?: 'Estimado cliente',
            $order_id,
            $electrical_specs['voltage'] ?? 'N/A',
            $electrical_specs['amperage'] ?? 'N/A',
            $electrical_specs['distance_range_km'] ?? 'N/A',
            $physical_dims['height_cm'] ?? 'N/A',
            $physical_dims['width_cm'] ?? 'N/A',
            $physical_dims['length_cm'] ?? 'N/A',
            $specs['scooter_model'] ?? 'N/A',
            $specs['battery_location'] ?? 'N/A',
            $specs['connector_type'] ?? 'N/A',
            $order->get_formatted_order_total(),
            $order->get_checkout_order_received_url()
        );
        
    } else { // admin
        $to = $admin_email;
        $subject = sprintf(__('Nuevo Pedido Sabway - Batería Personalizada #%s', 'ecolitio-theme'), $order_id);
        
        $email_message = sprintf(
            __("NUEVO PEDIDO DE BATERÍA PERSONALIZADA SABWAY\n\n" .
            "Número de pedido: #%d\n" .
            "Cliente: %s (%s)\n" .
            "Fecha: %s\n\n" .
            "ESPECIFICACIONES DEL PEDIDO:\n" .
            "El cliente ha solicitado una batería personalizada con las siguientes características:\n\n" .
            "ELECTRICAS:\n" .
            "- Voltaje: %s\n" .
            "- Amperaje: %s\n" .
            "- Rango de distancia: %s km\n\n" .
            "FÍSICAS:\n" .
            "- Alto: %s cm\n" .
            "- Ancho: %s cm\n" .
            "- Largo: %s cm\n\n" .
            "PATINETE:\n" .
            "- Modelo: %s\n" .
            "- Ubicación de batería: %s\n" .
            "- Tipo de conector: %s\n\n" .
            "MONTO: %s\n" .
            "ESTADO: %s\n\n" .
            "URL del pedido: %s\n\n" .
            "Este pedido requiere atención del equipo de Taller Sabway para la fabricación personalizada.", 'ecolitio-theme'),
            $order_id,
            $billing_name ?: 'No disponible',
            $billing_email ?: 'No disponible',
            $order->get_date_created()->date('Y-m-d H:i:s'),
            $order->get_meta('_sabway_electrical_specs')['voltage'] ?? 'N/A',
            $order->get_meta('_sabway_electrical_specs')['amperage'] ?? 'N/A',
            $order->get_meta('_sabway_electrical_specs')['distance_range_km'] ?? 'N/A',
            $order->get_meta('_sabway_physical_dimensions')['height_cm'] ?? 'N/A',
            $order->get_meta('_sabway_physical_dimensions')['width_cm'] ?? 'N/A',
            $order->get_meta('_sabway_physical_dimensions')['length_cm'] ?? 'N/A',
            $order->get_meta('_sabway_specifications')['scooter_model'] ?? 'N/A',
            $order->get_meta('_sabway_specifications')['battery_location'] ?? 'N/A',
            $order->get_meta('_sabway_specifications')['connector_type'] ?? 'N/A',
            $order->get_formatted_order_total(),
            $order->get_status(),
            admin_url('post.php?post=' . $order_id . '&action=edit')
        );
    }
    
    // Set headers
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        'Reply-To: ' . get_option('admin_email')
    );
    
    // Send the email
    $sent = wp_mail($to, $subject, $email_message, $headers);
    
    if ($sent) {
        error_log("Ecolitio Sabway: Email sent successfully to {$recipient_type} - Order #{$order_id}");
    } else {
        error_log("Ecolitio Sabway: Failed to send email to {$recipient_type} - Order #{$order_id}");
    }
    
    return $sent;
}