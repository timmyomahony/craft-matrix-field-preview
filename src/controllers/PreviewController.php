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

    /**
     * Get preview config 
     * 
     * Return a JSON configuration for the frontend to use
     * 
     * NOTE: there are two "handles" in play here: the matrix field handle
     * as well as the block type handles
     */
    public function actionGetPreviews($matrixFieldHandle)
    {
        $previewService = MatrixFieldPreview::getInstance()->previewService;

        $results = [];
        $previews = $previewService->getByHandle($matrixFieldHandle);

        foreach ($previews as $preview) {
            $blockType = $preview->blockType;
            $blockTypeHandle = $blockType->handle;
            $result = [
                'name' => $blockType->name,
                'description' => $preview->description,
                'image' => null,
                'thumb' => null
            ];
            if ($preview->previewImageId) {
                $asset = Craft::$app->assets->getAssetById($preview->previewImageId);
                $result['image'] = $asset ? $asset->getUrl([
                    'width' => 800,
                    'mode' => 'stretch',
                    'position' => 'center-center'
                ]) : "";
                $result['thumb'] = $asset ? $asset->getThumbUrl(300, 300) : "";
            }
            $results[$blockTypeHandle] = $result;
        }

        return $this->asJson([
            'success' => true,
            'handle' => $matrixFieldHandle,
            'previews' => $results
        ]);
    }
}
