<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Security\Security;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Authenticator;

/**
 * Extend Security to avoid database less logins
 * and avoid cms dependency
 */
class MicroSecurity extends Security
{
    // We need to redeclare this
    private static $allowed_actions = [
        'basicauthlogin',
        'changepassword',
        'index',
        'login',
        'logout',
        'lostpassword',
        'passwordsent',
        'ping',
    ];

    protected function init()
    {
        parent::init();
    }

    /**
     * Avoid 404 errrors
     *
     * @return HTTPResponse
     */
    public function index()
    {
        if (Security::getCurrentUser()) {
            return $this->redirect($this->Link('logout'));
        }
        return $this->redirect($this->Link('login'));
    }

    /**
     * Prepare the controller for handling the response to this request
     *
     * @link https://github.com/silverstripe/silverstripe-framework/pull/9830
     * @param string $title Title to use
     * @return Controller
     */
    protected function getResponseController($title)
    {
        $pageClass = $this->config()->get('page_class');
        if (!$pageClass) {
            $pageClass = MicroController::class;
        }
        $controller = new $pageClass;
        $controller->Title = $title;
        $controller->URLSegment = 'Security';
        // Disable ID-based caching  of the log-in page by making it a random number
        $controller->ID = -1 * random_int(1, 10000000);

        $controller->setRequest($this->getRequest());
        $controller->doInit();

        return $controller;
    }

    public function login($request = null, $service = Authenticator::LOGIN)
    {
        return parent::login($request, $service);
    }
}
