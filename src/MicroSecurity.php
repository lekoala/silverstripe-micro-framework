<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Security\Security;
use SilverStripe\Security\Authenticator;

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

        // Without db, we only support default admin
        if (!MicroKernel::usesDatabase()) {
            $auth = $this->getAuthenticators();
            $auth['default'] = new DefaultAdminAuthenticator;
            $this->setAuthenticators($auth);
        }
    }

    /**
     * Prepare the controller for handling the response to this request
     *
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
