<?php
/**
 * Ecolitio Theme Functions
 *
 * Modular WordPress theme with WooCommerce integration
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once get_stylesheet_directory() . '/inc/custom-post-taxonomies.php';

/**
 * Include AJAX handlers
 */
require_once get_stylesheet_directory() . '/inc/ajax.php';

/**
 * Include Taller Sabway user role functionality
 */
require_once get_stylesheet_directory() . '/inc/class-taller-sabway-role.php';

// =============================================================================
// DEPENDENCIES & AUTOLOADING
// =============================================================================

/**
 * Load Composer autoloader for third-party dependencies
 */
$autoload_path = get_stylesheet_directory() . '/vendor/autoload.php';
if (!file_exists($autoload_path)):
    error_log('Autoload file not found at ' . $autoload_path);
else:
    require_once $autoload_path;
endif;

use Idleberg\WordPress\ViteAssets\Assets;

// =============================================================================
// THEME SETUP & CONFIGURATION
// =============================================================================

/**
 * Theme setup and configuration
 */
add_action('after_setup_theme', 'ecolitio_theme_setup');
function ecolitio_theme_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list'));
    add_theme_support('woocommerce');

    // Set content width
    if (!isset($content_width)) {
        $content_width = 1200;
    }
}

// =============================================================================
// ASSET MANAGEMENT
// =============================================================================

/**
 * Enqueue theme stylesheets
 * Ensures proper loading order and prevents duplication
 */
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_styles', 5); // Load earlier to prevent conflicts
function ecolitio_enqueue_styles() {
    // Check if parent style is already enqueued to prevent duplication
    if (!wp_style_is('storefront-style', 'enqueued') && !wp_style_is('parent-style', 'enqueued')) {
        // Enqueue parent theme stylesheet first
        wp_enqueue_style(
            'parent-style',
            get_template_directory_uri() . '/style.css',
            array(),
            wp_get_theme(get_template())->get('Version')
        );
    }

    // Only enqueue child theme stylesheet if not already enqueued
    if (!wp_style_is('ecolitio-style', 'enqueued')) {
        wp_enqueue_style(
            'ecolitio-style',
            get_stylesheet_uri(),
            array('parent-style'), // Ensure it loads after parent
            wp_get_theme()->get('Version')
        );
    }
}

/**
 * Enqueue JavaScript assets with Vite integration
 * Handles both development (HMR) and production builds
 */
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_scripts', 15); // Load after styles to ensure proper dependency order
function ecolitio_enqueue_scripts() {
    // Separate Vite JS and CSS enqueuing for better control
    ecolitio_enqueue_vite_js();
    ecolitio_enqueue_vite_css();
    
    // Generate and localize WooCommerce REST API nonce and consumer keys for taller_sabway role
    if (ecolitio_is_woocommerce_active() && current_user_can('taller_sabway')) {
        $wc_rest_nonce = wp_create_nonce('wp_rest');
        
        // Generate or retrieve consumer keys for REST API authentication
        $consumer_keys = generate_taller_sabway_consumer_keys();
        
        wp_localize_script('ecolitio-main-js', 'ecolitioWcApi', array(
            'restUrl' => rest_url('wc/v3/'),
            'restNonce' => $wc_rest_nonce,
            'consumerKey' => $consumer_keys['key'],
            'consumerSecret' => $consumer_keys['secret'],
            'userId' => get_current_user_id(),
            'authenticationMethod' => 'consumer_key',
            'userCapabilities' => array(
                'edit_shop_orders' => current_user_can('edit_shop_orders'),
                'create_shop_orders' => current_user_can('create_shop_orders'),
                'edit_shop_order_items' => current_user_can('edit_shop_order_items')
            )
        ));
    }
}

/**
 * Enqueue Vite JavaScript assets for development and production
 */
function ecolitio_enqueue_vite_js() {
    $manifest_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

    // Check if manifest exists (production build)
    if (file_exists($manifest_path) && is_readable($manifest_path)) {
        $manifest = json_decode(file_get_contents($manifest_path), true);

        if (isset($manifest['src/main.js'])) {
            // Enqueue main JavaScript file
            wp_enqueue_script(
                'ecolitio-main-js',
                get_stylesheet_directory_uri() . '/dist/' . $manifest['src/main.js']['file'],
                array(),
                null,
                true
            );
        }
    } else {
        // Fallback for development or when manifest doesn't exist
        wp_enqueue_script(
            'ecolitio-main-js-fallback',
            get_stylesheet_directory_uri() . '/src/main.js',
            array('jquery'),
            '1.0.0',
            true
        );
    }
}

/**
 * Enqueue Vite CSS assets for development and production
 * Ensures CSS loads after Elementor styles
 */
function ecolitio_enqueue_vite_css() {
    $manifest_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

    // Build dependency array - include Elementor if active
    $css_dependencies = array('parent-style', 'ecolitio-style');
    if (defined('ELEMENTOR_VERSION')) {
        $css_dependencies[] = 'elementor-frontend';
    }

    // Check if manifest exists (production build)
    if (file_exists($manifest_path) && is_readable($manifest_path)) {
        $manifest = json_decode(file_get_contents($manifest_path), true);

        if (isset($manifest['src/main.js']['css']) && is_array($manifest['src/main.js']['css'])) {
            foreach ($manifest['src/main.js']['css'] as $index => $css_file) {
                $handle = 'ecolitio-main-css-' . $index;

                // Avoid duplicate enqueuing
                if (!wp_style_is($handle, 'enqueued')) {
                    wp_enqueue_style(
                        $handle,
                        get_stylesheet_directory_uri() . '/dist/' . $css_file,
                        $css_dependencies,
                        null
                    );
                }
            }
        }
    }
    // Note: No CSS fallback needed for development as Vite handles HMR
}

/**
 * Ensure CSS loading priority and prevent duplication
 * This runs at a high priority to override any conflicting enqueues
 */
add_action('wp_enqueue_scripts', 'ecolitio_ensure_css_priority', 1000);
function ecolitio_ensure_css_priority() {
    // Prevent duplicate Storefront styles
    if (wp_style_is('storefront-style', 'enqueued')) {
        wp_dequeue_style('storefront-style');
    }

    // Ensure our Vite CSS loads after everything else
    $manifest_path = get_stylesheet_directory() . '/dist/.vite/manifest.json';

    if (file_exists($manifest_path) && is_readable($manifest_path)) {
        $manifest = json_decode(file_get_contents($manifest_path), true);

        if (isset($manifest['src/main.js']['css']) && is_array($manifest['src/main.js']['css'])) {
            // Get all registered styles to build comprehensive dependencies
            $all_deps = array_keys(wp_styles()->registered);

            foreach ($manifest['src/main.js']['css'] as $index => $css_file) {
                $handle = 'ecolitio-main-css-' . $index;

                // Dequeue if already enqueued to re-enqueue with proper dependencies
                if (wp_style_is($handle, 'enqueued')) {
                    wp_dequeue_style($handle);
                }

                // Enqueue with all current styles as dependencies to ensure it loads last
                wp_enqueue_style(
                    $handle,
                    get_stylesheet_directory_uri() . '/dist/' . $css_file,
                    $all_deps, // Load after ALL currently registered styles
                    null
                );
            }
        }
    }
}


// =============================================================================
// WOOCOMMERCE INTEGRATION
// =============================================================================

/**
 * Check if WooCommerce is active and properly configured
 */
function ecolitio_is_woocommerce_active() {
    return class_exists('WooCommerce');
}


// =============================================================================
// TEMPLATE FUNCTIONS
// =============================================================================

/**
 * Render products grid with pagination
 * Uses modular templates for clean separation of concerns
 */
function ecolitio_render_products_grid($args = array()): void
{
    $defaults = array(
        'posts_per_page' => 9,
        'current_page'   => 1,
        'show_pagination' => true,
    );

    $args = wp_parse_args($args, $defaults);

    // Check WooCommerce availability
    if (!ecolitio_is_woocommerce_active()):
        echo '<div class="text-center py-8"><p class="text-gray-500">' . esc_html__('WooCommerce no está activo.', 'ecolitio-theme') . '</p></div>';
        return;
    endif;

    // Build query
    $query_args = array(
        'post_type'      => 'product',
        'posts_per_page' => $args['posts_per_page'],
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );

    if ($args['current_page'] > 1):
        $query_args['offset'] = ($args['current_page'] - 1) * $args['posts_per_page'];
    endif;

    $products_query = new WP_Query($query_args);
    $total_products = wp_count_posts('product')->publish;
    $total_pages = ceil($total_products / $args['posts_per_page']);

    // Set template variables
    set_query_var('products_query', $products_query);
    set_query_var('current_page', $args['current_page']);
    set_query_var('total_pages', $total_pages);
    set_query_var('show_pagination', $args['show_pagination']);

    // Render template
    get_template_part('templates/products-grid');

    wp_reset_postdata();
}

// =============================================================================
// SABWAY BATTERY FORM SHORTCODE
// =============================================================================

/**
 * Register Sabway Battery Form Shortcode
 * 
 * Usage: [sabway_battery_form] or [sabway_battery_form product_id="123"]
 * 
 * @param array $atts Shortcode attributes
 * @return string Rendered form HTML
 */
add_shortcode('sabway_battery_form', 'ecolitio_sabway_battery_form_shortcode');
function ecolitio_sabway_battery_form_shortcode($atts = array()) {
	// Parse shortcode attributes
	$atts = shortcode_atts(array(
		'product_id' => 0,
		'show_title' => 'yes',
		'custom_class' => '',
	), $atts, 'sabway_battery_form');

	// Determine product ID
	$product_id = intval($atts['product_id']);
	
	// If no product ID provided, try to get from current product (if on product page)
	if (!$product_id) {
		global $product;
		if ($product && is_a($product, 'WC_Product')) {
			$product_id = $product->get_id();
		}
	}

	// Validate product exists and is published
	if (!$product_id) {
		return '<div class="sabway-form-error !p-4 !rounded-lg !bg-red-500 !text-white-eco">' . 
			esc_html__('Error: No product specified. Use [sabway_battery_form product_id="123"]', 'ecolitio-theme') . 
			'</div>';
	}

	// Get product object
	$product = wc_get_product($product_id);
	if (!$product || !$product->is_visible()) {
		return '<div class="sabway-form-error !p-4 !rounded-lg !bg-red-500 !text-white-eco">' . 
			esc_html__('Error: Product not found or not visible.', 'ecolitio-theme') . 
			'</div>';
	}

	// Get product attributes
	$getAttributes = $product->get_attributes();
	
	// Validate required attributes exist
	if (empty($getAttributes['voltios']) || empty($getAttributes['amperios'])) {
		return '<div class="sabway-form-error !p-4 !rounded-lg !bg-red-500 !text-white-eco">' . 
			esc_html__('Error: Product is missing required attributes (voltios, amperios).', 'ecolitio-theme') . 
			'</div>';
	}

	// Set up form data
	$icons = array(
		"step1" => array(
			"icon" => "ix:electrical-energy-filled",
			"title" => "Parte eléctrica",
		),
		"step2" => array(
			"icon" => "tabler:dimensions",
			"title" => "Dimensiones",
		),
		"step3" => array(
			"icon" => "material-symbols:cable",
			"title" => "Conectores",
		),
		"step4" => array(
			"icon" => "material-symbols:check-circle",
			"title" => "Confirmación",
		),
	);

	$distance = 30;
	$sabway_form_nonce = wp_create_nonce('ecolitio_sabway_form_nonce');

	// Start output buffering
	ob_start();
	
	// Load the form template
	set_query_var('product', $product);
	set_query_var('icons', $icons);
	set_query_var('getAttributes', $getAttributes);
	set_query_var('distance', $distance);
	set_query_var('sabway_form_nonce', $sabway_form_nonce);
	
	get_template_part('templates/sabway-battery-form');
	
	// Get buffered content
	$form_html = ob_get_clean();

	// Wrap with custom class if provided
	if (!empty($atts['custom_class'])) {
		$form_html = '<div class="' . esc_attr($atts['custom_class']) . '">' . $form_html . '</div>';
	}

	return $form_html;
}




// =============================================================================
// HOOKS & FILTERS
// =============================================================================

/**
 * Add custom body classes for theme-specific styling
 */
add_filter('body_class', 'ecolitio_body_classes');
function ecolitio_body_classes($classes) {
    $classes[] = 'ecolitio-theme';
    return $classes;
}

/**
 * Filter to modify products per page
 */
add_filter('ecolitio_products_per_page', 'ecolitio_modify_products_per_page');
function ecolitio_modify_products_per_page($per_page) {
    // Allow child themes or plugins to modify this value
    return apply_filters('ecolitio_products_per_page_override', $per_page);
}

if ( has_action( 'storefront_sidebar' ) ) {
	remove_action( 'storefront_sidebar' );
}

// =============================================================================
// DEVELOPMENT HELPERS (Remove in production)
// =============================================================================

if (defined('WP_DEBUG') && WP_DEBUG) {
    /**
     * Add development helper functions
     */
    add_action('wp_footer', 'ecolitio_development_info');
    function ecolitio_development_info() {
        if (current_user_can('administrator')) {
            echo '<!-- Ecolitio Theme Development Mode Active -->';
        }
    }
}

// =============================================================================
// WOOCOMMERCE REST API AUTHENTICATION HELPERS
// =============================================================================

/**
 * Generate WooCommerce Consumer Keys for REST API authentication
 * This provides an alternative to role-based permissions
 */
function generate_taller_sabway_consumer_keys() {
    // In production, you would store these in a secure location
    // For development/testing, we generate them dynamically
    $consumer_key = 'ck_' . wp_generate_password(64, false);
    $consumer_secret = 'cs_' . wp_generate_password(64, false);
    
    // You could store these in database or environment variables
    // For now, we'll use environment variables or generate them
    if (defined('TALLER_SABWAY_CONSUMER_KEY') && defined('TALLER_SABWAY_CONSUMER_SECRET')) {
        return array(
            'key' => TALLER_SABWAY_CONSUMER_KEY,
            'secret' => TALLER_SABWAY_CONSUMER_SECRET
        );
    }
    
    return array(
        'key' => $consumer_key,
        'secret' => $consumer_secret
    );
}

/**
 * Get WooCommerce REST API authentication credentials
 */
function get_taller_sabway_wc_auth_credentials() {
    $credentials = array();
    
    // Method 1: Check if we have environment variables set
    if (defined('TALLER_SABWAY_CONSUMER_KEY') && defined('TALLER_SABWAY_CONSUMER_SECRET')) {
        $credentials['key'] = TALLER_SABWAY_CONSUMER_KEY;
        $credentials['secret'] = TALLER_SABWAY_CONSUMER_SECRET;
        $credentials['method'] = 'consumer_key';
    }
    // Method 2: Use default developer credentials (for testing only)
    else {
        $credentials['key'] = 'ck_test_123456789';
        $credentials['secret'] = 'cs_test_987654321';
        $credentials['method'] = 'consumer_key_dev';
    }
    
    return $credentials;
/**
 * Enhanced Sabway Form Security System
 * Provides comprehensive security for form submissions
 */
class Sabway_Form_Security {
    
    /**
     * Create and manage form nonces
     */
    public static function create_form_nonce() {
        return wp_create_nonce('ecolitio_sabway_form_nonce');
    }
    
    /**
     * Verify form nonce
     */
    public static function verify_form_nonce($nonce) {
        return wp_verify_nonce($nonce, 'ecolitio_sabway_form_nonce');
    }
    
    /**
     * Get enhanced AJAX configuration for frontend
     */
    public static function get_ajax_config() {
        $ajax_config = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => self::create_form_nonce(),
            'sabway_form_nonce' => self::create_form_nonce(),
            'user_logged_in' => is_user_logged_in(),
            'current_user_id' => get_current_user_id(),
            'user_capabilities' => array(
                'taller_sabway' => current_user_can('taller_sabway'),
                'edit_shop_orders' => current_user_can('edit_shop_orders'),
                'create_shop_orders' => current_user_can('create_shop_orders'),
            ),
            'strings' => array(
                'loading' => __('Cargando...', 'ecolitio-theme'),
                'error' => __('Error al enviar el pedido', 'ecolitio-theme'),
                'validation_failed' => __('Datos del formulario inválidos', 'ecolitio-theme'),
                'permission_denied' => __('No tienes permisos para realizar esta acción', 'ecolitio-theme'),
                'session_expired' => __('Sesión expirada. Por favor, recarga la página', 'ecolitio-theme'),
                'nonce_failed' => __('Verificación de seguridad fallida', 'ecolitio-theme'),
            ),
            'validation_rules' => array(
                'voltage' => array('required' => true),
                'amperage' => array('required' => true),
                'distance_range_km' => array('required' => true, 'min' => 10, 'max' => 100),
                'height_cm' => array('required' => true, 'min' => 0.1),
                'width_cm' => array('required' => true, 'min' => 0.1),
                'length_cm' => array('required' => true, 'min' => 0.1),
                'scooter_model' => array('required' => true),
                'battery_location' => array('required' => true),
                'connector_type' => array('required' => true),
            )
        );
        
        return apply_filters('sabway_ajax_config', $ajax_config);
    }
}

/**
 * Enhanced script enqueue with security measures for Sabway forms
 */
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_sabway_form_security', 25);
function ecolitio_enqueue_sabway_form_security() {
    // Check if current page/post has the Sabway form
    // Removed strict content check as the form might be in a template
    global $post;
    if (!$post) {
        return;
    }

    // Get script handle from main enqueue function
    $main_script_handle = 'ecolitio-main-js';
    if (!wp_script_is($main_script_handle, 'enqueued')) {
        return;
    }

    // Localize script with enhanced AJAX data and security
    wp_localize_script($main_script_handle, 'taller_sabway_ajax', Sabway_Form_Security::get_ajax_config());
    
    // Also provide legacy support for old scripts
    wp_localize_script($main_script_handle, 'ecolitio_ajax', Sabway_Form_Security::get_ajax_config());
    
    // Add additional security headers via JavaScript
    wp_add_inline_script($main_script_handle, '
        // Security headers for AJAX requests
        (function() {
            // Add security headers for WordPress AJAX
            var originalFetch = window.fetch;
            window.fetch = function(url, options) {
                if (url.includes("admin-ajax.php") && url.includes("action=sabway_submit_form")) {
                    options = options || {};
                    options.credentials = "include";
                    options.headers = options.headers || {};
                    options.headers["X-Requested-With"] = "XMLHttpRequest";
                    options.headers["X-WP-Nonce"] = window.taller_sabway_ajax?.nonce || "";
                }
                return originalFetch.call(this, url, options);
            };
        })();
    ');
}
}

/**
 * Display custom battery data in cart
 */
add_filter('woocommerce_get_item_data', 'ecolitio_display_custom_battery_data_cart', 10, 2);
function ecolitio_display_custom_battery_data_cart($item_data, $cart_item) {
    if (isset($cart_item['_sabway_custom_order'])) {
        // Electrical Specs
        if (isset($cart_item['_sabway_electrical_specs'])) {
            $specs = $cart_item['_sabway_electrical_specs'];
            if (!empty($specs['voltage'])) {
                $item_data[] = array(
                    'key'     => __('Voltaje', 'ecolitio-theme'),
                    'value'   => $specs['voltage'],
                    'display' => $specs['voltage'],
                );
            }
            if (!empty($specs['amperage'])) {
                $item_data[] = array(
                    'key'     => __('Amperaje', 'ecolitio-theme'),
                    'value'   => $specs['amperage'],
                    'display' => $specs['amperage'],
                );
            }
            if (!empty($specs['distance_range_km'])) {
                $item_data[] = array(
                    'key'     => __('Autonomía', 'ecolitio-theme'),
                    'value'   => $specs['distance_range_km'] . ' km',
                    'display' => $specs['distance_range_km'] . ' km',
                );
            }
        }

        // Physical Dimensions
        if (isset($cart_item['_sabway_physical_dimensions'])) {
            $dims = $cart_item['_sabway_physical_dimensions'];
            $dimensions_str = sprintf(
                '%s x %s x %s cm',
                $dims['height_cm'],
                $dims['width_cm'],
                $dims['length_cm']
            );
            $item_data[] = array(
                'key'     => __('Dimensiones', 'ecolitio-theme'),
                'value'   => $dimensions_str,
                'display' => $dimensions_str,
            );
        }

        // Other Specs
        if (isset($cart_item['_sabway_specifications'])) {
            $other = $cart_item['_sabway_specifications'];
            if (!empty($other['scooter_model'])) {
                $item_data[] = array(
                    'key'     => __('Modelo Patinete', 'ecolitio-theme'),
                    'value'   => $other['scooter_model'],
                    'display' => $other['scooter_model'],
                );
            }
            if (!empty($other['battery_location'])) {
                $item_data[] = array(
                    'key'     => __('Ubicación', 'ecolitio-theme'),
                    'value'   => $other['battery_location'],
                    'display' => $other['battery_location'],
                );
            }
            if (!empty($other['connector_type'])) {
                $item_data[] = array(
                    'key'     => __('Conector', 'ecolitio-theme'),
                    'value'   => $other['connector_type'],
                    'display' => $other['connector_type'],
                );
            }
        }
    }
    return $item_data;
}

/**
 * Save custom battery data to order items
 */
add_action('woocommerce_checkout_create_order_line_item', 'ecolitio_save_custom_battery_data_order', 10, 4);
function ecolitio_save_custom_battery_data_order($item, $cart_item_key, $values, $order) {
    if (isset($values['_sabway_custom_order'])) {
        $item->add_meta_data('_sabway_custom_order', true);
        
        if (isset($values['_sabway_electrical_specs'])) {
            $item->add_meta_data('_sabway_electrical_specs', $values['_sabway_electrical_specs']);
            // Add visible meta for customer
            $specs = $values['_sabway_electrical_specs'];
            $item->add_meta_data(__('Voltaje', 'ecolitio-theme'), $specs['voltage']);
            $item->add_meta_data(__('Amperaje', 'ecolitio-theme'), $specs['amperage']);
            $item->add_meta_data(__('Autonomía', 'ecolitio-theme'), $specs['distance_range_km'] . ' km');
        }
        
        if (isset($values['_sabway_physical_dimensions'])) {
            $item->add_meta_data('_sabway_physical_dimensions', $values['_sabway_physical_dimensions']);
            $dims = $values['_sabway_physical_dimensions'];
            $item->add_meta_data(__('Dimensiones', 'ecolitio-theme'), sprintf('%s x %s x %s cm', $dims['height_cm'], $dims['width_cm'], $dims['length_cm']));
        }
        
        if (isset($values['_sabway_specifications'])) {
            $item->add_meta_data('_sabway_specifications', $values['_sabway_specifications']);
            $other = $values['_sabway_specifications'];
            $item->add_meta_data(__('Modelo Patinete', 'ecolitio-theme'), $other['scooter_model']);
            $item->add_meta_data(__('Ubicación', 'ecolitio-theme'), $other['battery_location']);
            $item->add_meta_data(__('Conector', 'ecolitio-theme'), $other['connector_type']);
        }
    }
}

//-------------------------------- Reparacion_form
// Refactored to use Elementor Pro Action_Base class
// See: themes/ecolitio-theme/inc/form-action.php

/**
 * Register Reparacion form action with Elementor Pro
 *
 * @since 1.0.0
 * @param ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $form_actions_registrar
 * @return void
 */
function ecolitio_register_reparacion_form_action( $form_actions_registrar ) {
	include_once( get_stylesheet_directory() . '/inc/form-action.php' );
	$form_actions_registrar->register( new ReparacionesAddtoCart() );
}
add_action( 'elementor_pro/forms/actions/register', 'ecolitio_register_reparacion_form_action' );

/**
 * Handle Reparacion form redirect via JavaScript
 * Elementor Pro sends redirect_url in response data
 */
add_action( 'wp_enqueue_scripts', 'ecolitio_enqueue_reparacion_redirect_handler', 999 );
function ecolitio_enqueue_reparacion_redirect_handler() {
	wp_add_inline_script( 'elementor-pro-forms', "
		(function() {
			// Listen for Elementor form submission response
			document.addEventListener( 'elementor_pro/forms/submit/response', function( event ) {
				if ( event.detail && event.detail.response && event.detail.response.redirect_url ) {
					console.log( 'Reparacion form redirect to:', event.detail.response.redirect_url );
					window.location.href = event.detail.response.redirect_url;
				}
			});
			
			// Also handle via jQuery if available
			if ( typeof jQuery !== 'undefined' ) {
				jQuery( document ).on( 'elementor_pro/forms/submit/response', function( event, response ) {
					if ( response && response.redirect_url ) {
						console.log( 'Reparacion form redirect (jQuery) to:', response.redirect_url );
						window.location.href = response.redirect_url;
					}
				});
			}
		})();
	", 'after' );
}

add_filter( 'woocommerce_get_item_data', 'ecolitio_show_reparacion_nota_in_cart', 10, 2 );

function ecolitio_show_reparacion_nota_in_cart( $item_data, $cart_item ) {
    if ( isset( $cart_item['reparacion_nota'] ) && ! empty( $cart_item['reparacion_nota'] ) ) {
        $item_data[] = array(
            'key'   => __( 'Nota de reparación', 'ecolitio' ),
            'value' => wp_kses_post( nl2br( $cart_item['reparacion_nota'] ) ),
        );
    }
    return $item_data;
}
// ---------------logout custom
add_action('wp_head', function() {
    // Create WooCommerce logout nonce with proper action
    $logout_nonce = wp_create_nonce('log-out');
    echo '<meta name="wc-logout-nonce" content="' . esc_attr($logout_nonce) . '">';
    
    // Also provide it as a JavaScript variable for easy access
    echo '<script>
        window.wcLogoutNonce = "' . esc_attr($logout_nonce) . '";
    </script>';
});

// remove the payment methods and cupons if youre a sabwaytaller
add_action('wp_head', function() {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        
        if (current_user_can('taller_sabway')) {
            echo '<style> .e-coupon-box { display: none !important; } li[data-selected="true"] {
                background: var(--e-global-color-c1140c0) !important;
            } button#place_order { background: var(--e-global-color-c1140c0) !important} button#place_order:hover { background: black !important; color: var(--e-global-color-c1140c0)}
            a[href]:focus, area[href]:focus, button:focus, input:not([type="hidden"]):focus, select:focus, textarea:focus, [tabindex]:not([tabindex="-1"]):focus, [contenteditable="true"]:focus { outline-color: var(--color-red-sabway) !important; 
            </style>';
        } else {
            echo '<style>
             a[href]:focus, area[href]:focus, button:focus, input:not([type="hidden"]):focus, select:focus, textarea:focus, [tabindex]:not([tabindex="-1"]):focus, [contenteditable="true"]:focus { outline-color: var(--color-green-eco) !important; } </style>';
        }
    }
});

