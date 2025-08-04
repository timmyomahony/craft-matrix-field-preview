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
        // First, drop any existing foreign key constraint for blockTypeId
        $tableName = '{{%matrixfieldpreview_blocktypes_config}}';
        $foreignKeyName = $this->db->getForeignKeyName($tableName, 'blockTypeId');

        // Check if the foreign key exists and drop it
        $tableSchema = $this->db->getTableSchema($tableName);
        if ($tableSchema !== null) {
            foreach ($tableSchema->foreignKeys as $fkName => $fkData) {
                if (isset($fkData['blockTypeId'])) {
                    $this->dropForeignKey($fkName, $tableName);
                    break;
                }
            }
        }

        // Unfortunately there's not a way to migrate previous block configs over
        // to the new entry types that Craft 5 uses for matrix fields. This means
        // we need to delete any existing configs so that they can be recreated
        // when the matrix field settings page is next visited by the user.
        $this->delete('{{%matrixfieldpreview_blocktypes_config}}');

        // Now add the new foreign key constraint pointing to entrytypes table
        $this->addForeignKey(
            $foreignKeyName,
            $tableName,
            'blockTypeId',
            '{{%entrytypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

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
