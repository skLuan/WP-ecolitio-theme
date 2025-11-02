<?php
/**
 * Sabway Form AJAX Debug Test Script
 * 
 * This file provides testing utilities for the Sabway form AJAX functionality
 * to validate cookie validation fixes and session handling.
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sabway Form AJAX Test Class
 */
class Sabway_Form_Ajax_Tests {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_sabway_test_validation', array($this, 'test_session_validation'));
        add_action('wp_ajax_nopriv_sabway_test_validation', array($this, 'test_session_validation'));
        add_action('wp_ajax_sabway_test_nonce', array($this, 'test_nonce_generation'));
        add_action('wp_ajax_nopriv_sabway_test_nonce', array($this, 'test_nonce_generation'));
    }

    /**
     * Test session validation functionality
     */
    public function test_session_validation() {
        // Check if this is a test request
        if (!isset($_POST['test_validation']) || $_POST['test_validation'] !== 'sabway') {
            wp_send_json_error(array('message' => 'Invalid test request'));
            return;
        }

        // Get the validate_user_session function result
        if (function_exists('validate_user_session')) {
            $validation_result = validate_user_session();
            
            wp_send_json_success(array(
                'session_validation' => $validation_result,
                'session_data_available' => !empty($validation_result['session_data']),
                'is_user_logged_in' => is_user_logged_in(),
                'current_user_id' => get_current_user_id(),
                'cookies_found' => $this->check_required_cookies()
            ));
        } else {
            wp_send_json_error(array('message' => 'validate_user_session function not found'));
        }
    }

    /**
     * Test nonce generation
     */
    public function test_nonce_generation() {
        if (!isset($_POST['test_nonce']) || $_POST['test_nonce'] !== 'sabway') {
            wp_send_json_error(array('message' => 'Invalid test request'));
            return;
        }

        // Generate test nonce
        $nonce = wp_create_nonce('ecolitio_sabway_form_nonce');
        
        wp_send_json_success(array(
            'nonce_generated' => !empty($nonce),
            'nonce_value' => $nonce,
            'nonce_length' => strlen($nonce),
            'verify_test' => wp_verify_nonce($nonce, 'ecolitio_sabway_form_nonce')
        ));
    }

    /**
     * Check for required cookies
     */
    private function check_required_cookies() {
        $cookie_keys = array('wp_cart_tracking', 'woocommerce_cart_hash', 'wp_woocommerce_session_');
        $found_cookies = array();
        
        foreach ($_COOKIE as $cookie_name => $cookie_value) {
            foreach ($cookie_keys as $key) {
                if (strpos($cookie_name, $key) !== false) {
                    $found_cookies[] = $cookie_name;
                }
            }
        }
        
        return $found_cookies;
    }

    /**
     * Debug output function
     */
    public static function debug_session_info() {
        if (!current_user_can('administrator')) {
            return;
        }

        echo '<div id="sabway-debug-info" style="background: #f0f0f0; padding: 20px; margin: 20px 0; border: 1px solid #ccc;">';
        echo '<h3>Sabway Form Debug Information</h3>';
        
        // Session validation
        if (function_exists('validate_user_session')) {
            $validation = validate_user_session();
            echo '<h4>Session Validation</h4>';
            echo '<pre>' . print_r($validation, true) . '</pre>';
        }
        
        // Current user info
        echo '<h4>Current User Info</h4>';
        echo '<p>User Logged In: ' . (is_user_logged_in() ? 'Yes' : 'No') . '</p>';
        echo '<p>Current User ID: ' . get_current_user_id() . '</p>';
        
        // Cookie info
        echo '<h4>Available Cookies</h4>';
        echo '<pre>' . print_r($_COOKIE, true) . '</pre>';
        
        // WooCommerce session
        if (function_exists('WC') && WC()->session) {
            echo '<h4>WooCommerce Session</h4>';
            $wc_session = WC()->session->get_session_data();
            echo '<pre>' . print_r($wc_session, true) . '</pre>';
        }
        
        echo '</div>';
    }
}

// Initialize the test class
new Sabway_Form_Ajax_Tests();

/**
 * Add debug info to admin footer for administrators
 */
add_action('wp_footer', function() {
    if (current_user_can('administrator') && isset($_GET['sabway_debug']) && $_GET['sabway_debug'] === '1') {
        Sabway_Form_Ajax_Tests::debug_session_info();
    }
});

/**
 * Add test JavaScript to admin
 */
add_action('admin_footer', function() {
    if (current_user_can('administrator') && isset($_GET['sabway_debug']) && $_GET['sabway_debug'] === '1') {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Add debug test buttons
            if ($('#wp-admin-bar-top-secondary').length) {
                $('<li id="sabway-debug-tests"><a href="#" id="run-sabway-tests">Run Sabway Tests</a></li>').insertAfter('#wp-admin-bar-my-account');
            }
            
            $('#run-sabway-tests').on('click', function(e) {
                e.preventDefault();
                
                // Test session validation
                $.post(ajaxurl, {
                    action: 'sabway_test_validation',
                    test_validation: 'sabway'
                }, function(sessionResponse) {
                    console.log('Session Test Result:', sessionResponse);
                    
                    // Test nonce generation
                    $.post(ajaxurl, {
                        action: 'sabway_test_nonce',
                        test_nonce: 'sabway'
                    }, function(nonceResponse) {
                        console.log('Nonce Test Result:', nonceResponse);
                        
                        alert('Tests completed. Check console for results.');
                    });
                });
            });
        });
        </script>
        <?php
    }
});

/**
 * Function to manually check and fix session issues
 */
function sabway_fix_session_issues() {
    // Force initialize WooCommerce session
    if (function_exists('WC') && WC()->session) {
        WC()->session->init_session();
    }
    
    // Force set cookies if missing
    if (!isset($_COOKIE['wp_woocommerce_session_' . COOKIEHASH])) {
        if (function_exists('WC') && WC()->session) {
            WC()->session->set_customer_session_cookie(true);
        }
    }
    
    return array(
        'session_initialized' => function_exists('WC') && WC()->session ? 'Yes' : 'No',
        'cookies_set' => isset($_COOKIE['wp_woocommerce_session_' . COOKIEHASH]),
        'user_logged_in' => is_user_logged_in(),
        'current_user_id' => get_current_user_id()
    );
}

/**
 * Add admin notice for debugging
 */
add_action('admin_notices', function() {
    if (isset($_GET['sabway_debug']) && $_GET['sabway_debug'] === '1') {
        $fix_result = sabway_fix_session_issues();
        echo '<div class="notice notice-info"><p>';
        echo '<strong>Sabway Session Debug:</strong><br>';
        echo 'Session Initialized: ' . $fix_result['session_initialized'] . '<br>';
        echo 'Cookies Set: ' . ($fix_result['cookies_set'] ? 'Yes' : 'No') . '<br>';
        echo 'User Logged In: ' . ($fix_result['user_logged_in'] ? 'Yes' : 'No') . '<br>';
        echo 'User ID: ' . $fix_result['current_user_id'] . '<br>';
        echo 'Add ?sabway_debug=1 to any URL to see debug info.';
        echo '</p></div>';
    }
});

/**
 * Shortcode to display debug information
 */
add_shortcode('sabway_debug', function($atts) {
    if (!current_user_can('administrator')) {
        return '<p>Administrator access required.</p>';
    }
    
    $atts = shortcode_atts(array(
        'show_session' => 'true',
        'show_cookies' => 'true',
        'show_user' => 'true'
    ), $atts);
    
    ob_start();
    ?>
    <div class="sabway-debug-shortcode" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin: 10px 0;">
        <h3>Sabway Form Debug Information</h3>
        
        <?php if ($atts['show_session'] === 'true'): ?>
            <h4>Session Validation</h4>
            <?php
            if (function_exists('validate_user_session')) {
                $validation = validate_user_session();
                echo '<pre>' . htmlspecialchars(print_r($validation, true)) . '</pre>';
            } else {
                echo '<p>validate_user_session function not found</p>';
            }
            ?>
        <?php endif; ?>
        
        <?php if ($atts['show_user'] === 'true'): ?>
            <h4>User Information</h4>
            <p>User Logged In: <?php echo is_user_logged_in() ? 'Yes' : 'No'; ?></p>
            <p>Current User ID: <?php echo get_current_user_id(); ?></p>
            <?php if (is_user_logged_in()): ?>
                <?php $user = wp_get_current_user(); ?>
                <p>Username: <?php echo $user->user_login; ?></p>
                <p>Email: <?php echo $user->user_email; ?></p>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($atts['show_cookies'] === 'true'): ?>
            <h4>Available Cookies</h4>
            <pre><?php echo htmlspecialchars(print_r($_COOKIE, true)); ?></pre>
        <?php endif; ?>
        
        <h4>Quick Actions</h4>
        <p>
            <button type="button" onclick="runSabwayTests()" class="button button-secondary">Run Tests</button>
            <button type="button" onclick="fixSabwaySession()" class="button button-secondary">Fix Session</button>
        </p>
        
        <script>
        function runSabwayTests() {
            console.log('Running Sabway tests...');
            alert('Check browser console for test results.');
        }
        
        function fixSabwaySession() {
            location.href = location.href + (location.href.indexOf('?') === -1 ? '?' : '&') + 'sabway_debug=1';
        }
        </script>
    </div>
    <?php
    return ob_get_clean();
});
?>