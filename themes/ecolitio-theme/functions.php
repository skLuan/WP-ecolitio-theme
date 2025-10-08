<?php
// Encolar estilos del tema
add_action( 'wp_enqueue_scripts', 'ecolitio_enqueue_styles' );

function ecolitio_enqueue_styles() {
    // Enqueue child theme style.css
    wp_enqueue_style( 'ecolitio-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version') );
}

// Add CSS custom properties for Elementor global styles
add_action( 'wp_enqueue_scripts', 'ecolitio_add_elementor_css_vars', 20 );

function ecolitio_add_elementor_css_vars() {
    if ( ! class_exists( '\Elementor\Plugin' ) ) {
        return;
    }

    $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
    if ( ! $kit ) {
        return;
    }

    $css = ":root {\n";

    // Get custom colors
    $custom_colors = $kit->get_settings_for_display( 'custom_colors' );
    if ( ! empty( $custom_colors ) ) {
        foreach ( $custom_colors as $color ) {
            if ( isset( $color['color'] ) && isset( $color['_id'] ) ) {
                $css .= "    --e-global-color-{$color['_id']}: {$color['color']};\n";
            }
        }
    }

    // Get system colors (primary, secondary, etc.)
    $system_colors = $kit->get_settings_for_display( 'system_colors' );
    if ( ! empty( $system_colors ) ) {
        foreach ( $system_colors as $color ) {
            if ( isset( $color['color'] ) && isset( $color['_id'] ) ) {
                $css .= "    --e-global-color-{$color['_id']}: {$color['color']};\n";
            }
        }
    }

    // Get custom typography
    $custom_typography = $kit->get_settings_for_display( 'custom_typography' );
    if ( ! empty( $custom_typography ) ) {
        foreach ( $custom_typography as $typo ) {
            if ( isset( $typo['_id'] ) && isset( $typo['typography'] ) ) {
                $typography = $typo['typography'];
                if ( isset( $typography['font_family'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-family: {$typography['font_family']};\n";
                }
                if ( isset( $typography['font_size']['size'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-size: {$typography['font_size']['size']}{$typography['font_size']['unit']};\n";
                }
                if ( isset( $typography['font_weight'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-weight: {$typography['font_weight']};\n";
                }
                if ( isset( $typography['text_transform'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-text-transform: {$typography['text_transform']};\n";
                }
                if ( isset( $typography['font_style'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-style: {$typography['font_style']};\n";
                }
                if ( isset( $typography['text_decoration'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-text-decoration: {$typography['text_decoration']};\n";
                }
                if ( isset( $typography['line_height']['size'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-line-height: {$typography['line_height']['size']}{$typography['line_height']['unit']};\n";
                }
                if ( isset( $typography['letter_spacing']['size'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-letter-spacing: {$typography['letter_spacing']['size']}{$typography['letter_spacing']['unit']};\n";
                }
            }
        }
    }

    // Get system typography
    $system_typography = $kit->get_settings_for_display( 'system_typography' );
    if ( ! empty( $system_typography ) ) {
        foreach ( $system_typography as $typo ) {
            if ( isset( $typo['_id'] ) && isset( $typo['typography'] ) ) {
                $typography = $typo['typography'];
                if ( isset( $typography['font_family'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-family: {$typography['font_family']};\n";
                }
                if ( isset( $typography['font_size']['size'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-size: {$typography['font_size']['size']}{$typography['font_size']['unit']};\n";
                }
                if ( isset( $typography['font_weight'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-weight: {$typography['font_weight']};\n";
                }
                if ( isset( $typography['text_transform'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-text-transform: {$typography['text_transform']};\n";
                }
                if ( isset( $typography['font_style'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-font-style: {$typography['font_style']};\n";
                }
                if ( isset( $typography['text_decoration'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-text-decoration: {$typography['text_decoration']};\n";
                }
                if ( isset( $typography['line_height']['size'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-line-height: {$typography['line_height']['size']}{$typography['line_height']['unit']};\n";
                }
                if ( isset( $typography['letter_spacing']['size'] ) ) {
                    $css .= "    --e-global-typography-{$typo['_id']}-letter-spacing: {$typography['letter_spacing']['size']}{$typography['letter_spacing']['unit']};\n";
                }
            }
        }
    }

    $css .= "}\n";

    wp_add_inline_style( 'ecolitio-style', $css );
}
?>
