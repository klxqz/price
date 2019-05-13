<?php

return array(
    'shop_price' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'purchase_price_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        'markup' => array('decimal', "15,4", 'null' => 0),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_price_purchase' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
        ),
    ),
    'shop_price_params' => array(
        'price_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        'route_hash' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'category_id' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'price_id' => array('price_id', 'route_hash', 'category_id'),
        ),
    ),
    'shop_price_discount' => array(
        'product_sku' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'discount' => array('decimal', "15,4", 'null' => 0),
        ':keys' => array(
            'product_sku' => 'product_sku',
        ),
    ),
);
