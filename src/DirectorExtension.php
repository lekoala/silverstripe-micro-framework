<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Extension;

/**
 * Automap url segment
 */
class DirectorExtension extends Extension
{
    public function updateRules(&$rules)
    {
        // Update security
        foreach ($rules as $rule => $ruleHandler) {
            if ($rule == 'Security//$Action/$ID/$OtherID') {
                // $rules[$rule] = 'LeKoala\MicroFramework\MicroSecurity';
            }
            // We don't use CMS
            if ($rule == 'CMSSecurity//$Action/$ID/$OtherID') {
                unset($rules[$rule]);
            }
        }


        // Auto routing
        $controllers = ClassInfo::subclassesFor(MicroController::class);
        array_shift($controllers);

        foreach ($controllers as $controller) {
            $config = $controller::config();
            $segment = $config->url_segment;
            if (!$segment) {
                continue;
            }
            $rules[$segment . '//$Action/$ID/$OtherID'] = $controller;

            if ($config->is_home) {
                $rules[""] = $controller;
                $rules[$segment . '//$Action/$ID/$OtherID'] = "->/";
            }
        }
    }
}
