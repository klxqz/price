<?php

class shopPricePluginSettingsPurchasePriceSortController extends waJsonController {

    public function execute() {
        $price_purchase_model = new shopPricePurchasePluginModel();
        $id = waRequest::post('id', 0, waRequest::TYPE_INT);
        $after_id = waRequest::post('after_id', 0, waRequest::TYPE_INT);
        if (!$id || !$after_id) {
            throw new waException('Ошибка передачи данных');
        }
        try {
            $price_purchase_model->move($id, $after_id);
        } catch (waException $e) {
            $this->setError($e->getMessage());
        }
    }

}
