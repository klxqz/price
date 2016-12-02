<?php

class shopPricePluginSettingsSavePriceController extends waJsonController {

    public function execute() {
        try {
            $price_model = new shopPricePluginModel();
            $price = waRequest::post('price', array());

            if (!empty($price['id'])) {
                $price_model->updateById($price['id'], $price);
            } else {
                $id = $price_model->insert($price);
                $price['id'] = $id;
                $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_{$id}` DECIMAL( 15, 4 ) NOT NULL";
                $price_model->query($sql);
            }

            $this->response['price'] = $price_model->getById($price['id']);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
