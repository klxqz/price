<?php

class shopPricePurchasePluginModel extends shopSortableModel
{

    protected $table = 'shop_price_purchase';

    public function deleteByField($field, $value = null)
    {
        $purchase_price = $this->getByField($field, $value);
        if ($purchase_price) {
            $sql = "ALTER TABLE `shop_product_skus` DROP `purchase_price_plugin_" . $this->escape($purchase_price['id']) . "`";
            $this->query($sql);
        }
        return parent::deleteByField($field, $value);
    }

}
