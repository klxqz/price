<?php

$model = new waModel();

try {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_price_discount` (
                `product_sku` varchar(255) NOT NULL,
                `discount` decimal(15,4) NOT NULL,
                KEY `product_sku` (`product_sku`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}


