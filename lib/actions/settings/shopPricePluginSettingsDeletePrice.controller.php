<?php

class shopPricePluginSettingsDeletePriceController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if (!$id) {
                throw new waException('Не указан идентификатор');
            }
            $price_model = new shopPricePluginModel();
            $price_model->deleteById($id);
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
