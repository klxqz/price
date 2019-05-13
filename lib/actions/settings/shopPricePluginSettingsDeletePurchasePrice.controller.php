<?php

class shopPricePluginSettingsDeletePurchasePriceController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if (!$id) {
                throw new waException('Ошибка передачи данных');
            }
            $price_purchase_model = new shopPricePurchasePluginModel();
            $price_purchase_model->deleteById($id);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
