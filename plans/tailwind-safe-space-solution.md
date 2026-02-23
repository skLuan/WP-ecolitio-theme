# Tailwind CSS Safe Space Solution for Dynamic Battery Colors

## Problem
The battery form uses dynamic Tailwind classes like `!text-<?= $battery_config['color_class'] ?>` which generates classes like `!text-red-sabway`, `!text-blue-eco`, and `!text-green-eco-taller`. However, Tailwind CSS uses static class name scanning during build time, so these dynamically generated classes are not recognized and therefore not included in the compiled CSS.

Currently, only `!text-red-sabway` works because it's hardcoded elsewhere in the codebase.

## Solution: CSS Custom Properties with Data Attributes

Instead of relying on Tailwind's class scanning, we'll use CSS custom properties (CSS variables) that are set based on a `data-battery-type` attribute on the form element.

### Implementation Steps

1. **Add CSS Custom Properties to tailwind.css**
   - Define color variables for each battery type
   - Create CSS rules that apply these variables based on `data-battery-type` attribute

2. **Update Templates**
   - Replace dynamic Tailwind classes with static classes that use CSS variables
   - Add `data-battery-type` attribute to the form element
   - Use `style="color: var(--battery-color)"` for inline styles where needed

3. **Benefits**
   - All color variations work within Tailwind's safe-listing
   - Cleaner HTML without complex class generation
   - Easier to maintain and extend
   - Works with all CSS states (hover, peer-checked, etc.)

## Files to Modify

1. `themes/ecolitio-theme/styles/tailwind.css` - Add CSS custom properties
2. `themes/ecolitio-theme/templates/sabway-battery-form.php` - Update form and classes
3. `themes/ecolitio-theme/templates/sab-batery-controls.php` - Update button classes

## CSS Variables to Create

```css
/* For sabway battery type */
--battery-color: #d02024;
--battery-color-rgb: 208, 32, 36;

/* For medida battery type */
--battery-color: #0066cc;
--battery-color-rgb: 0, 102, 204;

/* For patinete battery type */
--battery-color: #93E12D;
--battery-color-rgb: 147, 225, 45;
```

## Class Replacements

Instead of:
```html
<span class="!text-<?= esc_attr($battery_config['color_class']); ?>">
```

Use:
```html
<span class="!text-[var(--battery-color)]">
```

Or for better Tailwind integration:
```html
<span style="color: var(--battery-color)">
```
