<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

use weareferal\matrixfieldpreview\controllers\BaseFieldsController;


class NeoFieldsController extends BaseFieldsController
{
    public $defaultAction = 'index';

    public function actionIndex()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();        
        return $this->_actionFields(
            $plugin->neoFieldConfigService,
            'matrix-field-preview/settings/neo-fields/index'
        );
    }
}
