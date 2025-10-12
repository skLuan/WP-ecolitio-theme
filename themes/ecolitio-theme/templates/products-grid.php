<?php
/**
 * Products Grid Template
 *
 * Displays a responsive grid of WooCommerce products
 *
 * @package Ecolitio
 * @var WP_Query $products_query Products query object
 * @var bool $show_pagination Whether to show pagination
 * @var int $current_page Current page number
 * @var int $total_pages Total number of pages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $product;

// Apply filters for customization
$grid_classes = apply_filters('ecolitio_products_grid_classes', 'grid grid-cols-1 md:grid-cols-3 gap-6');
$show_pagination = apply_filters('ecolitio_products_grid_show_pagination', $show_pagination ?? true);
$current_page = apply_filters('ecolitio_products_grid_current_page', $current_page ?? 1);
$total_pages = apply_filters('ecolitio_products_grid_total_pages', $total_pages ?? 1);

// Action before products grid
do_action('ecolitio_before_products_grid', $products_query, $current_page, $total_pages);
?>
<div id="products-grid" class="<?= esc_attr($grid_classes); ?>">
    <?php
    // Check if we have products
    if (isset($products_query) && $products_query instanceof WP_Query && $products_query->have_posts()) :
        // Action before products loop
        do_action('ecolitio_before_products_loop', $products_query);

        // Products loop
        while ($products_query->have_posts()) :
            $products_query->the_post();

            // Action before individual product
            do_action('ecolitio_before_product_in_loop', $product);

            // Include product card template
            get_template_part('templates/product-card');

            // Action after individual product
            do_action('ecolitio_after_product_in_loop', $product);

        endwhile;

        // Action after products loop
        do_action('ecolitio_after_products_loop', $products_query);

        wp_reset_postdata();

    else :
        // No products found
        ?>
        <div class="col-span-full text-center py-8">
            <p class="text-gray-500">
                <?= esc_html__('No hay productos disponibles en este momento.', 'ecolitio-theme'); ?>
            </p>
        </div>
        <?php
    endif;
    ?>
</div>

<?php
// Pagination
if ($show_pagination && $total_pages > 1) :
    // Include pagination template
    get_template_part('templates/pagination');
endif;

// Action after products grid
do_action('ecolitio_after_products_grid', $products_query, $current_page, $total_pages);
?>