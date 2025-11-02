<?php
/**
 * Taller Sabway User Role Handler
 *
 * This class handles the "Taller Sabway" user role functionality
 * including role creation, capabilities, and product visibility filtering.
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Taller Sabway User Role Class
 */
class Taller_Sabway_Role {

    /**
     * Constructor - Initialize the role handler
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('woocommerce_product_query_tax_query', array($this, 'filter_products_for_sabway_role'), 10, 2);
        add_action('woocommerce_account_dashboard', array($this, 'add_sabway_dashboard_notice'));
        add_filter('woocommerce_account_menu_items', array($this, 'add_sabway_dashboard_menu_item'));
        add_action('woocommerce_account_taller-sabway-dashboard_endpoint', array($this, 'taller_sabway_dashboard_content'));
    }

    /**
     * Initialize the Taller Sabway role functionality
     */
    public function init() {
        $this->create_taller_sabway_role();
        $this->register_rewrite_endpoints();
    }

    /**
     * Create the Taller Sabway user role with appropriate capabilities
     */
    private function create_taller_sabway_role() {
        // Remove existing role if it exists
        remove_role('taller_sabway');
        remove_role('taller_sabway_technician');

        // Add the new role with WooCommerce customer capabilities plus additional permissions
        add_role(
            'taller_sabway',
            __('Taller Sabway', 'ecolitio-theme'),
            array(
                // Standard WooCommerce customer capabilities
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'manage_categories' => false,
                
                // WooCommerce specific capabilities
                'read_private_products' => true,
                'edit_products' => false,
                'edit_product_terms' => false,
                'edit_shop_orders' => true, // ✅ Allow creating/editing shop orders via REST API
                'read_shop_orders' => true,
                'view_woocommerce_reports' => false,
                'edit_shop_order_items' => true, // ✅ Allow editing order items
                'create_shop_orders' => true, // ✅ Allow creating orders
                
                // Additional capabilities for restricted product access
                'view_sabway_products' => true,
                'access_sabway_zone' => true,
            )
        );
    }

    /**
     * Register rewrite endpoints for the dashboard
     */
    private function register_rewrite_endpoints() {
        add_rewrite_endpoint('taller-sabway-dashboard', EP_ROOT | EP_PAGES);
    }

    /**
     * Enqueue scripts and styles for Taller Sabway users
     */
    public function enqueue_scripts() {
        if (current_user_can('taller_sabway')) {
            wp_enqueue_style(
                'taller-sabway-style',
                get_stylesheet_directory_uri() . '/css/taller-sabway.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'taller-sabway-script',
                get_stylesheet_directory_uri() . '/js/taller-sabway.js',
                array('jquery'),
                '1.0.0',
                true
            );

            // Localize script for AJAX
            wp_localize_script('taller-sabway-script', 'taller_sabway_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('taller_sabway_nonce'),
                'sabway_form_nonce' => wp_create_nonce('ecolitio_sabway_form_nonce'),
                'is_taller_sabway' => current_user_can('taller_sabway')
            ));
        }
    }

    /**
     * Filter products to show only "sabway" tagged products for Taller Sabway role
     *
     * @param array $tax_query Current tax query
     * @param WP_Query $query Current product query
     * @return array Modified tax query
     */
    public function filter_products_for_sabway_role($tax_query, $query = null) {
        // Only apply filter if user has Taller Sabway role
        if (!current_user_can('taller_sabway')) {
            return $tax_query;
        }

        // Only filter on front-end shop pages and product archives
        if (is_admin() && !wp_doing_ajax()) {
            return $tax_query;
        }

        // Add tax query to filter products with "sabway" tag
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => 'sabway',
            'operator' => 'IN'
        );

        return $tax_query;
    }

    /**
     * Add notice to WooCommerce dashboard for Taller Sabway users
     */
    public function add_sabway_dashboard_notice() {
        if (current_user_can('taller_sabway')) {
            echo '<div class="woocommerce-MyAccount-content">';
            echo '<div class="taller-sabway-notice" style="background: #d02024; color: white; padding: 15px; border-radius: 5px; margin: 20px 0;">';
            echo '<h3>' . __('Bienvenido a la Zona Taller Sabway', 'ecolitio-theme') . '</h3>';
            echo '<p>' . __('Tienes acceso exclusivo a productos etiquetados como "sabway". Puedes realizar pedidos y gestionar tus órdenes normalmente.', 'ecolitio-theme') . '</p>';
            echo '<p><a href="' . wc_get_page_permalink('shop') . '" style="color: white; text-decoration: underline;">' . __('Ver productos Sabway', 'ecolitio-theme') . '</a></p>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Add Taller Sabway dashboard menu item to WooCommerce account menu
     *
     * @param array $items Current menu items
     * @return array Modified menu items
     */
    public function add_sabway_dashboard_menu_item($items) {
        if (current_user_can('taller_sabway')) {
            $items['taller-sabway-dashboard'] = __('Dashboard Taller Sabway', 'ecolitio-theme');
        }
        return $items;
    }

    /**
     * Content for the Taller Sabway dashboard endpoint
     */
    public function taller_sabway_dashboard_content() {
        if (!current_user_can('taller_sabway')) {
            echo '<p>' . __('No tienes permisos para acceder a esta página.', 'ecolitio-theme') . '</p>';
            return;
        }

        $user = wp_get_current_user();
        ?>
        <div class="taller-sabway-dashboard">
            <h2><?php _e('Dashboard Taller Sabway', 'ecolitio-theme'); ?></h2>
            
            <div class="taller-sabway-welcome">
                <h3><?php _e('Bienvenido, ', 'ecolitio-theme') . esc_html($user->display_name); ?></h3>
                <p><?php _e('Esta es tu área privada donde puedes acceder a productos exclusivos de Sabway.', 'ecolitio-theme'); ?></p>
            </div>

            <div class="taller-sabway-stats">
                <div class="stats-grid">
                    <div class="stat-box">
                        <h4><?php _e('Productos Disponibles', 'ecolitio-theme'); ?></h4>
                        <?php
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
                        echo '<span class="stat-number">' . count($sabway_products) . '</span>';
                        ?>
                    </div>
                    
                    <div class="stat-box">
                        <h4><?php _e('Pedidos Totales', 'ecolitio-theme'); ?></h4>
                        <?php
                        $customer_orders = wc_get_orders(array(
                            'customer' => get_current_user_id(),
                            'limit' => -1,
                            'return' => 'ids'
                        ));
                        echo '<span class="stat-number">' . count($customer_orders) . '</span>';
                        ?>
                    </div>
                </div>
            </div>

            <div class="taller-sabway-actions">
                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="button button-primary">
                    <?php _e('Ver Productos Sabway', 'ecolitio-theme'); ?>
                </a>
                <a href="<?php echo wc_get_account_endpoint_url('orders'); ?>" class="button">
                    <?php _e('Mis Pedidos', 'ecolitio-theme'); ?>
                </a>
            </div>
        </div>
        <?php
    }
}

/**
 * Function to assign Taller Sabway role to users
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function assign_taller_sabway_role($user_id) {
    $user = get_userdata($user_id);
    if ($user) {
        $user->add_role('taller_sabway');
        return true;
    }
    return false;
}

/**
 * Function to remove Taller Sabway role from users
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function remove_taller_sabway_role($user_id) {
    $user = get_userdata($user_id);
    if ($user) {
        $user->remove_role('taller_sabway');
        return true;
    }
    return false;
}

/**
 * Function to check if user has Taller Sabway role
 *
 * @param int $user_id User ID (optional, uses current user if not provided)
 * @return bool True if user has Taller Sabway role
 */
function user_has_taller_sabway_role($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_userdata($user_id);
    return $user && in_array('taller_sabway', $user->roles);
}

/**
 * Function to get Taller Sabway users
 *
 * @return array Array of user IDs
 */
function get_taller_sabway_users() {
    $args = array(
        'role' => 'taller_sabway',
        'fields' => 'ID'
    );
    
    return get_users($args);
}

// Initialize the Taller Sabway role handler
new Taller_Sabway_Role();