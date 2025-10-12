<?php
/**
 * Pagination Template
 *
 * Displays numbered pagination with accessibility features
 *
 * @package Ecolitio
 * @var int $current_page Current page number
 * @var int $total_pages Total number of pages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Apply filters for customization
$current_page = apply_filters('ecolitio_pagination_current_page', $current_page ?? 1);
$total_pages = apply_filters('ecolitio_pagination_total_pages', $total_pages ?? 1);

// Don't show pagination if only one page
if ($total_pages <= 1) {
    return;
}

// Calculate page range with smart ellipsis
$show_ellipsis = apply_filters('ecolitio_pagination_show_ellipsis', true);
$range = apply_filters('ecolitio_pagination_range', 2); // Pages to show on each side of current

$start_page = max(1, $current_page - $range);
$end_page = min($total_pages, $current_page + $range);

// Action before pagination
do_action('ecolitio_before_pagination', $current_page, $total_pages);
?>
<nav class="flex justify-center mt-8"
     aria-label="<?= esc_attr__('Paginación de productos', 'ecolitio-theme'); ?>"
     role="navigation">

    <ul class="flex space-x-2">
        <?php
        // Previous button
        if ($current_page > 1) :
            $prev_page = $current_page - 1;
            ?>
            <li>
                <a href="#"
                   data-page="<?= esc_attr($prev_page); ?>"
                   class="pagination-link px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                   aria-label="<?= esc_attr__('Página anterior', 'ecolitio-theme'); ?>">
                    « <?= esc_html__('Anterior', 'ecolitio-theme'); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php
        // First page and ellipsis if needed
        if ($start_page > 1) :
            ?>
            <li>
                <a href="#"
                   data-page="1"
                   class="pagination-link px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                   aria-label="<?= esc_attr__('Ir a la página 1', 'ecolitio-theme'); ?>">
                    1
                </a>
            </li>
            <?php if ($show_ellipsis && $start_page > 2) : ?>
                <li>
                    <span class="px-2 py-2 text-gray-500" aria-hidden="true">...</span>
                </li>
            <?php endif; ?>
        <?php endif; ?>

        <?php
        // Page number links
        for ($page = $start_page; $page <= $end_page; $page++) :
            $is_current = ($page === $current_page);
            $active_class = $is_current ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300';
            ?>
            <li>
                <a href="#"
                   data-page="<?= esc_attr($page); ?>"
                   class="pagination-link px-3 py-2 <?= esc_attr($active_class); ?> rounded transition-colors"
                   aria-label="<?= esc_attr(sprintf(__('Ir a la página %d', 'ecolitio-theme'), $page)); ?>"
                   <?php if ($is_current) : ?>aria-current="page"<?php endif; ?>>
                    <?= esc_html($page); ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php
        // Last page and ellipsis if needed
        if ($end_page < $total_pages) :
            if ($show_ellipsis && $end_page < $total_pages - 1) :
                ?>
                <li>
                    <span class="px-2 py-2 text-gray-500" aria-hidden="true">...</span>
                </li>
            <?php endif; ?>
            <li>
                <a href="#"
                   data-page="<?= esc_attr($total_pages); ?>"
                   class="pagination-link px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                   aria-label="<?= esc_attr(sprintf(__('Ir a la página %d', 'ecolitio-theme'), $total_pages)); ?>">
                    <?= esc_html($total_pages); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php
        // Next button
        if ($current_page < $total_pages) :
            $next_page = $current_page + 1;
            ?>
            <li>
                <a href="#"
                   data-page="<?= esc_attr($next_page); ?>"
                   class="pagination-link px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded transition-colors"
                   aria-label="<?= esc_attr__('Página siguiente', 'ecolitio-theme'); ?>">
                    <?= esc_html__('Siguiente', 'ecolitio-theme'); ?> »
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<?php
// Action after pagination
do_action('ecolitio_after_pagination', $current_page, $total_pages);
?>