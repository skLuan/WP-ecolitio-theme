<?php

/**
 * Product Card Template
 *
 * Displays individual WooCommerce product cards with schema.org markup
 *
 * @package Ecolitio
 * @var WC_Product $product Global product object
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $product;

if (!$product instanceof WC_Product) {
    return;
}

// Get product data with filters for customization
$product_id = apply_filters('ecolitio_product_card_id', $product->get_id(), $product);
$product_title = apply_filters('ecolitio_product_card_title', $product->get_name(), $product);
$product_link = apply_filters('ecolitio_product_card_link', get_permalink($product_id), $product);
$product_image_id = apply_filters('ecolitio_product_card_image_id', get_post_thumbnail_id($product_id), $product);
$product_image = wp_get_attachment_image_src($product_image_id, apply_filters('ecolitio_product_card_image_size', 'full', $product));
$product_image_url = apply_filters('ecolitio_product_card_image_url', $product_image ? $product_image[0] : wc_placeholder_img_src(), $product);
$product_price = apply_filters('ecolitio_product_card_price', $product->get_price_html(), $product);
$is_on_sale = apply_filters('ecolitio_product_card_on_sale', $product->is_on_sale(), $product);

// Action before product card
do_action('ecolitio_before_product_card', $product);
?>
<article class="group relative !bg-black border-blue-eco-dark border rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden max-w-sm mx-auto"
    itemscope
    itemtype="https://schema.org/Product"
    role="article"
    aria-labelledby="product-title-<?= esc_attr($product_id); ?>"
    tabindex="0">

    <?php
    // Action before product image
    do_action('ecolitio_before_product_image', $product);
    ?>

    <!-- Product Image Container -->
    <div class="relative aspect-square bg-gray-50 overflow-hidden border-b border-blue-eco-dark">
        <a href="<?= esc_url($product_link); ?>" class="block">
            <img src="<?= esc_url($product_image_url); ?>"
                alt="<?= esc_attr($product_title); ?>"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                itemprop="image"
                loading="lazy"
                width="300"
                height="300">
        </a>

        <?php
        // Action before badges
        do_action('ecolitio_before_product_badges', $product);
        ?>

        <!-- Sale Badge -->
        <?php if ($is_on_sale) : ?>
            <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-medium"
                aria-label="<?= esc_attr__('Producto en oferta', 'ecolitio-theme'); ?>">
                <?= esc_html__('Oferta', 'ecolitio-theme'); ?>
            </div>
        <?php endif; ?>
        <?php
        // Action after badges
        do_action('ecolitio_after_product_badges', $product);
        ?>
    </div>

    <?php
    // Action after product image
    do_action('ecolitio_after_product_image', $product);
    ?>

    <!-- Product Information -->
    <div class="p-4">
        <?php
        // Action before product title
        do_action('ecolitio_before_product_title', $product);
        ?>

        <h3 id="product-title-<?= esc_attr($product_id); ?>" class="card-product-title" itemprop="name">
            <a href="<?= esc_url($product_link); ?>" class="hover:text-blue-600 transition-colors">
                <?= esc_html($product_title); ?>
            </a>
        </h3>

        <?php
        // Action after product title
        do_action('ecolitio_after_product_title', $product);

        // Action before product price
        do_action('ecolitio_before_product_price', $product);
        ?>

        <!-- Add to Cart Button with Price -->
        <?php
        if (apply_filters('ecolitio_show_add_to_cart', $product->is_purchasable() && $product->is_in_stock(), $product)) :
            // Get plain text price for button (strip HTML tags)
            $plain_price = wp_strip_all_tags($product_price);
            $button_classes = apply_filters('ecolitio_add_to_cart_button_classes', 'inline-flex items-center justify-center w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed', $product);
        ?>
            <div class="flex flex-row justify-end items-center mt-auto" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <?php if ($product->is_type('variable')) : ?>
                    <?php
                    // Get product attributes
                    $attributes = $product->get_attributes();
                    if (!empty($attributes)) :
                        // Get the first attribute
                        $first_attribute_key = array_key_first($attributes);
                        $first_attribute = $attributes[$first_attribute_key];
                        $attribute_options = $first_attribute->get_options();
                        if (!empty($attribute_options)) :
                            $attribute_name = $first_attribute->get_name();
                            $attribute_label = wc_attribute_label($attribute_name);
                    ?>
                        <div class="attribute-container flex flex-row flex-wrap gap-2">
                            <?php
                            $is_first = true;
                            foreach ($attribute_options as $option) :
                                $term = get_term($option);
                                $option_name = $term ? $term->name : $option;
                                $selected_class = $is_first ? ' selected' : '';
                            ?>
                                <div class="pill<?= esc_attr($selected_class); ?>" data-attribute="<?= esc_attr($attribute_name); ?>" data-value="<?= esc_attr($option); ?>">
                                    <?= esc_html($option_name); ?>
                                </div>
                            <?php
                                $is_first = false;
                            endforeach;
                            ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endif; ?>
                <meta itemprop="price" content="<?= esc_attr($product->get_price()); ?>" />
                <meta itemprop="priceCurrency" content="<?= esc_attr(get_woocommerce_currency()); ?>" />
                <button type="button"
                    class="<?= esc_attr($button_classes); ?> !w-fit !py-1 !px-4 !bg-transparent !flex flex-row"
                    data-product_id="<?= esc_attr($product_id); ?>"
                    data-product_sku="<?= esc_attr($product->get_sku()); ?>"
                    data-quantity="1"
                    aria-label="<?= esc_attr(sprintf(__('Añadir %s al carrito', 'ecolitio-theme'), $product_title)); ?>">
                    <div class="" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                        <span class="text-green-eco text-xl font-bold" itemprop="price" content="<?= esc_attr($product->get_price()); ?>">
                            <?= $product_price; ?>
                        </span>
                        <meta itemprop="priceCurrency" content="<?= esc_attr(get_woocommerce_currency()); ?>" />
                    </div>
                    <iconify-icon class="text-green-eco" icon="mynaui:cart-plus-solid" width="32" height="32"></iconify-icon>
                    <span class="loading-text hidden" aria-hidden="true"><?= esc_html__('Añadiendo...', 'ecolitio-theme'); ?></span>
                </button>
            </div>
        <?php endif; ?>

        <?php
        // Action after product price
        do_action('ecolitio_after_product_price', $product);
        ?>
    </div>

    <?php
    // Action after product card
    do_action('ecolitio_after_product_card', $product);
    ?>
</article>
<?php
// Action after product card template
do_action('ecolitio_after_product_card_template', $product);
?>