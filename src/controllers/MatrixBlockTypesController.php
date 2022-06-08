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

    protected function getEditTemplate()
    {
        return 'matrix-field-preview/settings/matrix-block-types/_edit';
    }

    protected function getUploadAction()
    {
        return 'matrix-field-preview/matrix-block-types/upload-preview';
    }

    protected function getDeleteAction()
    {
        return 'matrix-field-preview/matrix-block-types/delete-preview';
    }

    protected function getEditAction($id)
    {
        return 'matrix-field-preview/settings/matrix-block-types/' . $id;
    }
}
