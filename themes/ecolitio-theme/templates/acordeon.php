<?php
/**
 * Accordion Template for FAQs
 *
 * Displays FAQs in an accordion layout
 *
 * @package Ecolitio
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

global $post;

// Get FAQ data
$faq_title = get_the_title($post);
$faq_content = apply_filters('the_content', get_the_content($post));
$faq_categories = get_the_terms($post->ID, 'faq_category');
$faq_tags = get_the_terms($post->ID, 'faq_tag');

// Generate unique ID for accordion
$accordion_id = 'faq-' . $post->ID;
?>

<div id="acordeon-wrapper" class="border-b border-gray-200 last:border-b-0">
    <h4 class="flex items-center justify-between py-4 px-6 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-colors duration-200"
        onclick="toggleAccordion('<?php echo esc_js($accordion_id); ?>')"
        aria-expanded="false"
        aria-controls="<?php echo esc_attr($accordion_id); ?>-content">
        <span class="font-medium text-gray-900 pr-4"><?php echo esc_html($faq_title); ?></span>
        <div class="opener-icon flex-shrink-0 transition-transform duration-200">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </h4>

    <div id="<?php echo esc_attr($accordion_id); ?>-content"
         class="overflow-hidden transition-all duration-300 max-h-0"
         aria-labelledby="<?php echo esc_attr($accordion_id); ?>"
         style="display: none;">

        <div class="px-6 pb-4">
            <div class="prose prose-sm max-w-none text-gray-700">
                <?php echo $faq_content; ?>
            </div>

            <?php if ($faq_categories || $faq_tags) : ?>
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex flex-wrap gap-2 text-sm">
                        <?php if ($faq_categories) : ?>
                            <?php foreach ($faq_categories as $category) : ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?php echo esc_html($category->name); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if ($faq_tags) : ?>
                            <?php foreach ($faq_tags as $tag) : ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <?php echo esc_html($tag->name); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleAccordion(faqId) {
    const header = event.currentTarget;
    const content = document.getElementById(faqId + '-content');
    const icon = header.querySelector('.opener-icon svg');
    const isExpanded = header.getAttribute('aria-expanded') === 'true';

    // Toggle aria-expanded
    header.setAttribute('aria-expanded', !isExpanded);

    // Toggle content visibility
    if (isExpanded) {
        content.style.maxHeight = '0px';
        setTimeout(() => {
            content.style.display = 'none';
        }, 300);
        icon.style.transform = 'rotate(0deg)';
    } else {
        content.style.display = 'block';
        content.style.maxHeight = content.scrollHeight + 'px';
        icon.style.transform = 'rotate(180deg)';
    }
}
</script>