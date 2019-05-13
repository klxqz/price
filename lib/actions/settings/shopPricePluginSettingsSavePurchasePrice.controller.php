<?php

class shopPricePluginSettingsSavePurchasePriceController extends waJsonController {

    public function execute() {
        try {
            $price_purchase_model = new shopPricePurchasePluginModel();
            $purchase_price = waRequest::post('purchase_price', array(), waRequest::TYPE_ARRAY);
            if (empty($purchase_price)) {
                throw new waException('Ошибка передачи данных');
            }
            if (!empty($purchase_price['id'])) {
                $price_purchase_model->updateById($purchase_price['id'], $purchase_price);
            } else {
                $id = $price_purchase_model->insert($purchase_price);


                $sql = "ALTER TABLE `shop_product_skus` ADD `purchase_price_plugin_{$id}` DECIMAL(15,4) NOT NULL DEFAULT '0.0000';";
                $price_purchase_model->query($sql);

                $purchase_price['id'] = $id;
            }
            $purchase_price = $price_purchase_model->getById($purchase_price['id']);
            $this->response['purchase_price'] = $purchase_price;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
