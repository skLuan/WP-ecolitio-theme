<?php

/**
 * Taller Sabway User Role Handler
 *
 * This class instantiates the generic Taller_Role class with Sabway-specific configuration.
 * It handles "Taller Sabway" user role functionality including role creation, capabilities,
 * and product visibility filtering.
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Ensure the generic Taller_Role class is loaded
require_once get_stylesheet_directory() . '/inc/class-taller-role.php';

/**
 * Taller Sabway User Role Class
 * 
 * This class extends the generic Taller_Role functionality with Sabway-specific configuration.
 */
class Taller_Sabway_Role
{
    /**
     * Instance of the generic Taller_Role class
     * 
     * @var Taller_Role
     */
    private $taller_role;

    /**
     * Constructor - Initialize Sabway role handler
     */
    public function __construct()
    {
        // Configure Sabway-specific settings
        $sabway_config = array(
            'role_slug' => 'taller_sabway',
            'role_name' => 'Taller Sabway',
            'product_tag' => 'sabway',
            'capability_prefix' => 'sabway'
        );

        // Instantiate the generic Taller_Role with Sabway configuration
        $this->taller_role = new Taller_Role($sabway_config);
    }

    /**
     * Get the underlying Taller_Role instance
     * 
     * @return Taller_Role
     */
    public function get_taller_role()
    {
        return $this->taller_role;
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
    return assign_taller_role($user_id, 'taller_sabway');
}

/**
 * Function to remove Taller Sabway role from users
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function remove_taller_sabway_role($user_id)
{
    return remove_taller_role($user_id, 'taller_sabway');
}

/**
 * Function to check if user has Taller Sabway role
 *
 * @param int $user_id User ID (optional, uses current user if not provided)
 * @return bool True if user has Taller Sabway role
 */
function user_has_taller_sabway_role($user_id = null)
{
    return user_has_taller_role('taller_sabway', $user_id);
}

/**
 * Function to get Taller Sabway users
 *
 * @return array Array of user IDs
 */
function get_taller_sabway_users()
{
    return get_taller_users('taller_sabway');
}

// Initialize Taller Sabway role handler
new Taller_Sabway_Role();
