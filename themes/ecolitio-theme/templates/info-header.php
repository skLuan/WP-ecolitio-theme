<?php global $product; ?>
<div class="eco-main-info-header !z-10 !sticky !py-4 top-[134px] !bg-black-eco border-b !border-blue-eco-dark">
    <?php
    if ($product->get_slug() !== 'bateria-sabway') {
        woocommerce_breadcrumb();
    }
    ?>

    <?php do_action('ecolitio_single_product_summary'); ?>
    <div class="prices-container flex flex-row justify-between h-fit items-center">
        <?php if ($product->get_slug() !== 'bateria-sabway') : ?>
            <?php do_action('ecolitio_single_product_prices'); ?>
        <?php endif; ?>
    </div>
</div>