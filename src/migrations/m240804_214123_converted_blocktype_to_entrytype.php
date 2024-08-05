<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240804_214123_converted_blocktype_to_entrytype migration.
 */
class m240804_214123_converted_blocktype_to_entrytype extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Update the FK field to point to the entrytypes table
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'blockTypeId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'blockTypeId',
            '{{%entrytypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Unfortunately there's not a way to migrate previous block configs over
        // to the new entry types that Craft 5 uses for matrix fields. This means
        // we need to delete any existing configs so that they can be recreated
        // when the matrix field settings page is next visited by the user.
        $this->delete('matrixfieldpreview_blocktypes_config');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m240804_214123_converted_blocktype_to_entrytype cannot be reverted.\n";
        return false;
    }
}
