<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;

use weareferal\matrixfieldpreview\MatrixFieldPreview;


/**
 * m200717_143303_create_field_foreign_key migration.
 */
class m200717_143303_create_field_foreign_key extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Create a new foreign key to the Craft fields table
        $this->addColumn('{{%matrixfieldpreview_previewrecord}}', 'fieldId', 'integer');
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_previewrecord}}', 'fieldId'),
            '{{%matrixfieldpreview_previewrecord}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Now do a data migration to save the new fieldId FK
        foreach (MatrixFieldPreview::getInstance()->blockTypeConfigService->getAll() as $blockTypeConfig) {
            $blockTypeConfig->fieldId = $blockTypeConfig->blockType->fieldId;
            $blockTypeConfig->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_previewrecord}}', 'fieldId'),
            '{{%matrixfieldpreview_previewrecord}}'
        );
        $this->dropColumn('{{%matrixfieldpreview_previewrecord}}', 'fieldId');
        return true;
    }
}
