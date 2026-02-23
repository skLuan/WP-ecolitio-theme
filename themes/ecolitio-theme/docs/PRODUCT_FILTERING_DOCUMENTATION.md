# Product Filtering System Documentation

## Overview

The product filtering system ensures that restricted products (those tagged with specific taller role tags) are only visible to users with the appropriate role. Normal users cannot see products reserved for taller roles.

## How It Works

### Restricted Product Tags

The system maintains a centralized list of restricted product tags:

- `sabway` - Products for Taller Sabway role
- `taller-del-patinete` - Products for Taller Del Patinete role

### Filtering Logic

The filtering is implemented through the `woocommerce_product_query_tax_query` hook, which applies to:

1. **Shop pages** - Product archive pages
2. **Related products** - Products shown on single product pages
3. **Elementor widgets** - Related products widgets in Elementor
4. **AJAX product queries** - Dynamic product loading

### User Role Behavior

#### Normal Users (Not Logged In or Without Taller Role)
- **See**: All products EXCEPT those with restricted tags
- **Cannot see**: Products tagged with `sabway` or `taller-del-patinete`

#### Taller Sabway Users
- **See**: Only products tagged with `sabway`
- **Cannot see**: Products without the `sabway` tag (handled by their role-specific filter)

#### Taller Del Patinete Users
- **See**: Only products tagged with `taller-del-patinete`
- **Cannot see**: Products without the `taller-del-patinete` tag (handled by their role-specific filter)

#### Admin Users
- **See**: All products (no filtering applied in admin)

## Implementation Details

### Core Functions

#### `ecolitio_get_restricted_product_tags()`

Returns an array of all restricted product tag slugs.

```php
$restricted_tags = ecolitio_get_restricted_product_tags();
// Returns: array('sabway', 'taller-del-patinete')
```

**Extensibility**: This function uses the `ecolitio_restricted_product_tags` filter, allowing plugins/themes to add more restricted tags:

```php
add_filter('ecolitio_restricted_product_tags', function($tags) {
    $tags[] = 'new-taller-role-tag';
    return $tags;
});
```

#### `ecolitio_filter_restricted_products_for_normal_users()`

Applies the filtering logic to product queries. This function:

1. Checks if the user has a taller role
2. If yes, skips filtering (lets role-specific filter handle it)
3. If no, adds a `NOT IN` tax query to exclude restricted tags

**Hook**: `woocommerce_product_query_tax_query` (priority: 5)

**Parameters**:
- `$tax_query` (array) - Current tax query array
- `$query` (WP_Query) - Current product query object

**Returns**: Modified tax query array

## Adding New Taller Roles

When adding a new taller role in the future:

### Step 1: Create the Taller Role Class

Create a new file following the pattern of existing taller roles:

```php
// themes/ecolitio-theme/inc/class-taller-new-role.php
class Taller_New_Role {
    public function __construct() {
        $config = array(
            'role_slug' => 'taller_new',
            'role_name' => 'Taller New',
            'product_tag' => 'new-taller-tag',
            'capability_prefix' => 'new'
        );
        $this->taller_role = new Taller_Role($config);
    }
}
new Taller_New_Role();
```

### Step 2: Register the Restricted Tag

Add the new tag to the restricted tags list using the filter:

```php
// In your plugin or theme functions.php
add_filter('ecolitio_restricted_product_tags', function($tags) {
    $tags[] = 'new-taller-tag';
    return $tags;
});
```

Or modify the `ecolitio_get_restricted_product_tags()` function directly:

```php
function ecolitio_get_restricted_product_tags() {
    $restricted_tags = array(
        'sabway',
        'taller-del-patinete',
        'new-taller-tag'  // Add new tag here
    );
    return apply_filters('ecolitio_restricted_product_tags', $restricted_tags);
}
```

### Step 3: Include the New Role Class

Add the require statement to `functions.php`:

```php
require_once get_stylesheet_directory() . '/inc/class-taller-new-role.php';
```

## Testing the Filtering

### Test Case 1: Normal User Viewing Related Products

1. Log out or use an incognito window
2. Navigate to a product page
3. Scroll to "Related Products" section
4. Verify that products tagged with `sabway` or `taller-del-patinete` are NOT shown
5. Verify that other products ARE shown

### Test Case 2: Taller Sabway User

1. Log in as a user with `taller_sabway` role
2. Navigate to shop page or product page
3. Verify that ONLY products tagged with `sabway` are shown
4. Verify that products with other tags are NOT shown

### Test Case 3: Taller Del Patinete User

1. Log in as a user with `taller_del_patinete` role
2. Navigate to shop page or product page
3. Verify that ONLY products tagged with `taller-del-patinete` are shown
4. Verify that products with other tags are NOT shown

### Test Case 4: Elementor Related Products Widget

1. Create a page with Elementor
2. Add a "Related Products" widget
3. Test with normal user (should not show restricted products)
4. Test with taller users (should show only their role's products)

## Troubleshooting

### Products Still Showing for Normal Users

**Issue**: Restricted products are still visible to normal users

**Solutions**:
1. Verify the product has the correct tag assigned
2. Check that the tag slug matches exactly (case-sensitive)
3. Clear any caching plugins
4. Verify the filter is being applied: Check `wp_doing_ajax()` is not blocking the filter

### Taller Users Not Seeing Their Products

**Issue**: Taller users cannot see products tagged for their role

**Solutions**:
1. Verify the user has the correct role assigned
2. Check that the product tag matches the role's `product_tag` configuration
3. Verify the role-specific filter in `class-taller-role.php` is working
4. Check user capabilities: `current_user_can('taller_sabway')` should return true

### Filter Not Applied to Elementor Widgets

**Issue**: Elementor widgets are not respecting the product filter

**Solutions**:
1. Verify Elementor is using WooCommerce queries (it should)
2. Check that the `woocommerce_product_query_tax_query` hook is being called
3. Add debugging: `error_log('Filter applied: ' . print_r($tax_query, true));`
4. Ensure the filter priority (5) is not being overridden by other filters

## Performance Considerations

- The filtering adds one additional tax query condition
- This is minimal overhead and follows WooCommerce best practices
- The filter runs only on front-end queries (admin is excluded)
- AJAX queries are included for dynamic product loading

## Security Notes

- The filtering is role-based and enforced server-side
- Normal users cannot bypass this by modifying URLs or AJAX requests
- The filter applies to all product queries, including REST API queries
- Admin users can always see all products for management purposes

## Related Files

- [`themes/ecolitio-theme/functions.php`](../functions.php) - Core filtering functions
- [`themes/ecolitio-theme/inc/class-taller-role.php`](../inc/class-taller-role.php) - Generic taller role implementation
- [`themes/ecolitio-theme/inc/class-taller-sabway-role.php`](../inc/class-taller-sabway-role.php) - Sabway role implementation
- [`themes/ecolitio-theme/inc/class-taller-del-patinete-role.php`](../inc/class-taller-del-patinete-role.php) - Taller Del Patinete role implementation
