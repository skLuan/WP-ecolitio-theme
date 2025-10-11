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

// 1.1. Vite hot reloading for development and production build enqueue
function enqueue_vite_scripts() {
    $manifestPath = get_stylesheet_directory() . '/dist.vite/manifest.json';

    // Check if the manifest file exists and is readable before using it
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        
        // Check if the file is in the manifest before enqueuing
        if (isset($manifest['src/main.js'])) {
            wp_enqueue_script('ecolitio-js', get_stylesheet_directory_uri() . '/dist/' . $manifest['src/main.js']['file'], array(), null, true);
            // Enqueue the CSS files (handle multiple)
            if (isset($manifest['src/main.js']['css']) && is_array($manifest['src/main.js']['css'])) {
                foreach ($manifest['src/main.js']['css'] as $index => $css_file) {
                    wp_enqueue_style('ecolitio-css-' . $index, get_stylesheet_directory_uri() . '/dist/' . $css_file);
                }
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'enqueue_vite_scripts');
// -- Todo, implement later
// $entryPoint = "src/main.js";
// $viteAssets = new Assets($manifest, $baseUrl);
// $viteAssets->inject($entryPoint);

// Enqueue products AJAX script
add_action('wp_enqueue_scripts', 'ecolitio_enqueue_products_scripts');
function ecolitio_enqueue_products_scripts() {
    wp_enqueue_script('ecolitio-products-ajax', get_stylesheet_directory_uri() . '/src/products-ajax.js', array('jquery'), '1.0.0', true);
    wp_localize_script('ecolitio-products-ajax', 'ecolitio_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ecolitio_products_nonce')
    ));
}

// AJAX handler for loading products page
add_action('wp_ajax_load_products_page', 'ecolitio_load_products_page');
add_action('wp_ajax_nopriv_load_products_page', 'ecolitio_load_products_page');
function ecolitio_load_products_page() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'ecolitio_products_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce is not active');
        return;
    }

    $page = intval($_POST['page']);
    $per_page = 9;
    $offset = ($page - 1) * $per_page;

    // Query products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'post_status' => 'publish'
    );

    $products_query = new WP_Query($args);
    $total_products = wp_count_posts('product')->publish;
    $total_pages = ceil($total_products / $per_page);

    // Generate product HTML
    $html = '';
    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            $html .= ecolitio_generate_product_card_html();
        }
    } else {
        $html = '<div class="col-span-full text-center py-8"><p class="text-gray-500">No hay más productos disponibles.</p></div>';
    }
    wp_reset_postdata();

    // Generate pagination HTML
    $pagination_html = ecolitio_generate_pagination_html($page, $total_pages);

    wp_send_json_success(array(
        'html' => $html,
        'pagination' => $pagination_html,
        'total_pages' => $total_pages
    ));
}

// Generate product card HTML
function ecolitio_generate_product_card_html() {
    global $product;
    $product = wc_get_product(get_the_ID());

    if (!$product) return '';

    $product_id = $product->get_id();
    $product_title = $product->get_name();
    $product_link = get_permalink($product_id);
    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
    $product_image_url = $product_image ? $product_image[0] : wc_placeholder_img_src();
    $product_price = $product->get_price_html();
    $is_on_sale = $product->is_on_sale();

    ob_start();
    ?>
    <article class="group relative bg-white rounded-2xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden max-w-sm mx-auto"
             itemscope
             itemtype="https://schema.org/Product"
             role="article"
             aria-labelledby="product-title-<?php echo $product_id; ?>"
             tabindex="0">

      <!-- Product Image Container -->
      <div class="relative aspect-square bg-gray-50 overflow-hidden">
        <a href="<?php echo esc_url($product_link); ?>" class="block">
            <img src="<?php echo esc_url($product_image_url); ?>"
                 alt="<?php echo esc_attr($product_title); ?>"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                 itemprop="image"
                 loading="lazy"
                 width="300"
                 height="300">
        </a>

        <!-- Sale Badge (if applicable) -->
        <?php if ($is_on_sale) : ?>
        <div class="absolute top-3 left-3 bg-red-500 text-white px-2 py-1 rounded-md text-xs font-medium"
             aria-label="Producto en oferta">
          Oferta
        </div>
        <?php endif; ?>

        <!-- Add to Cart Button -->
        <div class="absolute top-3 right-3">
            <?php
            if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
                woocommerce_template_loop_add_to_cart(array('class' => 'absolute top-3 right-3 p-2 rounded-full bg-white/80 hover:bg-white transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'));
            }
            ?>
        </div>
      </div>

      <!-- Product Info -->
      <div class="p-4">
        <h3 id="product-title-<?php echo $product_id; ?>" class="font-semibold text-lg mb-2" itemprop="name">
            <a href="<?php echo esc_url($product_link); ?>" class="hover:text-blue-600 transition-colors">
                <?php echo esc_html($product_title); ?>
            </a>
        </h3>

        <div class="flex items-center justify-between">
            <div class="text-xl font-bold text-gray-900" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                <span itemprop="price" content="<?php echo esc_attr($product->get_price()); ?>">
                    <?php echo $product_price; ?>
                </span>
                <meta itemprop="priceCurrency" content="<?php echo esc_attr(get_woocommerce_currency()); ?>" />
            </div>
        </div>
      </div>
    </article>
    <?php
    return ob_get_clean();
}

// Generate pagination HTML
function ecolitio_generate_pagination_html($current_page, $total_pages) {
    if ($total_pages <= 1) return '';

    $html = '<nav class="flex justify-center mt-8" aria-label="Paginación de productos" role="navigation">';
    $html .= '<ul class="flex space-x-2">';

    // Previous button
    if ($current_page > 1) {
        $html .= '<li><a href="#" data-page="' . ($current_page - 1) . '" class="pagination-link px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors" aria-label="Página anterior">« Anterior</a></li>';
    }

    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active_class = ($i == $current_page) ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300';
        $html .= '<li><a href="#" data-page="' . $i . '" class="pagination-link px-3 py-2 ' . $active_class . ' rounded transition-colors" aria-label="Ir a la página ' . $i . '">' . $i . '</a></li>';
    }

    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li><a href="#" data-page="' . ($current_page + 1) . '" class="pagination-link px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors" aria-label="Página siguiente">Siguiente »</a></li>';
    }

    $html .= '</ul></nav>';
    return $html;
}
