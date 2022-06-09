<?php
namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;

use Craft;
use craft\web\Controller;


/**
 * A shared controller for configuring fields for preview
 * 
 * A field in this context can either be a matrix field or a neo field. Having
 * a shared controller like this allows us to reduce rewritten code between
 * the two systems.
 */
abstract class BaseFieldsController extends Controller {

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
     * List all fields for configuration
     * 
     */
    public function actionIndex()
    {
        $this->view->registerAssetBundle(MatrixFieldPreviewSettingsAsset::class);
        $plugin = MatrixFieldPreview::getInstance();       
        $service = $this->getService($plugin);
        $template = $this->getTemplate();

        $fields = $service->getAllFields();
        $fieldConfigs = $service->getAll($sort = true);

        return $this->renderTemplate($template, [
            'fields' => $fields,
            'fieldConfigs' => $fieldConfigs
        ]);
    }

    /**
     * Save the configuration of fields
     * 
     */
    public function actionSave()
    {
        $this->requirePostRequest();
        $plugin = MatrixFieldPreview::getInstance();
        $service = $this->getService($plugin);

        $post = $this->request->post();

        if (! $post['settings']) {
            return null;
        }

        foreach ($post['settings'] as $handle => $values) {
            $fieldConfig = $service->getOrCreateByFieldHandle($handle);
            if ($fieldConfig) {
                $fieldConfig->enablePreviews = $values['enablePreviews'];
                $fieldConfig->enableTakeover = $values['enableTakeover'];
                if ($fieldConfig->validate()) {
                    $fieldConfig->save();
                }
            }
        }

        $fields = $service->getAllFields();
        $fieldConfigs = $service->getAll($sort = true);

        $this->setSuccessFlash($this->getSuccessMessage());
        return $this->redirectToPostedUrl();
    }

    /**
     * Get the underlying service for the field type
     * 
     */
    protected function getService($plugin) {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented')); 
    }

    /**
     * Get the underlying template to render
     * 
     */
    protected function getTemplate() {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented')); 
    }

    /**
     * Get the underlying message for successful saves
     */
    protected function getSuccessMessage() {
        throw new Exception(Craft::t('matrix-field-preview', 'Not implemented')); 
    }
}
