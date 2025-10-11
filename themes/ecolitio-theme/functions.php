<?php

$autoload_path = get_stylesheet_directory() . '/vendor/autoload.php';
if (!file_exists($autoload_path)) {
    error_log('Autoload file not found at ' . $autoload_path);
} else {
    require_once $autoload_path;
}

use Idleberg\WordPress\ViteAssets\Assets;


// Encolar estilos del tema
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_styles');

function ecolitio_enqueue_styles()
{
    // Enqueue child theme style.css
    wp_enqueue_style('ecolitio-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
}

if (defined('WP_DEBUG') && WP_DEBUG) {
    // Dev mode: load from Vite dev server
    add_action('wp_enqueue_scripts', function() {
        wp_enqueue_script('vite-main', 'http://localhost:3000/src/main.js', [], null, true);
    });
} else {
    // Prod mode: use manifest
    $baseUrl = get_stylesheet_directory_uri() . '/dist';
    $manifest = get_stylesheet_directory() . "/dist/.vite/manifest.json";
    $entryPoint = "src/main.js";

    $viteAssets = new Assets($manifest, $baseUrl);
    $viteAssets->inject($entryPoint);
}
