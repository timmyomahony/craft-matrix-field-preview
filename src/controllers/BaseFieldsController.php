<?php
namespace weareferal\matrixfieldpreview\controllers;

use Craft;
use craft\web\Controller;
use craft\helpers\UrlHelper;

use weareferal\matrixfieldpreview\assets\MatrixFieldPreviewSettings\MatrixFieldPreviewSettingsAsset;
use weareferal\matrixfieldpreview\MatrixFieldPreview;

/**
 * A shared controller for configuring fields for preview
 *
 * A field in this context can either be a matrix field or a neo field. Having
 * a shared controller like this allows us to reduce rewritten code between
 * the two systems.
 */
abstract class BaseFieldsController extends Controller
{

    /**
     * Enforce admin privileges
     *
     * But ignore the settings `allowAdminChanges`, allowing users to
     * configure the plugin while on production.
     */
    public function beforeAction($action): bool
    {
        $this->requireAdmin($requireAdminChanges = false);
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
        $settings = $plugin->getSettings();
    
        $service = $this->getService($plugin);

        $fields = $service->getAllFields();
        $fieldConfigs = $service->getAll($sort = true);

        // Tabledata is required for use with the existing Craft.VueAdminTable
        $tableData = [];
        foreach ($fieldConfigs as $fieldConfig) {
            $url = UrlHelper::url($this->getEditAction((string) $fieldConfig->field->id));  // Note field id and not fieldConfig id
            array_push($tableData, [
                "id" => $fieldConfig->id,
                "title" => $fieldConfig->field->name,
                "enablePreviews" => $fieldConfig->enablePreviews,
                "enableTakeover" => $fieldConfig->enableTakeover,
                "url" => $url
            ]);
        }

        return $this->renderTemplate($this->getIndexTemplate(), [
            'assets' => [
                'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png'),
                'cancel' => Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/MatrixFieldPreviewSettings/dist/img/cancel.png', true)
            ],
            'tableData' => $tableData,
            'fields' => $fields,
            'settings' => $settings,
        ]);
    }

     /**
     * Edit a field configuration
     * 
     * Note that we're using the fieldId from Craft, and not our own fieldConfigId. This
     * is because we are lazily creating our field previews, so we need to know the field
     * to associated the config with if it doesn't already exist.
     */
    public function actionEdit(int $fieldId)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = MatrixFieldPreview::getInstance()->getSettings();

        $service = $this->getService($plugin);

        $fieldConfig = $service->getOrCreateByFieldId($fieldId);
    
        return $this->renderTemplate($this->getEditTemplate(), [
            'fieldConfig' => $fieldConfig,
        ]);
    }

    /**
     * Save the matrix field configuration
     *
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $plugin = MatrixFieldPreview::getInstance();
        $service = $this->getService($plugin);

        $fieldId = $this->request->getBodyParam('fieldId');
        $fieldConfig = $service->getOrCreateByFieldId($fieldId);
        if (!$fieldConfig) {
            throw new BadRequestHttpException("Couldn't find field config. Invalid field id supplied: $fieldId");
        }

        $fieldConfig->enablePreviews = $this->request->getBodyParam('enablePreviews');
        $fieldConfig->enableTakeover = $this->request->getBodyParam('enableTakeover');
        $fieldConfig->buttonLabel = $this->request->getBodyParam('buttonLabel');
        $fieldConfig->buttonIcon = $this->request->getBodyParam('buttonIcon');

        if (! $service->save($fieldConfig)) {
            $this->setFailFlash(Craft::t('matrix-field-preview', 'Couldn\'t save the field config.'));

            // Send user back to the template
            Craft::$app->getUrlManager()->setRouteParams([
                'fieldConfig' => $fieldConfig,
            ]);

            return null;
        }

        $this->setSuccessFlash(Craft::t('matrix-field-preview', 'Field config saved.'));
        return $this->redirectToPostedUrl($fieldConfig);
    }

    /**
     * Get the underlying service for the field type
     *
     * This base class is abstract, so this method must be implemented by the
     * child matrix field or neo field controller.
     */
    protected function getService($plugin)
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getIndexTemplate()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getEditTemplate()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    /**
     * Get the underlying message for successful saves
     */
    protected function getSuccessMessage()
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }

    protected function getEditAction($id)
    {
        throw new \BadMethodCallException(Craft::t('matrix-field-preview', 'Not implemented'));
    }
}
