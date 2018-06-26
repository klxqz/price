<?php

class shopPricePluginSettingsGetPriceController extends waJsonController {

    public function execute() {
        try {
            $id = waRequest::post('id', 0, waRequest::TYPE_INT);
            if (!$id) {
                throw new waException('Ошибка передачи данных');
            }
            $price_model = new shopPricePluginModel();
            $price = $price_model->getById($id);
            $price_params_model = new shopPricePluginParamsModel();
            $params = $price_params_model->getByField('price_id', $id, true);
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
