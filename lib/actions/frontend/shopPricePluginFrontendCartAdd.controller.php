<?php

class shopPricePluginFrontendCartAddController extends shopFrontendCartAddController {

    public function execute() {
        parent::execute();
        $total = shopPricePlugin::fixTotalCart();
        $this->response['total'] = $this->currencyFormat($total);
    }

}
