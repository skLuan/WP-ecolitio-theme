<?php
/**
 * Plugin Name: ECOS Stores Plugin
 * Plugin URI: https://example.com/
 * Description: Aplica impuestos de WooCommerce solo al rol taller_sabway y deja a los demás usuarios con impuestos en 0.
 * Version: 1.0.0
 * Author: ECOS Stores
 * Author URI: https://example.com/
 * License: GPL2+
 * Text Domain: ecos-stores-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'ECOS_Stores_Plugin' ) ) {

    final class ECOS_Stores_Plugin {

        const VERSION = '1.0.0';
        const TAX_ROLE = 'taller_sabway';

        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init' ) );
        }

        public function init() {
            if ( ! class_exists( 'WooCommerce' ) ) {
                return;
            }

            add_filter( 'woocommerce_product_get_tax_status', array( $this, 'filter_tax_status' ), 10, 2 );
            add_filter( 'woocommerce_product_variation_get_tax_status', array( $this, 'filter_tax_status' ), 10, 2 );
        }

        public function filter_tax_status( $tax_status, $product ) {
            if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
                return $tax_status;
            }

            if ( $this->user_has_tax_role() ) {
                return 'taxable';
            }

            return 'none';
        }

        private function user_has_tax_role() {
            if ( ! is_user_logged_in() ) {
                return false;
            }

            $user = wp_get_current_user();

            if ( empty( $user ) || empty( $user->roles ) ) {
                return false;
            }

            return in_array( self::TAX_ROLE, (array) $user->roles, true );
        }
    }
}

new ECOS_Stores_Plugin();