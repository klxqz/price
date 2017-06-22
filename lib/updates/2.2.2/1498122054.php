<?php

try {
    $files = array(
        'plugins/price/lib/actions/settings/shopPricePluginSettingsdSort.controller.php',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}