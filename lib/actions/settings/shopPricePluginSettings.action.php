<?php

class shopPricePluginSettingsAction extends waViewAction {

    public function execute() {
        $ccm = new waContactCategoryModel();
        $categories = array(
            array('id' => 0, 'name' => 'Все покупатели')
        );
        foreach ($ccm->getAll() as $c) {
            if ($c['app_id'] == 'shop') {
                $categories[$c['id']] = $c;
            }
        }
        $this->view->assign(array(
            'plugin' => wa()->getPlugin('price'),
            'route_hashs' => shopPriceRouteHelper::getRouteHashs(),
            'categories' => $categories,
        ));
    }

}
