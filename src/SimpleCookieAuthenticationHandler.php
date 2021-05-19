<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Security\Member;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\MemberAuthenticator\CookieAuthenticationHandler;

/**
 * We extend the default cookie auth handler in order to avoid
 * direct calls to Member::get if the database is not configured
 */
class SimpleCookieAuthenticationHandler extends CookieAuthenticationHandler
{
    /**
     * @param HTTPRequest $request
     * @return Member
     */
    public function authenticateRequest(HTTPRequest $request)
    {
        if (!MicroKernel::usesDatabase()) {
            return null;
        }

        return parent::authenticateRequest($request);
    }
}
