<?php

class shopPricePluginBackendSortController extends waJsonController {

    public function execute() {
        $price_model = new shopPricePluginModel();
        $id = waRequest::post('id', 0, waRequest::TYPE_INT);
        $after_id = waRequest::post('after_id', 0, waRequest::TYPE_INT);
        try {
            $price_model->move($id, $after_id);
        } catch (waException $e) {
            $this->setError($e->getMessage());
        }
    }

}
