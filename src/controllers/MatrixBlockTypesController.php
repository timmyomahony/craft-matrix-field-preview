<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\controllers\BaseBlockTypesController;


class MatrixBlockTypesController extends BaseBlockTypesController
{
    public $defaultAction = 'index';

    protected function getFieldsConfigService($plugin)
    {
        return $plugin->matrixFieldConfigService;
    }

    protected function getBlockTypeConfigService($plugin)
    {
        return $plugin->matrixBlockTypeConfigService;
    }

    protected function getIndexTemplate()
    {
        return 'matrix-field-preview/settings/matrix-block-types/index';
    }
}
