<?php

class shopPricePluginFrontendCartSaveController extends shopFrontendCartSaveController {

    public function execute() {
        parent::execute();

        $is_html = waRequest::request('html');

        $total = shopPricePlugin::fixTotalCart();
        $this->response['total'] = $is_html ? shop_currency_html($total, true) : shop_currency($total, true);

        $item_id = waRequest::post('id');
        $cart_items_model = new shopCartItemsModel();
        $item = $cart_items_model->getById($item_id);


        $price_model = new shopPricePluginModel();
        $domain_hash = shopPrice::getRouteHash();
        $category_ids = shopPricePlugin::getUserCategoryId();
        $params = array(
            'domain_hash' => $domain_hash,
            'sku_id' => $item['sku_id'],
            'category_id' => $category_ids,
        );

        $price = $price_model->getPriceByParams($params, null, false);
        if ($price) {
            if ($q = waRequest::post('quantity', 0, 'int')) {
                $this->response['item_total'] = $is_html ?
                        shop_currency_html($price['price'] * $item['quantity'], true) :
                        shop_currency($price['price'] * $item['quantity'], true);
            }
        }
    }

}
