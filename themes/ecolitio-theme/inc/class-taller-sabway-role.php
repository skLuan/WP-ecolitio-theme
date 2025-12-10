<?php

/**
 * Taller Sabway User Role Handler
 *
 * This class handles "Taller Sabway" user role functionality
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
class Taller_Sabway_Role
{

    /**
     * Constructor - Initialize role handler
     */
    public function __construct()
    {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('woocommerce_product_query_tax_query', array($this, 'filter_products_for_sabway_role'), 10, 2);
    }

    /**
     * Initialize Taller Sabway role functionality
     */
    public function init()
    {
        $this->create_taller_sabway_role();
        $this->register_rewrite_endpoints();
        $this->auto_register_consumer_keys();
    }

    /**
     * Create Taller Sabway user role with appropriate capabilities
     */
    private function create_taller_sabway_role()
    {
        // Remove existing role if it exists
        remove_role('taller_sabway');
        remove_role('taller_sabway_technician');

        // Add new role with WooCommerce customer capabilities plus additional permissions
        add_role(
            'taller_sabway',
            __('Taller Sabway', 'ecolitio-theme'),
            array(
                // Standard WordPress capabilities
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'manage_categories' => false,

                // WooCommerce specific capabilities that actually exist
                'read_shop_orders' => true,
                'edit_shop_orders' => true, // ✅ Key capability for WooCommerce REST API
                'create_shop_orders' => true,
                'edit_shop_order_items' => true,
                'read_private_shop_orders' => false,
                'read_products' => true,
                'read_private_products' => true,
                'edit_products' => false,
                'edit_product_terms' => false,
                'view_woocommerce_reports' => false,
                'delete_shop_orders' => false,
                'edit_others_shop_orders' => false,
                'publish_shop_orders' => true,
                'delete_shop_order_items' => false,
                'edit_private_shop_orders' => false,

                // Additional WooCommerce capabilities
                'manage_woocommerce' => false, // Too broad, keep false
                'view_woocommerce_specific_reports' => false,
                'manage_woocommerce_specific_products' => false,

                // Additional capabilities for restricted product access
                'view_sabway_products' => true,
                'access_sabway_zone' => true,
            )
        );
    }

    /**
     * Register rewrite endpoints for Sabway account pages
     */
    private function register_rewrite_endpoints()
    {
        add_rewrite_endpoint('mi-cuenta-sabway', EP_ROOT | EP_PAGES);
    }

    /**
     * Enqueue scripts and styles for Taller Sabway users
     */
    public function enqueue_scripts()
    {
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
    public function filter_products_for_sabway_role($tax_query, $query = null)
    {
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
     * Auto-register consumer keys for Taller Sabway users
     * This ensures that users with taller_sabway role have valid consumer keys for REST API
     */
    private function auto_register_consumer_keys()
    {
        // Only proceed if WooCommerce is active
        if (!function_exists('WC') || !WC()) {
            return;
        }

        // Get all users with taller_sabway role
        $taller_sabway_users = get_users(array(
            'role' => 'taller_sabway',
            'fields' => 'ID'
        ));

        foreach ($taller_sabway_users as $user) {
            // FIXED: $user is already a user ID (string/int), not an object
            // since get_users() was called with 'fields' => 'ID'
            $user_id = is_object($user) ? $user->ID : intval($user);
            
            // Prevent any output before headers are sent
            if (headers_sent()) {
                error_log("Taller Sabway: Cannot modify headers, output already started at line " . __LINE__);
                return;
            }

            // Check if user already has consumer keys
            $existing_keys = get_user_meta($user_id, 'woocommerce_api_consumer_key', true);

            if (empty($existing_keys)) {
                // Generate new consumer keys
                $consumer_key = 'ck_' . wp_generate_password(32, false);
                $consumer_secret = 'cs_' . wp_generate_password(32, false);

                // Store consumer keys in user meta
                update_user_meta($user_id, 'woocommerce_api_consumer_key', $consumer_key);
                update_user_meta($user_id, 'woocommerce_api_consumer_secret', $consumer_secret);

                // Also store in WooCommerce's API keys table if it exists
                if (function_exists('WC_API')) {
                    global $wpdb;

                    // Check if WooCommerce API keys table exists
                    $table_name = $wpdb->prefix . 'woocommerce_api_keys';

                    // Insert into WooCommerce API keys table
                    $wpdb->insert(
                        $table_name,
                        array(
                            'user_id' => $user_id,
                            'consumer_key' => $consumer_key,
                            'consumer_secret' => $consumer_secret,
                            'permissions' => 'read,write', // Grant read and write permissions
                            'last_access' => current_time('mysql'),
                            'created_at' => current_time('mysql')
                        ),
                        array('%s', '%s', '%d')
                    );
                }

                error_log("Taller Sabway: Auto-registered consumer keys for user {$user_id}");
            }
        }
    }
}

/**
 * Function to assign Taller Sabway role to users
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function assign_taller_sabway_role($user_id)
{
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
function remove_taller_sabway_role($user_id)
{
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
function user_has_taller_sabway_role($user_id = null)
{
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
function get_taller_sabway_users()
{
    $args = array(
        'role' => 'taller_sabway',
        'fields' => 'ID'
    );

    return get_users($args);
}

// Initialize Taller Sabway role handler
new Taller_Sabway_Role();
