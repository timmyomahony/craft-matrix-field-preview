<?php

namespace weareferal\matrixfieldpreview\migrations;


use Craft;
use craft\db\Migration;


class Install extends Migration
{
    public $driver;

    private function neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        if ($this->createMatrixFieldTables()) {
            $this->addMatrixFieldForeignKeys();
            Craft::$app->db->schema->refresh();
        }

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
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeMatrixFieldTables();
        // Always remove, even if Neo not currently installed
        $this->removeNeoFieldTables();
        return true;
    }


    protected function createMatrixFieldTables()
    {
        // Field Config Table
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_fields_config}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_fields_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'fieldId' => $this->integer()->notNull(),
                    'enablePreviews' => $this->boolean(true),
                    'enableTakeover' => $this->boolean(true)
                ]
            );
        }

        // Block Type Config Table
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_blocktypes_config}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_blocktypes_config}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'blockTypeId' => $this->integer()->notNull(),
                    'fieldId' => $this->integer()->notNull(),
                    'previewImageId' => $this->integer(),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                    'categoryId' => $this->integer(),
                    'sortOrder' => $this->smallInteger()->defaultValue(0),
                ]
            );
        }

        // Category
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_category}}') === null) {
            $this->createTable(
                '{{%matrixfieldpreview_category}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'name' => $this->string(100)->notNull()->defaultValue(''),
                    'description' => $this->string(1024)->notNull()->defaultValue(''),
                    'sortOrder' => $this->integer()->notNull()->defaultValue(0),
                ]
            );
        }

        return true;
    }

    protected function createNeoFieldTables()
    {
        if (Craft::$app->db->schema->getTableSchema('{{%matrixfieldpreview_neo_fields_config}}') === null) {
            // Field Config Table
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
            // Block Type Config Table
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
                    'categoryId' => $this->integer(),
                    'sortOrder' => $this->smallInteger()->defaultValue(0),
                ]
            );
        }

        return true;
    }

    protected function addMatrixFieldForeignKeys()
    {
        // Field Config Table - Craft Field FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_fields_config}}', 'fieldId'),
            '{{%matrixfieldpreview_fields_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Craft Field FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'fieldId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Matrix Block Type FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'blockTypeId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'blockTypeId',
            '{{%matrixblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Asset FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', 'previewImageId'),
            '{{%matrixfieldpreview_blocktypes_config}}',
            'previewImageId',
            '{{%assets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Category FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_blocktypes_config}}', "categoryId"),
            '{{%matrixfieldpreview_blocktypes_config}}',
            "categoryId",
            "{{%matrixfieldpreview_category}}",
            "id",
            "SET NULL",
            "SET NULL"
        );

        return true;
    }
    

    protected function addNeoFieldForeignKeys()
    {
        // Config Table - Craft Field FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_fields_config}}', 'fieldId'),
            '{{%matrixfieldpreview_neo_fields_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Craft Field FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', 'fieldId'),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            'fieldId',
            '{{%fields}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Craft Block Type FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', 'blockTypeId'),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            'blockTypeId',
            '{{%neoblocktypes}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Asset FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', 'previewImageId'),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            'previewImageId',
            '{{%assets}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Block Type Config Table - Category FK
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%matrixfieldpreview_neo_blocktypes_config}}', "categoryId"),
            '{{%matrixfieldpreview_neo_blocktypes_config}}',
            "categoryId",
            "{{%matrixfieldpreview_category}}",
            "id",
            "SET NULL",
            "SET NULL"
        );

        return true;
    }

    protected function removeMatrixFieldTables()
    {
        $this->dropTableIfExists('{{%matrixfieldpreview_previewrecord}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_blocktypes_config}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_fields_config}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_category}}');

        return true;
    }

    protected function removeNeoFieldTables()
    {
        $this->dropTableIfExists('{{%matrixfieldpreview_neo_blocktypes_config}}');
        $this->dropTableIfExists('{{%matrixfieldpreview_neo_fields_config}}');

        return true;
    }
}
