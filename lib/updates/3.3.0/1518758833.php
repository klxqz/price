<?php

$model = new waModel();

try {
    $sql = "ALTER TABLE `shop_price` ADD `markup` DECIMAL( 15, 4 ) NOT NULL DEFAULT '0.0000' AFTER `name` ;";
    $model->query($sql);
} catch (waDbException $ex) {
    
}

