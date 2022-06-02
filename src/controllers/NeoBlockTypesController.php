<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\controllers\BaseBlockTypesController;

use Craft;


class NeoBlockTypesController extends BaseBlockTypesController
{
    public function beforeAction($action): bool
    {
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }
        return parent::beforeAction($action);
    }
    
    protected function getFieldsConfigService($plugin)
    {
        return $plugin->neoFieldConfigService;
    }

    protected function getBlockTypeConfigService($plugin)
    {
        return $plugin->neoBlockTypeConfigService;
    }

    protected function getIndexTemplate()
    {
        return 'matrix-field-preview/settings/neo-block-types/index';
    }
}
