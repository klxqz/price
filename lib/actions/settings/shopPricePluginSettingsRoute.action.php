<?php

class shopPricePluginSettingsRouteAction extends waViewAction {

    public function execute() {
        $route_hash = waRequest::get('route_hash');

        $params = array(
            'route_hash' => $route_hash,
        );
        $price_model = new shopPricePluginModel();
        $prices = $price_model->getPriceByParams($params, true);
        
        $ccm = new waContactCategoryModel();
        $categories = array(
            array('id' => 0, 'name' => 'Все покупатели')
        );
        foreach ($ccm->getAll() as $c) {
            if ($c['app_id'] == 'shop') {
                $categories[$c['id']] = $c;
            }
        }
        $view = wa()->getView();
        $view->assign(array(
            'categories' => $categories,
            'prices' => $prices,
            'route_settings' => shopPriceRouteHelper::getRouteSettings($route_hash),
        ));
    }

}
