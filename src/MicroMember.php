<?php

namespace LeKoala\MicroFramework;

use SilverStripe\Security\Member;

class MicroMember extends Member
{
    public function isPasswordExpired()
    {
        return false;
    }

    public function registerFailedLogin()
    {
        return true;
    }

    public function registerSuccessfulLogin()
    {
        return true;
    }

    public function regenerateTempID()
    {
        return true;
    }

    public function onBeforeWrite()
    {
        $this->brokenOnWrite = false;
    }
}
