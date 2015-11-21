<?php

return array(
    'name' => 'Мульти Цены (Оптовые цены)',
    'description' => 'Поддержка различных типов цен для разных групп пользователей',
    'vendor' => 985310,
    'version' => '1.0.0',
    'img' => 'img/price.png',
    'shop_settings' => true,
    'frontend' => true,
    'handlers' => array(
        'backend_product' => 'backendProduct',
        'frontend_product' => 'frontendProduct',
        'frontend_category' => 'frontendCategory',
        'frontend_search' => 'frontendSearch',
        'frontend_cart' => 'frontendCart',
        'frontend_checkout' => 'frontendCheckout',
        'routing' => 'routing',
        'order_action.create' => 'orderActionCreate',
    ),
);
//EOF
