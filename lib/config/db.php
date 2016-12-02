<?php

return array(
    'shop_price' => array(
        'id' => array('int', 11, 'null' => 0, 'autoincrement' => 1),
        'route_hash' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'category_id' => array('int', 11, 'null' => 0),
        'name' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'sort' => array('int', 11, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'PRIMARY' => 'id',
            'route_hash' => 'route_hash',
            'category_id' => 'category_id',
        ),
    ),
);
