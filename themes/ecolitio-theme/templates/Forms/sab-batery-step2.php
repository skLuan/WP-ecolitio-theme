<!-- For latter use -->
<!-- For latter use -->
<!-- For latter use -->
<!-- For latter use -->
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

                // $value = $getAttributes['voltios']['options'];
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
    <?php
    get_template_part('templates/sab-batery-controls', null); // -------- Progress bar
    ?>
</div>