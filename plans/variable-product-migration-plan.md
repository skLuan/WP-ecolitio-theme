# Variable Product Migration Plan - Sabway Battery Form

## Executive Summary

This plan outlines the safest and easiest approach to convert the current simple product system (with attributes) to WooCommerce Variable Products with dynamic pricing based on voltage and amperage combinations.

**Chosen Approach:** Native WooCommerce Variable Products with Variations

This approach is the safest because:
- Uses WooCommerce's native architecture (no custom hacks)
- Maintains full compatibility with WooCommerce ecosystem
- Allows proper inventory management per variation
- Supports native WooCommerce pricing and discounts
- Easier to maintain and scale

---

## Current System Analysis

### What Exists Now
- **Product Type:** Simple products with custom attributes (voltios, amperios, ubicacion-de-bateria, tipo-de-conector)
- **Pricing:** Single price for all attribute combinations
- **Form:** Multi-step customization form that collects specifications
- **Submission:** Form data added to cart as custom meta data
- **Files Involved:**
  - [`themes/ecolitio-theme/templates/sabway-battery-form.php`](themes/ecolitio-theme/templates/sabway-battery-form.php) - Form template
  - [`themes/ecolitio-theme/src/formController.js`](themes/ecolitio-theme/src/formController.js) - Form logic
  - [`themes/ecolitio-theme/inc/ajax.php`](themes/ecolitio-theme/inc/ajax.php) - AJAX handlers
  - [`themes/ecolitio-theme/functions.php`](themes/ecolitio-theme/functions.php) - Shortcode and helpers

### Problem to Solve
- Need different prices based on voltage and amperage combinations
- Current system treats all combinations as the same product
- No native WooCommerce way to differentiate pricing

---

## Migration Strategy

### Phase 1: Product Structure Setup

#### Step 1.1: Create Pricing Matrix
Define the price multipliers for each voltage/amperage combination:

```
Base Price: $100 (example)

Voltage Multiplier:
- 24V: 1.0x
- 36V: 1.2x
- 48V: 1.5x

Amperage Multiplier:
- 10Ah: 1.0x
- 15Ah: 1.3x
- 20Ah: 1.6x

Final Price = Base Price × Voltage Multiplier × Amperage Multiplier
```

#### Step 1.2: Convert Simple Product to Variable Product
- Change product type from "Simple" to "Variable"
- Keep existing attributes (voltios, amperios)
- Add other attributes as non-pricing attributes (ubicacion-de-bateria, tipo-de-conector)

#### Step 1.3: Create Variations
For each voltage/amperage combination:
- Create a variation
- Set unique SKU
- Set calculated price
- Set stock quantity
- Keep other attributes as defaults

**Example Variations:**
- Variation 1: 24V + 10Ah = $100
- Variation 2: 24V + 15Ah = $130
- Variation 3: 36V + 10Ah = $120
- Variation 4: 36V + 15Ah = $156
- etc.

---

### Phase 2: Form Integration Updates

#### Step 2.1: Modify Form Behavior
The form will now:
1. Collect voltage and amperage selections
2. Use these to select the correct variation
3. Collect other specifications (dimensions, connector, location)
4. Add variation to cart with custom meta data

#### Step 2.2: Update Form Controller
- Modify [`themes/ecolitio-theme/src/formController.js`](themes/ecolitio-theme/src/formController.js) to:
  - Track voltage/amperage selections
  - Calculate/display dynamic price
  - Pass variation ID to cart submission

#### Step 2.3: Update AJAX Handler
- Modify [`themes/ecolitio-theme/inc/ajax.php`](themes/ecolitio-theme/inc/ajax.php) to:
  - Accept variation ID from form
  - Add variation to cart instead of simple product
  - Maintain custom meta data for other specifications

---

### Phase 3: Frontend Display Updates

#### Step 3.1: Dynamic Price Display
- Show base price
- Show price multipliers based on selections
- Display final calculated price in real-time

#### Step 3.2: Update Cart Display
- Show variation details (voltage, amperage)
- Show other specifications as meta data
- Maintain current cart display format

#### Step 3.3: Update Order Display
- Show variation information
- Show all custom specifications
- Maintain current order format

---

### Phase 4: Data Migration (If Existing Products)

#### Step 4.1: Backup Current Products
- Export all current simple products
- Document current pricing

#### Step 4.2: Create Migration Script
- Convert simple products to variable products
- Create variations based on attribute combinations
- Calculate prices using pricing matrix
- Maintain product metadata

#### Step 4.3: Test Migration
- Verify all variations created correctly
- Check pricing calculations
- Test form submission with new variations
- Verify cart and order display

---

## Implementation Details

### Key Changes Required

#### 1. Database/Product Structure
```
Current: Simple Product
├── Attributes (voltios, amperios, etc.)
└── Single Price

New: Variable Product
├── Attributes (voltios, amperios, ubicacion-de-bateria, tipo-de-conector)
└── Variations
    ├── Variation 1 (24V, 10Ah)
    │   ├── Price: $100
    │   ├── SKU: BATT-24V-10AH
    │   └── Stock: 50
    ├── Variation 2 (24V, 15Ah)
    │   ├── Price: $130
    │   ├── SKU: BATT-24V-15AH
    │   └── Stock: 50
    └── ... more variations
```

#### 2. Form Submission Flow
```
User Selects Voltage & Amperage
    ↓
Form calculates/displays price
    ↓
Form identifies matching variation ID
    ↓
User completes other specifications
    ↓
Form submits with:
  - variation_id
  - voltage
  - amperage
  - distance_range_km
  - dimensions/liters
  - connector_type
  - battery_location
    ↓
AJAX adds variation to cart with custom meta
    ↓
Cart displays variation + custom specs
```

#### 3. Price Calculation (Frontend)
```javascript
// In formController.js
const priceMatrix = {
  '24V': 1.0,
  '36V': 1.2,
  '48V': 1.5
};

const amperageMatrix = {
  '10Ah': 1.0,
  '15Ah': 1.3,
  '20Ah': 1.6
};

const basePrice = 100; // From product

function calculatePrice(voltage, amperage) {
  const voltageMultiplier = priceMatrix[voltage] || 1.0;
  const amperageMultiplier = amperageMatrix[amperage] || 1.0;
  return basePrice * voltageMultiplier * amperageMultiplier;
}
```

---

## Safety Considerations

### 1. Backward Compatibility
- Keep existing form structure and UX
- Maintain custom meta data storage
- Preserve order history and data

### 2. Testing Strategy
- Test with staging environment first
- Verify all variations created correctly
- Test form submission with each variation
- Test cart and checkout flow
- Test order creation and emails

### 3. Rollback Plan
- Keep backup of original simple products
- Document all changes
- Have migration script reversible if needed

### 4. Data Integrity
- Verify no data loss during migration
- Check all custom meta data preserved
- Validate pricing calculations
- Ensure inventory tracking works

---

## Files to Modify

### Core Files
1. **`themes/ecolitio-theme/src/formController.js`**
   - Add price calculation logic
   - Add variation ID tracking
   - Update form submission to include variation_id

2. **`themes/ecolitio-theme/inc/ajax.php`**
   - Update `ecolitio_custom_batery_add_to_cart()` to handle variations
   - Update `ecolitio_sabway_submit_form()` to handle variations
   - Add variation validation

3. **`themes/ecolitio-theme/functions.php`**
   - Add pricing matrix configuration
   - Add variation creation helper functions
   - Update shortcode to pass pricing info to form

4. **`themes/ecolitio-theme/templates/sabway-battery-form.php`**
   - Add dynamic price display
   - Add hidden variation_id field
   - Update form to show calculated price

### Supporting Files
5. **`themes/ecolitio-theme/woocommerce/content-single-product-bateria-sabway.php`**
   - May need updates if WooCommerce variation display changes

6. **`themes/ecolitio-theme/woocommerce/single-product/add-to-cart/simple.php`**
   - May need custom handling for variable products

---

## Advantages of This Approach

✅ **Native WooCommerce Integration**
- Uses standard WooCommerce variable product system
- Compatible with all WooCommerce extensions
- Proper inventory management per variation

✅ **Easy Price Management**
- Prices stored in database (not calculated on-the-fly)
- Can be managed via WooCommerce admin
- Supports discounts and promotions

✅ **Better SEO**
- Each variation can have unique URL
- Better for search engines
- Cleaner product structure

✅ **Scalability**
- Easy to add new voltage/amperage combinations
- Easy to modify pricing
- Easy to manage inventory

✅ **User Experience**
- Familiar WooCommerce checkout flow
- Native variation selection
- Proper cart display

---

## Potential Challenges & Solutions

| Challenge | Solution |
|-----------|----------|
| Many variations to create | Create migration script to auto-generate variations |
| Price calculation complexity | Store calculated prices in variation, not dynamic |
| Form customization needed | Keep custom form, just add variation selection |
| Existing orders affected | Migration only affects new orders, old orders unaffected |
| Inventory management | Each variation has own stock, easier to track |

---

## Timeline & Effort Estimate

### Phase 1: Setup (Planning & Configuration)
- Define pricing matrix
- Plan variation structure
- Create migration script

### Phase 2: Development (Code Changes)
- Update form controller
- Update AJAX handlers
- Update functions.php
- Add price display logic

### Phase 3: Testing
- Test form submission
- Test cart/checkout
- Test order creation
- Test email notifications

### Phase 4: Migration & Deployment
- Backup current products
- Run migration script
- Verify all data
- Deploy to production

---

## Recommended Next Steps

1. **Confirm Pricing Matrix** - Define exact price multipliers for each voltage/amperage
2. **Create Migration Script** - Build tool to convert existing products
3. **Update Form Controller** - Implement price calculation and variation selection
4. **Update AJAX Handlers** - Modify cart submission for variations
5. **Comprehensive Testing** - Test all scenarios before production
6. **Documentation** - Document new system for future maintenance

---

## Questions to Clarify

1. What is the base price for batteries?
2. What are the exact voltage and amperage options?
3. Are there any other attributes that affect pricing?
4. Do you have existing products to migrate?
5. What's your timeline for this change?
6. Do you need to maintain backward compatibility with existing orders?

