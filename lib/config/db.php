<?php

return array(
    'shop_price' => array(
        'domain_hash' => array('varchar', 255, 'null' => 0, 'default' => ''),
        'category_id' => array('int', 11, 'null' => 0),
        'product_id' => array('int', 11, 'null' => 0),
        'sku_id' => array('int', 11, 'null' => 0),
        'price' => array('decimal', "15,4", 'null' => 0),
        ':keys' => array(
            'PRIMARY' => array('domain_hash', 'category_id', 'product_id', 'sku_id'),
            'domain_hash' => 'domain_hash',
            'category_id' => 'category_id',
            'product_id' => 'product_id',
            'sku_id' => 'sku_id',
        ),
    ),
);
