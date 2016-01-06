<?php

class shopPricePluginSettingsAction extends waViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopPricePlugin::$plugin_id);
        $ccm = new waContactCategoryModel();
        $categories = array(
            array('id' => 0, 'name' => 'Все покупатели')
        );
        foreach ($ccm->getAll() as $c) {
            if ($c['app_id'] == 'shop') {
                $categories[$c['id']] = $c;
            }
        }
        $price_model = new shopPricePluginModel();
        $prices = $price_model->getAll();
        $_prices = array();
        foreach ($prices as $price) {
            $_prices[$price['domain_hash']][] = $price;
        }

        $domain_routes = wa()->getRouting()->getByApp('shop');
        $domains_settings = shopPrice::getDomainsSettings();

        $this->view->assign('prices', $_prices);
        $this->view->assign('domain_routes', $domain_routes);
        $this->view->assign('categories', $categories);
        $this->view->assign('settings', $settings);
        $this->view->assign('domain_settings', $domains_settings);
    }

}
