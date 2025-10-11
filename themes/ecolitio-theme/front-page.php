<?php
/**
 * The template for displaying full width pages.
 *
 * Template Name: Full width
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) :
				the_post();

				do_action( 'storefront_page_before' );

				get_template_part( 'content', 'page' );

				/**
				 * Functions hooked in to storefront_page_after action
				 *
				 * @hooked storefront_display_comments - 10
				 */
				do_action( 'storefront_page_after' );

			endwhile; // End of the loop.
			?>

			<section id="nuestros-productos">
				<div class="container mx-auto px-4 py-8">
					<h2 class="text-2xl font-bold mb-6 text-center"><?php _e('Nuestros Productos', 'ecolitio-theme'); ?></h2>

					<div id="products-grid" class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<?php
						// Check if WooCommerce is active
						if (!class_exists('WooCommerce')) {
							echo '<div class="col-span-full text-center py-8"><p class="text-gray-500">WooCommerce no está activado.</p></div>';
						} else {
							// Initial load of first 9 products
							$args = array(
								'post_type' => 'product',
								'posts_per_page' => 9,
								'post_status' => 'publish',
								'offset' => 0
							);

							$products_query = new WP_Query($args);

							if ($products_query->have_posts()) {
								while ($products_query->have_posts()) {
									$products_query->the_post();
									echo ecolitio_generate_product_card_html();
								}
							} else {
								echo '<div class="col-span-full text-center py-8"><p class="text-gray-500">No hay productos disponibles en este momento.</p></div>';
							}
							wp_reset_postdata();
						}
						?>
					</div>

					<div id="products-pagination" class="mt-8">
						<?php
						if (class_exists('WooCommerce')) {
							$total_products = wp_count_posts('product')->publish;
							$total_pages = ceil($total_products / 9);
							echo ecolitio_generate_pagination_html(1, $total_pages);
						}
						?>
					</div>
				</div>
			</section>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
