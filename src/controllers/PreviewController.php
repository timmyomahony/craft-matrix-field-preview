<?php

namespace weareferal\matrixfieldpreview\controllers;

use Craft;
use craft\helpers\Cp;
use craft\helpers\Json;
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

        // It's possible to override the handle for entry types in matrix fields. These overrides
        // appear to only be stored on the matrix field settings object. So we create a lookup
        // object here for this situation
        //
        // https://github.com/timmyomahony/craft-matrix-field-preview/issues/137
        // https://github.com/craftcms/cms/pull/16453
        $entryTypeOverrides = [];
        $fieldSettings = Json::decode($fieldConfig->field->settings);
        if (isset($fieldSettings['entryTypes'])) {
            foreach ($fieldSettings['entryTypes'] as $entryType) {
                if (isset($entryType['handle'])) {
                    $entryTypeOverrides[$entryType['uid']] = $entryType['handle'];
                }
            }
        }

        // Add field info
        $response['config']['field'] = [
            "name" => $fieldConfig->field->name,
            "handle" => $fieldConfig->field->handle,
            "enablePreviews" => (bool)$fieldConfig->enablePreviews,
            "enableTakeover" => (bool)$fieldConfig->enableTakeover,
            "buttonLabel" => $fieldConfig->buttonLabel,
            "buttonIcon" => "",
            "buttonIconSvg" => "",
            "entryTypeOverrides" => $entryTypeOverrides
        ];

        // Add button icon SVG if configured
        if ($fieldConfig->buttonIcon && $fieldConfig->buttonIcon !== "") {
            $response['config']['field']['buttonIcon'] = $fieldConfig->buttonIcon;
            $response['config']['field']['buttonIconSvg'] = Cp::iconSvg($fieldConfig->buttonIcon);
        }

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
            // Skip block types that are not enabled
            if (! (bool)$blockTypeConfig->enabled) {
                continue;
            }
            // Skip Neo block types that are not enabled
            if ($type == "neo" && $blockType->hasAttribute('enabled') && ! (bool)$blockType->enabled) {
                continue;
            }
            $result = [
                "name" => $blockType->name,
                "handle" => $blockType->handle,
                "uid" => $blockType->uid,  // the entry type uid, not ours
                "description" => $blockTypeConfig->description,
                "descriptionHTML" => Markdown::process($blockTypeConfig->description),
                "categoryId" => $blockTypeConfig->categoryId,
                "image" => null,
                "thumb" => null,
            ];

            // Deal with the situation where a handle has been overridden, discussed above
            if (isset($entryTypeOverrides[$blockType->uid])) {
                $result["handle"] = $entryTypeOverrides[$blockType->uid];
            }

            if ($blockTypeConfig->previewImageId) {
                $asset = Craft::$app->assets->getAssetById($blockTypeConfig->previewImageId);
                $result["imageId"] = $blockTypeConfig->previewImageId;
                if ($asset->extension == "gif" && Craft::$app->config->general->transformGifs == false) {
                    $result["image"] = $asset ? $asset->getUrl() : "";
                    $result["thumb"] = $asset ? $asset->getUrl() : "";
                } else {
                    $result["image"] = $asset ? $asset->getUrl([
                        "width" => 1600,
                        "mode" => "fit",
                        "position" => "center-center",
                        "quality" => 99
                    ]) : "";
                    $result["thumb"] = $asset ? Craft::$app->assets->getThumbUrl($asset, 600, 600) : "";
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
