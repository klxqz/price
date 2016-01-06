<?php

$model = new waModel();

try {
    $sql = "TRUNCATE TABLE `shop_price`";
    $model->query($sql);
} catch (waDbException $e) {
    
}

//удаление устаревших полей
try {
    $sql = "ALTER TABLE `shop_price` DROP `price`";
    $model->query($sql);
} catch (waDbException $e) {
    
}
try {
    $sql = "ALTER TABLE `shop_price` DROP `sku_id`";
    $model->query($sql);
} catch (waDbException $e) {
    
}
try {
    $sql = "ALTER TABLE `shop_price` DROP `product_id`";
    $model->query($sql);
} catch (waDbException $e) {
    
}

//удаление первичного ключа
try {
    $sql = "ALTER TABLE `shop_price` DROP PRIMARY KEY";
    $model->query($sql);
} catch (waDbException $e) {
    
}

//добавление нового первичного ключа
try {
    $sql = "ALTER TABLE `shop_price` ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
    $model->query($sql);
} catch (waDbException $e) {
    
}

try {
    $sql = "ALTER TABLE `shop_price` ADD `name` VARCHAR( 255 ) NOT NULL";
    $model->query($sql);
} catch (waDbException $e) {
    
}

try {
    $files = array(
        'plugins/price/lib/actions/backend/shopPricePluginBackendSaveProduct.controller.php',
        'plugins/price/lib/actions/backend/shopPricePluginBackendProduct.action.php',
        'plugins/price/templates/actions/backend/BackendProduct.html',
        'plugins/price/templates/BackendProduct.html',
        'plugins/price/js/loadcontent.js',
        'plugins/price/js/price.js',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}