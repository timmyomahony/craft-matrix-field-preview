<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;

use craft\web\Controller;


class CategoriesController extends Controller
{
    public $defaultAction = 'index';

    public function actionIndex()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $categories = $plugin->categoryService->getAll();
        return $this->renderTemplate('matrix-field-preview/settings/categories/index', [
            'settings' => $settings,
            'plugin' => $plugin,
            'categories' => $categories
        ]);
    }

    public function actionCreate()
    {
        $this->requireAdmin();
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        return $this->renderTemplate('matrix-field-preview/settings/categories/create', [
            'settings' => $settings,
            'plugin' => $plugin,
        ]);
    }
}
