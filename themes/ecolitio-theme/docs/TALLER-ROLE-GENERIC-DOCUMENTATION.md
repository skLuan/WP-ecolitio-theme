# Generic Taller Role System Documentation

## Overview

The generic `Taller_Role` class provides a reusable, configurable framework for managing taller (workshop) user roles in the Ecolitio theme. This system allows you to easily create and manage multiple taller roles with different product access restrictions.

## Architecture

### Core Components

1. **`class-taller-role.php`** - Generic base class that handles all taller role functionality
2. **`class-taller-sabway-role.php`** - Sabway-specific implementation
3. **`class-taller-del-patinete-role.php`** - Taller Del Patinete-specific implementation

### Design Pattern

The system uses a **configuration-based instantiation pattern**:
- The generic `Taller_Role` class accepts a configuration array
- Specific taller implementations wrap the generic class with their own configuration
- Each taller role is completely independent with its own capabilities and product filtering

## Configuration

### Configuration Array Structure

```php
$config = array(
    'role_slug'           => 'taller_sabway',        // WordPress role slug (lowercase, underscores)
    'role_name'           => 'Taller Sabway',        // Display name for the role
    'product_tag'         => 'sabway',               // Product tag to filter by
    'capability_prefix'   => 'sabway'                // Prefix for custom capabilities
);
```

### Configuration Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `role_slug` | string | WordPress role identifier (lowercase, underscores) | `taller_sabway` |
| `role_name` | string | Human-readable role name (translatable) | `Taller Sabway` |
| `product_tag` | string | Product tag slug for filtering | `sabway` |
| `capability_prefix` | string | Prefix for custom capabilities | `sabway` |

## Usage

### Creating a New Taller Role

To add a new taller role (e.g., "Taller Bicicletas"), create a new file:

**File: `themes/ecolitio-theme/inc/class-taller-bicicletas-role.php`**

```php
<?php

require_once get_stylesheet_directory() . '/inc/class-taller-role.php';

class Taller_Bicicletas_Role
{
    private $taller_role;

    public function __construct()
    {
        $bicicletas_config = array(
            'role_slug'         => 'taller_bicicletas',
            'role_name'         => 'Taller Bicicletas',
            'product_tag'       => 'taller-bicicletas',
            'capability_prefix' => 'bicicletas'
        );

        $this->taller_role = new Taller_Role($bicicletas_config);
    }

    public function get_taller_role()
    {
        return $this->taller_role;
    }
}

function assign_taller_bicicletas_role($user_id)
{
    return assign_taller_role($user_id, 'taller_bicicletas');
}

function remove_taller_bicicletas_role($user_id)
{
    return remove_taller_role($user_id, 'taller_bicicletas');
}

function user_has_taller_bicicletas_role($user_id = null)
{
    return user_has_taller_role('taller_bicicletas', $user_id);
}

function get_taller_bicicletas_users()
{
    return get_taller_users('taller_bicicletas');
}

new Taller_Bicicletas_Role();
```

Then include it in `functions.php`:

```php
require_once get_stylesheet_directory() . '/inc/class-taller-bicicletas-role.php';
```

## Features

### 1. Role Creation

The system automatically creates WordPress roles with appropriate capabilities:

- **Standard WordPress Capabilities**
  - `read` - Can read content
  - `edit_posts` - Disabled
  - `delete_posts` - Disabled
  - `manage_categories` - Disabled

- **WooCommerce Capabilities**
  - `read_shop_orders` - Can view orders
  - `edit_shop_orders` - Can edit orders (required for REST API)
  - `create_shop_orders` - Can create orders
  - `edit_shop_order_items` - Can edit order items
  - `read_products` - Can view products
  - `read_private_products` - Can view private products
  - `publish_shop_orders` - Can publish orders

- **Custom Capabilities** (dynamically generated)
  - `view_{capability_prefix}_products` - Can view taller-specific products
  - `access_{capability_prefix}_zone` - Can access taller-specific zone

### 2. Product Filtering

Products are automatically filtered based on the configured product tag:

- Only users with the specific taller role see products tagged with that taller's tag
- Filtering applies to:
  - Shop pages
  - Product archives
  - AJAX requests
- Admin pages are excluded from filtering

### 3. Consumer Key Auto-Registration

The system automatically generates and stores WooCommerce REST API consumer keys for taller users:

- Keys are generated on first role assignment
- Stored in user meta: `woocommerce_api_consumer_key` and `woocommerce_api_consumer_secret`
- Also stored in WooCommerce API keys table if available
- Enables REST API access for taller users

### 4. Asset Enqueuing

The system automatically enqueues CSS and JavaScript files if they exist:

- **CSS**: `css/taller-{role-slug}.css`
- **JavaScript**: `js/taller-{role-slug}.js`

Example for Sabway:
- `css/taller-sabway.css`
- `js/taller-sabway.js`

## Helper Functions

### Generic Functions (in `class-taller-role.php`)

```php
// Assign a taller role to a user
assign_taller_role($user_id, $role_slug);

// Remove a taller role from a user
remove_taller_role($user_id, $role_slug);

// Check if user has a taller role
user_has_taller_role($role_slug, $user_id = null);

// Get all users with a taller role
get_taller_users($role_slug);
```

### Sabway-Specific Functions

```php
// Assign Sabway role
assign_taller_sabway_role($user_id);

// Remove Sabway role
remove_taller_sabway_role($user_id);

// Check if user has Sabway role
user_has_taller_sabway_role($user_id = null);

// Get all Sabway users
get_taller_sabway_users();
```

### Taller Del Patinete-Specific Functions

```php
// Assign Taller Del Patinete role
assign_taller_del_patinete_role($user_id);

// Remove Taller Del Patinete role
remove_taller_del_patinete_role($user_id);

// Check if user has Taller Del Patinete role
user_has_taller_del_patinete_role($user_id = null);

// Get all Taller Del Patinete users
get_taller_del_patinete_users();
```

## Product Tag Setup

For each taller role, you need to create a corresponding product tag in WooCommerce:

1. Go to **Products → Tags** in WordPress admin
2. Create a new tag with the slug matching your `product_tag` configuration
3. Tag products that should be visible to that taller role

### Existing Tags

- **Sabway**: `sabway`
- **Taller Del Patinete**: `taller-del-patinete`

## Capabilities Reference

### Sabway Role Capabilities

- `view_sabway_products`
- `access_sabway_zone`

### Taller Del Patinete Role Capabilities

- `view_patinete_products`
- `access_patinete_zone`

### Custom Capabilities for New Taller

For a new taller with `capability_prefix` = `bicicletas`:

- `view_bicicletas_products`
- `access_bicicletas_zone`

## Hooks and Filters

The system uses standard WordPress hooks:

- `init` - Role creation and consumer key registration
- `wp_enqueue_scripts` - Asset enqueuing
- `woocommerce_product_query_tax_query` - Product filtering

## Troubleshooting

### Products Not Filtering

1. Verify the user has the correct taller role
2. Check that products are tagged with the correct tag
3. Ensure the tag slug matches the `product_tag` configuration
4. Clear any caching plugins

### Consumer Keys Not Generated

1. Verify WooCommerce is active
2. Check error logs for messages starting with "Taller_Role"
3. Ensure the user has the taller role assigned
4. Check that the WooCommerce API keys table exists

### Assets Not Loading

1. Verify CSS/JS files exist in the correct directories
2. Check file naming: `taller-{role-slug}.css` and `taller-{role-slug}.js`
3. Verify the user has the correct taller role
4. Check browser console for 404 errors

## Best Practices

1. **Naming Convention**: Use lowercase with hyphens for role slugs and underscores for PHP identifiers
2. **Product Tags**: Create descriptive, unique tags for each taller
3. **Capabilities**: Use the capability prefix consistently across your implementation
4. **Documentation**: Document any custom functionality added to specific taller implementations
5. **Testing**: Test product filtering and role assignment after creating new taller roles

## Future Enhancements

Potential improvements to the system:

- Dashboard endpoints for each taller (currently removed as Elementor handles this)
- Role-specific pricing or discounts
- Taller-specific order workflows
- Advanced reporting per taller
- Role hierarchy and inheritance

## File Structure

```
themes/ecolitio-theme/inc/
├── class-taller-role.php                    # Generic base class
├── class-taller-sabway-role.php             # Sabway implementation
└── class-taller-del-patinete-role.php       # Taller Del Patinete implementation

themes/ecolitio-theme/css/
├── taller-sabway.css                        # Sabway styles
└── taller-del-patinete.css                  # Taller Del Patinete styles (optional)

themes/ecolitio-theme/js/
├── taller-sabway.js                         # Sabway scripts
└── taller-del-patinete.js                   # Taller Del Patinete scripts (optional)
```

## Version History

- **1.0.0** - Initial generic taller role system with Sabway and Taller Del Patinete implementations
