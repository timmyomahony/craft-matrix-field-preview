<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220606_112005_add_category_fk_to_neo migration.
 * 
 */
class m220606_112005_add_category_fk_to_neo extends Migration
{
    private function _neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }

    public function safeUp()
    {
        if ($this->_neoInstalled()) {
            $this->addColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "categoryId",
                "integer null"
            );
            $this->addForeignKey(
                "categoryId",
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "categoryId",
                "{{%matrixfieldpreview_category}}",
                "id",
                "SET NULL",
                "SET NULL"
            );
        }
    }

    public function safeDown()
    {
        if ($this->_neoInstalled()) {
            $this->dropColumn(
                "{{%matrixfieldpreview_neo_blocktypes_config}}",
                "categoryId");
        }
        return true;
    }
}
