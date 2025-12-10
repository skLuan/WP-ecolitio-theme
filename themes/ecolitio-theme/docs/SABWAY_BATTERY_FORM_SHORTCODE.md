# Sabway Battery Form Shortcode Documentation

## Overview

The Sabway Battery Customization Form has been converted into a reusable WordPress shortcode that can be easily integrated into any page or post, including Elementor pages.

## Shortcode Syntax

### Basic Usage

```
[sabway_battery_form]
```

This will automatically detect the current product if you're on a WooCommerce product page.

### With Product ID Parameter

```
[sabway_battery_form product_id="123"]
```

Specify a product ID to display the form for a specific product.

### With Custom CSS Class

```
[sabway_battery_form product_id="123" custom_class="my-custom-class"]
```

Add custom CSS classes to the form wrapper for additional styling.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `product_id` | Integer | 0 | WooCommerce product ID. If not provided, auto-detects current product. |
| `show_title` | String | 'yes' | Show/hide form title (reserved for future use). |
| `custom_class` | String | '' | Additional CSS classes to apply to the form wrapper. |

## How to Use in Elementor

### Method 1: Using Elementor's Shortcode Widget

1. Open your Elementor page editor
2. Add a **Shortcode** widget to your page
3. In the shortcode field, enter:
   ```
   [sabway_battery_form]
   ```
4. Or with a specific product:
   ```
   [sabway_battery_form product_id="123"]
   ```
5. Save and publish

### Method 2: Using Elementor's HTML Widget

1. Add an **HTML** widget to your page
2. Enter the shortcode:
   ```html
   [sabway_battery_form product_id="123"]
   ```
3. Save and publish

## Form Features

The shortcode renders a complete multi-step battery customization form with the following steps:

### Step 0: Introduction
- Welcome message
- Form overview (4 steps, ~2 minutes)
- Visual icons for each step

### Step 1: Electrical Specifications
- Voltage selection (radio buttons)
- Amperage selection (radio buttons)
- Distance range slider (8-184 km)
- Real-time autonomy calculation

### Step 2: Physical Dimensions
- Battery location selection (interior/exterior)
- Height input (cm)
- Width input (cm)
- Length input (cm)
- Scooter model input
- Visual scooter diagrams

### Step 3: Connector Type
- Connector type selection with images
- Visual preview of connector options

### Step 4: Confirmation
- Summary of all selected specifications
- Submit button to finalize order

### Step 5: Success
- Success message
- Order summary
- Option to create new battery

## Requirements

### Product Requirements

The product must have the following WooCommerce attributes:

- **voltios** (Voltage) - with options
- **amperios** (Amperage) - with options
- **ubicacion-de-bateria** (Battery Location) - optional, with options
- **tipo-de-conector** (Connector Type) - optional, with options

### Asset Requirements

The following assets must be present in the theme:

- `themes/ecolitio-theme/assets/PatineteInterior.jpg` - Interior scooter diagram
- `themes/ecolitio-theme/assets/PatineteExterior.jpg` - Exterior scooter diagram
- `themes/ecolitio-theme/assets/conectores/` - Connector images (named after connector types)

### JavaScript Dependencies

The form requires the following JavaScript libraries:

- **Swiper.js** - For carousel/slider functionality
- **Iconify** - For icon rendering
- **Custom formController.js** - For form logic and AJAX submission

These are automatically enqueued by the theme.

## Security Features

### Nonce Protection

The form includes WordPress nonce verification for CSRF protection:

```php
$sabway_form_nonce = wp_create_nonce('ecolitio_sabway_form_nonce');
```

### AJAX Submission

Form submissions are handled via WordPress AJAX with:

- Nonce verification
- User capability checks
- Input sanitization
- Error handling

### Supported User Roles

- **taller_sabway** - Can create orders directly
- **General customers** - Can add to cart

## Styling

The form uses Tailwind CSS utility classes for styling. Key classes:

- `.sabway-form` - Main form wrapper
- `.swiper-sab-batery` - Carousel container
- `.step` - Individual step container
- `.final-check-*` - Confirmation field elements

### Custom Styling

To add custom styles, use the `custom_class` parameter:

```
[sabway_battery_form custom_class="my-custom-wrapper"]
```

Then add your CSS:

```css
.my-custom-wrapper .sabway-form {
    /* Your custom styles */
}
```

## AJAX Endpoints

The form uses the following WordPress AJAX actions:

### For Taller Sabway Users
- **Action:** `sabway_submit_form`
- **Handler:** Creates order directly via WooCommerce REST API
- **Response:** Order ID, order key, redirect URL

### For General Customers
- **Action:** `custom_batery_add_to_cart`
- **Handler:** Adds product to cart with custom specifications
- **Response:** Cart URL for redirect

## Troubleshooting

### Form Not Displaying

**Issue:** Shortcode returns error message

**Solutions:**
1. Verify product ID is correct: `[sabway_battery_form product_id="123"]`
2. Check product has required attributes (voltios, amperios)
3. Ensure product is published and visible
4. Check browser console for JavaScript errors

### Form Not Submitting

**Issue:** Submit button doesn't work

**Solutions:**
1. Verify nonce is present in form
2. Check AJAX URL is correct
3. Verify user has appropriate permissions
4. Check browser console for AJAX errors
5. Ensure WooCommerce is active

### Styling Issues

**Issue:** Form looks broken or misaligned

**Solutions:**
1. Verify Tailwind CSS is loaded
2. Check for CSS conflicts with other plugins
3. Use browser DevTools to inspect elements
4. Add custom CSS to override conflicting styles

### Missing Images

**Issue:** Scooter diagrams or connector images not showing

**Solutions:**
1. Verify image files exist in correct directories
2. Check file permissions
3. Verify image paths in template
4. Check browser console for 404 errors

## Examples

### Example 1: Basic Product Page

```
[sabway_battery_form]
```

### Example 2: Specific Product in Elementor

```
[sabway_battery_form product_id="456"]
```

### Example 3: Custom Styled Form

```
[sabway_battery_form product_id="456" custom_class="battery-form-dark"]
```

CSS:
```css
.battery-form-dark .sabway-form {
    background-color: #1a1a1a;
    border: 2px solid #00ff00;
}
```

### Example 4: Multiple Forms on Same Page

```
<!-- Battery Form 1 -->
[sabway_battery_form product_id="123"]

<!-- Battery Form 2 -->
[sabway_battery_form product_id="456"]
```

## API Reference

### Shortcode Function

**Location:** `themes/ecolitio-theme/functions.php`

**Function:** `ecolitio_sabway_battery_form_shortcode()`

**Parameters:**
- `$atts` (array) - Shortcode attributes

**Returns:**
- (string) - Rendered form HTML or error message

### Template File

**Location:** `themes/ecolitio-theme/templates/sabway-battery-form.php`

**Variables Available:**
- `$product` - WC_Product object
- `$icons` - Array of step icons
- `$getAttributes` - Product attributes
- `$distance` - Default distance value
- `$sabway_form_nonce` - Security nonce

## Performance Considerations

- Form is rendered server-side (no performance impact)
- JavaScript is loaded once per page
- AJAX requests are optimized
- Nonce is generated fresh for each page load

## Browser Compatibility

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Future Enhancements

Potential improvements for future versions:

- [ ] Custom form step configuration
- [ ] Conditional field display
- [ ] Multi-language support
- [ ] Form analytics tracking
- [ ] Custom email notifications
- [ ] PDF order summary generation

## Support

For issues or questions:

1. Check the troubleshooting section above
2. Review browser console for errors
3. Check WordPress debug log
4. Contact theme support

## Version History

### v1.0.0 (Current)
- Initial shortcode implementation
- Full form functionality
- Elementor integration
- Security features
- Documentation

---

**Last Updated:** December 2024
**Compatibility:** WordPress 5.9+, WooCommerce 6.0+, Elementor 3.0+
