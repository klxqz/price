<?php

class shopPricePluginBackendSaveController extends waJsonController {

    public function execute() {
        try {
            $app_settings_model = new waAppSettingsModel();
            $shop_price = waRequest::post('shop_price');
            $domains_settings = waRequest::post('domains_settings', array());
            $reset = waRequest::post('reset');

            foreach ($shop_price as $key => $value) {
                $app_settings_model->set(shopPricePlugin::$plugin_id, $key, $value);
            }

            foreach ($domains_settings as $domain_hash => &$domains_setting) {
                $price_name = !empty($domains_setting['price_name']) ? $domains_setting['price_name'] : array();
                $price_category_id = !empty($domains_setting['price_category_id']) ? $domains_setting['price_category_id'] : array();
                $prices = array();
                foreach ($price_name as $id => $value) {
                    $prices[] = array(
                        'name' => $value,
                        'category_id' => $price_category_id[$id],
                    );
                }
                $domains_setting['prices'] = $prices;
            }
            unset($domains_setting);

            if ($reset) {
                $domains_settings = array();
            }
            shopPrice::saveDomainsSettings($domains_settings);
            $this->response['message'] = "Сохранено";
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }
    }

}
