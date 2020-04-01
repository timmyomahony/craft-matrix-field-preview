<?php


namespace weareferal\matrixfieldpreview\controllers;

use weareferal\matrixfieldpreview\MatrixFieldPreview;
use weareferal\matrixfieldpreview\records\PreviewRecord;
use weareferal\matrixfieldpreview\assets\previewimage\PreviewImageAsset;

use Craft;
use craft\web\Controller;

use yii\web\NotFoundHttpException;

class PreviewController extends Controller
{

    protected $allowAnonymous = [];

    public $defaultAction = 'preview';

    /**
     * Render a particular matrix block type with a form
     */
    public function actionPreview($blockTypeId)
    {
        $this->view->registerAssetBundle(PreviewImageAsset::class);

        $siteId = Craft::$app->getSites()->currentSite->id;
        $previewService = MatrixFieldPreview::getInstance()->previewService;
        $request = Craft::$app->request;
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();

        $blockType = Craft::$app->matrix->getBlockTypeById($blockTypeId);
        if (!$blockType) {
            throw new NotFoundHttpException('Invalid matrix block type ID: ' . $blockTypeId);
        }

        $preview = $previewService->getByBlockTypeId($blockTypeId);
        if (!$preview) {
            $preview = new PreviewRecord();
            $preview->blockType = $blockType;
            $preview->siteId = $siteId;
            $preview->description = "";
            $preview->matrixFieldHandle = $blockType->field->handle;
            $preview->save();
        }

        if ($request->isPost) {
            $post = $request->post();
            $preview->description = $post['description'];
            if ($preview->validate()) {
                $preview->save();
                Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
                return $this->redirect('/admin/settings/plugins/' . $plugin->id);
            } else {
                Craft::$app->getSession()->setError(Craft::t('app', 'Couldn’t save plugin settings.'));
            }
        }

        return $this->renderTemplate(
            'matrix-field-preview/preview',
            [
                'preview' => $preview,
                'plugin' => $plugin,
                'fullPageForm' => true,
                'settings' => $settings
            ]
        );
    }

    /**
     * Get preview config 
     * 
     * Return a JSON configuration for the frontend to use
     * 
     * NOTE: there are two "handles" in play here: the matrix field handle
     * as well as the block type handles
     */
    public function actionGetPreviews($handle)
    {
        $previewService = MatrixFieldPreview::getInstance()->previewService;

        $results = [];
        $previews = $previewService->getByHandle($handle);
        foreach ($previews as $preview) {
            $blockType = $preview->blockType;
            $asset = Craft::$app->assets->getAssetById($preview->previewImageId);
            $results[$blockType->handle] = [
                'name' => $blockType->name,
                'description' => $preview->description,
                'image' => $asset ? $asset->getUrl([
                    'width' => 800,
                    'height' => 600,
                    'mode' => 'fit',
                    'position' => 'center-center'
                ]) : "",
                'thumb' => $asset ? $asset->getThumbUrl(300, 300) : "",
            ];
        }

        return $this->asJson([
            'success' => true,
            'handle' => $handle,
            'previews' => $results
        ]);
    }
}
