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
            $url = UrlHelper::url($this->getEditAction((string) $fieldConfig->id));
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
        ]);
    }

    /**
     * Save the configuration of fields
     *
     */
    // public function actionSave()
    // {
    //     $this->requirePostRequest();
    //     $plugin = MatrixFieldPreview::getInstance();
    //     $service = $this->getService($plugin);

    //     $post = $this->request->post();

    //     if (!$post['settings']) {
    //         return null;
    //     }

    //     foreach ($post['settings'] as $handle => $values) {
    //         $fieldConfig = $service->getOrCreateByFieldHandle($handle);
    //         if ($fieldConfig) {
    //             $fieldConfig->enablePreviews = $values['enablePreviews'];
    //             if (isset($values['enableTakeover'])) {
    //                 $fieldConfig->enableTakeover = $values['enableTakeover'];
    //             }
    //             if ($fieldConfig->validate()) {
    //                 $fieldConfig->save();
    //             }
    //         }
    //     }

    //     // $fields = $service->getAllFields();
    //     // $fieldConfigs = $service->getAll();

    //     $this->setSuccessFlash($this->getSuccessMessage());
    //     return $this->redirectToPostedUrl();
    // }

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
