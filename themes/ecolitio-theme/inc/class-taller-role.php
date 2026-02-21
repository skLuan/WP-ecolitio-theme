<?php

/**
 * Generic Taller User Role Handler
 *
 * This class provides a generic, reusable framework for handling taller user roles
 * including role creation, capabilities, and product visibility filtering.
 * It can be extended or instantiated with different configurations for various taller types.
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generic Taller User Role Class
 * 
 * Usage:
 * $sabway_config = array(
 *     'role_slug' => 'taller_sabway',
 *     'role_name' => 'Taller Sabway',
 *     'product_tag' => 'sabway',
 *     'capability_prefix' => 'sabway'
 * );
 * new Taller_Role($sabway_config);
 */
class Taller_Role
{
    /**
     * Configuration array for the taller role
     * 
     * @var array
     */
    private $config = array();

    /**
     * Constructor - Initialize role handler with configuration
     * 
     * @param array $config Configuration array with keys:
     *                      - role_slug: WordPress role slug (e.g., 'taller_sabway')
     *                      - role_name: Display name for the role (e.g., 'Taller Sabway')
     *                      - product_tag: Product tag to filter by (e.g., 'sabway')
     *                      - capability_prefix: Prefix for custom capabilities (e.g., 'sabway')
     */
    public function __construct($config = array())
    {
        // Validate required configuration
        $required_keys = array('role_slug', 'role_name', 'product_tag', 'capability_prefix');
        foreach ($required_keys as $key) {
            if (!isset($config[$key])) {
                error_log("Taller_Role: Missing required configuration key: {$key}");
                return;
            }
        }

        $this->config = $config;

        // Register hooks
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_filter('woocommerce_product_query_tax_query', array($this, 'filter_products_for_taller_role'), 10, 2);
    }

    /**
     * Initialize taller role functionality
     */
    public function init()
    {
        $this->create_taller_role();
        $this->auto_register_consumer_keys();
    }

    /**
     * Create taller user role with appropriate capabilities
     */
    private function create_taller_role()
    {
        $role_slug = $this->config['role_slug'];
        $role_name = $this->config['role_name'];
        $capability_prefix = $this->config['capability_prefix'];

        // Remove existing role if it exists (to ensure fresh capabilities)
        remove_role($role_slug);

        // Add new role with WooCommerce customer capabilities plus additional permissions
        add_role(
            $role_slug,
            __($role_name, 'ecolitio-theme'),
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
                'view_' . $capability_prefix . '_products' => true,
                'access_' . $capability_prefix . '_zone' => true,
            )
        );
    }

    /**
     * Enqueue scripts and styles for taller users
     */
    public function enqueue_scripts()
    {
        $role_slug = $this->config['role_slug'];

        if (current_user_can($role_slug)) {
            // Enqueue generic taller styles if they exist
            $taller_style_path = get_stylesheet_directory() . '/css/taller-' . str_replace('_', '-', $role_slug) . '.css';
            if (file_exists($taller_style_path)) {
                wp_enqueue_style(
                    'taller-' . str_replace('_', '-', $role_slug) . '-style',
                    get_stylesheet_directory_uri() . '/css/taller-' . str_replace('_', '-', $role_slug) . '.css',
                    array(),
                    '1.0.0'
                );
            }

            // Enqueue generic taller scripts if they exist
            $taller_script_path = get_stylesheet_directory() . '/js/taller-' . str_replace('_', '-', $role_slug) . '.js';
            if (file_exists($taller_script_path)) {
                wp_enqueue_script(
                    'taller-' . str_replace('_', '-', $role_slug) . '-script',
                    get_stylesheet_directory_uri() . '/js/taller-' . str_replace('_', '-', $role_slug) . '.js',
                    array('jquery'),
                    '1.0.0',
                    true
                );

                // Localize script for AJAX
                wp_localize_script(
                    'taller-' . str_replace('_', '-', $role_slug) . '-script',
                    'taller_' . str_replace('-', '_', str_replace('_', '-', $role_slug)) . '_ajax',
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        'nonce' => wp_create_nonce('taller_' . $role_slug . '_nonce'),
                        'form_nonce' => wp_create_nonce('ecolitio_' . $role_slug . '_form_nonce'),
                        'is_taller_user' => current_user_can($role_slug)
                    )
                );
            }
        }
    }

    /**
     * Filter products to show only tagged products for taller role
     *
     * @param array $tax_query Current tax query
     * @param WP_Query $query Current product query
     * @return array Modified tax query
     */
    public function filter_products_for_taller_role($tax_query, $query = null)
    {
        $role_slug = $this->config['role_slug'];
        $product_tag = $this->config['product_tag'];

        // Only apply filter if user has this taller role
        if (!current_user_can($role_slug)) {
            return $tax_query;
        }

        // Only filter on front-end shop pages and product archives
        if (is_admin() && !wp_doing_ajax()) {
            return $tax_query;
        }

        // Add tax query to filter products with the specific tag
        $tax_query[] = array(
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $product_tag,
            'operator' => 'IN'
        );

        return $tax_query;
    }

    /**
     * Auto-register consumer keys for taller users
     * This ensures that users with this taller role have valid consumer keys for REST API
     */
    private function auto_register_consumer_keys()
    {
        // Only proceed if WooCommerce is active
        if (!function_exists('WC') || !WC()) {
            return;
        }

        $role_slug = $this->config['role_slug'];

        // Get all users with this taller role
        $taller_users = get_users(array(
            'role' => $role_slug,
            'fields' => 'ID'
        ));

        foreach ($taller_users as $user) {
            // FIXED: $user is already a user ID (string/int), not an object
            // since get_users() was called with 'fields' => 'ID'
            $user_id = is_object($user) ? $user->ID : intval($user);

            // Prevent any output before headers are sent
            if (headers_sent()) {
                error_log("Taller_Role ({$role_slug}): Cannot modify headers, output already started at line " . __LINE__);
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

                error_log("Taller_Role ({$role_slug}): Auto-registered consumer keys for user {$user_id}");
            }
        }
    }

    /**
     * Get the role slug for this taller
     * 
     * @return string Role slug
     */
    public function get_role_slug()
    {
        return $this->config['role_slug'];
    }

    /**
     * Get the product tag for this taller
     * 
     * @return string Product tag
     */
    public function get_product_tag()
    {
        return $this->config['product_tag'];
    }

    /**
     * Get the capability prefix for this taller
     * 
     * @return string Capability prefix
     */
    public function get_capability_prefix()
    {
        return $this->config['capability_prefix'];
    }
}

/**
 * Generic function to assign taller role to users
 *
 * @param int $user_id User ID
 * @param string $role_slug Role slug to assign
 * @return bool Success status
 */
function assign_taller_role($user_id, $role_slug)
{
    $user = get_userdata($user_id);
    if ($user) {
        $user->add_role($role_slug);
        return true;
    }
    return false;
}

/**
 * Generic function to remove taller role from users
 *
 * @param int $user_id User ID
 * @param string $role_slug Role slug to remove
 * @return bool Success status
 */
function remove_taller_role($user_id, $role_slug)
{
    $user = get_userdata($user_id);
    if ($user) {
        $user->remove_role($role_slug);
        return true;
    }
    return false;
}

/**
 * Generic function to check if user has taller role
 *
 * @param string $role_slug Role slug to check
 * @param int $user_id User ID (optional, uses current user if not provided)
 * @return bool True if user has the taller role
 */
function user_has_taller_role($role_slug, $user_id = null)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);
    return $user && in_array($role_slug, $user->roles);
}

/**
 * Generic function to get taller users
 *
 * @param string $role_slug Role slug to query
 * @return array Array of user IDs
 */
function get_taller_users($role_slug)
{
    $args = array(
        'role' => $role_slug,
        'fields' => 'ID'
    );

    return get_users($args);
}
