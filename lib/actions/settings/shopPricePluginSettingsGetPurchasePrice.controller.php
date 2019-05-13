<?php

class shopPricePluginSettingsGetPurchasePriceController extends waJsonController
{

    public function execute()
    {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if (!$id) {
                throw new waException('Ошибка передачи данных');
            }
            $price_purchase_model = new shopPricePurchasePluginModel();
            $purchase_price = $price_purchase_model->getById($id);
            $this->response['purchase_price'] = $purchase_price;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
