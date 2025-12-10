# Using Sabway Battery Form Shortcode in Elementor

## Quick Start

The Sabway Battery Form is now available as a WordPress shortcode that works seamlessly with Elementor.

## Step-by-Step Guide

### Step 1: Open Elementor Editor

1. Go to your WordPress dashboard
2. Navigate to **Pages** or **Posts**
3. Create a new page or edit an existing one
4. Click **Edit with Elementor**

### Step 2: Add Shortcode Widget

1. In the Elementor editor, click the **+** icon to add a new element
2. Search for **Shortcode** widget
3. Drag it to your desired location on the page

### Step 3: Enter the Shortcode

In the Shortcode widget, enter one of the following:

#### Option A: Auto-detect Product (if on product page)
```
[sabway_battery_form]
```

#### Option B: Specify Product ID
```
[sabway_battery_form product_id="123"]
```

Replace `123` with your actual product ID.

#### Option C: With Custom Styling
```
[sabway_battery_form product_id="123" custom_class="my-battery-form"]
```

### Step 4: Save and Publish

1. Click **Update** to save your page
2. Click **Publish** to make it live
3. View the page to see the form in action

## Finding Your Product ID

### Method 1: From Products List
1. Go to **Products** in WordPress admin
2. Hover over the product name
3. Look at the URL in your browser - the ID is in the URL: `post=123`

### Method 2: From Product Edit Page
1. Edit the product
2. Look at the URL bar - the ID appears as `post=123`

### Method 3: From WooCommerce
1. Go to **Products**
2. Find your product
3. The ID is shown in the product list column

## Common Use Cases

### Use Case 1: Product Page with Form

Add the shortcode directly to your product page template:

```
[sabway_battery_form]
```

The form will automatically use the current product.

### Use Case 2: Dedicated Battery Customization Page

Create a new page and add:

```
[sabway_battery_form product_id="456"]
```

### Use Case 3: Multiple Products on One Page

Add multiple shortcodes with different product IDs:

```
<h2>Battery Option 1</h2>
[sabway_battery_form product_id="123"]

<h2>Battery Option 2</h2>
[sabway_battery_form product_id="456"]
```

### Use Case 4: Custom Styled Form

Add custom CSS class:

```
[sabway_battery_form product_id="123" custom_class="premium-battery-form"]
```

Then add CSS in your theme:

```css
.premium-battery-form .sabway-form {
    background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
    border: 2px solid #00ff00;
    box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
}
```

## Styling the Form

### Using Elementor's Advanced Tab

1. Select the Shortcode widget
2. Go to the **Advanced** tab
3. Add custom CSS classes in the **CSS Classes** field
4. Style using Elementor's styling options

### Using Custom CSS

Add to your theme's custom CSS:

```css
/* Style the form container */
.sabway-form {
    background-color: #000;
    border-radius: 12px;
    padding: 24px;
}

/* Style form buttons */
.sabway-form button {
    font-weight: bold;
    transition: all 0.3s ease;
}

/* Style form inputs */
.sabway-form input[type="radio"],
.sabway-form input[type="number"],
.sabway-form input[type="text"] {
    border-radius: 8px;
}
```

## Responsive Design

The form is fully responsive and works on:

- ✅ Desktop (1920px and above)
- ✅ Tablet (768px - 1024px)
- ✅ Mobile (320px - 767px)

No additional configuration needed!

## Troubleshooting

### Issue: Shortcode Not Rendering

**Solution:**
1. Verify the shortcode syntax is correct
2. Check that the product ID exists
3. Ensure the product is published
4. Clear your browser cache
5. Check WordPress debug log

### Issue: Form Looks Broken

**Solution:**
1. Verify Tailwind CSS is loaded
2. Check for CSS conflicts
3. Inspect element in browser DevTools
4. Add custom CSS to fix styling

### Issue: Form Not Submitting

**Solution:**
1. Check browser console for JavaScript errors
2. Verify AJAX is working
3. Check user permissions
4. Ensure nonce is present
5. Check WordPress error log

### Issue: Images Not Showing

**Solution:**
1. Verify image files exist in theme assets
2. Check file paths are correct
3. Verify file permissions
4. Check browser console for 404 errors

## Advanced Configuration

### Custom Product Attributes

The form requires these product attributes:

- **voltios** (Voltage)
- **amperios** (Amperage)
- **ubicacion-de-bateria** (Battery Location) - optional
- **tipo-de-conector** (Connector Type) - optional

To add attributes to a product:

1. Edit the product
2. Go to **Attributes** tab
3. Add the required attributes with options
4. Save the product

### Custom Form Behavior

To modify form behavior, edit:

```
themes/ecolitio-theme/src/formController.js
```

### Custom Styling

To customize the form appearance, edit:

```
themes/ecolitio-theme/templates/sabway-battery-form.php
```

## Performance Tips

1. **Use Lazy Loading:** Enable lazy loading in Elementor for images
2. **Minimize CSS:** Use Elementor's CSS minification
3. **Cache:** Enable WordPress caching plugins
4. **CDN:** Use a CDN for static assets
5. **Optimize Images:** Compress scooter diagram images

## Security Best Practices

1. ✅ Nonce verification is automatic
2. ✅ User capabilities are checked
3. ✅ Input is sanitized
4. ✅ CSRF protection is enabled
5. ✅ AJAX requests are validated

No additional security configuration needed!

## Browser Support

| Browser | Version | Support |
|---------|---------|---------|
| Chrome | 90+ | ✅ Full |
| Firefox | 88+ | ✅ Full |
| Safari | 14+ | ✅ Full |
| Edge | 90+ | ✅ Full |
| Mobile Chrome | Latest | ✅ Full |
| Mobile Safari | Latest | ✅ Full |

## Examples

### Example 1: Simple Product Page

```
[sabway_battery_form]
```

### Example 2: Specific Product

```
[sabway_battery_form product_id="789"]
```

### Example 3: With Custom Class

```
[sabway_battery_form product_id="789" custom_class="dark-theme"]
```

### Example 4: Multiple Forms

```
[sabway_battery_form product_id="123"]
[sabway_battery_form product_id="456"]
[sabway_battery_form product_id="789"]
```

## Getting Help

For detailed documentation, see:

- [`SABWAY_BATTERY_FORM_SHORTCODE.md`](./SABWAY_BATTERY_FORM_SHORTCODE.md) - Complete API reference
- Browser console - JavaScript errors
- WordPress debug log - PHP errors
- Elementor documentation - Widget-specific help

## Next Steps

1. ✅ Add the shortcode to your page
2. ✅ Test the form functionality
3. ✅ Customize styling as needed
4. ✅ Test on mobile devices
5. ✅ Publish and monitor

---

**Last Updated:** December 2024
**Compatibility:** Elementor 3.0+, WordPress 5.9+, WooCommerce 6.0+
