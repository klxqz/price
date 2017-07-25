<?php

$model = new waModel();

try {
    $sql = "ALTER TABLE `shop_price` DROP `route_hash`, DROP `category_id`;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $sql = "CREATE TABLE IF NOT EXISTS `shop_price_params` (
            `price_id` int(11) NOT NULL,
            `route_hash` varchar(32) NOT NULL,
            `category_id` int(11) NOT NULL,
            UNIQUE KEY `price_id` (`price_id`,`route_hash`,`category_id`)
          ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

try {
    $files = array(
        'plugins/price/lib/actions/settings/shopPricePluginSettingsRoute.action.php',
        'plugins/price/templates/actions/settings/SettingsRoute.html',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}