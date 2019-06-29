<?php

class shopPricePluginSettingsAction extends waViewAction
{

    public function execute()
    {
        if (0) {
            $this->updateScheme();
        }


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

        $price_purchase_model = new shopPricePurchasePluginModel();
        $purchase_prices = $price_purchase_model->getAll('id');

        $price_params_model = new shopPricePluginParamsModel();
        foreach ($prices as &$price) {
            $params = $price_params_model->getByField('price_id', $price['id'], true);
            $price['route_hash'] = array();
            $price['category_id'] = array();
            if ($params) {
                foreach ($params as $param) {
                    $price['route_hash'][] = $param['route_hash'];
                    $price['category_id'][] = $param['category_id'];
                }
            }
            $price['route_hash'] = array_unique($price['route_hash']);
            $price['category_id'] = array_unique($price['category_id']);

            if (!empty($purchase_prices[$price['purchase_price_id']])) {
                $price['purchase_price_name'] = $purchase_prices[$price['purchase_price_id']]['name'];
            } else {
                $price['purchase_price_name'] = 'Стандартная закупочная цена';
            }
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

        $price_discount_model = new shopPricePluginDiscountModel();
        $discounts = $price_discount_model->getAll();

        $this->view->assign(array(
            'plugin' => wa()->getPlugin('price'),
            'route_hashs' => $route_hashs,
            'categories' => $categories,
            'prices' => $prices,
            'purchase_prices' => $purchase_prices,
            'discounts' => $discounts,
        ));
    }


    private function updateScheme()
    {
        $model = new waModel();

        $sql = <<<SQL
ALTER TABLE `shop_price` ADD `purchase_price_id` INT(11) NOT NULL DEFAULT '0' AFTER `name`; 
SQL;
        $model->query($sql);


        $sql = <<<SQL
CREATE TABLE `shop_price_purchase` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $model->query($sql);

        $sql = <<<SQL
ALTER TABLE `shop_price_purchase`
  ADD PRIMARY KEY (`id`);
SQL;
        $model->query($sql);


        $sql = <<<SQL
ALTER TABLE `shop_price_purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
SQL;
        $model->query($sql);
    }

}
