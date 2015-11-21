<?php

class shopPricePluginSettingsAction extends waViewAction {

    public function execute() {
        $app_settings_model = new waAppSettingsModel();
        $settings = $app_settings_model->get(shopPricePlugin::$plugin_id);
        $ccm = new waContactCategoryModel();
        $categories = array();
        foreach ($ccm->getAll() as $c) {
            if ($c['app_id'] == 'shop') {
                $categories[$c['id']] = $c;
            }
        }
        if (!empty($settings['prices'])) {
            $settings['prices'] = json_decode($settings['prices'], true);
        }
        
        $domain_routes = wa()->getRouting()->getByApp('shop');
        $domains_settings = shopPrice::getDomainsSettings();
    
        $this->view->assign('domain_routes', $domain_routes);
        $this->view->assign('categories', $categories);
        $this->view->assign('settings', $settings);
        $this->view->assign('domain_settings', $domains_settings);
    }

}
