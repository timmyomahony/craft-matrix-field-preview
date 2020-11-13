<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m201031_120401_add_neo_support migration.
 */
class m201031_120401_add_neo_support extends Migration
{
    private function neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }

    public function safeUp()
    {
        // Neo support
        if ($this->neoInstalled()) {
            if ($this->createNeoFieldTables()) {
                $this->addNeoFieldForeignKeys();
                Craft::$app->db->schema->refresh();
            }
        }
        return true;
    }

    public function safeDown()
    {
        if ($this->neoInstalled()) {
            $this->removeNeoFieldTables();
        }
        return true;
    }

    protected function createNeoFieldTables()
    {
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_neo_fields_config}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_neo_fields_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'fieldId' => $this->integer()->notNull(),
                    'enablePreviews' => $this->boolean(true),
                    'enableTakeover' => $this->boolean(false)
                ]
            );
        }

        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_neo_blocktypes_config}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_neo_blocktypes_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'blockTypeId' => $this->integer()->notNull(),
                    'fieldId' => $this->integer()->notNull(),
                    'previewImageId' => $this->integer(),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                ]
            );
        }

        return true;
    }

    protected function addNeoFieldForeignKeys()
    {
        // Neo fields
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_fields_config}}', 'fieldId'),
            '{{%matrixfieldpreview_neo_fields_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Neo blocktypes
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', 'fieldId'),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', 'blockTypeId'),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            'blockTypeId',
            '{{%neoblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', 'previewImageId'),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            'previewImageId',
            '{{%assets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        return true;
    }

    protected function removeNeoFieldTables()
    {
        $this->dropTableIfExists('{{%matrixfieldpreview_neo_blocktypes_config}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_neo_fields_config}}');
        return true;
    }
}
