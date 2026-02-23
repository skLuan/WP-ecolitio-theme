# Product Filtering Plan for Normal Users

## Problem Statement
Currently, the "Taller Sabway" and "Taller Del Patinete" roles filter products by their specific tags (sabway and taller-del-patinete). However, normal users can see these restricted products in related products sections (especially those added via Elementor), which should not be visible to them.

## Solution Overview
Implement a centralized product filtering system that:
1. Excludes restricted product tags from normal users
2. Allows taller roles to see only their specific products
3. Is scalable for future taller role additions

## Current Implementation Analysis

### Taller Role Filtering (Working)
- **Location**: [`themes/ecolitio-theme/inc/class-taller-role.php`](themes/ecolitio-theme/inc/class-taller-role.php:181)
- **Method**: `filter_products_for_taller_role()` hook on `woocommerce_product_query_tax_query`
- **Behavior**: 
  - Only applies to users with specific taller role
  - Adds tax query to filter by product tag
  - Works for shop pages and product archives

### Current Restricted Tags
- `sabway` - Taller Sabway products
- `taller-del-patinete` - Taller Del Patinete products

## Implementation Plan

### Step 1: Create Centralized Function
Create a function to get all restricted product tags that:
- Returns array of all taller-specific tags
- Can be easily extended for future taller roles
- Location: `themes/ecolitio-theme/functions.php`

```php
function ecolitio_get_restricted_product_tags() {
    $restricted_tags = array(
        'sabway',
        'taller-del-patinete'
    );
    return apply_filters('ecolitio_restricted_product_tags', $restricted_tags);
}
```

### Step 2: Implement Normal User Filtering
Add a filter hook that:
- Applies to normal users (not logged in or without taller role)
- Excludes restricted tags from product queries
- Works for related products, Elementor widgets, and shop pages
- Location: `themes/ecolitio-theme/functions.php`

```php
add_filter('woocommerce_product_query_tax_query', 'ecolitio_filter_restricted_products_for_normal_users', 10, 2);
```

### Step 3: Apply to Related Products
The WooCommerce related products query uses the same `woocommerce_product_query_tax_query` hook, so the filter will automatically apply to:
- WooCommerce native related products template
- Elementor related products widget

### Step 4: Testing Strategy
- Test normal user sees products without restricted tags
- Test normal user doesn't see products with restricted tags
- Test taller_sabway user sees only sabway products
- Test taller_del_patinete user sees only taller-del-patinete products
- Test admin can see all products

## Files to Modify
1. [`themes/ecolitio-theme/functions.php`](themes/ecolitio-theme/functions.php) - Add centralized function and filter

## Future Extensibility
When adding new taller roles:
1. Add new tag to `ecolitio_get_restricted_product_tags()` via filter
2. Create new taller role class (already follows pattern)
3. No changes needed to normal user filtering logic

## Benefits
- ✅ Scalable for future taller roles
- ✅ Centralized tag management
- ✅ Works for all product queries (shop, related, Elementor)
- ✅ Maintains existing taller role functionality
- ✅ No breaking changes
