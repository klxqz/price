<?php

class shopPricePluginUploadController extends waJsonController {

    public function execute() {
        try {
            $file = waRequest::file('files');
            if ($file->uploaded()) {
                $filepath = wa()->getCachePath('plugins/price/import-price.csv', 'shop');
                $file->moveTo($filepath);
            } else {
                throw new waException('Ошибка загрузки файла');
            }
        } catch (Exception $ex) {
            $this->setError($ex->getMessage());
        }
    }

}
