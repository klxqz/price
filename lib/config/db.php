<?php

return array(
    'shop_price' => array(
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
);
