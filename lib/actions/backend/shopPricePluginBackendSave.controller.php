<?php

class shopPricePluginBackendSaveController extends waJsonController {

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $shop_price = waRequest::post('shop_price');
            $domains_settings = waRequest::post('domains_settings', array());

            foreach ($shop_price as $key => $value) {
                $app_settings_model->set(shopPricePlugin::$plugin_id, $key, $value);
            }

            shopPrice::saveDomainsSettings($domains_settings);
            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
