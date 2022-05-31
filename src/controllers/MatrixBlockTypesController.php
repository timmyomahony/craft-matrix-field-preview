<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\controllers\BaseBlockTypesController;


class MatrixBlockTypesController extends BaseBlockTypesController
{
    public $defaultAction = 'index';

    public function actionIndex()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionIndex(
            $plugin->matrixBlockTypeConfigService,
            $plugin->matrixFieldConfigService,
            'matrix-field-preview/settings/matrix-block-types/index'
        );
    }

    public function actionEdit($blockTypeId)
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionEdit(
            $blockTypeId,
            $plugin->matrixBlockTypeConfigService,
            'matrix-field-preview/settings/matrix-block-types/upload',
            'matrix-field-preview/settings/matrix-block-types/preview',
            'matrix-field-preview/settings/matrix-block-types/index',
            'matrix-field-preview/settings/matrix-block-type/edit'
        );
    }

    public function actionUpload()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionUpload(
            $plugin->matrixBlockTypeConfigService
        );
    }

    public function actionDelete()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        return $this->_actionDelete(
            $plugin->matrixBlockTypeConfigService
        );
    }
}
