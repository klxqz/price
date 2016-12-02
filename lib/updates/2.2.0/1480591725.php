<?php

$model = new waModel();
try {
    $sql = "ALTER TABLE `shop_price` CHANGE `domain_hash` `route_hash` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
    $model->query($sql);
} catch (waDbException $ex) {
    
}


try {
    $files = array(
        'plugins/price/lib/actions/backend/shopPricePluginBackendSave.controller.php',
        'plugins/price/lib/actions/backend/shopPricePluginBackendDeletePrice.controller.php',
        'plugins/price/lib/classes/shopPrice.class.php',
        'plugins/price/lib/actions/backend/shopPricePluginBackendSavePrice.controller.php',
        'plugins/price/lib/actions/backend/shopPricePluginBackendSort.controller.php',
        'plugins/price/lib/actions/shopPricePluginSettings.action.php',
        'plugins/price/templates/BackendProductSkuSettings.html',
        'plugins/price/templates/BackendOrderEdit.html',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}