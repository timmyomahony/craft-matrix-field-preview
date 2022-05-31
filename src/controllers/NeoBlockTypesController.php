<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\controllers\BaseBlockTypesController;

use Craft;


class NeoBlockTypesController extends BaseBlockTypesController
{  
    public $defaultAction = 'index';

    public function actionIndex()
    {
        $this->requireAdmin();
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionIndex(
            $plugin->neoBlockTypeConfigService,
            $plugin->neoFieldConfigService,
            'matrix-field-preview/settings/neo-block-types/index'
        );
    }

    public function actionEdit($blockTypeId)
    {
        $this->requireAdmin();
        if (!Craft::$app->plugins->isPluginEnabled("neo")) {
            throw new BadRequestHttpException('Plugin is not enabled');
        }
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionEdit(
            $blockTypeId,
            $plugin->neoBlockTypeConfigService,
            'matrix-field-preview/settings/neo-block-types/upload',
            'matrix-field-preview/settings/neo-block-types/preview',
            'matrix-field-preview/settings/neo-block-types/index',
            'matrix-field-preview/settings/neo-block-types/edit'
        );
    }

    public function actionUpload()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionUpload(
            $plugin->neoBlockTypeConfigService
        );
    }

    public function actionDelete()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionDelete(
            $plugin->neoBlockTypeConfigService
        );
    }

}
