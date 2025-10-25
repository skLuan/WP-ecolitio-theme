<?php
/**
 * Ecolitio Theme Custom Post Types and Taxonomies
 *
 * This file contains all custom post types and taxonomies for the Ecolitio theme.
 *
 * @package Ecolitio
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// =============================================================================
// CUSTOM POST TYPES & TAXONOMIES
// =============================================================================

/**
 * Register FAQ custom post type
 */
function ecolitio_register_faq_post_type() {
    $labels = array(
        'name'                  => _x('Preguntas Frecuentes', 'Post type general name', 'ecolitio-theme'),
        'singular_name'         => _x('Pregunta Frecuente', 'Post type singular name', 'ecolitio-theme'),
        'menu_name'             => _x('Preguntas Frecuentes', 'Admin Menu text', 'ecolitio-theme'),
        'name_admin_bar'        => _x('Pregunta Frecuente', 'Add New on Toolbar', 'ecolitio-theme'),
        'add_new'               => __('Añadir Nueva', 'ecolitio-theme'),
        'add_new_item'          => __('Añadir Nueva Pregunta Frecuente', 'ecolitio-theme'),
        'new_item'              => __('Nueva Pregunta Frecuente', 'ecolitio-theme'),
        'edit_item'             => __('Editar Pregunta Frecuente', 'ecolitio-theme'),
        'view_item'             => __('Ver Pregunta Frecuente', 'ecolitio-theme'),
        'all_items'             => __('Todas las Preguntas Frecuentes', 'ecolitio-theme'),
        'search_items'          => __('Buscar Preguntas Frecuentes', 'ecolitio-theme'),
        'parent_item_colon'     => __('Pregunta Frecuente Padre:', 'ecolitio-theme'),
        'not_found'             => __('No se encontraron preguntas frecuentes.', 'ecolitio-theme'),
        'not_found_in_trash'    => __('No se encontraron preguntas frecuentes en la papelera.', 'ecolitio-theme'),
        'featured_image'        => _x('Imagen Destacada', 'Overrides the "Featured Image" phrase', 'ecolitio-theme'),
        'set_featured_image'    => _x('Establecer imagen destacada', 'Overrides the "Set featured image" phrase', 'ecolitio-theme'),
        'remove_featured_image' => _x('Remover imagen destacada', 'Overrides the "Remove featured image" phrase', 'ecolitio-theme'),
        'use_featured_image'    => _x('Usar como imagen destacada', 'Overrides the "Use as featured image" phrase', 'ecolitio-theme'),
        'archives'              => _x('Archivo de Preguntas Frecuentes', 'The post type archive label used in nav menus', 'ecolitio-theme'),
        'insert_into_item'      => _x('Insertar en pregunta frecuente', 'Overrides the "Insert into post"/"Insert into page" phrase', 'ecolitio-theme'),
        'uploaded_to_this_item' => _x('Subido a esta pregunta frecuente', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase', 'ecolitio-theme'),
        'filter_items_list'     => _x('Filtrar lista de preguntas frecuentes', 'Screen reader text for the filter links heading on the post type listing screen', 'ecolitio-theme'),
        'items_list_navigation' => _x('Navegación de lista de preguntas frecuentes', 'Screen reader text for the pagination heading on the post type listing screen', 'ecolitio-theme'),
        'items_list'            => _x('Lista de preguntas frecuentes', 'Screen reader text for the items list heading on the post type listing screen', 'ecolitio-theme'),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'faq'),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'menu_icon'          => 'dashicons-editor-help',
        'supports'           => array('title', 'editor', 'custom-fields'),
        'show_in_rest'       => true,
    );

    register_post_type('faq', $args);
}
add_action('init', 'ecolitio_register_faq_post_type');

/**
 * Register FAQ category taxonomy
 */
function ecolitio_register_faq_taxonomies() {
    $labels = array(
        'name'              => _x('Categorías FAQ', 'taxonomy general name', 'ecolitio-theme'),
        'singular_name'     => _x('Categoría FAQ', 'taxonomy singular name', 'ecolitio-theme'),
        'search_items'      => __('Buscar Categorías FAQ', 'ecolitio-theme'),
        'all_items'         => __('Todas las Categorías FAQ', 'ecolitio-theme'),
        'parent_item'       => __('Categoría FAQ Padre', 'ecolitio-theme'),
        'parent_item_colon' => __('Categoría FAQ Padre:', 'ecolitio-theme'),
        'edit_item'         => __('Editar Categoría FAQ', 'ecolitio-theme'),
        'update_item'       => __('Actualizar Categoría FAQ', 'ecolitio-theme'),
        'add_new_item'      => __('Añadir Nueva Categoría FAQ', 'ecolitio-theme'),
        'new_item_name'     => __('Nombre de Nueva Categoría FAQ', 'ecolitio-theme'),
        'menu_name'         => __('Categorías FAQ', 'ecolitio-theme'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq_category'),
        'show_in_rest'      => true,
    );

    register_taxonomy('faq_category', array('faq'), $args);

    // Register tag taxonomy
    $tag_labels = array(
        'name'              => _x('Etiquetas FAQ', 'taxonomy general name', 'ecolitio-theme'),
        'singular_name'     => _x('Etiqueta FAQ', 'taxonomy singular name', 'ecolitio-theme'),
        'search_items'      => __('Buscar Etiquetas FAQ', 'ecolitio-theme'),
        'all_items'         => __('Todas las Etiquetas FAQ', 'ecolitio-theme'),
        'edit_item'         => __('Editar Etiqueta FAQ', 'ecolitio-theme'),
        'update_item'       => __('Actualizar Etiqueta FAQ', 'ecolitio-theme'),
        'add_new_item'      => __('Añadir Nueva Etiqueta FAQ', 'ecolitio-theme'),
        'new_item_name'     => __('Nombre de Nueva Etiqueta FAQ', 'ecolitio-theme'),
        'menu_name'         => __('Etiquetas FAQ', 'ecolitio-theme'),
    );

    $tag_args = array(
        'hierarchical'      => false,
        'labels'            => $tag_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'faq_tag'),
        'show_in_rest'      => true,
    );

    register_taxonomy('faq_tag', array('faq'), $tag_args);
}
add_action('init', 'ecolitio_register_faq_taxonomies');