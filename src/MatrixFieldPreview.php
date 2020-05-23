<?php

/**
 * Matrix Field Preview plugin for Craft CMS 3.x
 *
 * Gives you the ability to configure a preview for all your matrix field blocks, giving your clients a better publishing experience.
 *
 * @link      https://weareferal.com
 * @copyright Copyright (c) 2020 Timmy O'Mahony 
 */

namespace weareferal\matrixfieldpreview;

use weareferal\matrixfieldpreview\services\PreviewService;
use weareferal\matrixfieldpreview\services\PreviewImageService;
use weareferal\matrixfieldpreview\models\Settings;
use weareferal\matrixfieldpreview\assets\previewfield\PreviewFieldAsset;
use weareferal\matrixfieldpreview\assets\previewsettings\PreviewSettingsAsset;

use Craft;
use craft\base\Plugin;
use craft\web\View;
use craft\events\TemplateEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    Timmy O'Mahony 
 * @package   MatrixFieldPreview
 * @since     1.0.0
 *
 * @property  PreviewService $previewService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class MatrixFieldPreview extends Plugin
{
    public static $plugin;

    public $schemaVersion = '1.0.0';

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Register our services
        $this->setComponents([
            'previewService' => PreviewService::class,
            'previewImageService' => PreviewImageService::class
        ]);

        // Inject custom matrix field input JavaScript
        //
        // @fixme find better way to insert JavaScript, taking into account its
        // dependency on the MatrixInput's JavaScript
        // 
        // Craft does not support any way (that I am aware of) to 
        // order JavaScript initialisation. In other words, there is no way
        // built-in way to guarantee that *our* JavaScript is loaded after 
        // the MatrixInput's JavaScript.
        //
        // Therefore, the only way to do this is to use this 
        // EVENT_AFTER_RENDER_TEMPLATE event to insert and run our asset
        // bundles after the entire view has been rendered.
        Event::on(
            View::class,
            View::EVENT_AFTER_RENDER_TEMPLATE,
            function (TemplateEvent $event) {
                if (Craft::$app->request->isCpRequest) {
                    $defaultImage = Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/previewimage/dist/img/no-dummy-image.svg', true);
                    $view = Craft::$app->getView();
                    $view->registerAssetBundle(PreviewFieldAsset::class);
                    $view->registerJs('new Craft.MatrixFieldPreview(".matrix-field", "' . $defaultImage . '");');
                }
            }
        );

        Craft::info(
            Craft::t(
                'matrix-field-preview',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        $view = Craft::$app->getView();
        $view->registerAssetBundle(PreviewSettingsAsset::class);

        $assets = [
            'success' => Craft::$app->getAssetManager()->getPublishedUrl('@app/web/assets/cp/dist', true, 'images/success.png')
        ];

        $blockTypes = Craft::$app->matrix->getAllBlockTypes();
        usort($blockTypes, function ($a, $b) {
            return strcmp($a->name, $b->name);
        });

        $previewService = $this->previewService;

        // Annotate the matrix block types with our previews
        $previews = $previewService->getAll();
        $previewsMap = [];
        foreach ($previews as $preview) {
            $previewsMap[$preview->blockType->handle] = $preview;
        }

        $rows = [];
        foreach ($blockTypes as $blockType) {
            $preview = null;
            if (array_key_exists($blockType->handle, $previewsMap)) {
                $preview = $previewsMap[$blockType->handle];
            }
            array_push($rows, [
                'blockType' => $blockType,
                'preview' => $preview
            ]);
        }

        return $view->renderTemplate('matrix-field-preview/settings', [
            'settings' => $this->getSettings(),
            'assets' => $assets,
            'rows' => $rows
        ]);
    }
}
