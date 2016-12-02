<?php

class shopPricePluginSettingsDeletePriceController extends waJsonController {

    public function execute() {
        try {
            if ($id = waRequest::post('id')) {
                $price_model = new shopPricePluginModel();
                $price_model->deleteById($id);
                $sql = "ALTER TABLE `shop_product_skus` DROP `price_plugin_" . $price_model->escape($id) . "`";
                $price_model->query($sql);
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
