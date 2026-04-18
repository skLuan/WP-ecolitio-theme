<?php
/**
 * Battery Variation Helper Functions
 * 
 * Provides utilities for working with variable battery products
 * Uses SKU-based lookup for variation identification
 * 
 * @package Ecolitio
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Get variation ID by SKU
 * 
 * Uses SKU format: {parent-product-sku}-{voltage}-{amperage}
 * Example: eco-bame-24v-4,8ah
 * 
 * @param string $sku The variation SKU
 * @return int|null Variation ID or null if not found
 */
function ecolitio_get_variation_id_by_sku($sku) {
    global $wpdb;
    
    $variation_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta} 
         WHERE meta_key = '_sku' AND meta_value = %s 
         LIMIT 1",
        $sku
    ));
    
    return $variation_id ? intval($variation_id) : null;
}

/**
 * Build SKU from voltage and amperage
 * 
 * @param string $voltage  Voltage value (e.g., "24V")
 * @param string $amperage Amperage value (e.g., "4,8AH")
 * @param string $prefix   SKU prefix (defaults to "eco-bame" for backward compatibility)
 * @return string The constructed SKU
 */
function ecolitio_build_variation_sku($voltage, $amperage, $prefix = 'eco-bame') {
    // Normalize values
    $voltage  = strtolower(str_replace(' ', '', $voltage));
    $amperage = strtolower(str_replace(' ', '', $amperage));

    return $prefix . '-' . $voltage . '-' . $amperage;
}

/**
 * Build variation SKU using the parent product's own SKU as the prefix
 *
 * SKU format: {parent-product-sku}-{voltage}-{amperage}
 * Example: eco-tapa-36v-9,6ah
 *
 * @param int    $product_id Parent product ID
 * @param string $voltage    Voltage value (e.g., "24V")
 * @param string $amperage   Amperage value (e.g., "4,8AH")
 * @return string The constructed SKU
 */
function ecolitio_build_variation_sku_for_product($product_id, $voltage, $amperage) {
    $parent = wc_get_product($product_id);
    $prefix = $parent ? $parent->get_sku() : 'eco-battery';

    $voltage  = strtolower(str_replace(' ', '', $voltage));
    $amperage = strtolower(str_replace(' ', '', $amperage));

    return $prefix . '-' . $voltage . '-' . $amperage;
}

/**
 * Get variation ID for voltage and amperage combination
 * 
 * @param int $product_id Parent product ID
 * @param string $voltage Voltage value (e.g., "24V")
 * @param string $amperage Amperage value (e.g., "4,8AH")
 * @return int|null Variation ID or null if not found
 */
function ecolitio_get_variation_id_for_specs($product_id, $voltage, $amperage) {
    // Build SKU using parent product's SKU as prefix and look it up
    $sku = ecolitio_build_variation_sku_for_product($product_id, $voltage, $amperage);
    return ecolitio_get_variation_id_by_sku($sku);
}

/**
 * Get variation details by SKU
 * 
 * @param string $sku The variation SKU
 * @return array|null Variation details or null if not found
 */
function ecolitio_get_variation_by_sku($sku) {
    $variation_id = ecolitio_get_variation_id_by_sku($sku);
    
    if (!$variation_id) {
        return null;
    }
    
    $variation = wc_get_product($variation_id);
    
    if (!$variation || !$variation->is_type('variation')) {
        return null;
    }
    
    return array(
        'id' => $variation_id,
        'sku' => $sku,
        'price' => $variation->get_price(),
        'regular_price' => $variation->get_regular_price(),
        'sale_price' => $variation->get_sale_price(),
        'stock' => $variation->get_stock_quantity(),
        'in_stock' => $variation->is_in_stock(),
        'attributes' => $variation->get_attributes(),
    );
}

/**
 * Get all variations for a product with their details
 * 
 * @param int $product_id Parent product ID
 * @return array Array of variation details
 */
function ecolitio_get_all_variations_for_product($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return array();
    }
    
    $variations = array();
    $variation_ids = $product->get_children();
    
    foreach ($variation_ids as $variation_id) {
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            continue;
        }
        
        $sku = $variation->get_sku();
        $attributes = $variation->get_attributes();
        
        $variations[] = array(
            'id' => $variation_id,
            'sku' => $sku,
            'price' => $variation->get_price(),
            'regular_price' => $variation->get_regular_price(),
            'sale_price' => $variation->get_sale_price(),
            'stock' => $variation->get_stock_quantity(),
            'in_stock' => $variation->is_in_stock(),
            'voltage' => $attributes['voltios'] ?? null,
            'amperage' => $attributes['amperios'] ?? null,
        );
    }
    
    return $variations;
}

/**
 * Get pricing matrix for battery variations
 * 
 * Calculates price based on voltage and amperage multipliers
 * 
 * @param int $product_id Parent product ID
 * @return array Pricing matrix with base price and multipliers
 */
function ecolitio_get_battery_pricing_matrix($product_id = null) {
    $matrix = array(
        'base_price' => 100, // Default base price
        'voltage_options' => array('24V', '36V', '48V', '52V', '60V'),
        'amperage_options' => array('4,8AH', '9,6AH', '14,4AH', '19,2AH', '24AH', '28,8AH', '33,6AH', '38,4AH'),
        'voltage_multipliers' => array(
            '24V' => 1.0,
            '36V' => 1.2,
            '48V' => 1.5,
            '52V' => 1.65,
            '60V' => 1.9,
        ),
        'amperage_multipliers' => array(
            '4,8AH' => 1.0,
            '9,6AH' => 1.2,
            '14,4AH' => 1.4,
            '19,2AH' => 1.6,
            '24AH' => 1.8,
            '28,8AH' => 2.0,
            '33,6AH' => 2.2,
            '38,4AH' => 2.4,
        ),
    );
    
    // Allow filtering of pricing matrix
    return apply_filters('ecolitio_battery_pricing_matrix', $matrix, $product_id);
}

/**
 * Calculate price for voltage and amperage combination
 * 
 * @param string $voltage Voltage value (e.g., "24V")
 * @param string $amperage Amperage value (e.g., "4,8AH")
 * @param int $product_id Optional product ID for context
 * @return float Calculated price
 */
function ecolitio_calculate_battery_price($voltage, $amperage, $product_id = null) {
    $matrix = ecolitio_get_battery_pricing_matrix($product_id);
    
    $base_price = $matrix['base_price'];
    $voltage_mult = $matrix['voltage_multipliers'][$voltage] ?? 1.0;
    $amperage_mult = $matrix['amperage_multipliers'][$amperage] ?? 1.0;
    
    return $base_price * $voltage_mult * $amperage_mult;
}

/**
 * Get variation price by voltage and amperage
 * 
 * Looks up actual variation price from database
 * 
 * @param int $product_id Parent product ID
 * @param string $voltage Voltage value
 * @param string $amperage Amperage value
 * @return float|null Variation price or null if not found
 */
function ecolitio_get_variation_price($product_id, $voltage, $amperage) {
    $variation_id = ecolitio_get_variation_id_for_specs($product_id, $voltage, $amperage);
    
    if (!$variation_id) {
        return null;
    }
    
    $variation = wc_get_product($variation_id);
    
    if (!$variation) {
        return null;
    }
    
    return floatval($variation->get_price());
}

/**
 * Check if variation is in stock
 * 
 * @param int $product_id Parent product ID
 * @param string $voltage Voltage value
 * @param string $amperage Amperage value
 * @return bool True if in stock, false otherwise
 */
function ecolitio_is_variation_in_stock($product_id, $voltage, $amperage) {
    $variation_id = ecolitio_get_variation_id_for_specs($product_id, $voltage, $amperage);
    
    if (!$variation_id) {
        return false;
    }
    
    $variation = wc_get_product($variation_id);
    
    if (!$variation) {
        return false;
    }
    
    return $variation->is_in_stock();
}

/**
 * Get variation stock quantity
 * 
 * @param int $product_id Parent product ID
 * @param string $voltage Voltage value
 * @param string $amperage Amperage value
 * @return int|null Stock quantity or null if not found
 */
function ecolitio_get_variation_stock($product_id, $voltage, $amperage) {
    $variation_id = ecolitio_get_variation_id_for_specs($product_id, $voltage, $amperage);
    
    if (!$variation_id) {
        return null;
    }
    
    $variation = wc_get_product($variation_id);
    
    if (!$variation) {
        return null;
    }
    
    return $variation->get_stock_quantity();
}

/**
 * Validate voltage and amperage combination exists
 * 
 * @param int $product_id Parent product ID
 * @param string $voltage Voltage value
 * @param string $amperage Amperage value
 * @return bool True if combination exists, false otherwise
 */
function ecolitio_variation_exists($product_id, $voltage, $amperage) {
    $variation_id = ecolitio_get_variation_id_for_specs($product_id, $voltage, $amperage);
    return $variation_id !== null;
}

/**
 * Get all voltage options for a product
 * 
 * @param int $product_id Parent product ID
 * @return array Array of voltage options
 */
function ecolitio_get_voltage_options($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return array();
    }
    
    $voltages = array();
    $variation_ids = $product->get_children();
    
    foreach ($variation_ids as $variation_id) {
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            continue;
        }
        
        $voltage = $variation->get_attribute('voltios');
        
        if ($voltage && !in_array($voltage, $voltages)) {
            $voltages[] = $voltage;
        }
    }
    
    return $voltages;
}

/**
 * Get all amperage options for a product
 * 
 * @param int $product_id Parent product ID
 * @return array Array of amperage options
 */
function ecolitio_get_amperage_options($product_id) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return array();
    }
    
    $amperages = array();
    $variation_ids = $product->get_children();
    
    foreach ($variation_ids as $variation_id) {
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            continue;
        }
        
        $amperage = $variation->get_attribute('amperios');
        
        if ($amperage && !in_array($amperage, $amperages)) {
            $amperages[] = $amperage;
        }
    }
    
    return $amperages;
}

/**
 * Get amperage options for a specific voltage
 * 
 * @param int $product_id Parent product ID
 * @param string $voltage Voltage value
 * @return array Array of available amperage options for the voltage
 */
function ecolitio_get_amperage_options_for_voltage($product_id, $voltage) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return array();
    }
    
    $amperages = array();
    $variation_ids = $product->get_children();
    
    foreach ($variation_ids as $variation_id) {
        $variation = wc_get_product($variation_id);
        
        if (!$variation) {
            continue;
        }
        
        $var_voltage = $variation->get_attribute('voltios');
        $amperage = $variation->get_attribute('amperios');
        
        if ($var_voltage === $voltage && $amperage && !in_array($amperage, $amperages)) {
            $amperages[] = $amperage;
        }
    }
    
    return $amperages;
}
