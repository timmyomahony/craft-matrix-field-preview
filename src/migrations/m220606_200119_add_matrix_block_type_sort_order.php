<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m220606_200119_add_matrix_block_type_sort_order migration.
 * 
 * Add "sort order" to the matrix field block type configuration table
 */
class m220606_200119_add_matrix_block_type_sort_order extends Migration
{

    public function safeUp()
    {
        $this->addColumn(
            "{{%matrixfieldpreview_blocktypes_config}}",
            "sortOrder",
            $this->smallInteger()->defaultValue(0)
        );
    }

    public function safeDown()
    {
        $this->dropColumn(
            "{{%matrixfieldpreview_blocktypes_config}}",
            "sortOrder"
        );
        return true;
    }
}
