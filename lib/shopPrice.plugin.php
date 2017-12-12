<?php

class shopPricePlugin extends shopPlugin {

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
        if (!wa()->getPlugin('price')->getSettings('status') && !$price_id) {
            return $products;
        }
        $route_hash = shopPriceRouteHelper::getRouteHash($storefront);

        $price_model = new shopPricePluginModel();
        if ($price_id) {
            $prices = array($price_model->getById($price_id));
        } else {
            $category_ids = self::getUserCategoryId($contact_id);
            $prices = $price_model->getPrices($route_hash, $category_ids);
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
                    $price_field_type = "price_plugin_type_{$price['id']}";
                    $sku = $sku_model->getById($product['sku_id']);
                    if (isset($sku[$price_field]) && $sku[$price_field] != 0) {
                        if (wa()->getPlugin('price')->getSettings('set_compare_price')) {
                            $product['compare_price'] = $product['price'];
                        } elseif ($product['compare_price'] > 0 && $product['compare_price'] < $sku[$price_field]) {
                            $product['compare_price'] = 0;
                        }
                        $price_value = $sku[$price_field];
                        $price_type = $sku[$price_field_type];
                        if ($price_type == '%') {
                            $price_value = $sku['price'] + $sku['price'] * ($price_value / 100);
                        } elseif ($price_type == '+') {
                            $price_value = $sku['price'] + $price_value;
                        }
                        if (wa()->getEnv() == 'backend') {
                            $product['price'] = shop_currency($price_value, $product['currency'], $currency, false);
                        } else {

                            if (!empty($product['unconverted_currency'])) {
                                $product_currency = $product['unconverted_currency'];
                            } else {
                                $product_currency = $product['currency'];
                            }
                            $price_value = shop_currency($price_value, $product_currency, $frontend_currency, false);
                            $price_value = shopRounding::roundCurrency($price_value, $frontend_currency);
                            $product['price'] = shop_currency($price_value, $frontend_currency, $currency, false);
                        }
                        break;
                    }
                }
            }
            unset($product);
        }

        return $products;
    }

    public static function prepareSkus($skus = array(), $contact_id = null, $currency = null, $storefront = null, $price_id = null) {
        if (!wa()->getPlugin('price')->getSettings('status') && !$price_id) {
            return $skus;
        }
        $route_hash = shopPriceRouteHelper::getRouteHash($storefront);

        $price_model = new shopPricePluginModel();
        if ($price_id) {
            $prices = array($price_model->getById($price_id));
        } else {
            $category_ids = self::getUserCategoryId($contact_id);
            $prices = $price_model->getPrices($route_hash, $category_ids);
        }

        if ($prices) {
            if (!$currency) {
                $currency = wa('shop')->getConfig()->getCurrency(true);
            }
            $product_model = new shopProductModel();
            foreach ($skus as &$sku) {
                foreach ($prices as $price) {
                    $price_field = "price_plugin_{$price['id']}";
                    $price_field_type = "price_plugin_type_{$price['id']}";
                    if (isset($sku[$price_field]) && $sku[$price_field] != 0) {
                        if ($sku['compare_price'] > 0 && $sku['compare_price'] < $sku['price']) {
                            $sku['compare_price'] = 0;
                        }
                        $product = $product_model->getById($sku['product_id']);

                        $price_value = $sku[$price_field];
                        $price_type = $sku[$price_field_type];
                        if ($price_type == '%') {
                            $price_value = $sku['price'] + $sku['price'] * ($price_value / 100);
                        } elseif ($price_type == '+') {
                            $price_value = $sku['price'] + $price_value;
                        }
                        if (wa()->getEnv() == 'backend') {
                            $sku['price'] = shop_currency($price_value, $product['currency'], $currency, false);
                        } else {
                            if (wa()->getPlugin('price')->getSettings('set_compare_price')) {
                                $sku['compare_price'] = shop_currency($sku['price'], $currency, $product['currency'], false);
                            }
                            $sku['price'] = $price_value;
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

        return $skus;
    }

    public function frontendProducts(&$params) {
        if (!$this->getSettings('status')) {
            return;
        }
        if (!empty($params['products'])) {
            $params['products'] = self::prepareProducts($params['products']);
        }
        if (!empty($params['skus'])) {
            $params['skus'] = self::prepareSkus($params['skus']);
        }
    }

    public function frontendCategory($category) {
        if (!$this->getSettings('status')) {
            return;
        }

        $view = wa()->getView();
        $filters = $view->getVars('filters');

        // Исправление минимальной максимальной цены в фильтре товаров
        if (!empty($filters['price'])) {
            $min = array(
                $filters['price']['min']
            );
            $max = array(
                $filters['price']['max']
            );

            $route_hash = shopPriceRouteHelper::getRouteHash();
            $category_ids = self::getUserCategoryId();
            $price_model = new shopPricePluginModel();
            $prices = $price_model->getPrices($route_hash, $category_ids);

            $currency = wa('shop')->getConfig()->getCurrency(true);
            $frontend_currency = wa('shop')->getConfig()->getCurrency(false);

            foreach ($prices as $price) {
                foreach (array('', '+', '%') as $type) {
                    $collection = new shopProductsCollection('category/' . $category['id']);
                    $skus_alias = $collection->addJoin('shop_product_skus', ':table.product_id = p.id', ":table.price_plugin_type_{$price['id']} = '{$type}'");
                    $currency_alias = $collection->addJoin('shop_currency', ':table.code = p.currency');

                    if ($type) {
                        $field = "({$skus_alias}.price {$type} {$skus_alias}.price_plugin_{$price['id']}) * {$currency_alias}.rate";
                    } else {
                        $field = "({$skus_alias}.price_plugin_{$price['id']}) * {$currency_alias}.rate";
                    }

                    $collection->addWhere("{$field} != 0");
                    $sql = $collection->getSQL();
                    $sql = "SELECT MIN(" . $field . ") min, MAX(" . $field . ") max " . $sql;
                    $model = new waModel();
                    $data = $model->query($sql)->fetch();

                    if (isset($data['min'])) {
                        $min[] = shop_currency($data['min'], $currency, $frontend_currency, false);
                    }
                    if (isset($data['max'])) {
                        $max[] = shop_currency($data['max'], $currency, $frontend_currency, false);
                    }
                }
            }

            $filters['price']['min'] = min($min);
            $filters['price']['max'] = max($max);
            $view->assign('filters', $filters);
        }

        $products = $view->getVars('products');
        //Исправление цены после фильтрации shopFrontendCategoryAction::filterListSkus
        if ($products) {
            foreach ($products as $p_id => $p) {
                if ($p['sku_count'] > 1) {
                    $product_ids[] = $p_id;
                }
            }
            if ($product_ids && $filters) {
                $tmp = array();
                foreach ($filters as $fid => $f) {
                    if ($fid != 'price') {
                        $fvalues = waRequest::get($f['code']);
                        if ($fvalues && !isset($fvalues['min']) && !isset($fvalues['max'])) {
                            $tmp[$fid] = $fvalues;
                        }
                    }
                }
                if ($tmp) {
                    $products = $this->prepareProducts($products);
                    $view->assign('products', $products);
                }
            }
        }
    }

    public function backendProductSkuSettings($params) {
        if (!$this->getSettings('status')) {
            return;
        }
        $product = $params['product'];
        $sku = $params['sku'];

        $price_model = new shopPricePluginModel();
        $prices = $price_model->getAll();

        $view = wa()->getView();
        $view->assign('product', $product);
        $view->assign('sku', $sku);
        $view->assign('prices', $prices);
        $view->assign('sku_id', $params['sku_id']);
        $html = $view->fetch('plugins/price/templates/actions/backend/BackendProductSkuSettings.html');
        return $html;
    }

    public function productCustomFields() {
        if (!$this->getSettings('status')) {
            return;
        }

        $price_model = new shopPricePluginModel();
        $prices = $price_model->getAll();

        $sku_fields = array();

        foreach ($prices as $price) {
            $field = 'price_plugin_' . $price['id'];
            $sku_fields[$field] = $price['name'];
            $field_type = 'price_plugin_type_' . $price['id'];
            $sku_fields[$field_type] = $price['name'] . ' (Тип цены)';
        }

        return array(
            'sku' => $sku_fields,
        );
    }

    public function productSave($params) {
        if (!$this->getSettings('status')) {
            return;
        }
        $sku_model = new shopProductSkusModel();
        if (!empty($params['data']['skus'])) {
            foreach ($params['data']['skus'] as $sku) {
                if (!empty($sku['price_plugin'])) {
                    $sku_model->updateById($sku['id'], $sku['price_plugin']);
                }
            }
        }
    }

    public function backendOrderEdit($order) {
        if (!$this->getSettings('status')) {
            return;
        }
        $price_model = new shopPricePluginModel();
        $prices = $price_model->getAll();

        $view = wa()->getView();
        $view->assign(array(
            'plugin_url' => $this->getPluginStaticUrl(),
            'version' => $this->getVersion(),
            'prices' => $prices,
        ));
        $html = $view->fetch('plugins/price/templates/actions/backend/BackendOrderEdit.html');
        return $html;
    }

}
