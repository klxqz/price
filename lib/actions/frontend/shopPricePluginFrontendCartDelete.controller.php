<?php

class shopPricePluginFrontendCartDeleteController extends shopFrontendCartDeleteController {

    public function execute() {
        parent::execute();
        $is_html = waRequest::request('html');
        $total = shopPricePlugin::fixTotalCart();
        $this->response['total'] = $is_html ? shop_currency_html($total, true) : shop_currency($total, true);
    }

}
