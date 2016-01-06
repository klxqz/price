<?php

$price_model = new shopPricePluginModel();
$prices = $price_model->getAll();


try {
    foreach ($prices as $price) {
        $sql = "ALTER TABLE `shop_product_skus` DROP `price_plugin_" . $price_model->escape($price['id']) . "`";
        $price_model->query($sql);
        
        $price_model->deleteById($price['id']);
    }
} catch (waDbException $e) {
    
}