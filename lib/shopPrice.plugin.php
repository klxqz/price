<?php

class shopPricePlugin extends shopPlugin {

    public static $plugin_id = array('shop', 'price');
    public static $default_settings = array(
        'status' => 1,
    );

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
        if (empty($category_ids)) {
            $category_ids[] = 0;
        }
        return $category_ids;
    }

    public static function prepareProducts($products = array()) {
        $app_settings_model = new waAppSettingsModel();
        if ($app_settings_model->get(self::$plugin_id, 'status') && shopPrice::getDomainSetting('status')) {
            $category_ids = self::getUserCategoryId();
            $domain_hash = shopPrice::getRouteHash();
            $params = array(
                'domain_hash' => $domain_hash,
                'category_id' => $category_ids,
            );
            $price_model = new shopPricePluginModel();
            $price = $price_model->getPriceByParams($params, false);

            if ($price) {
                $price_field = "price_plugin_{$price['id']}";
                $def_currency = wa('shop')->getConfig()->getCurrency(true);
                $sku_model = new shopProductSkusModel();

                foreach ($products as &$product) {
                    $sku = $sku_model->getById($product['sku_id']);
                    if (!empty($sku[$price_field]) && $sku[$price_field] > 0) {
                        if (!empty($product['unconverted_currency'])) {
                            $product['price'] = shop_currency($sku[$price_field], $product['unconverted_currency'], $def_currency, false);
                        } elseif (!empty($product['currency'])) {
                            $product['price'] = shop_currency($sku[$price_field], $product['currency'], $def_currency, false);
                        }
                    }
                }
                unset($product);
            }
        }
        return $products;
    }

    public static function prepareSkus($skus = array()) {
        $app_settings_model = new waAppSettingsModel();
        if ($app_settings_model->get(self::$plugin_id, 'status') && shopPrice::getDomainSetting('status')) {
            $category_ids = self::getUserCategoryId();
            $domain_hash = shopPrice::getRouteHash();
            $params = array(
                'domain_hash' => $domain_hash,
                'category_id' => $category_ids,
            );
            $price_model = new shopPricePluginModel();
            $price = $price_model->getPriceByParams($params, false);
            if ($price) {
                $price_field = "price_plugin_{$price['id']}";
                $def_currency = wa('shop')->getConfig()->getCurrency(true);
                foreach ($skus as &$sku) {
                    if (!empty($sku[$price_field]) && $sku[$price_field] > 0) {
                        $sku['price'] = $sku[$price_field];
                    }
                }
                unset($sku);
            }
        }
        return $skus;
    }

    public function frontendProducts(&$params) {
        if ($this->getSettings('status') && shopPrice::getDomainSetting('status')) {
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
                $_prices[$price['domain_hash']][] = $price;
            }

            $view = wa()->getView();
            $view->assign('product', $product);
            $view->assign('sku', $sku);
            $view->assign('domains', $this->getDomains());
            $view->assign('prices', $_prices);
            $html = $view->fetch('plugins/price/templates/BackendProductSkuSettings.html');
            return $html;
        }
    }

    public function productCustomFields() {
        if ($this->getSettings('status')) {
            $domains = $this->getDomains();

            $price_model = new shopPricePluginModel();
            $prices = $price_model->getAll();

            $sku_fields = array();

            foreach ($prices as $price) {
                $field = 'price_plugin_' . $price['id'];
                $field_name = $price['name'] . " (" . $domains[$price['domain_hash']] . ")";
                $sku_fields[$field] = $field_name;
            }

            return array(
                'sku' => $sku_fields,
            );
        }
    }

    private function getDomains() {
        $domain_routes = wa()->getRouting()->getByApp('shop');
        $domains = array();
        foreach ($domain_routes as $domain => $routes) {
            foreach ($routes as $route) {
                $domain_route = "{$domain}/{$route['url']}";
                $domain_hash = md5($domain_route);
                $domains[$domain_hash] = $domain_route;
            }
        }
        return $domains;
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

}
