<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250508_160503_create_matrix_block_type_enabled_field migration.
 */
class m250508_160503_create_matrix_block_type_enabled_field extends Migration
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
        $this->addColumn(
            "{{%matrixfieldpreview_blocktypes_config}}",
            "enabled",
            $this->boolean(true)
        );

        // Update all existing rows to have enabled = true
        $this->update(
            "{{%matrixfieldpreview_blocktypes_config}}",
            ['enabled' => true],
            ['enabled' => null]
        );

        if ($this->_neoInstalled()) {
            $this->addColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "enabled",
                $this->boolean(true)
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
        $this->dropColumn(
            "{{%matrixfieldpreview_blocktypes_config}}",
            "enabled"
        );

        if ($this->_neoInstalled()) {
            $this->dropColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "enabled"
            );
        }

        return true;
    }
}
