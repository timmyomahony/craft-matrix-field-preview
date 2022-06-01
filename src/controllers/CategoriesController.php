<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;

use Craft;
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
        $request = Craft::$app->request;

        $name = "";
        $description = "";

        if ($request->isPost) {
            $post = $request->post();
            $name = $post['name'];
            $description = $post['description'];
            $category = $plugin->categoryService->create($name, $description);
            if ($category->validate()) {
                $category->save();
                Craft::$app->getSession()->setNotice(Craft::t('matrix-field-preview', 'Category created.'));
                return $this->redirect('matrix-field-preview/settings/categories');
            } else {
                Craft::$app->getSession()->setError(Craft::t('matrix-field-preview', 'Couldn\'t create category.'));
            }
        }

        return $this->renderTemplate('matrix-field-preview/settings/categories/create', [
            'settings' => $settings,
            'plugin' => $plugin,
            'name' => $name,
            'description' => $description
        ]);
    }
}
