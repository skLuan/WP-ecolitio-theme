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

			<section id="preguntas-frecuentes" class="bg-white-eco py-8">
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
