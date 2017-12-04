<?php

$price_model = new shopPricePluginModel();
$prices = $price_model->getAll();
foreach ($prices as $price) {
    $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_type_" . (int) $price['id'] . "` ENUM( '', '%', '+' ) NOT NULL DEFAULT ''";
    $price_model->query($sql);
}


