<?php

$price_model = new shopPricePluginModel();

try {
    $sql = "ALTER TABLE `shop_price` ADD `currency` CHAR( 3 ) NULL DEFAULT NULL AFTER `name`";
    $price_model->query($sql);
} catch (waDbException $ex) {
    
}



$prices = $price_model->getAll();
foreach ($prices as $price) {
    try {
        $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_currency_" . (int) $price['id'] . "` CHAR( 3 ) NULL DEFAULT NULL ";
        $price_model->query($sql);
    } catch (waDbException $ex) {
        
    }
}

