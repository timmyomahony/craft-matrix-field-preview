<?php

namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\CategoryRecord;

use Craft;
use craft\web\Controller;
use craft\helpers\Json;

use yii\web\Response;


class CategoriesController extends Controller
{
    public $defaultAction = 'index';

    /**
     * Enforce admin privileges
     * 
     * But ignore the settings `allowAdminChanges`, allowing users to
     * configure the plugin while on production.
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin($requireAdminChanges=false);
        return parent::beforeAction($action);
    }

    /**
     * List all categories
     * 
     */
    public function actionIndex()
    {
        $plugin = MatrixFieldPreview::getInstance();
        $categories = $plugin->categoryService->getAll();

        return $this->renderTemplate('matrix-field-preview/settings/categories/index', [
            'categories' => $categories
        ]);
    }

    /**
     * Create a category
     * 
     */
    public function actionCreate(?CategoryRecord $category = null)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        if (! $category) {
            $category = new CategoryRecord();
        }

        return $this->renderTemplate('matrix-field-preview/settings/categories/_edit', [
            'category' => $category
        ]);
    }

    /**
     * Edit a category
     * 
     */
    public function actionEdit(int $categoryId, ?CategoryRecord $category = null)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        
        if (! $category) {
            $category = $plugin->categoryService->getById($categoryId);
        }

        return $this->renderTemplate('matrix-field-preview/settings/categories/_edit', [
            'category' => $category
        ]);
    }

    /**
     * Save a category
     * 
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $categoryService = $plugin->categoryService;

        $categoryId = $this->request->getBodyParam('categoryId');

        if ($categoryId) {
            $category = $categoryService->getById($categoryId);
            if (!$category) {
                throw new BadRequestHttpException("Invalid category ID: $categoryId");
            }
        } else {
            $category = new CategoryRecord();
        }

        $category->name = $this->request->getBodyParam('name');
        $category->description = $this->request->getBodyParam('description');

        if (! $categoryService->save($category)) {
            $this->setFailFlash(Craft::t('matrix-field-preview', 'Couldn\'t save the category.'));

            // Send user back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'category' => $category,
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('matrix-field-preview', 'Category saved.'));
        return $this->redirectToPostedUrl($category);
    }

    /**
     * Delete a category
     * 
     */
    public function actionDelete():Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $plugin = MatrixFieldPreview::getInstance();
        $categoryService = $plugin->categoryService;
        $categoryId = $this->request->getRequiredBodyParam('id');
        $categoryService->deleteById($categoryId);
        return $this->asJson(['success' => true]);
    }

    /**
     * Reorder categories
     * 
     */
    public function actionReorder():Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();
        $plugin = MatrixFieldPreview::getInstance();

        $categoryService = $plugin->categoryService;
        $categoryIds = Json::decode($this->request->getRequiredBodyParam('ids'));
        $categoryService->reorder($categoryIds);

        return $this->asJson(['success' => true]);
    }
}
