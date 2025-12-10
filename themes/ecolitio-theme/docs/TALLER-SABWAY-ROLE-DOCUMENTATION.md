# Taller Sabway Role Documentation

## Overview

The "Taller Sabway" user role is a specialized WordPress/WooCommerce user role that provides restricted access to products tagged specifically with "sabway". This role functions as a standard client account type with additional features tailored for the Sabway business workflow.

## Features

### Core Functionality
- **Restricted Product Visibility**: Users with this role can only see products tagged with "sabway"
- **Standard Client Capabilities**: Full WooCommerce customer functionality including checkout, order management, and account features
- **Enhanced UI**: Branded styling specific to the Taller Sabway role

### User Capabilities
- View only "sabway" tagged products
- Standard shopping cart and checkout functionality
- Order management through WooCommerce account
- Product search and filtering within the Sabway catalog

## Installation & Setup

### Files Created
1. **`inc/class-taller-sabway-role.php`** - Main role handler class
2. **`css/taller-sabway.css`** - Role-specific styling
3. **`js/taller-sabway.js`** - Client-side functionality
4. **Updated `functions.php`** - Includes the role handler
5. **Updated `inc/ajax.php`** - Additional AJAX handlers

### Role Creation
The role is automatically created when the theme is activated. The role includes:

```php
'taller_sabway' => array(
    'read' => true,
    'edit_posts' => false,
    'delete_posts' => false,
    'manage_categories' => false,
    'read_private_products' => true,
    'edit_products' => false,
    'edit_product_terms' => false,
    'edit_shop_orders' => false,
    'read_shop_orders' => true,
    'view_woocommerce_reports' => false,
    'edit_shop_order_items' => false,
    'view_sabway_products' => true,
    'access_sabway_zone' => true,
)
```

## Usage

### Assigning the Role

#### Via WordPress Admin (Manual)
1. Go to **Users > All Users**
2. Edit the desired user
3. Change **Role** to "Taller Sabway"
4. Save changes

#### Via Code (Programmatic)
```php
// Assign role to user
assign_taller_sabway_role($user_id);

// Remove role from user
remove_taller_sabway_role($user_id);

// Check if user has role
if (user_has_taller_sabway_role($user_id)) {
    // User has Taller Sabway role
}
```

#### Via Bulk Action
1. Go to **Users > All Users**
2. Select multiple users
3. Choose **Change Role To > Taller Sabway**
4. Apply

### User Experience

#### Product Filtering
- Only products with "sabway" tag are visible
- Filtering is applied automatically on shop pages
- Search functionality respects the Sabway restriction
- Notice displayed to inform users of the restriction



## Technical Implementation

### Product Filtering Mechanism
The role uses the `woocommerce_product_query_tax_query` filter to automatically apply taxonomy restrictions:

```php
public function filter_products_for_sabway_role($tax_query, $query = null) {
    if (!current_user_can('taller_sabway')) {
        return $tax_query;
    }
    
    $tax_query[] = array(
        'taxonomy' => 'product_tag',
        'field' => 'slug',
        'terms' => 'sabway',
        'operator' => 'IN'
    );
    
    return $tax_query;
}
```

### AJAX Functionality
Main AJAX handler:
1. **Product Filtering**: Real-time search within Sabway products

## Customization

### Styling
Edit `css/taller-sabway.css` to customize:
- Color scheme (currently uses Sabway brand colors)
- Layout and spacing
- Responsive behavior

### JavaScript Functionality
Edit `js/taller-sabway.js` to customize:
- Product filtering interactions
- AJAX behavior
- User interface enhancements

### Role Capabilities
Modify `class-taller-sabway-role.php` to add/remove capabilities:
- Add new permissions in the `create_taller_sabway_role()` method
- Update filtering logic in relevant methods

## Testing

### Manual Testing Checklist
- [ ] Role creation on theme activation
- [ ] Product visibility restriction (only "sabway" tagged products)
- [ ] Standard WooCommerce functionality (cart, checkout, orders)
- [ ] Search and filtering within Sabway products
- [ ] Role assignment/removal via admin
- [ ] Responsive design on mobile devices

### Automated Testing
The implementation includes a test file structure (though specific tests may need to be created):
- Test role creation and capabilities
- Test product filtering logic
- Test AJAX handlers

## Troubleshooting

### Common Issues

#### Products Not Filtering
- Ensure products have the "sabway" tag
- Check user role assignment
- Verify WooCommerce is active
- Clear any caching plugins

#### Styling Issues
- Check CSS file loading in browser developer tools
- Ensure CSS file path is correct
- Verify no caching conflicts

### Debug Mode
Enable WordPress debug mode to see additional error information:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Maintenance

### Regular Tasks
- Monitor user role assignments
- Check product tag consistency
- Review and update styling if needed
- Test functionality after theme updates

### Updates
When updating the theme:
1. Backup current role settings
2. Test new implementation thoroughly
3. Update documentation if needed

## Security Considerations

- All AJAX requests use nonces for security
- Role capabilities are strictly controlled
- No sensitive data exposed in client-side JavaScript
- Proper sanitization of all user inputs

## Future Enhancements

Potential improvements for future versions:
- Email notifications specific to Sabway orders
- Advanced product catalog browsing
- Integration with external Sabway systems
- Custom reporting features
- Mobile app compatibility

## Support

For technical support or feature requests:
- Check this documentation first
- Review WordPress/WooCommerce logs
- Contact the development team with specific details

---

**Version**: 1.0.0  
**Last Updated**: November 1, 2025  
**Compatibility**: WordPress 5.0+, WooCommerce 4.0+