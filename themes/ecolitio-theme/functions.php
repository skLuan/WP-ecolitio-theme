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

// =============================================================================
// INCLUDES
// =============================================================================

/**
 * Include custom post types and taxonomies
 */
require_once get_stylesheet_directory() . '/inc/custom-post-taxonomies.php';

/**
 * Include AJAX handlers
 */
require_once get_stylesheet_directory() . '/inc/ajax.php';

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
