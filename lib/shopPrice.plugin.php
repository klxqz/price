<?php

class shopPricePlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'price');

    public function saveSettings($settings = array()) {
        $route_hash = waRequest::post('route_hash');
        $route_settings = waRequest::post('route_settings');

        if ($routes = $this->getSettings('routes')) {
            $settings['routes'] = $routes;
        } else {
            $settings['routes'] = array();
        }
        $settings['routes'][$route_hash] = $route_settings;
        $settings['route_hash'] = $route_hash;
        parent::saveSettings($settings);
    }

    public static function getUserCategoryId($contact_id = null) {
        if ($contact_id === null) {
            $contact_id = wa()->getUser()->getId();
        }
        $model = new waModel();
        $sql = "SELECT * FROM `wa_contact_categories` WHERE `contact_id` = '" . $model->escape($contact_id) . "'";
        $categories = $model->query($sql)->fetchAll();
        $category_ids = array();
        $category_ids[] = 0;
        foreach ($categories as $category) {
            $category_ids[] = $category['category_id'];
        }
        return $category_ids;
    }

    public static function prepareProducts($products = array(), $contact_id = null, $currency = null, $storefront = null, $price_id = null) {
        $app_settings_model = new waAppSettingsModel();
        $route_hash = shopPriceRouteHelper::getRouteHash($storefront);
        if ($app_settings_model->get(self::$plugin_id, 'status') && (shopPriceRouteHelper::getRouteSettings($route_hash, 'status') || $price_id)) {
            $price_model = new shopPricePluginModel();
            if ($price_id) {
                $prices = array($price_model->getById($price_id));
            } else {
                $category_ids = self::getUserCategoryId($contact_id);
                $params = array(
                    'route_hash' => $route_hash,
                    'category_id' => $category_ids,
                );
                $prices = $price_model->getPriceByParams($params, true);
            }
            if ($prices) {
                if (!$currency) {
                    $currency = wa('shop')->getConfig()->getCurrency(true);
                }
                $frontend_currency = wa('shop')->getConfig()->getCurrency(false);
                $sku_model = new shopProductSkusModel();
                foreach ($products as &$product) {
                    foreach ($prices as $price) {
                        $price_field = "price_plugin_{$price['id']}";
                        $sku = $sku_model->getById($product['sku_id']);
                        if (!empty($sku[$price_field]) && $sku[$price_field] > 0) {
                            if ($product['compare_price'] > 0 && $product['compare_price'] < $sku[$price_field]) {
                                $product['compare_price'] = 0;
                            }
                            if (wa()->getEnv() == 'backend') {
                                $product['price'] = shop_currency($sku[$price_field], $product['currency'], $currency, false);
                            } else {
                                $price = $sku[$price_field];
                                if (!empty($product['unconverted_currency'])) {
                                    $product_currency = $product['unconverted_currency'];
                                } else {
                                    $product_currency = $product['currency'];
                                }
                                $price = shop_currency($price, $product_currency, $frontend_currency, false);
                                $price = shopRounding::roundCurrency($price, $frontend_currency);
                                $product['price'] = shop_currency($price, $frontend_currency, $currency, false);
                            }
                            break;
                        }
                    }
                }
                unset($product);
            }
        }
        return $products;
    }

    public static function prepareSkus($skus = array(), $contact_id = null, $currency = null, $storefront = null, $price_id = null) {
        $app_settings_model = new waAppSettingsModel();
        $route_hash = shopPriceRouteHelper::getRouteHash($storefront);
        if ($app_settings_model->get(self::$plugin_id, 'status') && (shopPriceRouteHelper::getRouteSettings($route_hash, 'status') || $price_id)) {
            $price_model = new shopPricePluginModel();
            if ($price_id) {
                $prices = array($price_model->getById($price_id));
            } else {
                $category_ids = self::getUserCategoryId($contact_id);
                $params = array(
                    'route_hash' => $route_hash,
                    'category_id' => $category_ids,
                );
                $prices = $price_model->getPriceByParams($params, true);
            }

            if ($prices) {
                if (!$currency) {
                    $currency = wa('shop')->getConfig()->getCurrency(true);
                }
                foreach ($skus as &$sku) {
                    foreach ($prices as $price) {
                        $price_field = "price_plugin_{$price['id']}";
                        if (!empty($sku[$price_field]) && $sku[$price_field] > 0) {
                            if ($sku['compare_price'] > 0 && $sku['compare_price'] < $sku['price']) {
                                $sku['compare_price'] = 0;
                            }
                            $product_model = new shopProductModel();
                            $product = $product_model->getById($sku['product_id']);
                            if (wa()->getEnv() == 'backend') {
                                $sku['price'] = shop_currency($sku[$price_field], $product['currency'], $currency, false);
                            } else {
                                $sku['price'] = $sku[$price_field];
                                if (!empty($sku['unconverted_currency'])) {
                                    unset($sku['unconverted_currency']);
                                    $round_skus = array($sku['id'] => $sku);
                                    shopRounding::roundSkus($round_skus);
                                    $sku = array_pop($round_skus);
                                }
                            }
                            break;
                        }
                    }
                }
                unset($sku);
            }
        }
        return $skus;
    }

    public function frontendProducts(&$params) {
        if ($this->getSettings('status')) {
            if (!empty($params['products'])) {
                $params['products'] = $this->prepareProducts($params['products']);
            }
            if (!empty($params['skus'])) {
                $params['skus'] = $this->prepareSkus($params['skus']);
            }
        }
    }

    public function backendProductSkuSettings($params) {
        if ($this->getSettings('status')) {
            $product = $params['product'];
            $sku = $params['sku'];

            $price_model = new shopPricePluginModel();
            $prices = $price_model->getAll();
            $_prices = array();
            foreach ($prices as $price) {
                $_prices[$price['route_hash']][] = $price;
            }

            $view = wa()->getView();
            $view->assign('product', $product);
            $view->assign('sku', $sku);
            $view->assign('routes', $this->getRoutes());
            $view->assign('prices', $_prices);
            $view->assign('sku_id', $params['sku_id']);
            $html = $view->fetch('plugins/price/templates/actions/backend/BackendProductSkuSettings.html');
            return $html;
        }
    }

    public function productCustomFields() {
        if ($this->getSettings('status')) {
            $routes = $this->getRoutes();

            $price_model = new shopPricePluginModel();
            $prices = $price_model->getAll();

            $sku_fields = array();

            foreach ($prices as $price) {
                if (!empty($routes[$price['route_hash']])) {
                    $field = 'price_plugin_' . $price['id'];
                    $field_name = $price['name'] . " (" . $routes[$price['route_hash']] . ")";
                    $sku_fields[$field] = $field_name;
                }
            }

            return array(
                'sku' => $sku_fields,
            );
        }
    }

    private function getRoutes() {
        $routes = array();
        $route_hashs = shopPriceRouteHelper::getRouteHashs();
        foreach ($route_hashs as $route => $route_hash) {
            $routes[$route_hash] = $route;
        }
        return $routes;
    }

    public function productSave($params) {
        if ($this->getSettings('status')) {
            $sku_model = new shopProductSkusModel();
            if (!empty($params['data']['skus'])) {
                foreach ($params['data']['skus'] as $sku) {
                    if (!empty($sku['price_plugin'])) {
                        $sku_model->updateById($sku['id'], $sku['price_plugin']);
                    }
                }
            }
        }
    }

    public function backendOrderEdit($order) {
        if ($this->getSettings('status')) {          
            $price_model = new shopPricePluginModel();
            $prices = $price_model->getAll();
            $_prices = array();
            foreach ($prices as $price) {
                $_prices[$price['route_hash']][] = $price;
            }
            $view = wa()->getView();
            $view->assign(array(
                'plugin_url' => $this->getPluginStaticUrl(),
                'version' => $this->getVersion(),
                'prices' => $_prices,
                'routes' => $this->getRoutes(),
            ));
            $html = $view->fetch('plugins/price/templates/actions/backend/BackendOrderEdit.html');
            return $html;
        }
    }

}
