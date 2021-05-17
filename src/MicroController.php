<?php

namespace LeKoala\MicroFramework;

use ReflectionClass;
use ReflectionNamedType;
use SilverStripe\View\SSViewer;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;

/**
 * This micro controller builds upon the default controller
 *
 * - Accept by default any action that accepts a HTTPRequest as "allowed"
 * - a forTemplate method to avoid errors when using $CurrentPage global variable
 * - set a base "Page" template regardless of class hierarchy
 * - set url segment for routing
 *
 * @link https://docs.silverstripe.org/en/4/developer_guides/controllers/
 */
class MicroController extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->URLSegment = self::config()->url_segment;
    }

    /**
     * Checks if this request handler has a specific action,
     * even if the current user cannot access it.
     * Includes class ancestry and extensions in the checks.
     *
     * @param string $action
     * @return bool
     */
    public function hasAction($action)
    {
        if ($action == 'index') {
            return true;
        }

        // Don't allow access to any non-public methods (inspect instance plus all extensions)
        $insts = array_merge([$this], (array) $this->getExtensionInstances());
        foreach ($insts as $inst) {
            if (!method_exists($inst, $action)) {
                continue;
            }
            $r = new ReflectionClass(get_class($inst));
            $m = $r->getMethod($action);
            if (!$m || !$m->isPublic()) {
                return false;
            }
            // But allow public actions that accept an instance of HTTPRequest
            $args = $m->getParameters();
            $firstArg = $args[0] ?? null;
            if ($firstArg) {
                // We use getType for PHP 8 compat
                $type = $firstArg->getType();
                if ($type instanceof ReflectionNamedType && $type->getName() == HTTPRequest::class) {
                    return true;
                }
            }
        }

        $action  = strtolower($action);
        $actions = $this->allowedActions();

        // Check if the action is defined in the allowed actions of any ancestry class
        // as either a key or value. Note that if the action is numeric, then keys are not
        // searched for actions to prevent actual array keys being recognised as actions.
        if (is_array($actions)) {
            $isKey   = !is_numeric($action) && array_key_exists($action, $actions);
            $isValue = in_array($action, $actions, true);
            if ($isKey || $isValue) {
                return true;
            }
        }

        $actionsWithoutExtra = $this->config()->get('allowed_actions', true);
        if (!is_array($actions) || !$actionsWithoutExtra) {
            if (!in_array(strtolower($action), ['run', 'doinit']) && method_exists($this, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check that the given action is allowed to be called from a URL.
     * It will interrogate {@link self::$allowed_actions} to determine this.
     *
     * @param string $action
     * @return bool
     * @throws Exception
     */
    public function checkAccessAction($action)
    {
        $actionOrigCasing = $action;
        $action = strtolower($action);

        // Get actions for this specific class (without inheritance)
        $definingClass = $this->definingClassForAction($actionOrigCasing);
        $allowedActions = $this->allowedActions($definingClass);

        // There is no rule but it is a valid action, allow by default
        if (!isset($allowedActions[$action]) && $this->hasAction($action)) {
            return true;
        }

        return parent::checkAccessAction($action);
    }


    /**
     * Return the viewer identified being the default handler for this Controller/Action combination.
     *
     * @param string $action
     * @return SSViewer
     */
    public function getViewer($action)
    {
        // Hard-coded templates
        if (isset($this->templates[$action]) && $this->templates[$action]) {
            $templates = $this->templates[$action];
        } elseif (isset($this->templates['index']) && $this->templates['index']) {
            $templates = $this->templates['index'];
        } elseif ($this->template) {
            $templates = $this->template;
        } else {
            $templates = $this->getDefaultTemplates($action);
        }

        return SSViewer::create($templates);
    }

    /**
     * Find a template. Page is always seen as the base template regardless
     * of the controller being linked to an actual page or not
     *
     * @param string $action
     * @return array
     */
    protected function getDefaultTemplates($action)
    {
        // Build templates based on class hierarchy
        $actionTemplates = [];
        $classTemplates = [];
        $parentClass = static::class;
        while ($parentClass !== parent::class) {
            // Ignore micro controller
            if ($parentClass == self::class) {
                $parentClass = get_parent_class($parentClass);
                continue;
            }
            // _action templates have higher priority
            if ($action && $action != 'index') {
                $actionTemplates[] = strtok($parentClass, '_') . '_' . $action;
            }
            // class templates have lower priority
            $classTemplates[] = strtok($parentClass, '_');
            $parentClass = get_parent_class($parentClass);
        }

        // Add controller templates for inheritance chain
        $templates = array_unique(array_merge($actionTemplates, $classTemplates));

        // Always accept Page as a base template
        if (!in_array('Page', $templates)) {
            $templates[] = 'Page';
        }
        return $templates;
    }

    /**
     * Avoid error if you call Me expecting this to be a page
     *
     * @return string
     */
    public function forTemplate()
    {
        return '(' . get_called_class() . ' controller)';
    }
}
