<?php

class shopPricePluginSettingsAction extends waViewAction {

    public function execute() {
        $ccm = new waContactCategoryModel();
        $categories = array();
        $categories[] = array(
            'id' => 0,
            'name' => 'Все покупатели',
            'icon' => 'contact',
        );
        $categories = array_merge($categories, $ccm->getByField('app_id', 'shop', true));
        $price_model = new shopPricePluginModel();
        $prices = $price_model->getAll();

        $price_params_model = new shopPricePluginParamsModel();
        foreach ($prices as &$price) {
            $params = $price_params_model->getByField('price_id', $price['id'], true);
            if ($params) {
                foreach ($params as $param) {
                    $price['route_hash'][$param['route_hash']] = 1;
                    $price['category_id'][$param['category_id']] = 1;
                }
            }
        }
        unset($price);

        $_route_hashs = shopPriceRouteHelper::getRouteHashs();
        $route_hashs = array(
            array(
                'storefront' => 'Все витрины',
                'route_hash' => 0,
            )
        );
        foreach ($_route_hashs as $storefront => $route_hash) {
            $route_hashs[] = array(
                'storefront' => $storefront,
                'route_hash' => $route_hash,
                'url' => 'http://' . str_replace('*', '', $storefront),
            );
        }

        $this->view->assign(array(
            'plugin' => wa('shop')->getPlugin('price'),
            'route_hashs' => $route_hashs,
            'categories' => $categories,
            'prices' => $prices,
        ));
    }

}
