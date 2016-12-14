<?php

try {
    $files = array(
        'plugins/price/templates/actions/backend/BackendOrderEdit.html',
    );

    foreach ($files as $file) {
        waFiles::delete(wa()->getAppPath($file, 'shop'), true);
    }
} catch (Exception $e) {
    
}