<?php

return array(
    'name' => 'Мульти Цены (Оптовые цены)',
    'description' => 'Поддержка различных типов цен для разных групп пользователей',
    'vendor' => 985310,
    'version' => '2.0.0',
    'img' => 'img/price.png',
    'shop_settings' => true,
    'frontend' => true,
    'handlers' => array(
        'frontend_products' => 'frontendProducts',
        'backend_product_sku_settings' => 'backendProductSkuSettings',
        'product_custom_fields' => 'productCustomFields',
    ),
);
//EOF
