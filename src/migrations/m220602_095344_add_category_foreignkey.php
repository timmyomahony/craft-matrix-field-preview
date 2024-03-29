<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220602_095344_add_category_foreignkey migration.
 */
class m220602_095344_add_category_foreignkey extends Migration
{
    public function safeUp()
    {
        $this->addColumn(
            "{{%matrixfieldpreview_blocktypes_config}}",
            "categoryId",
            "integer null");
        $this->addForeignKey(
            "categoryId",
            "{{%matrixfieldpreview_blocktypes_config}}",
            "categoryId",
            "{{%matrixfieldpreview_category}}",
            "id",
            "SET NULL",
            "SET NULL"
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            "{{%matrixfieldpreview_blocktypes_config}}",
            "categoryId");
        return true;
    }
}
