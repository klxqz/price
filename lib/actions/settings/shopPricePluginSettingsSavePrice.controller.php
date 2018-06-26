<?php

class shopPricePluginSettingsSavePriceController extends waJsonController {

    public function execute() {
        try {
            $price_model = new shopPricePluginModel();
            $price = waRequest::post('price', array(), waRequest::TYPE_ARRAY);
            if (empty($price)) {
                throw new waException('Ошибка передачи данных');
            }
            if (!empty($price['id'])) {
                $price_model->updatePriceById($price['id'], $price);
            } else {
                $id = $price_model->insertPrice($price);
                $price['id'] = $id;
            }
            $price = $price_model->getById($price['id']);
            $price_params_model = new shopPricePluginParamsModel();
            $params = $price_params_model->getByField('price_id', $price['id'], true);
            foreach ($params as $param) {
                $price['route_hash'][] = $param['route_hash'];
                $price['category_id'][] = $param['category_id'];
            }
            $price['route_hash'] = array_unique($price['route_hash']);
            $price['category_id'] = array_unique($price['category_id']);
            $this->response['price'] = $price;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
