<?php

class shopPricePluginBackendGetProductsController extends shopOrdersGetProductController {

    public function execute() {
        $price_id = waRequest::post('price_id', null, waRequest::TYPE_INT);
        $order_id = waRequest::post('order_id', null, waRequest::TYPE_INT);
        $customer_id = waRequest::post('customer_id', null, waRequest::TYPE_INT);
        $order_id = $order_id ? $order_id : null;
        $currency = waRequest::post('currency');
        $storefront = waRequest::post('storefront');

        $_products = waRequest::post('product');
        $product_ids = array();

        if (!empty($_products['edit'])) {
            foreach ($_products['edit'] as $product_id) {
                $product_ids[] = $product_id;
            }
        }
        if (!empty($_products['add'])) {
            foreach ($_products['add'] as $product_id) {
                $product_ids[] = $product_id;
            }
        }

        if (!$product_ids) {
            return;
        }

        $products = array();
        foreach ($product_ids as $product_id) {
            $products[$product_id] = $this->getProduct($product_id, $order_id);
        }

        $response = array();

        if ($price_id !== 0) {
            $products = shopPricePlugin::prepareProducts($products, $customer_id, $currency, $storefront, $price_id);
        }
        foreach ($products as &$product) {
            if ($price_id !== 0) {
                $product['skus'] = shopPricePlugin::prepareSkus($product['skus'], $customer_id, $currency, $storefront, $price_id);
            }
            foreach ($product['skus'] as &$sku) {
                if (isset($sku['price'])) {
                    $sku['price_str'] = wa_currency($sku['price'], $currency);
                    $sku['price_html'] = wa_currency_html($sku['price'], $currency);
                }
            }
            unset($sku);

            $response[$product['id']] = array(
                'product' => $product,
                'sku_ids' => array_keys($product['skus']),
                'service_ids' => array_keys($product['services']),
            );
        }
        unset($product);
        $this->response = $response;
    }

}
