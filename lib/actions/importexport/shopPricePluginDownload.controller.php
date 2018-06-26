<?php

class shopPricePluginDownloadController extends waController {

    public function execute() {
        $filename = waRequest::get('filename');
        $file = wa()->getTempPath('plugins/price/' . $filename);
        if (file_exists($file)) {
            waFiles::readFile($file, $filename);
        } else {
            throw new waException($file . ' not found', 404);
        }
    }

}
