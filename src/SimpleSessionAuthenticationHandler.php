<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Security\Member;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\MemberAuthenticator\SessionAuthenticationHandler;

class SimpleSessionAuthenticationHandler extends SessionAuthenticationHandler
{
    /**
     * @param HTTPRequest $request
     * @return Member
     */
    public function authenticateRequest(HTTPRequest $request)
    {
        $session = $request->getSession();

        // Sessions are only started when a session cookie is detected
        if (!$session->isStarted()) {
            return null;
        }

        // If ID is a bad ID it will be treated as if the user is not logged in, rather than throwing a
        // ValidationException
        $id = $session->get($this->getSessionVariable());
        if (!$id) {
            return null;
        }
        if (MicroKernel::usesDatabase()) {
            $member = Member::get()->byID($id);
        } else {
            $member = DefaultAdminAuthenticator::buildDefaultMember(["ID" => $id], $request);
        }

        return $member;
    }
}
