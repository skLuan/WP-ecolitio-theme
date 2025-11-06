<?php

/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}

remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);


add_action('ecolitio_single_product_summary', 'woocommerce_template_single_title', 15);
add_action('ecolitio_single_product_summary', 'woocommerce_template_single_rating', 20);
add_action('ecolitio_single_product_prices', 'woocommerce_template_single_price', 20);
add_action('ecolitio_single_product_prices', 'woocommerce_template_single_add_to_cart', 20);
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>

	<div class="summary entry-summary ecolitio-item">
		<?php get_template_part('templates/info-header'); ?>
		<div>
		</div>
		<?php
		/**
		 * Hook: woocommerce_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 * @hooked WC_Structured_Data::generate_product_data() - 60
		 */
		do_action('woocommerce_single_product_summary');

		$icons = [
			"step1" => [
				"icon" => "ix:electrical-energy-filled",
				"title" => "Parte eléctrica",
			],
			"step2" => [
				"icon" => "tabler:dimensions",
				"title" => "Dimensiones",
			],
			"step3" => [
				"icon" => "material-symbols:cable",
				"title" => "Conectores",
			],
			"step4" => [
				"icon" => "material-symbols:check-circle",
				"title" => "Confirmación",
			],
		];


		$getAttributes = $product->get_attributes();
		$values = ['s', 'o', 'w'];


		$distance = 30;
		?>

		<article class="my-6">
			<form action="submit" method="post" class="sabway-form !bg-black !rounded-lg !px-4 !py-6">
				<?php
				// Generate nonce for form submission
				$sabway_form_nonce = wp_create_nonce('ecolitio_sabway_form_nonce');
				?>
				<input type="hidden" name="ecolitio_sabway_nonce" value="<?php echo esc_attr($sabway_form_nonce); ?>" data-sabway-nonce="<?php echo esc_attr($sabway_form_nonce); ?>">


				<!-- Slider main container -->
				<div class="swiper swiper-sab-batery">
					<!-- Additional required wrapper -->
					<div class="swiper-wrapper">
						<!-- Slides -->
						<div class="swiper-slide">
							<div id="sab-step-0" class="step !flex !flex-col !gap-y-10">
								<div class="!flex !flex-row !gap-4"><iconify-icon icon="" class="!hidden"></iconify-icon>
									<h2 class="!text-white-eco">Tu bateria a media</h2>
								</div>
								<p class="!text-white-eco">El formulario consiste de 4 pasos, no toma más de 2 minutos!</p>
								<div class="ec-icons !flex !flex-row !w-full !justify-around">
									<?php foreach ($icons as $icon) : ?>
										<div class="ec-icon-container flex !flex-col !items-center justify-center !gap-1">
											<iconify-icon icon="<?= esc_attr($icon['icon']); ?>" class="ec-icon !text-blue-eco-dark bg-white-eco rounded-full p-2" width="36" height="36"></iconify-icon>
											<h4 class="!text-white-eco"><?= esc_html($icon['title']); ?></h4>
										</div>
									<?php endforeach; ?>
								</div>
								<?php get_template_part('templates/progress-bar'); // -------- Progress bar
								?>
								<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
								?>
							</div>
						</div>
						<div class="swiper-slide">
							<div id="sab-step-1" class="step !flex !flex-col !gap-y-10">
								<?php //----------------------------------------------------- 1. Especificaciones eléctricas
								$props = array('icon' => esc_attr($icons["step1"]['icon']), 'title' => 'Paso 1: Especificaciones Eléctricas');
								get_template_part('templates/icon-title', null, $props);
								?>
								<?php get_template_part('templates/progress-bar'); // -------- Progress bar 
								?>
								<p>Cuantos kilometros extra quieres recorrer?</p>
								<div class="flex flex-col">
									<p><strong>Autonomia: </strong><span><?= esc_attr($distance) ?></span>Km</p>
									<input type="range" id="sab-distance-range" name="sab-distance-range" min="10" max="100" value="<?= intval($distance) ?>" step="1" class="w-full">
									<span id="progress-minval" class="">Min value</span>
									<span id="progress-maxval" class="ml-auto">Max value</span>
								</div>
								<div id="sab-form-energy-advanced">
									<h4 class="!text-white-eco !font-bold !flex flex-row items-center gap-2 !mb-0">Opciones Avanzadas <iconify-icon icon="material-symbols:arrow-drop-down" class="!text-white-eco !cursor-pointer" width="24" height="24"></iconify-icon></h4>
									<p>Cambiar estas propiedades cambia directamente la Autonomía <br>
										Aprende a como funciona esta tabla leyendo <a href="#" class="!text-green-eco">nuestra guía</a>
									</p>
									<div class="mb-8 voltage">
										<h5 class="!text-white-eco !font-bold !mb-2"><?php esc_html_e($getAttributes['voltios']['name'], 'text-domain'); ?>:</h5>
										<div class="label-container flex flex-row gap-4 justify-evenly">
											<?php

											$values = $getAttributes['voltios']['options'];
											foreach ($values as $option) : ?>
												<label for="input-voltage-<?= esc_attr($option); ?>" class="!px-9 !py-2 !bg-blue-eco !text-white-eco ! font-bold !rounded-full">
													<input type="radio" name="voltage" id="input-voltage-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
													<span class="!text-white-eco"><?= esc_attr($option); ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									</div>
									<div class="mb-8 amperage">
										<h5 class="!text-white-eco !font-bold !mb-2"><?php esc_html_e($getAttributes['amperios']['name'], 'text-domain'); ?>:</h5>
										<div class="label-container flex flex-row gap-4 justify-evenly">
											<?php
											$values = $getAttributes['amperios']['options'];
											foreach ($values as $option) : ?>
												<label for="input-amperage-<?= esc_attr($option); ?>" class="!px-9 !py-2 !bg-blue-eco !text-white-eco ! font-bold !rounded-full">
													<input type="radio" name="amperage" id="input-amperage-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
													<span class="!text-white-eco"><?= esc_attr($option); ?></span>
												</label>
											<?php endforeach; ?>
										</div>
									</div>
								</div>
							</div>
							<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
							?>
						</div>
						<div class="swiper-slide">
							<div id="sab-step-2" class="step !flex !flex-col !gap-y-10">
								<?php //----------------------------------------------------- 2. Dimensiones Físicas
								$props = array('icon' => esc_attr($icons["step2"]['icon']), 'title' => 'Paso 2: Dimensiones Físicas');

								get_template_part('templates/icon-title', null, $props);
								get_template_part('templates/progress-bar'); // -------- Progress bar 

								$ubication_values = isset($getAttributes['ubicacion-de-bateria']['options']) ? $getAttributes['ubicacion-de-bateria']['options'] : $values;
								?>
								<figure class="relative z-0">
									<div class="w-2/3 lg:w-1/2 flex flex-row justify-evenly absolute right-0 top-1/4">
										<?php foreach ($ubication_values as $option) : ?>
											<label for="input-ubication-<?= esc_attr($option); ?>" class="!px-9 !py-2 !bg-blue-eco !text-white-eco !font-bold !rounded-full">
												<input type="radio" name="ubicacion-de-bateria" id="input-ubication-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
												<span class="!text-white-eco"><?= esc_attr($option); ?></span>
											</label>
										<?php endforeach; ?>

									</div>
									<picture>
										<img src="<?= get_stylesheet_directory_uri() . '/assets/PatineteInterior.jpg' ?>" alt="">
									</picture>
								</figure>
								<div class="dimensiones-sab-batery !grid !grid-cols-1 md:!grid-cols-3 !gap-6">
									<label for="alto-bateria">
										<span class="!font-semibold !text-blue-eco-clarisimo pb-2">Alto(cm):</span>
										<input type="number" name="alto-bateria" id="alto-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !border-blue-eco !text-white-eco" placeholder="Ej: 10">
									</label>
									<label for="ancho-bateria">
										<span class="!font-semibold !text-blue-eco-clarisimo pb-2">Ancho(cm):</span>
										<input type="number" name="ancho-bateria" id="ancho-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !border-blue-eco !text-white-eco" placeholder="Ej: 25">
									</label>
									<label for="largo-bateria">
										<span class="!font-semibold !text-blue-eco-clarisimo pb-2">Largo(cm):</span>
										<input type="number" name="largo-bateria" id="largo-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !border-blue-eco !text-white-eco" placeholder="Ej: 40">
									</label>
								</div>
								<label for="modelo-patinete">
									<span class="!font-semibold !text-blue-eco-clarisimo pb-2">Modelo de patinete:</span>
									<input type="text" name="modelo-patinete" id="modelo-patinete" class="w-full !p-2 !rounded-md !bg-black-eco !border !border-blue-eco !text-white-eco" placeholder="Ej: Ninebot KickScooter Serie E E20">
								</label>
							</div>
							<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
							?>
						</div>
						<div class="swiper-slide">
							<div id="sab-step-3" class="step !flex !flex-col !gap-y-10">
								<?php
								$props = array('icon' => esc_attr($icons["step3"]['icon']), 'title' => 'Paso 3: Conectores');

								get_template_part('templates/icon-title', null, $props);
								get_template_part('templates/progress-bar'); // -------- Progress bar 
								?>
								<div class="tipo-de-conector">
									<h4 class="!text-white-eco !font-bold">Tipo de Conector:</h4>
									<div class="label-container flex flex-row gap-4 justify-evenly">
										<?php
										$connector_values = isset($getAttributes['tipo-de-conector']['options']) ? $getAttributes['tipo-de-conector']['options'] : $values;
										foreach ($connector_values as $option) : ?>
											<label for="input-connector-<?= esc_attr($option); ?>" class="!px-9 !py-2 !bg-blue-eco !text-white-eco !font-bold !rounded-full">
												<input type="radio" name="tipo-de-conector" id="input-connector-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
												<span class="!text-white-eco"><?= esc_attr($option); ?></span>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
							<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
							?>
						</div>
						<div class="swiper-slide">
							<div id="sab-step-4" class="step !flex !flex-col !gap-y-10">
								<?php $props = array('icon' => esc_attr($icons["step4"]['icon']), 'title' => 'Paso 4: Confirmación');
								get_template_part('templates/icon-title', null, $props); ?>
								<ul>
									<?php foreach ($getAttributes as $attr) :
										$value = $attr['name'];
										// Sanitize value for HTML ID by removing spaces and special characters
										$sanitized_id = strtolower(preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace(' ', '-', $value)));
									?>
										<li id="" class="final-check-<?= esc_attr($sanitized_id) ?> grid grid-cols-2 gap-2">
											<strong><?= esc_html($value); ?></strong>
											<p id="">
											</p>
										</li>
									<?php endforeach; ?>
								</ul>
								<div id="sab-form-controls" class="!flex !flex-row !justify-end !w-full">
									<div id="" class="swiper-button-prev sab-back-button cursor-pointer !w-fit border !border-white-eco opacity-70 !text-white-eco !bg-transparent !rounded-full !px-14 !py-3">
										<iconify-icon icon="material-symbols:arrow-back-ios-new" class="!align-middle !mr-2" width="16" height="16"></iconify-icon>
										Atrás
									</div>
									<button type="button" id="sab-submit-button" class="sab-button-next !w-fit !bg-green-eco !border-green-eco !text-black-eco !rounded-full !px-14 !py-3">Finalizar Pedido</button>
								</div>
							</div>
						</div>
						<div class="swiper-slide">
							<div id="sab-step-5" class="step !flex !flex-col !gap-y-10">
								<?php
								$props = array('icon' => "material-symbols:check-circle", 'title' => 'Gracias! - pedido realizado con éxito!');

								get_template_part('templates/icon-title', null, $props);
								?>
								<ul>
									<?php foreach ($getAttributes as $attr) :
										$value = $attr['name'];
										// Sanitize value for HTML ID by removing spaces and special characters
										$sanitized_id = strtolower(preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace(' ', '-', $value)));
									?>
										<li id="" class="final-check-<?= esc_attr($sanitized_id) ?> grid grid-cols-2 gap-2">
											<strong><?= esc_html($value); ?></strong>
											<p id="">
											</p>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
							<div id="sab-form-controls" class="!flex !flex-row !justify-end !w-full">
								<button type="button" id="reset-form-button" class="cursor-pointer !w-fit border !border-white-eco opacity-70 !text-white-eco !bg-transparent !rounded-full !px-14 !py-3">
									<iconify-icon icon="material-symbols:arrow-back-ios-new" class="!align-middle !mr-2" width="16" height="16"></iconify-icon>
									Crear nueva batería
								</button>
							</div>
						</div>
					</div>
				</div>
			</form>
		</article>
	</div>
</div>

<?php
/**
 * Hook: woocommerce_before_single_product_summary.
 *
 * @hooked woocommerce_show_product_sale_flash - 10
 * @hooked woocommerce_show_product_images - 20
 */
do_action('woocommerce_before_single_product_summary');
?>

</div>
<?php
/**
 * Hook: woocommerce_after_single_product_summary.
 *
 * @hooked woocommerce_output_product_data_tabs - 10
 * @hooked woocommerce_upsell_display - 15
 * @hooked woocommerce_output_related_products - 20
 */
do_action('woocommerce_after_single_product_summary');
?>

<?php do_action('woocommerce_after_single_product'); ?>