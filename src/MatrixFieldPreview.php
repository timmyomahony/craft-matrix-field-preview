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

use weareferal\matrixfieldpreview\services\MatrixBlockTypeConfigService;
use weareferal\matrixfieldpreview\services\MatrixFieldConfigService;
use weareferal\matrixfieldpreview\services\NeoFieldConfigService;
use weareferal\matrixfieldpreview\services\NeoBlockTypeConfigService;
use weareferal\matrixfieldpreview\services\UtilsService;
use weareferal\matrixfieldpreview\services\PreviewImageService;
use weareferal\matrixfieldpreview\services\CategoryService;
use weareferal\matrixfieldpreview\models\Settings;
use weareferal\matrixfieldpreview\assets\BaseFieldPreview\BaseFieldPreviewAsset;
use weareferal\matrixfieldpreview\assets\MatrixFieldPreview\MatrixFieldPreviewAsset;
use weareferal\matrixfieldpreview\assets\NeoFieldPreview\NeoFieldPreviewAsset;
use weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\web\View;
use craft\helpers\UrlHelper;
use craft\events\TemplateEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\base\Model;

use yii\db\MigrationInterface;
use yii\di\Instance;
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

    public string $schemaVersion = '5.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = false;

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_registerCpRoutes();
        $this->_registerAssetBundles();
        $this->_setTemplateVariables();
        $this->_runManualMigrations();
    }

    protected function createSettingsModel(): Model|null
    {
        return new Settings();
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->controller->redirect(
            UrlHelper::cpUrl('matrix-field-preview/settings')
        );
    }

    private function _setPluginComponents()
    {
        $components = [
            'previewImageService' => PreviewImageService::class,
            'matrixFieldConfigService' => MatrixFieldConfigService::class,
            'matrixBlockTypeConfigService' => MatrixBlockTypeConfigService::class,
            'utilsService' => UtilsService::class,
            'categoryService' => CategoryService::class
        ];
        // Neo support
        if (Craft::$app->plugins->isPluginEnabled("neo")) {
            $components = array_merge($components, [
                'neoFieldConfigService' => NeoFieldConfigService::class,
                'neoBlockTypeConfigService' => NeoBlockTypeConfigService::class,
            ]);
        }

        $this->setComponents($components);
    }

    private function _setTemplateVariables()
    {
        // Add our helper service as a template variable {{ craft.mfpNeoHelper... }}
        if (Craft::$app->plugins->isPluginEnabled("neo")) {
            Event::on(
                CraftVariable::class,
                CraftVariable::EVENT_INIT,
                function (Event $event) {
                    $variable = $event->sender;
                    $variable->set('matrixFieldPreview', UtilsService::class);
                }
            );
        }
    }

    private function _registerAssetBundles()
    {
        // Insert our asset bundles into the control panel
        //
        // FIXME: At the time this plugin was created, there was no easy way
        // to detect when a matrix field input was being rendered so we just
        // added a catch-all page-render event. Since then there is a more
        // accurate way to track when a matrix field is being rendered:
        //
        // https://github.com/craftcms/cms/issues/5867
        Event::on(
            View::class,
            View::EVENT_BEFORE_RENDER_PAGE_TEMPLATE,
            function (TemplateEvent $event) {
                if (Craft::$app->request->isCpRequest) {
                    $view = Craft::$app->getView();
                    $defaultImage = Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/MatrixFieldPreviewSettings/dist/img/no-dummy-image.png', true);
                    $iconImage = Craft::$app->getAssetManager()->getPublishedUrl('@weareferal/matrixfieldpreview/assets/MatrixFieldPreviewSettings/dist/img/preview-icon.svg', true);
                    $view->registerJsVar('matrixFieldPreviewDefaultImage', $defaultImage);
                    $view->registerJsVar('matrixFieldPreviewIcon', $iconImage);
                    $view->registerAssetBundle(BaseFieldPreviewAsset::class);
                    $view->registerAssetBundle(MatrixFieldPreviewAsset::class);
                    $view->registerJs('new MFP.MatrixFieldPreview();', View::POS_END, 'matrix-field-preview');
                    if (Craft::$app->plugins->isPluginEnabled("neo")) {
                        $view->registerAssetBundle(NeoFieldPreviewAsset::class);
                        $view->registerJs('new MFP.NeoFieldPreview();', View::POS_END, 'neo-field-preview');
                    }
                    // Most of our translations are only registered within the
                    // templates they are in, but these translations are used
                    // by the MFP JavaScript interface and therefore need to
                    // be always registered.
                    $view->registerTranslations('matrix-field-preview', [
                        'All Categories',
                        'New Entry',
                        'Content Preview',
                        'Matrix Field Previews',
                        'Neo Field Previews',
                        'Close',
                    ]);
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
                    'matrix-field-preview/settings' => 'matrix-field-preview/settings/index',
                    'matrix-field-preview/settings/categories' => 'matrix-field-preview/categories/index',
                    'matrix-field-preview/settings/categories/create' => 'matrix-field-preview/categories/create',
                    'matrix-field-preview/settings/categories/<categoryId:\d+>' => 'matrix-field-preview/categories/edit',
                    'matrix-field-preview/settings/matrix-fields' => 'matrix-field-preview/matrix-fields/index',
                    'matrix-field-preview/settings/matrix-block-types' => 'matrix-field-preview/matrix-block-types/index',
                    'matrix-field-preview/settings/matrix-block-types/<blockTypeId:\d+>' => 'matrix-field-preview/matrix-block-types/edit',
                ];

                // Neo support
                if (Craft::$app->plugins->isPluginEnabled("neo")) {
                    $urls = array_merge($urls, [
                        'matrix-field-preview/settings/neo-fields' => 'matrix-field-preview/neo-fields',
                        'matrix-field-preview/settings/neo-block-types' => 'matrix-field-preview/neo-block-types/index',
                        'matrix-field-preview/settings/neo-block-types/<blockTypeId:\d+>' => 'matrix-field-preview/neo-block-types/edit',
                    ]);
                }

                $event->rules = array_merge($event->rules, $urls);
            }
        );
    }

    private function _runManualMigrations()
    {
        // If Neo is installed _after_ Matrix Field Preview, the migrations
        // required to create our custom tables will never be run (although they
        // will be recorded in the migrations table as having been run) so we
        // re-run the relevent Neo migration manually in this situation.
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (Event $event) {
                if ($event->plugin->handle == "neo") {
                    // NOTE: You can't use the plugin->getMigrator()->migrateUp(...)
                    // approach as it requires inserting the migration into the migration
                    // table, which already exist, so we just run it manually
                    //
                    // See https://craftcms.stackexchange.com/q/36657/9612
                    $neoMigrations = [
                        new migrations\m201031_120401_add_neo_support(),
                        new migrations\m220606_112005_add_category_fk_to_neo(),
                        new migrations\m220606_200131_add_neo_block_type_sort_order()
                    ];

                    foreach ($neoMigrations as $neoMigration) {
                        $neoMigration->db->schema->refresh();
                        $neoMigration->safeUp();
                        $neoMigration->db->schema->refresh();
                    }
                }
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_BEFORE_UNINSTALL_PLUGIN,
            function (Event $event) {
                if ($event->plugin->handle == "neo") {
                    $neoMigration = new migrations\m201031_120401_add_neo_support();
                    $neoMigration->db->schema->refresh();
                    $neoMigration->safeDown();
                    $neoMigration->db->schema->refresh();

                    // NOTE: Don't need to run the other Neo migrations as
                    // we're simply deleting the tables entirely.
                }
            }
        );
    }
}
