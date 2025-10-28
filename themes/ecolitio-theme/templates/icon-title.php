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
<div class="!flex !flex-row !gap-4">
	<iconify-icon icon="<?= esc_attr($icon) ?>"></iconify-icon>
	<h2 class="!text-white-eco"><?= esc_html($title) ?></h2>
</div>