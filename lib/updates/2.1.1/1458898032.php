<?php

$model = new waModel();

try {
    $sql = 'SELECT `sort` FROM `shop_price` WHERE 0';
    $model->query($sql);
} catch (waDbException $ex) {
    $sql = "ALTER TABLE `shop_price` ADD `sort` INT NOT NULL DEFAULT '0'";
    $model->query($sql);
}

try {
    $sql = "SELECT * FROM `shop_price`";
    $prices = $model->query($sql)->fetchAll();
    foreach ($prices as $i => $price) {
        $sql = "UPDATE `shop_price` SET `sort` = {$i} WHERE `id` = {$price['id']}";
        $model->query($sql);
    }
} catch (waDbException $ex) {
    
}