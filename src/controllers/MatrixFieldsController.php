<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\controllers\BaseFieldsController;

use Craft;


class MatrixFieldsController extends BaseFieldsController
{
    protected function getIndexTemplate() {
        return 'matrix-field-preview/settings/matrix-fields/index';
    }

    protected function getEditTemplate() {
        return 'matrix-field-preview/settings/matrix-fields/_edit';
    }

    protected function getService($plugin) {
        return $plugin->matrixFieldConfigService;
    }

    protected function getSuccessMessage() {
        return Craft::t('matrix-field-preview', 'Matrix field configuration saved.');
    }

    protected function getEditAction($id)
    {
        return 'matrix-field-preview/settings/matrix-fields/' . $id;
    }
}
