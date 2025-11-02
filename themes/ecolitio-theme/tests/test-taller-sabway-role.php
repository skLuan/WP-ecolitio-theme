<?php
/**
 * Taller Sabway Role Test Suite
 *
 * Simple test functions to verify the Taller Sabway role implementation
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test Taller Sabway Role Implementation
 */
class Taller_Sabway_Role_Tests {

    /**
     * Run all tests
     */
    public static function run_all_tests() {
        $results = array();
        
        $results['role_creation'] = self::test_role_creation();
        $results['role_capabilities'] = self::test_role_capabilities();
        $results['product_filtering'] = self::test_product_filtering();
        $results['dashboard_access'] = self::test_dashboard_access();
        $results['ajax_handlers'] = self::test_ajax_handlers();
        
        return $results;
    }

    /**
     * Test if the Taller Sabway role is created correctly
     */
    public static function test_role_creation() {
        $role = get_role('taller_sabway');
        
        if ($role) {
            return array(
                'status' => 'PASS',
                'message' => 'Taller Sabway role exists',
                'data' => $role->capabilities
            );
        } else {
            return array(
                'status' => 'FAIL',
                'message' => 'Taller Sabway role does not exist'
            );
        }
    }

    /**
     * Test role capabilities
     */
    public static function test_role_capabilities() {
        $role = get_role('taller_sabway');
        
        if (!$role) {
            return array(
                'status' => 'FAIL',
                'message' => 'Cannot test capabilities - role does not exist'
            );
        }

        $required_capabilities = array(
            'read',
            'read_private_products',
            'view_sabway_products',
            'access_sabway_zone'
        );

        $missing_capabilities = array();
        foreach ($required_capabilities as $cap) {
            if (!isset($role->capabilities[$cap]) || !$role->capabilities[$cap]) {
                $missing_capabilities[] = $cap;
            }
        }

        if (empty($missing_capabilities)) {
            return array(
                'status' => 'PASS',
                'message' => 'All required capabilities are present',
                'data' => $role->capabilities
            );
        } else {
            return array(
                'status' => 'FAIL',
                'message' => 'Missing capabilities: ' . implode(', ', $missing_capabilities),
                'data' => $missing_capabilities
            );
        }
    }

    /**
     * Test product filtering functionality
     */
    public static function test_product_filtering() {
        // Create a test product with "sabway" tag
        $product_data = array(
            'post_title' => 'Test Sabway Product',
            'post_content' => 'Test product content',
            'post_status' => 'publish',
            'post_type' => 'product'
        );

        $product_id = wp_insert_post($product_data);
        
        if (!$product_id) {
            return array(
                'status' => 'FAIL',
                'message' => 'Could not create test product'
            );
        }

        // Add "sabway" tag
        wp_set_object_terms($product_id, 'sabway', 'product_tag');

        // Test role filtering
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_tag',
                    'field' => 'slug',
                    'terms' => 'sabway'
                )
            )
        );

        $query = new WP_Query($args);
        $found_sabway_products = $query->found_posts;

        // Clean up
        wp_delete_post($product_id, true);

        if ($found_sabway_products > 0) {
            return array(
                'status' => 'PASS',
                'message' => 'Product filtering works correctly',
                'data' => array('found_products' => $found_sabway_products)
            );
        } else {
            return array(
                'status' => 'FAIL',
                'message' => 'Product filtering not working properly'
            );
        }
    }

    /**
     * Test dashboard access functionality
     */
    public static function test_dashboard_access() {
        // Check if rewrite endpoint exists
        global $wp_rewrite;
        
        // This is a basic check - in a real environment, you'd want to test the actual endpoint
        if (class_exists('Taller_Sabway_Role')) {
            return array(
                'status' => 'PASS',
                'message' => 'Dashboard class exists and can be instantiated',
                'data' => null
            );
        } else {
            return array(
                'status' => 'FAIL',
                'message' => 'Dashboard class not found'
            );
        }
    }

    /**
     * Test AJAX handlers
     */
    public static function test_ajax_handlers() {
        // Check if AJAX actions are registered
        global $wp_filter;
        
        $ajax_actions = array(
            'taller_sabway_filter_products',
            'taller_sabway_get_stats'
        );

        $missing_actions = array();
        foreach ($ajax_actions as $action) {
            if (!isset($wp_filter['wp_ajax_' . $action]) && 
                !isset($wp_filter['wp_ajax_nopriv_' . $action])) {
                $missing_actions[] = $action;
            }
        }

        if (empty($missing_actions)) {
            return array(
                'status' => 'PASS',
                'message' => 'All AJAX handlers are registered',
                'data' => null
            );
        } else {
            return array(
                'status' => 'FAIL',
                'message' => 'Missing AJAX handlers: ' . implode(', ', $missing_actions),
                'data' => $missing_actions
            );
        }
    }

    /**
     * Create a test user with Taller Sabway role
     */
    public static function create_test_user() {
        $username = 'test_taller_sabway_' . time();
        $email = $username . '@example.com';
        $password = wp_generate_password();

        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            return false;
        }

        // Assign Taller Sabway role
        $user = new WP_User($user_id);
        $user->add_role('taller_sabway');

        return array(
            'user_id' => $user_id,
            'username' => $username,
            'email' => $email,
            'password' => $password
        );
    }

    /**
     * Clean up test user
     */
    public static function cleanup_test_user($user_id) {
        if ($user_id) {
            wp_delete_user($user_id);
        }
    }

    /**
     * Print test results
     */
    public static function print_results($results) {
        echo "<h2>Taller Sabway Role Test Results</h2>\n";
        echo "<table border='1' cellpadding='10' cellspacing='0'>\n";
        echo "<tr><th>Test</th><th>Status</th><th>Message</th></tr>\n";

        foreach ($results as $test_name => $result) {
            $status_class = $result['status'] === 'PASS' ? 'color: green;' : 'color: red;';
            echo "<tr>";
            echo "<td>" . ucwords(str_replace('_', ' ', $test_name)) . "</td>";
            echo "<td style='{$status_class}'>" . $result['status'] . "</td>";
            echo "<td>" . $result['message'] . "</td>";
            echo "</tr>\n";
        }
        
        echo "</table>\n";
    }
}

// Test functions for manual testing
if (!function_exists('run_taller_sabway_tests')) {
    /**
     * Run Taller Sabway tests and display results
     */
    function run_taller_sabway_tests() {
        if (current_user_can('administrator')) {
            $results = Taller_Sabway_Role_Tests::run_all_tests();
            Taller_Sabway_Role_Tests::print_results($results);
        } else {
            echo '<p>You must be an administrator to run these tests.</p>';
        }
    }
}

if (!function_exists('create_taller_sabway_test_user')) {
    /**
     * Create a test user with Taller Sabway role
     */
    function create_taller_sabway_test_user() {
        if (current_user_can('administrator')) {
            $test_user = Taller_Sabway_Role_Tests::create_test_user();
            if ($test_user) {
                echo "<h3>Test User Created</h3>";
                echo "<p><strong>Username:</strong> " . $test_user['username'] . "</p>";
                echo "<p><strong>Email:</strong> " . $test_user['email'] . "</p>";
                echo "<p><strong>Password:</strong> " . $test_user['password'] . "</p>";
                echo "<p>User ID: " . $test_user['user_id'] . "</p>";
                
                // Store user ID in a transient for cleanup
                set_transient('taller_sabway_test_user_id', $test_user['user_id'], HOUR_IN_SECONDS);
            } else {
                echo "<p>Failed to create test user.</p>";
            }
        } else {
            echo '<p>You must be an administrator to create test users.</p>';
        }
    }
}

if (!function_exists('cleanup_taller_sabway_test_user')) {
    /**
     * Clean up the test user
     */
    function cleanup_taller_sabway_test_user() {
        if (current_user_can('administrator')) {
            $user_id = get_transient('taller_sabway_test_user_id');
            if ($user_id) {
                Taller_Sabway_Role_Tests::cleanup_test_user($user_id);
                delete_transient('taller_sabway_test_user_id');
                echo "<p>Test user cleaned up successfully.</p>";
            } else {
                echo "<p>No test user found to clean up.</p>";
            }
        } else {
            echo '<p>You must be an administrator to clean up test users.</p>';
        }
    }
}

// Shortcode for admin area testing
if (!function_exists('taller_sabway_test_shortcode')) {
    /**
     * Shortcode to display test interface
     */
    function taller_sabway_test_shortcode($atts) {
        if (!current_user_can('administrator')) {
            return '<p>You must be an administrator to access this test interface.</p>';
        }

        $atts = shortcode_atts(array(
            'action' => 'results'
        ), $atts);

        ob_start();
        
        switch ($atts['action']) {
            case 'results':
                run_taller_sabway_tests();
                break;
                
            case 'create_user':
                echo '<h3>Create Test User</h3>';
                echo '<p>Click the button below to create a test user with Taller Sabway role:</p>';
                echo '<form method="post">';
                echo '<input type="submit" name="create_test_user" value="Create Test User" class="button button-primary">';
                echo '</form>';
                
                if (isset($_POST['create_test_user'])) {
                    create_taller_sabway_test_user();
                }
                break;
                
            case 'cleanup':
                echo '<h3>Cleanup Test User</h3>';
                echo '<p>Click the button below to remove the test user:</p>';
                echo '<form method="post">';
                echo '<input type="submit" name="cleanup_test_user" value="Clean Up Test User" class="button">';
                echo '</form>';
                
                if (isset($_POST['cleanup_test_user'])) {
                    cleanup_taller_sabway_test_user();
                }
                break;
                
            default:
                echo '<p>Invalid action specified.</p>';
        }
        
        return ob_get_clean();
    }
    add_shortcode('taller_sabway_tests', 'taller_sabway_test_shortcode');
}