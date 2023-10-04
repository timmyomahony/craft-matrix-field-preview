<?php

namespace weareferal\matrixfieldpreview\models;

use craft\base\Model;

class Settings extends Model
{
    public $previewVolumeUid = null;
    public $previewSubpath = null;

    // For Neo only, when a field allows children, but there's only one
    // configured, then don't show Matrix Field Previews
    //
    // FIXME: Booleans are being saved as "" and "1"
    public $neoDisableForSingleChilden = false;

    public function rules(): array
    {
        return [
            [
                [
                    'previewVolumeUid',
                    'previewSubpath'
                ],
                'string'],
            [
                [
                    'neoDisableForSingleChilden',
                ],
                'boolean',
            ],
            [
                [
                    'previewVolumeUid'
                ],
                'required'],
        ];
    }
}
