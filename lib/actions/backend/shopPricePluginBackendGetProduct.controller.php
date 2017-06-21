<?php

class shopPricePluginBackendGetProductController extends shopOrdersGetProductController {

    public function execute() {
        $price_id = waRequest::get('price_id', null, waRequest::TYPE_INT);
        $order_id = waRequest::get('order_id', null, waRequest::TYPE_INT);
        $customer_id = waRequest::get('customer_id', null, waRequest::TYPE_INT);
        $order_id = $order_id ? $order_id : null;
        $currency = waRequest::get('currency');
        $storefront = waRequest::get('storefront');

        if (!$currency && $order_id) {
            $order_model = new shopOrderModel();
            $order = $order_model->getOrder($order_id);
            $currency = $order['currency'];
        }

        $product_id = waRequest::get('product_id', 0, waRequest::TYPE_INT);
        if (!$product_id) {
            $this->errors[] = _w("Unknown product");
            return;
        }

        $sku_id = waRequest::get('sku_id', 0, waRequest::TYPE_INT);
        if ($sku_id) {
            $sku = $this->getSku($sku_id, $order_id);
            $skus = shopPricePlugin::prepareSkus(array($sku_id => $sku), $customer_id, $currency, $storefront, $price_id);
            if (!empty($skus[$sku_id])) {
                $sku = $skus[$sku_id];
            }
            $this->response['sku'] = $sku;
            $this->response['service_ids'] = array_keys($sku['services']);
        } else {
            $product = $this->getProduct($product_id, $order_id);
            $products = shopPricePlugin::prepareProducts(array($product_id => $product), $customer_id, $currency, $storefront, $price_id);
            if (!empty($products[$product_id])) {
                $product = $products[$product_id];
            }
            $product['skus'] = shopPricePlugin::prepareSkus($product['skus'], $customer_id, $currency, $storefront, $price_id);

            foreach ($product['skus'] as &$sku) {
                if (isset($sku['price'])) {
                    $sku['price_str'] = wa_currency($sku['price'], $currency);
                    $sku['price_html'] = wa_currency_html($sku['price'], $currency);
                }
            }
            unset($sku);

            $this->response['product'] = $product;
            $this->response['sku_ids'] = array_keys($product['skus']);
            $this->response['service_ids'] = array_keys($product['services']);
        }
    }

}
