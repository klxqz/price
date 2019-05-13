<?php

class shopPricePluginSettingsUploadController extends waJsonController {

    public function execute() {
        try {
            $file = waRequest::file('file');
            if (!$file->uploaded()) {
                throw new Exception('Ошибка загрузки файла');
            }
            $filepath = wa()->getCachePath('price_plugin/', 'shop');
            waFiles::create($filepath, true);
            $filepath .= $file->name;
            $file->moveTo($filepath);

            $price_discount_model = new shopPricePluginDiscountModel();
            $price_discount_model->truncate();
            $f = fopen($filepath, 'r');
            if (!$f) {
                throw new Exception('Ошибка открытия файла ' . $filepath);
            }
            $data = array();
            while (($row = fgetcsv($f, null, ";")) !== FALSE) {
                if (trim($row[0]) && (string) intval(trim($row[1])) == (string) trim($row[1])) {
                    $product_sku = trim($row[0]);
                    $discount = trim($row[1]);
                    $data[] = array(
                        'product_sku' => iconv('CP1251', 'UTF-8', $product_sku),
                        'discount' => iconv('CP1251', 'UTF-8', $discount),
                    );
                }
            }
            fclose($f);
            if ($data) {
                $price_discount_model->multiInsert($data);
            }
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
