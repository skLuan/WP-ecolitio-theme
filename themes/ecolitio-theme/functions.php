<?php
// Encolar estilos del tema padre
add_action( 'wp_enqueue_scripts', 'ecolitio_enqueue_styles' );

function ecolitio_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

// Add CSS custom properties for all Storefront colors
add_action( 'wp_enqueue_scripts', 'ecolitio_add_css_vars', 20 );

function ecolitio_add_css_vars() {
    // Get all Storefront theme mods
    $theme_mods = array(
        'background_color' => '#' . get_theme_mod( 'background_color', 'ffffff' ),
        'accent_color' => get_theme_mod( 'storefront_accent_color', '#7f54b3' ),
        'hero_heading_color' => get_theme_mod( 'storefront_hero_heading_color', '#000000' ),
        'hero_text_color' => get_theme_mod( 'storefront_hero_text_color', '#000000' ),
        'header_background_color' => get_theme_mod( 'storefront_header_background_color', '#2c2d33' ),
        'header_link_color' => get_theme_mod( 'storefront_header_link_color', '#d5d9db' ),
        'header_text_color' => get_theme_mod( 'storefront_header_text_color', '#9aa0a7' ),
        'footer_background_color' => get_theme_mod( 'storefront_footer_background_color', '#f0f0f0' ),
        'footer_link_color' => get_theme_mod( 'storefront_footer_link_color', '#2c2d33' ),
        'footer_heading_color' => get_theme_mod( 'storefront_footer_heading_color', '#494c50' ),
        'footer_text_color' => get_theme_mod( 'storefront_footer_text_color', '#61656b' ),
        'text_color' => get_theme_mod( 'storefront_text_color', '#43454b' ),
        'heading_color' => get_theme_mod( 'storefront_heading_color', '#484c51' ),
        'button_background_color' => get_theme_mod( 'storefront_button_background_color', '#96588a' ),
        'button_text_color' => get_theme_mod( 'storefront_button_text_color', '#ffffff' ),
        'button_alt_background_color' => get_theme_mod( 'storefront_button_alt_background_color', '#2c2d33' ),
        'button_alt_text_color' => get_theme_mod( 'storefront_button_alt_text_color', '#ffffff' ),
    );

    $css = ":root {\n";
    foreach ( $theme_mods as $key => $value ) {
        $css .= "    --storefront-{$key}: {$value};\n";
    }
    $css .= "}\n";

    wp_add_inline_style( 'parent-style', $css );
}
?>
