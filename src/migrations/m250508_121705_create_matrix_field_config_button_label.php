<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250508_121705_create_matrix_field_config_button_label migration.
 */
class m250508_121705_create_matrix_field_config_button_label extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->addColumn(
            "{{%matrixfieldpreview_fields_config}}",
            "buttonLabel",
            $this->string(50)->notNull()->defaultValue('')
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropColumn(
            "{{%matrixfieldpreview_fields_config}}",
            "buttonLabel"
        );

        return true;
    }
}
