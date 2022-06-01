<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;

use weareferal\matrixfieldpreview\records\CategoryRecord;


class CategoryService extends Component
{
    public function getAll()
    {
        return $this->CategoryRecord::find()->all();
    }

    public function create(string $name, string $description) {
        $record = new CategoryRecord();
        $record->name = $name;
        $record->description = $description;
        $record->save();
    }
}
