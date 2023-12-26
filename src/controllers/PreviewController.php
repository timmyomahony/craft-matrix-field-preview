<?php

namespace weareferal\matrixfieldpreview\controllers;

use Craft;
use craft\web\Controller;
use weareferal\matrixfieldpreview\MatrixFieldPreview;
use yii\helpers\Markdown;

/**
 * Preview controller
 *
 * Controller to handle Ajax requests for configuration from the cp
 */
class PreviewController extends Controller
{

    protected array|bool|int $allowAnonymous = [];

    /**
     * Get preview config
     *
     * Return a JSON configuration for the frontend to use
     *
     * NOTE: there are two "handles" in play here: the matrix field handle
     * as well as the block type handles
     */
    public function actionGetPreviews($type, $fieldHandle)
    {
        $plugin = MatrixFieldPreview::getInstance();
        $settings = $plugin->getSettings();
        $response = [
            "success" => false,
            "config" => [
                "field" => null,
                "blockTypes" => [],
                "categories" => []
            ],
        ];

        switch ($type) {
            case "matrix":
                $fieldService = $plugin->matrixFieldConfigService;
                $blockTypeService = $plugin->matrixBlockTypeConfigService;
                break;
            case "neo":
                $fieldService = $plugin->neoFieldConfigService;
                $blockTypeService = $plugin->neoBlockTypeConfigService;
                break;
            default:
                $response["error"] = "'type' must be 'matrix' or 'neo'";
                return $this->asJson($response);
        }

        $fieldConfig = $fieldService->getOrCreateByFieldHandle($fieldHandle);

        if (!$fieldConfig) {
            return $this->asJson($response);
        }

        // Add field info
        $response['config']['field'] = [
            "name" => $fieldConfig->field->name,
            "handle" => $fieldConfig->field->handle,
            "enablePreviews" => $fieldConfig->enablePreviews,
            "enableTakeover" => $fieldConfig->enableTakeover,
        ];

        // Add categories
        foreach ($plugin->categoryService->getAll() as $category) {
            array_push($response['config']["categories"], [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                "descriptionHTML" => Markdown::process($category->description),
            ]);
        }

        // Add block type preview info
        $blockTypeConfigs = $blockTypeService->getOrCreateByFieldHandle($fieldHandle);
        foreach ($blockTypeConfigs as $blockTypeConfig) {
            $blockType = $blockTypeConfig->blockType;
            if ($blockType->hasAttribute('enabled') && !$blockType->enabled) {
                continue;
            }
            $result = [
                "name" => $blockType->name,
                "handle" => $blockType->handle,
                "description" => $blockTypeConfig->description,
                "descriptionHTML" => Markdown::process($blockTypeConfig->description),
                "categoryId" => $blockTypeConfig->categoryId,
                "image" => null,
                "thumb" => null,
            ];
            if ($blockTypeConfig->previewImageId) {
                $asset = Craft::$app->assets->getAssetById($blockTypeConfig->previewImageId);
                $result["imageId"] = $blockTypeConfig->previewImageId;
                if ($asset->extension == "gif" && Craft::$app->config->general->transformGifs == false) {
                    $result["image"] = $asset ? $asset->getUrl() : "";
                    $result["thumb"] = $asset ? $asset->getUrl() : "";
                } else {
                    $result["image"] = $asset ? $asset->getUrl([
                        "width" => 800,
                        "mode" => "fit",
                        "position" => "center-center",
                    ]) : "";
                    $result["thumb"] = $asset ? $asset->getThumbUrl(300, 300) : "";
                }


            }
            $response['config']["blockTypes"][$blockType->handle] = $result;
        }

        // Add neo-specific setting
        if ($type == "neo") {
            $response["config"]["neo"] = [
                "neoDisableForSingleChilden" => $settings->neoDisableForSingleChilden
            ];
        }

        $response["success"] = true;

        return $this->asJson($response);
    }
}
