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
                $price_model->updateById($price['id'], $price);
            } else {
                $id = $price_model->insert($price);
                $price['id'] = $id;
            }
            $price = $price_model->getById($price['id']);
            $price_params_model = new shopPricePluginParamsModel();
            $params = $price_params_model->getByField('price_id', $price['id'], true);
            foreach ($params as $param) {
                $price['route_hash'][$param['route_hash']] = 1;
                $price['category_id'][$param['category_id']] = 1;
            }
            $this->response['price'] = $price;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
