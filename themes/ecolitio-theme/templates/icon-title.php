<?php
/**
 * Reusable icon-title component template
 *
 * @param string $icon The iconify-icon identifier
 * @param string $title The heading text
 */

$icon = $args['icon'] ?? '';
$title = $args['title'] ?? '';
?>
<div class="!flex !flex-row !gap-4 items-center">
	<iconify-icon icon="<?= esc_attr($icon) ?>" class="ec-icon !text-blue-eco-dark bg-white-eco rounded-full p-2 !h-fit" width="36" height="36"></iconify-icon>
	<h2 class="!text-white-eco !mb-0 !text-2xl"><?= esc_html($title) ?></h2>
</div>