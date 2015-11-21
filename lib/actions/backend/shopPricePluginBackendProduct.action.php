<?php

class shopPricePluginBackendProductAction extends waViewAction {

    public function execute() {
        $product_id = waRequest::get('product_id', null, waRequest::TYPE_INT);
        $domain_routes = wa()->getRouting()->getByApp('shop');
        $domains_settings = shopPrice::getDomainsSettings();

        $product = new shopProduct($product_id);
        $price_model = new shopPricePluginModel();
        $prices = array();
        if ($product->id) {
            $prices = $price_model->getByField('product_id', $product->id, true);
            $prices = $this->preparePrices($prices);
        }

        $this->view->assign('domain_routes', $domain_routes);
        $this->view->assign('domain_settings', $domains_settings);
        $this->view->assign('product', $product);
        $this->view->assign('prices', $prices);
    }

    protected function preparePrices($prices) {
        $return = array();
        foreach ($prices as $price) {
            $domain_hash = $price['domain_hash'];
            $category_id = $price['category_id'];
            $sku_id = $price['sku_id'];
            $return[$domain_hash][$category_id][$sku_id] = $price['price'];
        }

        return $return;
    }

}
