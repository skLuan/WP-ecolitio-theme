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
		<div class="eco-main-info-header !sticky !py-4 top-36 !bg-black-eco border-b !border-blue-eco-dark">
			<?php woocommerce_breadcrumb(); ?>

			<?php do_action('ecolitio_single_product_summary'); ?>
			<div class="prices-container flex flex-row justify-between h-fit items-center">
				<?php do_action('ecolitio_single_product_prices'); ?>
			</div>
		</div>
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
		$attribute_definitions = [
			'Voltios' => ['type' => 'radio', 'default_options' => ['s', 'o', 'w']],
			'Amperios' => ['meta_key' => 'amperios', 'type' => 'radio', 'default_options' => ['s', 'o', 'w']],
			'Ubicación de Bateria' => ['meta_key' => 'ubicacion_de_bateria', 'type' => 'radio', 'default_options' => ['interno', 'externo']],
			'Modelo de Patinete Eléctrico' => ['meta_key' => 'modelo_de_patinete_electrico', 'type' => 'text', 'default_options' => ['']],
			'Tipo de Conector' => ['meta_key' => 'tipo_de_conector', 'type' => 'radio', 'default_options' => ['s', 'o', 'w']],
			'Ancho(cm)' => ['meta_key' => 'ancho_cm', 'type' => 'number', 'default_options' => ['']],
			'Alto(cm)' => ['meta_key' => 'alto_cm', 'type' => 'number', 'default_options' => ['']],
			'Largo(cm)' => ['meta_key' => 'largo_cm', 'type' => 'number', 'default_options' => ['']],
		];


		$distance = get_post_meta(get_the_ID(), 'sabway_default_distance', true) ?: 30;
		?>

		<article class="my-6">
			<span><?= print_r($getAttributes); ?></span>
			<form action="submit" method="post" class="sabway-form !bg-black !rounded-lg !px-4 !py-6">

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
					<div class="!flex !flex-row !justify-end !w-full">
						<button type="button" id="sab-start-button" class="sab-button-next !w-fit !bg-green-eco !text-black-eco !rounded-full !px-14 !py-3">Crear bateria</button>
					</div>
				</div>



				<div id="sab-step-1" class="step !flex !flex-col !gap-y-10">
					<?php //----------------------------------------------------- 1. Especificaciones eléctricas
					$props = array('icon' => esc_attr($icons["step1"]['icon']), 'title' => 'Paso 1: Especificaciones Eléctricas');
					get_template_part('templates/icon-title', null, $props);
					?>
					<?php get_template_part('templates/progress-bar'); // -------- Progress bar 
					?>
					<p>Cuantos kilometros extra quieres recorrer?</p>
					<div>
						<p><strong>Autonomia: </strong><span><?= esc_attr($distance) ?></span></p>
						<input type="range" id="sab-distance-range" name="sab-distance-range" min="10" max="100" value="<?= intval($distance) ?>" step="1" class="w-full">
					</div>
					<div id="sab-form-energy-advanced">
						<h5>Opciones Avanzadas</h5>
						<p>Cambiar estas propiedades cambia directamente la Autonomía</p>
						<p>Aprende a como funciona esta tabla leyendo <a href="#" class="!text-green-eco">nuestra guía</a></p>
						<div class="voltage">
							<h4 class="!text-white-eco !font-bold ">Voltaje:</h4>
							<div class="label-container flex flex-row gap-4 justify-evenly">
								<?php

								// $value = $getAttributes['voltios']['options'];
								foreach ($values as $option) : ?>
									<label for="input-voltage-<?= esc_attr($option); ?>" class="!px-9 !py-2 !bg-blue-eco !text-white-eco ! font-bold !rounded-full">
										<input type="radio" name="voltage" id="input-voltage-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
										<span class="!text-white-eco"><?= esc_attr($option); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="amperage">
							<h4 class="!text-white-eco !font-bold ">Amperaje:</h4>
							<div class="label-container flex flex-row gap-4 justify-evenly">
								<?php
								// $value = $getAttributes['amperios']['options'];
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







				<div id="sab-step-2" class="step !flex !flex-col !gap-y-10">
					<?php //----------------------------------------------------- 2. Dimensiones Físicas
					$props = array('icon' => esc_attr($icons["step2"]['icon']), 'title' => 'Paso 2: Dimensiones Físicas');

					get_template_part('templates/icon-title', null, $props);
					?>
				</div>
				<div id="sab-step-3" class="step !flex !flex-col !gap-y-10">
					<?php
					$props = array('icon' => esc_attr($icons["step3"]['icon']), 'title' => 'Paso 3: Conectores');

					get_template_part('templates/icon-title', null, $props);
					?>
					<div class="tipo-de-conector">
						<h4 class="!text-white-eco !font-bold">Tipo de Conector:</h4>
						<div class="label-container flex flex-row gap-4 justify-evenly">
							<?php
							$connector_values = isset($getAttributes['tipo-de-conector']['options']) ? $getAttributes['tipo-de-conector']['options'] : ['s', 'o', 'w'];
							foreach ($connector_values as $option) : ?>
								<label for="input-connector-<?= esc_attr($option); ?>" class="!px-9 !py-2 !bg-blue-eco !text-white-eco !font-bold !rounded-full">
									<input type="radio" name="tipo-de-conector" id="input-connector-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
									<span class="!text-white-eco"><?= esc_attr($option); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<div id="sab-step-4" class="step !flex !flex-col !gap-y-10">
					<?php
					$props = array('icon' => esc_attr($icons["step4"]['icon']), 'title' => 'Paso 4: Confirmación');

					get_template_part('templates/icon-title', null, $props);
					?>
				</div>
				<div id="sab-step-5" class="step !flex !flex-col !gap-y-10">
					<?php
					$props = array('icon' => "material-symbols:check-circle", 'title' => 'Gracias! - pedido realizado con éxito!');

					get_template_part('templates/icon-title', null, $props);
					?>
				</div>
			</form>

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