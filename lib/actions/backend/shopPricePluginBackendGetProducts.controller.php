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

        if (!$currency) {
            $order_model = new shopOrderModel();
            $order = $order_model->getOrder($order_id);
            $currency = $order['currency'];
        }

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

            $min_price = null;
            $max_price = null;
            foreach ($product['skus'] as &$sku) {
                if (isset($sku['price'])) {
                    $sku['price_str'] = wa_currency($sku['price'], $currency);
                    $sku['price_html'] = wa_currency_html($sku['price'], $currency);
                }
                if (is_null($min_price) || $sku['price'] < $min_price) {
                    $min_price = $sku['price'];
                }
                if (is_null($max_price) || $sku['price'] > $max_price) {
                    $max_price = $sku['price'];
                }
            }
            unset($sku);

            $product['min_price'] = $min_price;
            $product['max_price'] = $max_price;

            if ($product['min_price'] == $product['max_price']) {
                $product['price_str'] = wa_currency($product['min_price'], $currency);
                $product['price_html'] = wa_currency_html($product['min_price'], $currency);
            } else {
                $product['price_str'] = wa_currency($product['min_price'], $currency) . '...' . wa_currency($product['max_price'], $currency);
                $product['price_html'] = wa_currency_html($product['min_price'], $currency) . '...' . wa_currency_html($product['max_price'], $currency);
            }

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
