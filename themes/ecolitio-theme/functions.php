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
 * Ensures proper loading order: parent first, then child
 */
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_styles', 10);
function ecolitio_enqueue_styles() {
    // Enqueue parent theme stylesheet first
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme(get_template())->get('Version')
    );

    // Enqueue child theme stylesheet with parent as dependency
    wp_enqueue_style(
        'ecolitio-style',
        get_stylesheet_uri(),
        array('parent-style'),
        wp_get_theme()->get('Version')
    );
}

/**
 * Enqueue JavaScript assets with Vite integration
 * Handles both development (HMR) and production builds
 */
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_scripts', 10);
function ecolitio_enqueue_scripts() {
    // Vite integration for development and production
    ecolitio_enqueue_vite_assets();

    // Products AJAX functionality
    ecolitio_enqueue_products_scripts();
}

/**
 * Handle Vite asset enqueuing for development and production
 */
function ecolitio_enqueue_vite_assets() {
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

            // Enqueue associated CSS files
            if (isset($manifest['src/main.js']['css']) && is_array($manifest['src/main.js']['css'])) {
                foreach ($manifest['src/main.js']['css'] as $index => $css_file) {
                    wp_enqueue_style(
                        'ecolitio-main-css-' . $index,
                        get_stylesheet_directory_uri() . '/dist/' . $css_file,
                        array('parent-style', 'ecolitio-style'),
                        null
                    );
                }
            }
        }
    } else {
        // Fallback for development or when manifest doesn't exist
        // This could be enhanced to check for dev server availability
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
// WOOCOMMERCE INTEGRATION
// =============================================================================

/**
 * Check if WooCommerce is active and properly configured
 */
function ecolitio_is_woocommerce_active() {
    return class_exists('WooCommerce');
}

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
// CUSTOM POST TYPES & TAXONOMIES
// =============================================================================

/**
 * Register FAQ custom post type
 */
function ecolitio_register_faq_post_type() {
    $labels = array(
        'name'                  => _x('Preguntas Frecuentes', 'Post type general name', 'ecolitio-theme'),
        'singular_name'         => _x('Pregunta Frecuente', 'Post type singular name', 'ecolitio-theme'),
        'menu_name'             => _x('Preguntas Frecuentes', 'Admin Menu text', 'ecolitio-theme'),
        'name_admin_bar'        => _x('Pregunta Frecuente', 'Add New on Toolbar', 'ecolitio-theme'),
        'add_new'               => __('Añadir Nueva', 'ecolitio-theme'),
        'add_new_item'          => __('Añadir Nueva Pregunta Frecuente', 'ecolitio-theme'),
        'new_item'              => __('Nueva Pregunta Frecuente', 'ecolitio-theme'),
        'edit_item'             => __('Editar Pregunta Frecuente', 'ecolitio-theme'),
        'view_item'             => __('Ver Pregunta Frecuente', 'ecolitio-theme'),
        'all_items'             => __('Todas las Preguntas Frecuentes', 'ecolitio-theme'),
        'search_items'          => __('Buscar Preguntas Frecuentes', 'ecolitio-theme'),
        'parent_item_colon'     => __('Pregunta Frecuente Padre:', 'ecolitio-theme'),
        'not_found'             => __('No se encontraron preguntas frecuentes.', 'ecolitio-theme'),
        'not_found_in_trash'    => __('No se encontraron preguntas frecuentes en la papelera.', 'ecolitio-theme'),
        'featured_image'        => _x('Imagen Destacada', 'Overrides the "Featured Image" phrase', 'ecolitio-theme'),
        'set_featured_image'    => _x('Establecer imagen destacada', 'Overrides the "Set featured image" phrase', 'ecolitio-theme'),
        'remove_featured_image' => _x('Remover imagen destacada', 'Overrides the "Remove featured image" phrase', 'ecolitio-theme'),
        'use_featured_image'    => _x('Usar como imagen destacada', 'Overrides the "Use as featured image" phrase', 'ecolitio-theme'),
        'archives'              => _x('Archivo de Preguntas Frecuentes', 'The post type archive label used in nav menus', 'ecolitio-theme'),
        'insert_into_item'      => _x('Insertar en pregunta frecuente', 'Overrides the "Insert into post"/"Insert into page" phrase', 'ecolitio-theme'),
        'uploaded_to_this_item' => _x('Subido a esta pregunta frecuente', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'ecolitio-theme'),
        'filter_items_list'     => _x('Filtrar lista de preguntas frecuentes', 'Screen reader text for the filter links heading on the post type listing screen', 'ecolitio-theme'),
        'items_list_navigation' => _x('Navegación de lista de preguntas frecuentes', 'Screen reader text for the pagination heading on the post type listing screen', 'ecolitio-theme'),
        'items_list'            => _x('Lista de preguntas frecuentes', 'Screen reader text for the items list heading on the post type listing screen', 'ecolitio-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'faq'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('faq', $args);
}
add_action('init', 'ecolitio_register_faq_post_type');

/**
 * Register FAQ category taxonomy
 */
function ecolitio_register_faq_taxonomies() {
    $labels = array(
        'name'              => _x('Categorías FAQ', 'taxonomy general name', 'ecolitio-theme'),
        'singular_name'     => _x('Categoría FAQ', 'taxonomy singular name', 'ecolitio-theme'),
        'search_items'      => __('Buscar Categorías FAQ', 'ecolitio-theme'),
        'all_items'         => __('Todas las Categorías FAQ', 'ecolitio-theme'),
        'parent_item'       => __('Categoría FAQ Padre', 'ecolitio-theme'),
        'parent_item_colon' => __('Categoría FAQ Padre:', 'ecolitio-theme'),
        'edit_item'         => __('Editar Categoría FAQ', 'ecolitio-theme'),
        'update_item'       => __('Actualizar Categoría FAQ', 'ecolitio-theme'),
        'add_new_item'      => __('Añadir Nueva Categoría FAQ', 'ecolitio-theme'),
        'new_item_name'     => __('Nombre de Nueva Categoría FAQ', 'ecolitio-theme'),
        'menu_name'         => __('Categorías FAQ', 'ecolitio-theme'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq_category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('faq_category', array('faq'), $args);

    // Register tag taxonomy
    $tag_labels = array(
        'name'              => _x('Etiquetas FAQ', 'taxonomy general name', 'ecolitio-theme'),
        'singular_name'     => _x('Etiqueta FAQ', 'taxonomy singular name', 'ecolitio-theme'),
        'search_items'      => __('Buscar Etiquetas FAQ', 'ecolitio-theme'),
        'all_items'         => __('Todas las Etiquetas FAQ', 'ecolitio-theme'),
        'edit_item'         => __('Editar Etiqueta FAQ', 'ecolitio-theme'),
        'update_item'       => __('Actualizar Etiqueta FAQ', 'ecolitio-theme'),
        'add_new_item'      => __('Añadir Nueva Etiqueta FAQ', 'ecolitio-theme'),
        'new_item_name'     => __('Nombre de Nueva Etiqueta FAQ', 'ecolitio-theme'),
        'menu_name'         => __('Etiquetas FAQ', 'ecolitio-theme'),
    );

    $tag_args = array(
        'hierarchical'      => false,
        'labels'            => $tag_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq_tag'),
        'show_in_rest'      => true,
    );

    register_taxonomy('faq_tag', array('faq'), $tag_args);
}
add_action('init', 'ecolitio_register_faq_taxonomies');

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
