<?php

namespace weareferal\matrixfieldpreview\migrations;

use Craft;
use craft\db\Migration;

/**
 * m250508_121705_create_matrix_field_config_button_label migration.
 */
class m250508_121705_create_matrix_field_config_button_label extends Migration
{

    private function _neoInstalled()
    {
        $neo = Craft::$app->plugins->getPlugin("neo", false);
        return $neo && $neo->isInstalled;
    }


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

        if ($this->_neoInstalled()) {
            $this->addColumn(
                "{{%matrixfieldpreview_neo_fields_config}}",
                "buttonLabel",
                $this->string(50)->notNull()->defaultValue('')
            );
        }

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

        if ($this->_neoInstalled()) {
            $this->dropColumn(
                "{{%matrixfieldpreview_neo_fields_config}}",
                "buttonLabel"
            );
        }

        return true;
    }
}
