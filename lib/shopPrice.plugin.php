<?php

class shopPricePlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'price');
    public static $default_settings = array(
        'status' => 1,
        'prices' => array(),
    );

    public function backendProduct($product) {
        if ($this->getSettings('status')) {
            $view = wa()->getView();
            $view->assign('product', $product);
            $html = $view->fetch('plugins/price/templates/BackendProduct.html');
            return array('edit_section_li' => $html);
        }
    }

    public function frontendProduct($product) {
        if ($this->getSettings('status') && shopPrice::getDomainSetting('status')) {
            $price_model = new shopPricePluginModel();

            $category_ids = self::getUserCategoryId();

            $domain_hash = shopPrice::getRouteHash();
            $params = array(
                'domain_hash' => $domain_hash,
                'product_id' => $product->id,
                'category_id' => $category_ids,
            );
            $prices = $price_model->getPriceByParams($params);

            if ($prices) {
                $view = wa()->getView();

                //замена цен для артикулов по параметрам
                $sku_features_selectable = $view->getVars('sku_features_selectable');
                if ($sku_features_selectable) {
                    foreach ($sku_features_selectable as &$_sku_features_selectable) {
                        $sku_id = $_sku_features_selectable['id'];
                        if (!empty($prices[$sku_id])) {
                            $_sku_features_selectable['price'] = $prices[$sku_id]['price'];
                        }
                    }
                    unset($_sku_features_selectable);

                    $view->assign('sku_features_selectable', $sku_features_selectable);
                }

                //замена базовой цены товара
                if (!empty($prices[$product->sku_id])) {
                    $product->price = $prices[$product->sku_id]['price'];
                }

                //замена цены артикулов
                $skus = $product->skus;
                foreach ($skus as $sku_id => &$sku) {
                    if (!empty($prices[$sku_id])) {
                        $sku['price'] = $prices[$sku_id]['price'];
                    }
                }
                unset($sku);
                $product->skus = $skus;
            }
        }
    }

    public function frontendCategory() {
        $this->fixProducts();
    }

    public function frontendSearch() {
        $this->fixProducts();
    }

    public function frontendCart() {

        if ($this->getSettings('status') && shopPrice::getDomainSetting('status')) {
            $view = wa()->getView();
            $cart = $view->getVars('cart');
            $items = $cart['items'];
            $saldo = 0;

            $price_model = new shopPricePluginModel();
            $domain_hash = shopPrice::getRouteHash();
            $category_ids = self::getUserCategoryId();
            foreach ($items as &$item) {
                if ($item['type'] == 'product') {
                    $params = array(
                        'domain_hash' => $domain_hash,
                        'sku_id' => $item['sku_id'],
                        'category_id' => $category_ids,
                    );

                    $price = $price_model->getPriceByParams($params, null, false);
                    if ($price) {
                        $saldo += $item['full_price'] - $item['quantity'] * $price['price'];
                        $item['price'] = $price['price'];
                        $item['full_price'] = $item['quantity'] * $item['price'];
                    }
                }
            }
            unset($item);

            $cart['items'] = $items;
            $cart['total'] -= $saldo;

            $view->assign('cart', $cart);
        }
    }

    public function frontendCheckout($params) {
        if ($this->getSettings('status') && shopPrice::getDomainSetting('status') && $params['step'] == 'confirmation') {
            $view = wa()->getView();
            $items = $view->getVars('items');
            $saldo = 0;

            $price_model = new shopPricePluginModel();
            $domain_hash = shopPrice::getRouteHash();
            $category_ids = self::getUserCategoryId();
            foreach ($items as &$item) {
                if ($item['type'] == 'product') {
                    $params = array(
                        'domain_hash' => $domain_hash,
                        'sku_id' => $item['sku_id'],
                        'category_id' => $category_ids,
                    );

                    $price = $price_model->getPriceByParams($params, null, false);
                    if ($price) {
                        $saldo += $item['price'] * $item['quantity'] - $price['price'] * $item['quantity'];
                        $item['price'] = $price['price'];
                    }
                }
            }
            unset($item);
            $subtotal = $view->getVars('subtotal');
            $total = $view->getVars('total');
            $subtotal -= $saldo;
            $total -= $saldo;
            $view->assign('items', $items);
            $view->assign('subtotal', $subtotal);
            $view->assign('total', $total);
        }
    }

    public function routing($route = array()) {
        if ($this->getSettings('status') && shopPrice::getDomainSetting('status')) {
            return array(
                'cart/add/' => 'frontend/cartAdd',
                'cart/save/' => 'frontend/cartSave',
                'cart/delete/' => 'frontend/cartDelete',
            );
        }
    }

    public function orderActionCreate($params) {
        if ($this->getSettings('status') && shopPrice::getDomainSetting('status')) {
            $order_model = new shopOrderModel();
            $order_items_model = new shopOrderItemsModel();

            $data = $order_model->getById($params['order_id']);
            $data['contact'] = new waContact($params['contact_id']);
            $data['items'] = $order_items_model->getItems($params['order_id']);

            $category_ids = self::getUserCategoryId($params['contact_id']);
            $domain_hash = shopPrice::getRouteHash();
            $price_model = new shopPricePluginModel();

            foreach ($data['items'] as &$item) {
                $params = array(
                    'domain_hash' => $domain_hash,
                    'sku_id' => $item['sku_id'],
                    'category_id' => $category_ids,
                );
                $price = $price_model->getPriceByParams($params, null, false);
                if ($price) {
                    $item['price'] = $price['price'];
                }
            }
            $data['discount'] = shopDiscounts::calculate($data);

            $workflow = new shopWorkflow();
            $workflow->getActionById('edit')->run($data);
        }
    }

    public static function getUserCategoryId($contact_id = null) {
        if (!$contact_id) {
            $contact_id = wa()->getUser()->getId();
        }
        $model = new waModel();
        $sql = "SELECT * FROM `wa_contact_categories` WHERE `contact_id` = '" . $model->escape($contact_id) . "'";
        $categories = $model->query($sql)->fetchAll();
        $category_ids = array();
        foreach ($categories as $category) {
            $category_ids[] = $category['category_id'];
        }
        return $category_ids;
    }

    private function fixProducts() {
        if ($this->getSettings('status') && shopPrice::getDomainSetting('status')) {
            $view = wa()->getView();
            $products = $view->getVars('products');
            $products = self::prepareProducts($products);
            $view->assign('products', $products);
        }
    }

    public static function prepareProducts($products) {
        $app_settings_model = new waAppSettingsModel();
        if ($app_settings_model->get(self::$plugin_id, 'status') && shopPrice::getDomainSetting('status')) {
            $sku_ids = array();
            foreach ($products as $product) {
                $sku_ids[] = $product['sku_id'];
            }

            $category_ids = self::getUserCategoryId();
            $domain_hash = shopPrice::getRouteHash();
            $params = array(
                'domain_hash' => $domain_hash,
                'sku_id' => $sku_ids,
                'category_id' => $category_ids,
            );
            $price_model = new shopPricePluginModel();
            $prices = $price_model->getPriceByParams($params);
            foreach ($products as &$product) {
                $sku_id = $product['sku_id'];
                if (!empty($prices[$sku_id])) {
                    $product['price'] = $prices[$sku_id]['price'];
                }
            }
            unset($product);
        }

        return $products;
    }

    public static function fixTotalCart($discount = true) {
        $cart = new shopCart();
        $items = $cart->items();

        $price_model = new shopPricePluginModel();
        $domain_hash = shopPrice::getRouteHash();
        $category_ids = shopPricePlugin::getUserCategoryId();
        $saldo = 0;

        foreach ($items as $item) {
            if ($item['type'] == 'product') {
                $params = array(
                    'domain_hash' => $domain_hash,
                    'sku_id' => $item['sku_id'],
                    'category_id' => $category_ids,
                );

                $price = $price_model->getPriceByParams($params, null, false);
                if ($price) {
                    $saldo += $item['price'] * $item['quantity'] - $price['price'] * $item['quantity'];
                }
            }
        }
        unset($item);

        $total = $cart->total($discount) - $saldo;
        return $total;
    }

}
