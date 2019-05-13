<?php

$price_model = new shopPricePluginModel();
$prices = $price_model->getAll();

try {
    foreach ($prices as $price) {
        $price_model->deleteById($price['id']);
    }
} catch (waDbException $e) {
    
}