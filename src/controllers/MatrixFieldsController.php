<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\controllers\BaseFieldsController;

use Craft;


class MatrixFieldsController extends BaseFieldsController
{
    protected function getTemplate() {
        return 'matrix-field-preview/settings/matrix-fields/index';
    }

    protected function getService($plugin) {
        return $plugin->matrixFieldConfigService;
    }

    protected function getSuccessMessage() {
        return Craft::t('matrix-field-preview', 'Matrix field configurations saved.');
    }
}
