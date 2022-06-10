<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220606_200131_add_neo_block_type_sort_order migration.
 * 
 * Add "sort order" to the neo field block type configuration table
 */
class m220606_200131_add_neo_block_type_sort_order extends Migration
{
    private function _neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        if ($this->_neoInstalled()) {
            $this->addColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "sortOrder",
                $this->smallInteger()->defaultValue(0)
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        if ($this->_neoInstalled()) {
            $this->dropColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "sortOrder"
            );
            return true;
        }
        return true;
    }
}
