<?php

class shopPricePluginModel extends waModel {

    protected $table = 'shop_price';

    public function getPriceByParams($params, $all = true) {
        $where = array();
        $enabled_params = array_keys($this->fields);
        foreach ($params as $param => $value) {
            if (in_array($param, $enabled_params)) {
                if (is_array($value) && !empty($value)) {
                    $where[] = "`" . $param . "` IN (" . implode(',', $value) . ")";
                } else {
                    $where[] = "`" . $param . "` = '" . $this->escape($value) . "'";
                }
            }
        }
        if ($where) {
            $sql = "SELECT * FROM `" . $this->table . "` WHERE " . implode(' AND ', $where);
            if ($all) {
                return $_prices = $this->query($sql)->fetchAll('id');
            } else {
                return $this->query($sql)->fetch();
            }
        }
    }

}
