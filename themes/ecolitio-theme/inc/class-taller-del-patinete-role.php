<?php

/**
 * Taller Del Patinete User Role Handler
 *
 * This class instantiates the generic Taller_Role class with Taller Del Patinete-specific configuration.
 * It handles "Taller Del Patinete" user role functionality including role creation, capabilities,
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
 * Taller Del Patinete User Role Class
 * 
 * This class extends the generic Taller_Role functionality with Taller Del Patinete-specific configuration.
 */
class Taller_Del_Patinete_Role
{
    /**
     * Instance of the generic Taller_Role class
     * 
     * @var Taller_Role
     */
    private $taller_role;

    /**
     * Constructor - Initialize Taller Del Patinete role handler
     */
    public function __construct()
    {
        // Configure Taller Del Patinete-specific settings
        $patinete_config = array(
            'role_slug' => 'taller_del_patinete',
            'role_name' => 'Taller Del Patinete',
            'product_tag' => 'taller-del-patinete',
            'capability_prefix' => 'patinete'
        );

        // Instantiate the generic Taller_Role with Taller Del Patinete configuration
        $this->taller_role = new Taller_Role($patinete_config);
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
 * Function to assign Taller Del Patinete role to users
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function assign_taller_del_patinete_role($user_id)
{
    return assign_taller_role($user_id, 'taller_del_patinete');
}

/**
 * Function to remove Taller Del Patinete role from users
 *
 * @param int $user_id User ID
 * @return bool Success status
 */
function remove_taller_del_patinete_role($user_id)
{
    return remove_taller_role($user_id, 'taller_del_patinete');
}

/**
 * Function to check if user has Taller Del Patinete role
 *
 * @param int $user_id User ID (optional, uses current user if not provided)
 * @return bool True if user has Taller Del Patinete role
 */
function user_has_taller_del_patinete_role($user_id = null)
{
    return user_has_taller_role('taller_del_patinete', $user_id);
}

/**
 * Function to get Taller Del Patinete users
 *
 * @return array Array of user IDs
 */
function get_taller_del_patinete_users()
{
    return get_taller_users('taller_del_patinete');
}

// Initialize Taller Del Patinete role handler
new Taller_Del_Patinete_Role();
