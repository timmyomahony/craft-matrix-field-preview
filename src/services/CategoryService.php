<?php

namespace weareferal\matrixfieldpreview\services;
use weareferal\matrixfieldpreview\records\CategoryRecord;

use Craft;
use craft\base\Component;


class CategoryService extends Component
{
    protected $CategoryRecord = CategoryRecord::class;

    public function getAll()
    {
        return $this->CategoryRecord::find()->all();
    }
}
