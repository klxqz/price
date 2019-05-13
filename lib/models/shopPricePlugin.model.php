<?php

class shopPricePluginModel extends shopSortableModel {

    protected $table = 'shop_price';

    public function getPrices($route_hash, $category_ids) {
        $sql = "SELECT DISTINCT `p`.* FROM `" . $this->table . "` as `p`
                LEFT JOIN `shop_price_params` as `pp` 
                ON `p`.`id` = `pp`.`price_id`
                WHERE (`pp`.`route_hash` = '" . $this->escape($route_hash) . "' AND `pp`.`category_id` IN (" . implode(',', $category_ids) . "))
                OR (`pp`.`route_hash` = '0' AND `pp`.`category_id` IN (" . implode(',', $category_ids) . "))
                ORDER BY " . $this->sort;

        return $this->query($sql)->fetchAll('id');
    }

    public function checkData($data) {
        if (empty($data['route_hash']) || !is_array($data['route_hash'])) {
            throw new waException('Не указаны витрины');
        }
        if (empty($data['category_id']) || !is_array($data['category_id'])) {
            throw new waException('Не указаны категории пользователей');
        }
    }

    public function deleteByField($field, $value = null) {
        $price = $this->getByField($field, $value);
        if ($price) {
            $params_model = new shopPricePluginParamsModel();
            $params_model->deleteByField('price_id', $price['id']);
            $sql = "ALTER TABLE `shop_product_skus` DROP `price_plugin_" . $this->escape($price['id']) . "`";
            $this->query($sql);
            $sql = "ALTER TABLE `shop_product_skus` DROP `price_plugin_type_" . $this->escape($price['id']) . "`";
            $this->query($sql);
        }
        return parent::deleteByField($field, $value);
    }

/*
    public function insert($data, $type = 0) {
        $this->checkData($data);
        $id = parent::insert($data, $type);
        $this->insertParams($id, $data['route_hash'], $data['category_id']);

        $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_{$id}` DECIMAL(15,4) NOT NULL DEFAULT '0.0000';";
        $this->query($sql);
        $sql = "ALTER TABLE `shop_product_skus` ADD `price_plugin_type_{$id}` ENUM( '', '%', '+' ) NOT NULL DEFAULT '';";
        $this->query($sql);

        return $id;
    }*/

/*
    public function updateById($id, $data, $options = null, $return_object = false) {
        return self::updateByField($this->remapId($id), $data, $options, $return_object);
    }*/

/*
    public function updateByField($field, $value, $data = null, $options = null, $return_object = false) {
        if (is_array($field)) {
            $this->checkData($value);
        } else {
            $this->checkData($data);
        }
        $result = parent::updateByField($field, $value, $data, $options, $return_object);
        if (is_array($field)) {
            $return_object = $options;
            $options = $data;
            $data = $value;
            $value = false;
        }
        $price_id = $this->getIdByParams($field, $value, $data);
        $this->insertParams($price_id, $data['route_hash'], $data['category_id']);

        return $result;
    }*/

    public function insertParams($price_id, $route_hashs, $category_ids) {
        $multi_data = array();
        foreach ($route_hashs as $route_hash) {
            foreach ($category_ids as $category_id) {
                $multi_data[] = array(
                    'price_id' => $price_id,
                    'route_hash' => $route_hash,
                    'category_id' => $category_id,
                );
            }
        }
        $params_model = new shopPricePluginParamsModel();
        $params_model->deleteByField('price_id', $price_id);
        $params_model->multiInsert($multi_data);
    }

    private function getIdByParams($field, $value, $data = null) {
        if (!empty($data['id'])) {
            return $data['id'];
        } elseif (is_array($field) && !empty($field['id'])) {
            return $field['id'];
        } elseif (!is_array($field) && $field == 'id') {
            return $value;
        } elseif ($result = $this->getByField($field, $value)) {
            return $result['id'];
        } else {
            throw new waException('Не определен идентификатор');
        }
    }

    private function remapId($id) {
        if (is_array($this->id)) {
            $field = array_fill_keys($this->id, null);
            foreach ($this->id as $n => $name) {
                if (isset($id[$name])) {
                    $field[$name] = $id[$name];
                } elseif (isset($id[$n])) {
                    $field[$name] = $id[$n];
                }
            }
        } else {
            $field = array($this->id => $id);
        }
        return $field;
    }

}
