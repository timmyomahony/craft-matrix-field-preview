<?php

/**
 * Plugin Settings
 *
 * These are the project.yaml settings, not the custom database settings that use within the plugin
 */

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

    // Controls the default behaviour for new fields and previews
    public $defaultFieldEnabledSetting = true;
    public $defaultFieldTakeoverSetting = true;
    public $defaultPreviewEnabledSetting = true;

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
                    'defaultFieldEnabledSetting',
                ],
                'boolean',
            ],
            [
                [
                    'defaultFieldTakeoverSetting',
                ],
                'boolean',
            ],
            [
                [
                    'defaultPreviewEnabledSetting',
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
