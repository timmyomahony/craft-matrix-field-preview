<?php

namespace weareferal\matrixfieldpreview\models;

use Craft;
use craft\base\Model;

class Settings extends Model
{
    public $previewVolumeUid = null;
    public $previewSubpath = null;

    public function rules(): array
    {
        return [
            [['previewVolumeUid', 'previewSubpath'], 'string'],
            [['previewVolumeUid'], 'required']
        ];
    }
}
