# Variable Product Implementation Guide - Technical Details

## Overview

This guide provides the technical implementation details for converting the Sabway battery form from simple products to variable products with dynamic pricing.

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    VARIABLE PRODUCT SYSTEM                      │
└─────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ WooCommerce Variable Product (Parent)                            │
│ ├─ Product ID: 123                                               │
│ ├─ Type: variable                                                │
│ ├─ Attributes:                                                   │
│ │  ├─ voltios (pricing attribute)                               │
│ │  ├─ amperios (pricing attribute)                              │
│ │  ├─ ubicacion-de-bateria (non-pricing)                        │
│ │  └─ tipo-de-conector (non-pricing)                            │
│ └─ Variations:                                                   │
│    ├─ Variation 1: 24V + 10Ah = $100 (SKU: BATT-24V-10AH)      │
│    ├─ Variation 2: 24V + 15Ah = $130 (SKU: BATT-24V-15AH)      │
│    ├─ Variation 3: 36V + 10Ah = $120 (SKU: BATT-36V-10AH)      │
│    ├─ Variation 4: 36V + 15Ah = $156 (SKU: BATT-36V-15AH)      │
│    └─ ... more variations                                        │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ Sabway Battery Form (Frontend)                                   │
│ ├─ Step 1: Select Voltage & Amperage                            │
│ │  └─ Triggers: Price Calculation & Variation ID Lookup         │
│ ├─ Step 2: Select Dimensions/Location                           │
│ ├─ Step 3: Select Connector Type                                │
│ ├─ Step 4: Review & Confirm                                     │
│ └─ Submit: Add Variation to Cart with Custom Meta               │
└──────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│ Cart Item Structure                                              │
│ ├─ Product ID: 123 (parent)                                     │
│ ├─ Variation ID: 456 (specific variation)                       │
│ ├─ Quantity: 1                                                  │
│ ├─ Price: $156 (from variation)                                 │
│ └─ Custom Meta Data:                                            │
│    ├─ _sabway_electrical_specs                                  │
│    ├─ _sabway_physical_dimensions                               │
│    ├─ _sabway_specifications                                    │
│    └─ _sabway_custom_order                                      │
└──────────────────────────────────────────────────────────────────┘
```

---

## Pricing Matrix Configuration

### Define in `functions.php`

```php
/**
 * Get Sabway battery pricing matrix
 * Defines multipliers for voltage and amperage combinations
 */
function ecolitio_get_battery_pricing_matrix() {
    return array(
        'base_price' => 100, // Base price in currency units
        'voltage_multipliers' => array(
            '24V' => 1.0,
            '36V' => 1.2,
            '48V' => 1.5,
        ),
        'amperage_multipliers' => array(
            '10Ah' => 1.0,
            '15Ah' => 1.3,
            '20Ah' => 1.6,
        ),
    );
}

/**
 * Calculate final price for voltage/amperage combination
 */
function ecolitio_calculate_battery_price($voltage, $amperage) {
    $matrix = ecolitio_get_battery_pricing_matrix();
    $base = $matrix['base_price'];
    $voltage_mult = $matrix['voltage_multipliers'][$voltage] ?? 1.0;
    $amperage_mult = $matrix['amperage_multipliers'][$amperage] ?? 1.0;
    
    return $base * $voltage_mult * $amperage_mult;
}

/**
 * Get variation ID for voltage/amperage combination
 */
function ecolitio_get_variation_id_for_specs($product_id, $voltage, $amperage) {
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        return null;
    }
    
    $variations = $product->get_children();
    
    foreach ($variations as $variation_id) {
        $variation = wc_get_product($variation_id);
        $attrs = $variation->get_attributes();
        
        if ($attrs['voltios'] === $voltage && $attrs['amperios'] === $amperage) {
            return $variation_id;
        }
    }
    
    return null;
}
```

---

## Form Controller Updates

### Update `formController.js`

```javascript
/**
 * Enhanced Data Collector with Variation Support
 */
const dataCollector = {
  collect() {
    const distanceRange = document.getElementById("sab-distance-range");
    const voltageSelected = document.querySelector('input[name="voltage"]:checked');
    const amperageSelected = document.querySelector('input[name="amperage"]:checked');
    
    // NEW: Calculate variation ID based on selections
    const variationId = this.getVariationId(
      voltageSelected?.value,
      amperageSelected?.value
    );
    
    // ... rest of existing code ...
    
    return {
      variation_id: variationId, // NEW: Add variation ID
      electrical_specifications: {
        voltage: voltageSelected ? voltageSelected.value : null,
        amperage: amperageSelected ? amperageSelected.value : null,
        distance_range_km: distanceRange ? parseInt(distanceRange.value) : null,
      },
      // ... rest of existing data ...
    };
  },
  
  /**
   * NEW: Get variation ID from hidden field or calculate
   */
  getVariationId(voltage, amperage) {
    // Try to get from hidden field first (set by form)
    const variationField = document.getElementById('variation-id');
    if (variationField && variationField.value) {
      return parseInt(variationField.value);
    }
    
    // Fallback: return null, will be looked up server-side
    return null;
  }
};

/**
 * Enhanced UI Manager with Price Display
 */
const uiManager = {
  // ... existing methods ...
  
  /**
   * NEW: Display dynamic price based on selections
   */
  displayDynamicPrice(voltage, amperage) {
    const priceElement = document.getElementById('dynamic-price-display');
    if (!priceElement) return;
    
    // Get price from data attribute or calculate
    const priceData = window.batteryPricingMatrix || {};
    const basePrice = priceData.base_price || 100;
    const voltageMultiplier = priceData.voltage_multipliers?.[voltage] || 1.0;
    const amperageMultiplier = priceData.amperage_multipliers?.[amperage] || 1.0;
    
    const finalPrice = basePrice * voltageMultiplier * amperageMultiplier;
    
    priceElement.textContent = `$${finalPrice.toFixed(2)}`;
    priceElement.dataset.price = finalPrice;
  },
  
  /**
   * NEW: Update variation ID when selections change
   */
  updateVariationId(voltage, amperage) {
    const variationField = document.getElementById('variation-id');
    if (!variationField) return;
    
    // This will be populated by AJAX lookup or form data
    // For now, we'll let the server handle it
    variationField.dataset.voltage = voltage;
    variationField.dataset.amperage = amperage;
  }
};

/**
 * Enhanced Form Controller with Price Updates
 */
export const formController = () => {
  const form = document.querySelector(".sabway-form");
  
  // ... existing code ...
  
  // NEW: Add listeners for voltage/amperage changes
  const handleVoltageAmperageChange = () => {
    const voltageRadios = document.querySelectorAll('input[name="voltage"]');
    const amperageRadios = document.querySelectorAll('input[name="amperage"]');
    
    const updatePrice = () => {
      const voltage = document.querySelector('input[name="voltage"]:checked')?.value;
      const amperage = document.querySelector('input[name="amperage"]:checked')?.value;
      
      if (voltage && amperage) {
        uiManager.displayDynamicPrice(voltage, amperage);
        uiManager.updateVariationId(voltage, amperage);
      }
    };
    
    voltageRadios.forEach(radio => radio.addEventListener('change', updatePrice));
    amperageRadios.forEach(radio => radio.addEventListener('change', updatePrice));
  };
  
  handleVoltageAmperageChange();
};
```

---

## AJAX Handler Updates

### Update `ajax.php`

```php
/**
 * Enhanced AJAX handler for adding custom battery to cart
 * Now handles variable products with variations
 */
add_action('wp_ajax_custom_batery_add_to_cart', 'ecolitio_custom_batery_add_to_cart');
add_action('wp_ajax_nopriv_custom_batery_add_to_cart', 'ecolitio_custom_batery_add_to_cart');
function ecolitio_custom_batery_add_to_cart() {
    // 1. Verify Nonce
    $nonce = $_POST['nonce'] ?? '';
    if (!wp_verify_nonce($nonce, 'ecolitio_sabway_form_nonce')) {
        wp_send_json_error(array(
            'message' => __('Verificación de seguridad fallida (Nonce)', 'ecolitio-theme'),
            'code' => 'nonce_failed'
        ));
        return;
    }

    // 2. Sanitize and Validate Form Data
    $form_data = array();
    try {
        // Electrical specifications
        $form_data['voltage'] = sanitize_text_field($_POST['voltage'] ?? '');
        $form_data['amperage'] = sanitize_text_field($_POST['amperage'] ?? '');
        $form_data['distance_range_km'] = intval($_POST['distance_range_km'] ?? 0);
        
        // NEW: Get variation ID
        $form_data['variation_id'] = intval($_POST['variation_id'] ?? 0);
        
        // Physical dimensions
        $form_data['height_cm'] = floatval($_POST['height_cm'] ?? 0);
        $form_data['width_cm'] = floatval($_POST['width_cm'] ?? 0);
        $form_data['length_cm'] = floatval($_POST['length_cm'] ?? 0);
        $form_data['liters'] = floatval($_POST['liters'] ?? 0);
        
        // Other specifications
        $form_data['scooter_model'] = sanitize_text_field($_POST['scooter_model'] ?? '');
        $form_data['battery_location'] = sanitize_text_field($_POST['battery_location'] ?? '');
        $form_data['connector_type'] = sanitize_text_field($_POST['connector_type'] ?? '');
        $form_data['product_id'] = intval($_POST['product_id'] ?? 0);
        $form_data['battery_type'] = sanitize_text_field($_POST['battery_type'] ?? 'sabway');
        
        // Validate required fields
        $validation_errors = validate_sabway_form_data($form_data);
        if (!empty($validation_errors)) {
            wp_send_json_error(array(
                'message' => __('Datos del formulario inválidos', 'ecolitio-theme'),
                'errors' => $validation_errors,
                'code' => 'validation_failed'
            ));
            return;
        }
        
    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => __('Error procesando datos del formulario', 'ecolitio-theme'),
            'code' => 'processing_failed'
        ));
        return;
    }

    // 3. Add to Cart with Variation Support
    try {
        $product_id = $form_data['product_id'];
        $variation_id = $form_data['variation_id'];
        $quantity = 1;
        
        // NEW: If no variation ID provided, look it up
        if (!$variation_id) {
            $variation_id = ecolitio_get_variation_id_for_specs(
                $product_id,
                $form_data['voltage'],
                $form_data['amperage']
            );
        }
        
        // Validate variation exists
        if (!$variation_id) {
            wp_send_json_error(array(
                'message' => __('Variación de producto no encontrada', 'ecolitio-theme'),
                'code' => 'variation_not_found'
            ));
            return;
        }
        
        // Prepare custom data to be stored in cart item
        $cart_item_data = array(
            '_sabway_electrical_specs' => array(
                'voltage' => $form_data['voltage'],
                'amperage' => $form_data['amperage'],
                'distance_range_km' => $form_data['distance_range_km'],
            ),
            '_sabway_physical_dimensions' => array(
                'height_cm' => $form_data['height_cm'],
                'width_cm' => $form_data['width_cm'],
                'length_cm' => $form_data['length_cm'],
                'liters' => $form_data['liters'],
            ),
            '_sabway_specifications' => array(
                'scooter_model' => $form_data['scooter_model'],
                'battery_location' => $form_data['battery_location'],
                'connector_type' => $form_data['connector_type'],
            ),
            '_sabway_custom_order' => true
        );

        // NEW: Add variation to cart instead of simple product
        $cart_item_key = WC()->cart->add_to_cart(
            $product_id,
            $quantity,
            $variation_id,  // NEW: Pass variation ID
            array(),        // Variation attributes (already set in variation)
            $cart_item_data
        );

        if ($cart_item_key) {
            wp_send_json_success(array(
                'message' => __('Producto añadido al carrito', 'ecolitio-theme'),
                'cart_url' => wc_get_cart_url()
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Error al añadir al carrito', 'ecolitio-theme'),
                'code' => 'add_to_cart_failed'
            ));
        }

    } catch (Exception $e) {
        wp_send_json_error(array(
            'message' => __('Error añadiendo al carrito', 'ecolitio-theme'),
            'code' => 'cart_exception'
        ));
    }
}
```

---

## Template Updates

### Update `sabway-battery-form.php`

Add dynamic price display and variation ID field:

```php
<!-- Add after Step 1 electrical specifications section -->
<div class="price-display-section !mb-6">
    <h4 class="!text-white-eco !font-bold">Precio Estimado:</h4>
    <div id="dynamic-price-display" class="!text-2xl !font-bold" style="color: var(--battery-color);">
        $0.00
    </div>
    <p class="!text-sm !text-white-eco !mt-2">
        El precio se actualiza según voltaje y amperaje seleccionados
    </p>
</div>

<!-- Add hidden field for variation ID -->
<input type="hidden" id="variation-id" name="variation_id" value="">

<!-- Pass pricing matrix to JavaScript -->
<script>
    window.batteryPricingMatrix = <?php echo json_encode(ecolitio_get_battery_pricing_matrix()); ?>;
</script>
```

---

## Migration Script

### Create `inc/migration-simple-to-variable.php`

```php
<?php
/**
 * Migration script to convert simple products to variable products
 * Run via WP-CLI or admin page
 */

function ecolitio_migrate_simple_to_variable_products() {
    $battery_products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_tag',
                'field' => 'slug',
                'terms' => array('sabway', 'bateria-a-medida', 'taller-del-patinete'),
                'operator' => 'IN'
            )
        )
    ));
    
    $results = array(
        'total' => count($battery_products),
        'migrated' => 0,
        'failed' => 0,
        'errors' => array()
    );
    
    foreach ($battery_products as $product) {
        try {
            // Skip if already variable
            if ($product->is_type('variable')) {
                continue;
            }
            
            // Convert to variable product
            $product->set_type('variable');
            $product->save();
            
            // Get voltage and amperage options
            $voltage_options = $product->get_attribute('voltios');
            $amperage_options = $product->get_attribute('amperios');
            
            if (!$voltage_options || !$amperage_options) {
                throw new Exception('Missing voltage or amperage attributes');
            }
            
            $voltages = explode(', ', $voltage_options);
            $amperages = explode(', ', $amperage_options);
            
            // Create variations for each combination
            foreach ($voltages as $voltage) {
                foreach ($amperages as $amperage) {
                    $variation = new WC_Product_Variation();
                    $variation->set_parent_id($product->get_id());
                    $variation->set_attributes(array(
                        'voltios' => trim($voltage),
                        'amperios' => trim($amperage)
                    ));
                    
                    // Calculate price
                    $price = ecolitio_calculate_battery_price(trim($voltage), trim($amperage));
                    $variation->set_price($price);
                    $variation->set_regular_price($price);
                    
                    // Set SKU
                    $sku = 'BATT-' . str_replace('V', 'V-', trim($voltage)) . '-' . trim($amperage);
                    $variation->set_sku($sku);
                    
                    // Set stock
                    $variation->set_stock_quantity(50);
                    $variation->set_manage_stock(true);
                    $variation->set_stock_status('instock');
                    
                    $variation->save();
                }
            }
            
            $results['migrated']++;
            
        } catch (Exception $e) {
            $results['failed']++;
            $results['errors'][] = array(
                'product_id' => $product->get_id(),
                'error' => $e->getMessage()
            );
        }
    }
    
    return $results;
}

// Register WP-CLI command if available
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('ecolitio migrate-products', function() {
        $results = ecolitio_migrate_simple_to_variable_products();
        WP_CLI::success(sprintf(
            'Migration complete: %d migrated, %d failed',
            $results['migrated'],
            $results['failed']
        ));
        if (!empty($results['errors'])) {
            WP_CLI::warning('Errors: ' . json_encode($results['errors']));
        }
    });
}
```

---

## Testing Checklist

### Unit Tests
- [ ] Price calculation function returns correct values
- [ ] Variation ID lookup finds correct variation
- [ ] Pricing matrix configuration loads correctly

### Integration Tests
- [ ] Form displays dynamic price on voltage/amperage selection
- [ ] Form submits with correct variation ID
- [ ] AJAX handler adds variation to cart
- [ ] Cart displays variation details correctly
- [ ] Checkout processes variation correctly
- [ ] Order created with variation information

### User Flow Tests
- [ ] User selects voltage and amperage
- [ ] Price updates dynamically
- [ ] User completes form and submits
- [ ] Product added to cart with correct price
- [ ] Cart shows variation details
- [ ] Checkout completes successfully
- [ ] Order confirmation shows all details

### Edge Cases
- [ ] Invalid voltage/amperage combination
- [ ] Missing variation
- [ ] Out of stock variation
- [ ] Multiple items in cart
- [ ] Variation price changes after adding to cart

---

## Rollback Procedure

If issues occur:

1. **Backup Database**
   ```bash
   wp db export backup-$(date +%Y%m%d-%H%M%S).sql
   ```

2. **Revert Products to Simple**
   ```php
   // Run reverse migration script
   ecolitio_migrate_variable_to_simple_products();
   ```

3. **Clear Cache**
   ```bash
   wp cache flush
   ```

4. **Verify Data**
   - Check product types
   - Verify prices
   - Check cart functionality

---

## Performance Considerations

### Database Queries
- Variation lookup uses indexed queries
- Cache variation IDs when possible
- Use transients for pricing matrix

### Frontend Performance
- Lazy load pricing matrix
- Debounce price calculation
- Minimize DOM updates

### Optimization Tips
```php
// Cache pricing matrix
function ecolitio_get_battery_pricing_matrix() {
    $cache_key = 'ecolitio_battery_pricing_matrix';
    $matrix = wp_cache_get($cache_key);
    
    if (false === $matrix) {
        $matrix = array(
            'base_price' => 100,
            'voltage_multipliers' => array(...),
            'amperage_multipliers' => array(...)
        );
        wp_cache_set($cache_key, $matrix, '', 3600); // Cache for 1 hour
    }
    
    return $matrix;
}
```

---

## Monitoring & Maintenance

### Post-Migration Checks
- Monitor error logs for issues
- Check cart abandonment rates
- Verify order data integrity
- Monitor performance metrics

### Regular Maintenance
- Update pricing matrix as needed
- Monitor variation stock levels
- Review customer feedback
- Optimize queries if needed

