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

use weareferal\matrixfieldpreview\services\BlockTypeConfigService;
use weareferal\matrixfieldpreview\services\MatrixFieldConfigService;
use weareferal\matrixfieldpreview\services\NeoFieldConfigService;
use weareferal\matrixfieldpreview\services\PreviewImageService;
use weareferal\matrixfieldpreview\models\Settings;
use weareferal\matrixfieldpreview\assets\PreviewField\PreviewFieldAsset;

use Craft;
use craft\base\Plugin;
use craft\web\View;
use craft\helpers\UrlHelper;
use craft\events\TemplateEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;

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
 * @property  BlockTypeConfigService $blockTypeConfigService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class MatrixFieldPreview extends Plugin
{
    public static $plugin;

    public $schemaVersion = '1.2.1';
    public $hasCpSettings = true;
    public $hasCpSection = false;

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_registerCpRoutes();
        $this->_registerMatrixFieldPreviews();
    }

    protected function createSettingsModel()
    {
        return new Settings();
    }

    public function getSettingsResponse()
    {
        Craft::$app->controller->redirect(
            UrlHelper::cpUrl('matrix-field-preview/settings/general')
        );
    }

    private function _setPluginComponents()
    {
        $this->setComponents([
            'previewImageService' => PreviewImageService::class,
            'matrixFieldConfigService' => MatrixFieldConfigService::class,
            'neoFieldConfigService' => NeoFieldConfigService::class,
            'blockTypeConfigService' => BlockTypeConfigService::class
        ]);
    }

    private function _registerMatrixFieldPreviews()
    {
        // Inject custom matrix field input JavaScript
        //
        // @fixme find better way to insert JavaScript, taking into account its
        // dependency on the MatrixInput's JavaScript
        // 
        // Craft does not support any way (that I am aware of) to 
        //
        // a) track the rendering/asset bundle insertion of a particular input
        //    so that we could conditionally insert our JavaScript only when
        //    a MatrixInput is rendered
        // b) control the initialisation order of JavaScript so that we could
        //    guarantee our JavaScript is only inserted/loaded *after* the
        //    MatrixInput is rendered
        //
        // Therefore, the only way to do this is to use EVENT_AFTER_RENDER_TEMPLATE 
        // event to insert and run our asset bundles after a control panel
        // view has been rendered
        //
        // More info here: https://github.com/craftcms/cms/issues/5867, and 
        // in particular this https://github.com/craftcms/cms/issues/5867#issuecomment-639912817
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                if (Craft::$app->request->isCpRequest) {
                    $defaultImage = Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/MatrixFieldPreviewSettings/dist/img/no-dummy-image.svg', true);
                    $view = Craft::$app->getView();
                    $view->registerAssetBundle(PreviewFieldAsset::class);
                    $view->registerJsVar('matrixFieldPreviewDefaultImage', $defaultImage);
                    $view->registerJs('new Craft.MatrixFieldPreview(".matrix-field");', View::POS_READY, 'matrix-field-preview');
                }
            }
        );
    }

    private function _registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $urls = [
                    'matrix-field-preview/settings/general' => 'matrix-field-preview/settings/general',
                    'matrix-field-preview/settings/matrix-fields' => 'matrix-field-preview/settings/matrix-fields',
                    'matrix-field-preview/settings/matrix-block-types' => 'matrix-field-preview/settings/matrix-block-types',
                    'matrix-field-preview/settings/matrix-block-type' => 'matrix-field-preview/settings/matrix-block-type'
                ];

                // Neo support
                if (Craft::$app->plugins->getPlugin("neo", false)->isInstalled) {
                    $urls = array_merge($urls, [
                        'matrix-field-preview/settings/neo-fields' => 'matrix-field-preview/settings/neo-fields',
                        'matrix-field-preview/settings/neo-block-types' => 'matrix-field-preview/settings/neo-block-types',
                        'matrix-field-preview/settings/neo-block-type' => 'matrix-field-preview/settings/neo-block-type',
                    ]);
                }

                $event->rules = array_merge($event->rules, $urls);
            }
        );
    }
}
