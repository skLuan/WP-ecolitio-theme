<?php
/**
 * The template for displaying full width pages.
 *
 * Template Name: Full width
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class=" mb-0">
		<main id="main" class="site-main mb-0" role="main">

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
					<h2 class="text-2xl !text-white-eco font-bold mb-6 text-center"><?php _e('Nuestros Productos', 'ecolitio-theme'); ?></h2>

					<?php
					// Render products grid with pagination using modular templates
					ecolitio_render_products_grid(array(
						'posts_per_page' => 9,
						'current_page'   => 1,
						'show_pagination' => true,
					));
					?>
				</div>
			</section>

			<section id="kits">
				<div class="container mx-auto px-4 py-8">
					<h2 class="text-2xl !text-white-eco font-bold mb-6 text-center"><?php _e('Kits', 'ecolitio-theme'); ?></h2>

					<?php
					// Query for grouped products
					$grouped_products_args = array(
						'post_type'      => 'product',
						'posts_per_page' => 3,
						'post_status'    => 'publish',
						'tax_query'      => array(
							array(
								'taxonomy' => 'product_type',
								'field'    => 'slug',
								'terms'    => 'grouped',
							),
						),
					);

					$grouped_products_query = new WP_Query($grouped_products_args);

					if ($grouped_products_query->have_posts()) :
						?>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
							<?php
							while ($grouped_products_query->have_posts()) :
								$grouped_products_query->the_post();
								echo ecolitio_generate_product_card_html();
							endwhile;
							wp_reset_postdata();
							?>
						</div>
						<?php
					else :
						// No grouped products found - show "Próximamente"
						?>
						<div class="text-center py-12">
							<h3 class="text-xl font-semibold text-gray-600 mb-4"><?php _e('Próximamente', 'ecolitio-theme'); ?></h3>
							<p class="text-gray-500"><?php _e('Kits de productos próximamente disponibles.', 'ecolitio-theme'); ?></p>
						</div>
						<?php
					endif;
					?>
				</div>
			</section>
			<section id="categorias">
				<div class="container mx-auto px-4 py-8">
					<h2 class="text-2xl !text-white-eco font-bold mb-6 text-center"><?php _e('Categorías', 'ecolitio-theme'); ?></h2>

					<?php
					// Query WooCommerce product categories
					$product_categories = get_terms(array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => true,
						'number'     => 6, // Limit to 6 categories
						'orderby'    => 'count',
						'order'      => 'DESC',
					));

					if (!empty($product_categories) && !is_wp_error($product_categories)) :
						?>
						<div class="flex flex-wrap justify-center gap-6">
							<?php
							foreach ($product_categories as $category) :
								$category_link = get_term_link($category, 'product_cat');
								$thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
								$category_image = wp_get_attachment_image_src($thumbnail_id, 'medium');

								if (!$category_image) {
									// Fallback to placeholder if no category image
									$category_image = array(wc_placeholder_img_src(), 300, 300);
								}
								?>
								<div class="flex flex-col items-center group cursor-pointer">
									<a href="<?php echo esc_url($category_link); ?>" class="block">
										<div class="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden border-4 border-gray-200 group-hover:border-blue-500 transition-colors duration-300 mb-3">
											<img src="<?php echo esc_url($category_image[0]); ?>"
											     alt="<?php echo esc_attr($category->name); ?>"
											     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
										</div>
										<h3 class="text-center font-medium text-gray-800 group-hover:text-blue-600 transition-colors duration-300">
											<?php echo esc_html($category->name); ?>
										</h3>
									</a>
								</div>
								<?php
							endforeach;
							?>
						</div>
						<?php
					else :
						// No categories found
						?>
						<div class="text-center py-12">
							<h3 class="text-xl font-semibold text-gray-600 mb-4"><?php _e('Categorías Próximamente', 'ecolitio-theme'); ?></h3>
							<p class="text-gray-500"><?php _e('Las categorías de productos estarán disponibles pronto.', 'ecolitio-theme'); ?></p>
						</div>
						<?php
					endif;
					?>
				</div>
			</section>
			<section id="powerCycle">
					<h2 class="text-2xl  font-bold mb-6 text-center"><?php _e('Power cycle
					', 'ecolitio-theme'); ?></h2>

			</section>
			<section id="productos_destacados">
				<div class="container mx-auto px-4 py-8">
					<h2 class="text-2xl !text-white-eco font-bold mb-6 text-center"><?php _e('Productos Destacados', 'ecolitio-theme'); ?></h2>

					<?php
					// Query for featured products
					$featured_products_args = array(
						'post_type'      => 'product',
						'posts_per_page' => 9,
						'post_status'    => 'publish',
						'meta_query'     => array(
							array(
								'key'   => '_featured',
								'value' => 'yes',
							),
						),
					);

					$featured_products_query = new WP_Query($featured_products_args);

					if ($featured_products_query->have_posts()) :
						?>
						<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
							<?php
							while ($featured_products_query->have_posts()) :
								$featured_products_query->the_post();
								get_template_part('templates/product-card');
							endwhile;
							wp_reset_postdata();
							?>
						</div>
						<?php
					else :
						// No featured products found
						?>
						<div class="text-center py-12">
							<h3 class="text-xl font-semibold text-gray-600 mb-4"><?php _e('Próximamente', 'ecolitio-theme'); ?></h3>
							<p class="text-gray-500"><?php _e('Productos destacados estarán disponibles pronto.', 'ecolitio-theme'); ?></p>
						</div>
						<?php
					endif;
					?>
				</div>
			</section>

			<section id="preguntas-frecuentes" class="bg-white-eco py-12">
				<div class="container mx-auto px-4 py-8">
					<h2 class="text-2xl font-bold mb-6 text-center"><?php _e('Preguntas Frecuentes', 'ecolitio-theme'); ?></h2>

					<?php
					// Query for FAQs
					$faqs_args = array(
						'post_type'      => 'faq',
						'posts_per_page' => -1, // Show all FAQs
						'post_status'    => 'publish',
						'orderby'        => 'menu_order',
						'order'          => 'ASC',
					);

					$faqs_query = new WP_Query($faqs_args);

					if ($faqs_query->have_posts()) :
						?>
						<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
							<?php
							while ($faqs_query->have_posts()) :
								$faqs_query->the_post();
								get_template_part('templates/acordeon');
							endwhile;
							wp_reset_postdata();
							?>
						</div>
						<?php
					else :
						// No FAQs found
						?>
						<div class="text-center py-12">
							<h3 class="text-xl font-semibold text-gray-600 mb-4"><?php _e('Preguntas Frecuentes Próximamente', 'ecolitio-theme'); ?></h3>
							<p class="text-gray-500"><?php _e('Las preguntas frecuentes estarán disponibles pronto.', 'ecolitio-theme'); ?></p>
						</div>
						<?php
					endif;
					?>
				</div>
			</section>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();
