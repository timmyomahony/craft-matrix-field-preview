<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;

use weareferal\matrixfieldpreview\records\CategoryRecord;


class CategoryService extends Component
{
    protected $CategoryRecord = CategoryRecord::class;

    public function getAll()
    {
        return $this->CategoryRecord::find()->all();
    }
}
