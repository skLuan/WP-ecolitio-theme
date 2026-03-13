# Step 3.1 - Dynamic Pricing Matrix from Variable Products

## Problem Statement

The current static pricing matrix approach has limitations:
- Hardcoded multipliers don't reflect actual product prices
- Requires manual updates when prices change
- Doesn't leverage existing variable product data in WooCommerce

## Solution

Create a dynamic pricing matrix by:
1. Querying all variable products with their variations
2. Extracting voltage/amperage from variation attributes
3. Building a real-time pricing matrix from actual product prices
4. Delivering matrix via AJAX call instead of inline script

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    User Opens Form                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         formController.js initializes                       │
│  - Calls AJAX: get_battery_pricing_matrix                  │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         AJAX Handler in ajax.php                            │
│  - Queries variable products                               │
│  - Extracts variations with voltage/amperage               │
│  - Builds pricing matrix from actual prices                │
│  - Returns JSON matrix                                      │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│         JavaScript receives matrix                          │
│  - Stores in window.batteryPricingMatrix                   │
│  - Ready for price calculations                            │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│    User selects voltage/amperage                           │
│  - displayDynamicPrice() calculates price                  │
│  - Uses matrix from AJAX response                          │
│  - Shows formatted price in EUR                            │
└─────────────────────────────────────────────────────────────┘
```

## Implementation Steps

### Step 3.1.1: Create AJAX Handler for Pricing Matrix

**File:** `themes/ecolitio-theme/inc/ajax.php`

Add new AJAX action:
```php
add_action('wp_ajax_get_battery_pricing_matrix', 'ecolitio_get_battery_pricing_matrix_ajax');
add_action('wp_ajax_nopriv_get_battery_pricing_matrix', 'ecolitio_get_battery_pricing_matrix_ajax');

function ecolitio_get_battery_pricing_matrix_ajax() {
    // Query variable products
    // Extract variations with voltage/amperage
    // Build pricing matrix
    // Return JSON
}
```

**Returns:**
```json
{
  "success": true,
  "data": {
    "24V": {
      "4,8AH": 100,
      "9,6AH": 120,
      "14,4AH": 140
    },
    "36V": {
      "4,8AH": 120,
      "9,6AH": 144,
      "14,4AH": 168
    }
  }
}
```

### Step 3.1.2: Update formController.js

**File:** `themes/ecolitio-theme/src/formController.js`

Modify `displayDynamicPrice()`:
```javascript
displayDynamicPrice(voltage, amperage) {
  const priceElement = document.getElementById('dynamic-price-display');
  if (!priceElement) return;
  
  // Get pricing matrix from window object (populated by AJAX)
  const pricingMatrix = window.batteryPricingMatrix || {};
  
  // Look up price directly from matrix
  const finalPrice = pricingMatrix[voltage]?.[amperage] || 0;
  
  if (finalPrice === 0) {
    priceElement.textContent = 'Precio no disponible';
    return;
  }
  
  // Format price with currency
  const formattedPrice = new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(finalPrice);
  
  priceElement.textContent = formattedPrice;
  priceElement.dataset.price = finalPrice;
}
```

Add initialization call:
```javascript
// Initialize pricing matrix on form load
const initializePricingMatrix = async () => {
  try {
    const response = await fetch(ajaxUrl, {
      method: 'POST',
      credentials: 'include',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'get_battery_pricing_matrix',
        nonce: nonce,
      }),
    });
    
    const result = await response.json();
    if (result.success) {
      window.batteryPricingMatrix = result.data;
      console.log('Pricing matrix loaded:', window.batteryPricingMatrix);
    }
  } catch (error) {
    console.error('Error loading pricing matrix:', error);
  }
};

// Call on form initialization
initializePricingMatrix();
```

### Step 3.1.3: Update sabway-battery-form.php

**File:** `themes/ecolitio-theme/templates/sabway-battery-form.php`

Remove inline script (if present):
```php
<!-- REMOVE THIS if it exists:
<script>
    window.batteryPricingMatrix = <?php echo json_encode(ecolitio_get_battery_pricing_matrix()); ?>;
</script>
-->
```

The matrix will now be loaded via AJAX instead.

## Benefits

✅ **Real-time Pricing** - Always reflects current product prices
✅ **No Manual Updates** - Automatically uses WooCommerce product data
✅ **Better Performance** - Loads only when needed
✅ **Scalable** - Works with any number of variations
✅ **Maintainable** - Single source of truth (WooCommerce products)
✅ **Flexible** - Easy to add new voltage/amperage combinations

## Data Flow

1. **Form loads** → AJAX call to get_battery_pricing_matrix
2. **Server queries** → All variable products with voltage/amperage attributes
3. **Server builds** → Nested matrix: voltage → amperage → price
4. **Server returns** → JSON with pricing data
5. **JavaScript stores** → window.batteryPricingMatrix
6. **User selects** → voltage/amperage
7. **JavaScript calculates** → Looks up price in matrix
8. **Price displays** → Formatted in EUR

## Example Matrix Structure

```javascript
{
  "24V": {
    "4,8AH": 100.00,
    "9,6AH": 120.00,
    "14,4AH": 140.00,
    "19,2AH": 160.00,
    "24AH": 180.00,
    "28,8AH": 200.00,
    "33,6AH": 220.00,
    "38,4AH": 240.00
  },
  "36V": {
    "4,8AH": 120.00,
    "9,6AH": 144.00,
    "14,4AH": 168.00,
    "19,2AH": 192.00,
    "24AH": 216.00,
    "28,8AH": 240.00,
    "33,6AH": 264.00,
    "38,4AH": 288.00
  },
  "48V": {
    "4,8AH": 150.00,
    "9,6AH": 180.00,
    "14,4AH": 210.00,
    "19,2AH": 240.00,
    "24AH": 270.00,
    "28,8AH": 300.00,
    "33,6AH": 330.00,
    "38,4AH": 360.00
  }
}
```

## Error Handling

- If AJAX call fails → Show "Precio no disponible"
- If voltage/amperage not in matrix → Show "Precio no disponible"
- If matrix not loaded → Retry on user interaction
- Console logs for debugging

## Testing Checklist

- [ ] AJAX call returns valid JSON
- [ ] Matrix contains all voltage/amperage combinations
- [ ] Prices match WooCommerce product prices
- [ ] Dynamic price updates when selections change
- [ ] Price displays in EUR format
- [ ] Error handling works if matrix unavailable
- [ ] Performance is acceptable (< 500ms load time)

