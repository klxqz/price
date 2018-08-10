<?php

class shopPricePluginBackendSetupAction extends waViewAction {

    public function execute() {
        $set_model = new shopSetModel();
        $type_model = new shopTypeModel();
        $this->view->assign(array(
            'sets' => $set_model->getAll(),
            'types' => $type_model->getTypes(),
        ));
    }

}
