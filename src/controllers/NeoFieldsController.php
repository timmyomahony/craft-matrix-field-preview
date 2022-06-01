<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\controllers\BaseFieldsController;

use Craft;


class NeoFieldsController extends BaseFieldsController
{
    protected function getTemplate() {
        return 'matrix-field-preview/settings/neo-fields/index';
    }

    protected function getService($plugin) {
        return $plugin->neoFieldConfigService;
    }

    protected function getSuccessMessage() {
        return Craft::t('matrix-field-preview', 'Neo field configurations saved.');
    }
}
