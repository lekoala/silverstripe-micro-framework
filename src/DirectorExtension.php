<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Extension;

/**
 * Automap url segment
 */
class DirectorExtension extends Extension
{
    /**
     * Extend SilverStripe\Control\Director::handleRequest
     *
     * @param array $rules
     * @return void
     */
    public function updateRules(&$rules)
    {
        // Update security
        foreach ($rules as $rule => $ruleHandler) {
            if ($rule == 'Security//$Action/$ID/$OtherID') {
                $rules[$rule] = MicroSecurity::class;
            }
            // We don't use CMS
            if ($rule == 'CMSSecurity//$Action/$ID/$OtherID') {
                unset($rules[$rule]);
            }
        }

        // Auto routing by url_segment
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

        // Clean unecessary middlewares
        $middlewares = $this->owner->getMiddlewares();
        $filteredMiddlewares = [];
        $excludedMiddlewares = [
            'AllowedHostsMiddleware',
            'RequestProcessorMiddleware',
            'ExecMetricMiddleware',
            'ErrorControlChainMiddleware',
        ];
        foreach ($middlewares as $name => $inst) {
            if (in_array($name, $excludedMiddlewares)) {
                continue;
            }
            $filteredMiddlewares[$name] = $inst;
        }
        $this->owner->setMiddlewares($filteredMiddlewares);
    }
}
