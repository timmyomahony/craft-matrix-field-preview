<?php

namespace weareferal\matrixfieldpreview\services;

use Craft;
use craft\base\Component;


class UtilsService extends Component
{
    public function neoIsCompatibleVersion()
    {
        if (Craft::$app->plugins->isPluginEnabled("neo")) {
            // WARNING: if you call this method within the MatrixFieldPreview
            // class it will return null, presumably because not all plugins
            // have finished loading. Therefore I don't think we can check 
            // for the correct verion of neo when configuring routes and
            // services etc. within MatrixFieldPreview. This means all we can
            // do is warn the user via templates if their version of neo is
            // too low
            $neo = Craft::$app->plugins->getPlugin("neo");
            if ($neo) {
                return version_compare($neo->getVersion(), "2.8.14") != -1;
            }
        }
        return false;
    }
}
