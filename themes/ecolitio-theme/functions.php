<?php
// Encolar estilos del tema
add_action( 'wp_enqueue_scripts', 'ecolitio_enqueue_styles' );

function ecolitio_enqueue_styles() {
    // Enqueue child theme style.css
    wp_enqueue_style( 'ecolitio-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version') );
}

?>
