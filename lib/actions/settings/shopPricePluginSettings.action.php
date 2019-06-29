<?php

class shopPricePluginSettingsAction extends waViewAction {

    public function execute() {
        $ccm = new waContactCategoryModel();
        $categories = array();
        $categories[0] = array(
            'id' => 0,
            'name' => 'Все покупатели',
            'icon' => 'contact',
        );
        foreach ($ccm->getByField('app_id', 'shop', true) as $category) {
            $categories[$category['id']] = $category;
        }

        $price_model = new shopPricePluginModel();
        $prices = $price_model->getAll();

        $price_params_model = new shopPricePluginParamsModel();
        foreach ($prices as &$price) {
            $price['route_hash'] = array();
            $price['category_id'] = array();
            $params = $price_params_model->getByField('price_id', $price['id'], true);
            if ($params) {
                foreach ($params as $param) {
                    $price['route_hash'][] = $param['route_hash'];
                    $price['category_id'][] = $param['category_id'];
                }
            }
            $price['route_hash'] = array_unique($price['route_hash']);
            $price['category_id'] = array_unique($price['category_id']);
        }
        unset($price);

        $_route_hashs = shopPriceRouteHelper::getRouteHashs();
        $route_hashs = array(
            0 => array(
                'storefront' => 'Все витрины',
                'route_hash' => 0,
            )
        );
        foreach ($_route_hashs as $storefront => $route_hash) {
            $route_hashs[$route_hash] = array(
                'storefront' => $storefront,
                'route_hash' => $route_hash,
                'url' => 'http://' . str_replace('*', '', $storefront),
            );
        }
        
        $currency_model = new shopCurrencyModel();
        $currencies = $currency_model->getCurrencies();
        
        $this->view->assign(array(
            'plugin' => wa()->getPlugin('price'),
            'route_hashs' => $route_hashs,
            'categories' => $categories,
            'prices' => $prices,
            'currencies' => $currencies,
        ));
    }

}
