<?php
/**
 * Reusable progress bar component template
 *
 * @param int $steps Number of steps in the progress bar (default: 4)
 */

$steps = $args['steps'] ?? 4;
?>
<div class="sab-progress-bar !overflow-hidden !rounded-full !flex !flex-row !justify-between !gap-1">
	<?php for ($i = 1; $i <= $steps; $i++) : ?>
		<div id="sab-progress-step-<?= $i ?>" class="progress-step !min-h-3 !w-full !border !border-blue-eco"></div>
	<?php endfor; ?>
</div>