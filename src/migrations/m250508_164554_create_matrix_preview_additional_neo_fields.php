<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250508_164554_create_matrix_preview_additional_neo_fields migration.
 */
class m250508_164554_create_matrix_preview_additional_neo_fields extends Migration
{
    private function _neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if ($this->_neoInstalled()) {
            $this->addColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "enabled",
                $this->boolean(true)
            );

            $this->addColumn(
                "{{%matrixfieldpreview_neo_fields_config}}",
                "buttonIcon",
                $this->string(50)->notNull()->defaultValue('')
            );

            $this->addColumn(
                "{{%matrixfieldpreview_neo_fields_config}}",
                "buttonLabel",
                $this->string(50)->notNull()->defaultValue('')
            );

            // Update all existing Neo rows to have enabled = true
            $this->update(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                ['enabled' => true],
                ['enabled' => null]
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        if ($this->_neoInstalled()) {
            $this->dropColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "enabled"
            );

            $this->dropColumn(
                "{{%matrixfieldpreview_neo_fields_config}}",
                "buttonIcon"
            );

            $this->dropColumn(
                "{{%matrixfieldpreview_neo_fields_config}}",
                "buttonLabel"
            );
        }

        return true;
    }
}
