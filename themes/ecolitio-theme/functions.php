<?php
// Encolar estilos del tema padre
add_action( 'wp_enqueue_scripts', 'ecolitio_enqueue_styles' );

function ecolitio_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
?>
