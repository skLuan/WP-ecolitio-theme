# Battery Form Refactoring Plan

## Overview
Refactor the hardcoded "Sabway Battery Form" into a generic battery customization form system that supports three battery types:
- **Bateria Sabway** (red color: #d02024)
- **Bateria a Medida** (blue color: #0066cc)
- **Bateria Taller Del Patinete** (green color: #93E12D)

## Current State Analysis

### Hardcoded References Found
1. **Shortcode**: `[sabway_battery_form]` in [`functions.php:335-336`](themes/ecolitio-theme/functions.php:335)
2. **Template**: [`templates/sabway-battery-form.php`](themes/ecolitio-theme/templates/sabway-battery-form.php) - hardcoded "Tu bateria a media" title
3. **Form Controller**: [`src/formController.js`](themes/ecolitio-theme/src/formController.js) - uses hardcoded selectors and class names
4. **AJAX Handler**: [`inc/ajax.php:513-612`](themes/ecolitio-theme/inc/ajax.php:513) - `custom_batery_add_to_cart` function
5. **Product Templates**:
   - [`woocommerce/content-single-product-bateria-sabway.php`](themes/ecolitio-theme/woocommerce/content-single-product-bateria-sabway.php) - uses shortcode
   - [`woocommerce/content-single-product-bateria-medida.php`](themes/ecolitio-theme/woocommerce/content-single-product-bateria-medida.php) - inline form with hardcoded colors
6. **CSS Classes**: Hardcoded color classes (red-sabway, blue-eco, green-eco)

### Key Observations
- All three battery types share the same form logic and validation
- Only differences are: colors, product tags, and order metadata
- Form uses Swiper slider for multi-step navigation
- Validation is comprehensive and reusable
- AJAX submission creates orders with custom metadata

## Refactoring Strategy

### Phase 1: Enhance Shortcode to Support Battery Types
**File**: [`functions.php:335-425`](themes/ecolitio-theme/functions.php:335)

**Changes**:
- Add `battery_type` parameter to shortcode attributes
- Map battery types to product tags and color schemes
- Detect battery type from product tag if not explicitly provided
- Pass battery type to template and JavaScript

**New Shortcode Signature**:
```php
[sabway_battery_form product_id="123" battery_type="sabway"]
```

**Battery Type Configuration**:
```php
$battery_types = array(
    'sabway' => array(
        'tag' => 'sabway',
        'color' => 'red-sabway',
        'color_hex' => '#d02024',
        'title' => 'Tu batería Sabway'
    ),
    'medida' => array(
        'tag' => 'bateria-medida',
        'color' => 'blue-eco',
        'color_hex' => '#0066cc',
        'title' => 'Tu batería a medida'
    ),
    'patinete' => array(
        'tag' => 'taller-del-patinete',
        'color' => 'green-eco',
        'color_hex' => '#93E12D',
        'title' => 'Tu batería Taller Del Patinete'
    )
)
```

### Phase 2: Refactor Form Template
**File**: [`templates/sabway-battery-form.php`](themes/ecolitio-theme/templates/sabway-battery-form.php)

**Changes**:
- Replace hardcoded "Tu bateria a media" with dynamic title from battery type
- Replace hardcoded color classes with dynamic CSS classes
- Add data attributes to form for battery type identification
- Keep all form logic identical

**New Template Variables**:
- `$battery_type` - Type of battery (sabway, medida, patinete)
- `$battery_config` - Configuration array with colors and titles
- `$color_class` - Dynamic color class based on battery type

### Phase 3: Update Form Controller
**File**: [`src/formController.js`](themes/ecolitio-theme/src/formController.js)

**Changes**:
- Read battery type from form data attribute
- Pass battery type to AJAX submission
- Maintain all existing validation logic
- Update CSS class references to use dynamic colors

**New Data Attributes**:
```html
<form class="sabway-form" data-battery-type="sabway" data-product-id="123">
```

### Phase 4: Update AJAX Handlers
**File**: [`inc/ajax.php:513-612`](themes/ecolitio-theme/inc/ajax.php:513)

**Changes**:
- Accept `battery_type` parameter in AJAX request
- Validate product has correct tag for battery type
- Update order metadata to use generic keys with battery type value
- Maintain backward compatibility with existing orders

**New Metadata Structure**:
```php
'_battery_customization_type' => 'sabway|medida|patinete'
'_battery_electrical_specs' => array(...)
'_battery_physical_dimensions' => array(...)
'_battery_specifications' => array(...)
```

### Phase 5: Update Product Templates
**Changes to Existing**:
- [`woocommerce/content-single-product-bateria-medida.php`](themes/ecolitio-theme/woocommerce/content-single-product-bateria-medida.php) - Replace inline form with shortcode
- [`woocommerce/content-single-product-bateria-sabway.php`](themes/ecolitio-theme/woocommerce/content-single-product-bateria-sabway.php) - Already uses shortcode (no changes needed)

**Note**: "Bateria Taller Del Patinete" product will use Elementor with the shortcode, similar to Sabway. No new PHP template file needed.

### Phase 6: Update Product Filtering
**File**: [`inc/ajax.php:124-174`](themes/ecolitio-theme/inc/ajax.php:124)

**Changes**:
- Update `taller_sabway_filter_products` to filter by correct tag
- Create similar filter for `taller_del_patinete` role
- Ensure product visibility based on user role and battery type

## Implementation Order

1. ✅ Analyze current implementation
2. Enhance shortcode with battery_type parameter
3. Refactor form template to use dynamic colors and titles
4. Update formController.js to handle battery types
5. Update AJAX handlers to accept and validate battery types
6. Create new product template for "bateria taller del patinete"
7. Update "bateria a medida" product template
8. Test all three battery types end-to-end
9. Verify order metadata and product registration

## Backward Compatibility

- Shortcode name remains `[sabway_battery_form]`
- Existing shortcode calls without `battery_type` default to 'sabway'
- Product detection via tag ensures correct type is used
- Old order metadata keys remain for historical data

## Testing Checklist

- [ ] Sabway battery form displays with red colors
- [ ] Medida battery form displays with blue colors
- [ ] Patinete battery form displays with green colors
- [ ] Form submission works for all three types
- [ ] Orders are created with correct metadata
- [ ] Product tags are validated correctly
- [ ] User roles can only see their assigned battery types
- [ ] Backward compatibility maintained for existing orders

## Files to Modify

1. [`themes/ecolitio-theme/functions.php`](themes/ecolitio-theme/functions.php) - Shortcode enhancement
2. [`themes/ecolitio-theme/templates/sabway-battery-form.php`](themes/ecolitio-theme/templates/sabway-battery-form.php) - Template refactoring
3. [`themes/ecolitio-theme/src/formController.js`](themes/ecolitio-theme/src/formController.js) - JavaScript updates
4. [`themes/ecolitio-theme/inc/ajax.php`](themes/ecolitio-theme/inc/ajax.php) - AJAX handler updates
5. [`themes/ecolitio-theme/woocommerce/content-single-product-bateria-medida.php`](themes/ecolitio-theme/woocommerce/content-single-product-bateria-medida.php) - Use shortcode
