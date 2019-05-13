<?php

class shopPricePluginSettingsSavePriceController extends waJsonController
{

    public function execute()
    {
        try {
            $price_model = new shopPricePluginModel();
            $price = waRequest::post('price', array(), waRequest::TYPE_ARRAY);
            if (empty($price)) {
                throw new waException('Ошибка передачи данных');
            }
            if (!empty($price['id'])) {
                $price_model->checkData($price);
                $price_model->updateById($price['id'], $price);
                $price_model->insertParams($price['id'], $price['route_hash'], $price['category_id']);
            } else {
                $price_model->checkData($price);
                $id = $price_model->insert($price);
                $price_model->insertParams($id, $price['route_hash'], $price['category_id']);

                $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_{$id}` DECIMAL(15,4) NOT NULL DEFAULT '0.0000';";
                $price_model->query($sql);
                $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_type_{$id}` ENUM( '', '%', '+' ) NOT NULL DEFAULT '';";
                $price_model->query($sql);

                $price['id'] = $id;
            }
            $price = $price_model->getById($price['id']);
            $price['markup'] = (float)$price['markup'];
            $price_params_model = new shopPricePluginParamsModel();
            $params = $price_params_model->getByField('price_id', $price['id'], true);
            foreach ($params as $param) {
                $price['route_hash'][] = $param['route_hash'];
                $price['category_id'][] = $param['category_id'];
            }
            $price['route_hash'] = array_unique($price['route_hash']);
            $price['category_id'] = array_unique($price['category_id']);

            $price_purchase_model = new shopPricePurchasePluginModel();
            $purchase_prices = $price_purchase_model->getAll('id');
            if (!empty($purchase_prices[$price['purchase_price_id']])) {
                $price['purchase_price_name'] = $purchase_prices[$price['purchase_price_id']]['name'];
            } else {
                $price['purchase_price_name'] = 'Стандартная закупочная цена';
            }

            $this->response['price'] = $price;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
