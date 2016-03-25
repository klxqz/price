<?php

class shopPricePluginModel extends shopSortableModel {

    protected $table = 'shop_price';

    public function getPriceByParams($params, $all = true) {
        $where = array();
        $enabled_params = array_keys($this->fields);
        foreach ($params as $param => $value) {
            if (in_array($param, $enabled_params)) {
                if (is_array($value) && !empty($value)) {
                    $where[] = "`" . $param . "` IN (" . implode(',', $value) . ")";
                } elseif (!is_array($value)) {
                    $where[] = "`" . $param . "` = '" . $this->escape($value) . "'";
                }
            }
        }
        if ($where) {
            $sql = "SELECT * FROM `" . $this->table . "` WHERE " . implode(' AND ', $where) . " ORDER BY " . $this->sort;
            if ($all) {
                return $_prices = $this->query($sql)->fetchAll('id');
            } else {
                return $this->query($sql)->fetch();
            }
        }
    }

}
