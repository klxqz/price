<?php

class shopPricePluginBackendSaveProductController extends waJsonController {

    public function execute() {
        try {
            $price_model = new shopPricePluginModel();
            $prices = waRequest::post('prices', array());
            $product = waRequest::post('product', array());

            if (!empty($product['id'])) {
                foreach ($prices as $domain_hash => $domain_prices) {
                    foreach ($domain_prices as $category_id => $skus) {
                        foreach ($skus as $sku_id => $price) {
                            $key = array(
                                'domain_hash' => $domain_hash,
                                'category_id' => $category_id,
                                'product_id' => $product['id'],
                                'sku_id' => $sku_id,
                            );
                            if ($price) {
                                $data = array(
                                    'price' => $price,
                                );
                                $row = array_merge($key, $data);
                                if ($price_model->getByField($key)) {
                                    $price_model->updateByField($key, $data);
                                } else {
                                    $price_model->insert($row);
                                }
                            } elseif ($price_model->getByField($key)) {
                                $price_model->deleteByField($key);
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
