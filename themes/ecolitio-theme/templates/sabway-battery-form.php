<?php

/**
 * Generic Battery Customization Form Template
 *
 * This template renders the multi-step battery customization form
 * Supports multiple battery types: sabway, medida, patinete
 * Can be used in product pages or via shortcode
 *
 * @package Ecolitio
 * @version 2.0.0
 *
 * Variables passed to this template:
 * @var WC_Product $product - WooCommerce product object
 * @var array $icons - Icon configuration for form steps
 * @var array $getAttributes - Product attributes
 * @var int $distance - Default distance value
 * @var string $sabway_form_nonce - Security nonce
 * @var string $battery_type - Battery type (sabway, medida, patinete)
 * @var array $battery_config - Battery type configuration with colors and titles
 */

defined('ABSPATH') || exit;

// Ensure required variables are set
if (!isset($product) || !isset($icons) || !isset($getAttributes) || !isset($distance) || !isset($sabway_form_nonce)) {
	echo '<div class="sabway-form-error !p-4 !rounded-lg !bg-red-500 !text-white-eco">';
	echo esc_html__('Error: Required form data is missing.', 'ecolitio-theme');
	echo '</div>';
	return;
}

// Set defaults for battery type and config if not provided
if (!isset($battery_type)) {
	$battery_type = 'sabway';
}

if (!isset($battery_config)) {
	$battery_config = array(
		'color_class' => 'red-sabway',
		'color_hex' => '#d02024',
		'title' => 'Tu batería Sabway',
		'icon_color' => 'red-sabway'
	);
}

$values = ['s', 'o', 'w'];
?>

<form action="submit" method="post" class="sabway-form !bg-black !rounded-lg !px-4 !py-6" data-battery-type="<?php echo esc_attr($battery_type); ?>">
	<?php
	// Generate nonce for form submission
	?>
	<input type="hidden" name="ecolitio_sabway_nonce" value="<?php echo esc_attr($sabway_form_nonce); ?>" data-sabway-nonce="<?php echo esc_attr($sabway_form_nonce); ?>">
	<input type="hidden" name="sabway_product_id" id="sabway_product_id" value="<?php echo esc_attr($product->get_id()); ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
	<input type="hidden" name="battery_type" value="<?php echo esc_attr($battery_type); ?>" data-battery-type="<?php echo esc_attr($battery_type); ?>">


	<!-- Slider main container -->
	<div class="swiper swiper-sab-batery">
		<!-- Additional required wrapper -->
		<div class="swiper-wrapper">
			<!-- Slides -->
			<div class="swiper-slide">
				<div id="sab-step-0" class="step !flex !flex-col !gap-y-6">
					<div class="!flex !flex-row !gap-4"><iconify-icon icon="" class="!hidden"></iconify-icon>
						<h3 class="!text-white-eco"><?php echo esc_html($battery_config['title']); ?></h3>
					</div>
					<p class="!text-white-eco">El formulario consiste de 4 pasos, no toma más de 2 minutos!</p>
					<div class="ec-icons !flex !flex-row !w-full !justify-around">
						<?php foreach ($icons as $icon) : ?>
							<div class="ec-icon-container flex !flex-col !items-center justify-center !gap-1">
								<iconify-icon icon="<?= esc_attr($icon['icon']); ?>" class="ec-icon min-h-12 bg-white-eco rounded-full p-2" style="color: var(--battery-color);" width="36" height="36"></iconify-icon>
								<h4 class="!text-white-eco"><?= esc_html($icon['title']); ?></h4>
							</div>
						<?php endforeach; ?>
					</div>
					<?php get_template_part('templates/progress-bar'); // -------- Progress bar
					?>
				</div>
				<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
				?>
			</div>
			<div class="swiper-slide">
				<div id="sab-step-1" class="step !flex !flex-col gap-y-10">
					<?php //----------------------------------------------------- 1. Especificaciones eléctricas
					$props = array('icon' => esc_attr($icons["step1"]['icon']), 'title' => 'Paso 1: Especificaciones Eléctricas');
					get_template_part('templates/icon-title', null, $props);
					?>
					<?php get_template_part('templates/progress-bar'); // -------- Progress bar 
					?>
					<div id="sab-form-energy-advanced">
						<p>Cambiar estas propiedades cambia directamente la Autonomía <br>
							Aprende a como funciona esta tabla leyendo <a href="#" style="color: var(--battery-color);">nuestra guía</a>
						</p>
						<div class="mb-8 voltage">
							<h5 class="!text-white-eco !font-bold !mb-2"><?php esc_html_e($getAttributes['voltios']['name'], 'text-domain'); ?>:</h5>
							<div class="label-container flex flex-row gap-4 justify-start flex-wrap">
								<?php

								$values = $getAttributes['voltios']['options'];
								foreach ($values as $option) : ?>
									<label for="input-voltage-<?= esc_attr($option); ?>" class="">
										<input type="radio" class="peer" name="voltage" id="input-voltage-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
										<span class="!px-9 !py-2 border bg-white-eco border-white-eco !rounded-full peer-checked:!text-white-eco peer-checked:!font-bold peer-checked:!bg-[var(--battery-color)]" style="color: var(--battery-color); border-color: var(--battery-color);" onmouseover="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; this.style.backgroundColor='black'; this.style.color='var(--battery-color)'; }" onmouseout="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; this.style.backgroundColor='white'; this.style.color='var(--battery-color)'; } else { this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='var(--battery-color)'; this.parentElement.parentElement.parentElement.querySelectorAll('span').forEach(s => { if(s !== this) { s.style.backgroundColor=''; s.style.borderColor=''; s.style.color='var(--battery-color)'; } }); }"><?= esc_attr($option); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="mb-8 amperage">
							<h5 class="!text-white-eco !font-bold !mb-2"><?php esc_html_e($getAttributes['amperios']['name'], 'text-domain'); ?>:</h5>
							<div class="label-container flex flex-row gap-4 justify-start flex-wrap">
								<?php
								$values = $getAttributes['amperios']['options'];
								foreach ($values as $option) : ?>
									<label for="input-amperage-<?= esc_attr($option); ?>" class="">
										<input type="radio" class="peer" name="amperage" id="input-amperage-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>">
										<span class="!px-9 !py-2 border bg-white-eco border-white-eco !rounded-full peer-checked:!text-white-eco peer-checked:!font-bold peer-checked:!bg-[var(--battery-color)]" style="color: var(--battery-color); border-color: var(--battery-color);" onmouseover="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; this.style.backgroundColor='black'; this.style.color='var(--battery-color)'; }" onmouseout="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; this.style.backgroundColor='white'; this.style.color='var(--battery-color)'; } else { this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='var(--battery-color)'; this.parentElement.parentElement.parentElement.querySelectorAll('span').forEach(s => { if(s !== this) { s.style.backgroundColor=''; s.style.borderColor=''; s.style.color='var(--battery-color)'; } }); }"><?= esc_attr($option); ?></span>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="flex flex-col eco-slider-pack">
							<p class="mb-1">Cuantos kilometros extra quieres recorrer?</p>
							<input type="range" id="sab-distance-range" class="custom-range" name="sab-distance-range" min="10" max="100" value="<?= intval($distance) ?>" step="1" class="w-full">
							<div class="flex flex-row w-full">
								<span id="progress-minval" class="">8 km</span>
								<span id="progress-maxval" class="ml-auto">184 km</span>
							</div>
							<p class="mb-0"><strong>Autonomia: </strong><span class="eco-distance-for-slider text-xl"><?= esc_attr($distance) ?>Km</span></p>

						</div>
					</div>
				</div>
				<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
				?>
			</div>
			<div class="swiper-slide">
				<div id="sab-step-2" class="step !flex !flex-col !gap-y-6">
					<?php //----------------------------------------------------- 2. Dimensiones Físicas
					$props = array('icon' => esc_attr($icons["step2"]['icon']), 'title' => 'Paso 2: Dimensiones Físicas');

					get_template_part('templates/icon-title', null, $props);
					get_template_part('templates/progress-bar'); // -------- Progress bar 

					$ubication_values = isset($getAttributes['ubicacion-de-bateria']['options']) ? $getAttributes['ubicacion-de-bateria']['options'] : $values;
					?>
					<div class="relative">
						<figure id="image-patinete-interior" class="relative z-0 transition-all ease-in-out duration-300">
							<picture>
								<img src="<?= get_stylesheet_directory_uri() . '/assets/PatineteInterior.jpg' ?>" alt="">
							</picture>
						</figure>
						<figure id="image-patinete-exterior" class="absolute top-0 left-0 z-0 transition-all ease-in-out duration-300">
							<picture>
								<img src="<?= get_stylesheet_directory_uri() . '/assets/PatineteExterior.jpg' ?>" alt="">
							</picture>
						</figure>
					</div>
					<div class="flex flex-row gap-4">
						<?php foreach ($ubication_values as $option) : ?>
							<label for="input-ubication-<?= esc_attr($option); ?>" class="">
								<input type="radio" class="peer" name="ubicacion-de-bateria" id="input-ubication-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>" <?php echo ($option === 'Externa') ? 'checked' : ''; ?>>
								<span class="!px-9 !py-2 border bg-white-eco border-white-eco !rounded-full peer-checked:!text-white-eco peer-checked:!font-bold peer-checked:!bg-[var(--battery-color)]" style="color: var(--battery-color); border-color: var(--battery-color);" onmouseover="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; this.style.backgroundColor='black'; this.style.color='var(--battery-color)'; }" onmouseout="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; this.style.backgroundColor='white'; this.style.color='var(--battery-color)'; } else { this.style.backgroundColor=''; this.style.borderColor=''; this.style.color='var(--battery-color)'; this.parentElement.parentElement.parentElement.querySelectorAll('span').forEach(s => { if(s !== this) { s.style.backgroundColor=''; s.style.borderColor=''; s.style.color='var(--battery-color)'; } }); }"><?= esc_attr($option); ?></span>
							</label>
						<?php endforeach; ?>

					</div>
					<div class="dimensiones-sab-batery !grid !grid-cols-1 md:!grid-cols-3 !gap-6">
						<p class="col-span-full battery-dimensions-text">Para baterías internas, mide el cajón donde va instalada la batería y escribe aquí el alto, ancho y largo del espacio interno.
						<br>
						Para baterías externas, indica las medidas del bolso o funda donde llevarás la batería, no las de la batería en sí.</p>
						
						<!-- Dimensions container - shown for internal batteries -->
						<div id="dimensions-container" class="col-span-full grid grid-cols-1 md:grid-cols-3 gap-6">
							<label for="alto-bateria">
								<span class="!font-semibold !text-white-eco pb-2">Alto(cm):</span>
								<input type="number" name="alto-bateria" id="alto-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !text-white-eco" style="border-color: var(--battery-color);" placeholder="Ej: 10">
							</label>
							<label for="ancho-bateria">
								<span class="!font-semibold !text-white-eco pb-2">Ancho(cm):</span>
								<input type="number" name="ancho-bateria" id="ancho-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !text-white-eco" style="border-color: var(--battery-color);" placeholder="Ej: 25">
							</label>
							<label for="largo-bateria">
								<span class="!font-semibold !text-white-eco pb-2">Largo(cm):</span>
								<input type="number" name="largo-bateria" id="largo-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !text-white-eco" style="border-color: var(--battery-color);" placeholder="Ej: 40">
							</label>
						</div>
						
						<!-- Liters container - shown for external batteries -->
						<div id="liters-container" class="col-span-full hidden">
							<label for="litros-bateria">
								<span class="!font-semibold !text-white-eco pb-2">Capacidad del bolso/funda (litros):</span>
								<input type="number" name="litros-bateria" id="litros-bateria" class="w-full !p-2 !rounded-md !bg-black-eco !border !text-white-eco" style="border-color: var(--battery-color);" placeholder="Ej: 20" step="0.1" min="0">
							</label>
						</div>
					</div>
					<label for="modelo-patinete">
						<span class="!font-semibold !text-white-eco pb-2">Modelo de patinete:</span>
						<input type="text" name="modelo-patinete" id="modelo-patinete" class="w-full !p-2 !rounded-md !bg-black-eco !border !text-white-eco" style="border-color: var(--battery-color);" placeholder="Ej: Ninebot KickScooter Serie E E20">
					</label>
				</div>
				<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
				?>
			</div>
			<div class="swiper-slide">
				<div id="sab-step-3" class="step !flex !flex-col !gap-y-6">
					<?php
					$props = array('icon' => esc_attr($icons["step3"]['icon']), 'title' => 'Paso 3: Conectores');

					get_template_part('templates/icon-title', null, $props);
					get_template_part('templates/progress-bar'); // -------- Progress bar 
					?>
					<div class="tipo-de-conector pb-2">
						<h4 class="!text-white-eco !font-bold">Tipo de Conector:</h4>
						<div class="label-container grid grid-cols-2 gap-2 grid-rows-2 justify-evenly">
							<?php
							$connector_values = isset($getAttributes['tipo-de-conector']['options']) ? $getAttributes['tipo-de-conector']['options'] : $values;
							foreach ($connector_values as $option) :
								?>
								<label for="input-connector-<?= esc_attr($option); ?>" class="">
									<input type="radio" class="peer connector-radio" name="tipo-de-conector" id="input-connector-<?= esc_attr($option); ?>" value="<?= esc_attr($option); ?>" data-connector-type="<?= esc_attr($option); ?>">
									<?php if($option !== 'OTROS') : ?>
									<figure class="cursor-pointer border-white-eco rounded-lg overflow-hidden peer-checked:border-[var(--battery-color)]" style="border-width: 1px;" onmouseover="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-color)'; }" onmouseout="if(!this.parentElement.querySelector('input').checked) { this.style.borderColor='var(--battery-text-color)'; } else { this.style.borderColor='var(--battery-color)'; this.parentElement.parentElement.parentElement.querySelectorAll('figure').forEach(s => { if(s !== this) {  s.style.borderColor='--battery-text-color'; } }); }">
										<picture>
											<img class="" width="250px" src="<?= get_stylesheet_directory_uri() . "/assets/conectores/" . esc_attr($option) . ".png" ?>" alt="<?= esc_attr($option) ?>">
										</picture>
									</figure>
									<?php endif; ?>
									<span class="!text-white-eco !px-9 !py-2 !rounded-full peer-checked:!font-bold" style="color: white;" onmouseover="this.style.color='white';" onmouseout="this.style.color='white';" data-checked-color="var(--battery-color)"><?= esc_attr($option); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
						<!-- Custom connector input field - shown only when OTROS is selected -->
						<div id="custom-connector-container" class="inline invisible mt-4">
							<label for="text-input-conector" class="block">
								<span class="!text-white-eco !font-semibold !pb-2 block">Nombre del conector personalizado:</span>
								<input placeholder="Ej: Conector tipo especial, Anderson, etc." type="text" name="text-input-conector" id="text-input-conector" class="w-full !p-2 !rounded-md !bg-black-eco !border !border-red-sabway !text-white-eco" />
							</label>
						</div>
					</div>
				</div>
				<?php get_template_part('templates/sab-batery-controls', null); // -------- Progress bar 
				?>
			</div>
			<div class="swiper-slide">
				<div id="sab-step-4" class="step !flex !flex-col !gap-y-6">
					<?php $props = array('icon' => esc_attr($icons["step4"]['icon']), 'title' => 'Paso 4: Confirmación');
					get_template_part('templates/icon-title', null, $props); ?>
					<ul>
						<?php foreach ($getAttributes as $attr) :
							$value = $attr['name'];
							// Sanitize value for HTML ID by removing spaces and special characters
							$sanitized_id = strtolower(preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace(' ', '-', $value)));
						?>
							<li id="" class="final-check-<?= esc_attr($sanitized_id) ?> grid grid-cols-2 gap-2 !border-b last:!border-b-0 justify-center p-4" style="border-color: var(--battery-color);">
								<strong><?= esc_html($value); ?></strong>
								<p id="" class="!m-0">
								</p>
							</li>
						<?php endforeach; ?>
					</ul>
					<div id="sab-form-controls" class="!flex !flex-row !justify-end gap-4 items-center !w-full">
						<div id="" class="swiper-button-prev sab-back-button cursor-pointer !w-fit border !border-white-eco opacity-70 !text-white-eco !bg-transparent !rounded-full !px-14 !py-3">
							<iconify-icon icon="material-symbols:arrow-back-ios-new" class="!align-middle !mr-2" width="16" height="16"></iconify-icon>
							Atrás
						</div>
						<button type="button" id="sab-submit-button" class="sab-button-next !w-fit !text-black-eco !rounded-full !px-14 !py-3" style="background-color: var(--battery-color); border-color: var(--battery-color); border: 2px solid var(--battery-color);">Finalizar Pedido</button>
					</div>
				</div>
			</div>
			<div class="swiper-slide">
				<div id="sab-step-5" class="step !flex !flex-col !gap-y-6">
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
