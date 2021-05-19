<?php

namespace LeKoala\MicroFramework;

use SilverStripe\View\ArrayData;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\DefaultAdminService;
use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;

/**
 * Authenticator for the default admin
 */
class DefaultAdminAuthenticator extends MemberAuthenticator
{
    public function authenticate(array $data, HTTPRequest $request, ValidationResult &$result = null)
    {
        $member = $this->authenticateAdmin($data, $result, $request);

        if ($member) {
            $request->getSession()->clear('BackURL');
        }

        return $result->isValid() ? $member : null;
    }

    /**
     * @param array $data
     * @param HTTPRequest $request
     * @return Member
     */
    public static function buildDefaultMember($data = [], HTTPRequest $request)
    {
        $data['ClassName'] = Member::class;
        if (empty($data['ID'])) {
            $data['ID'] = 1;
        }
        if (empty($data['Email'])) {
            $data['Email'] = $request->getSession()->get("Email");
        }
        if (empty($data['FirstName'])) {
            $data['FirstName'] = explode("@", $data["Email"])[0];
        }
        if (empty($data['Surname'])) {
            $data['Surname'] = 'Admin';
        }
        $data['FailedLoginCount'] = 0;
        $data['TempIDHash'] = null;
        $data['TempIDExpired'] = null;
        $data['LockedOutUntil'] = null;

        // Create an anonymous class to avoid polluting namespace
        $class = new class extends Member
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

            protected function loadLazyFields($class = null)
            {
                return true;
            }

            public function getHtmlEditorConfigForCMS()
            {
                return 'cms';
            }

            public function onBeforeWrite()
            {
                $this->brokenOnWrite = false;
            }
        };

        return new $class($data, DataObject::CREATE_HYDRATED);
    }

    /**
     * Attempt to find and authenticate member if possible from the given data
     *
     * @skipUpgrade
     * @param array $data Form submitted data
     * @param ValidationResult $result
     * @param HTTPRequest $request
     * @return ArrayData Found member, regardless of successful login
     */
    protected function authenticateAdmin($data, ValidationResult &$result = null, HTTPRequest $request)
    {
        $email = !empty($data['Email']) ? $data['Email'] : null;
        $result = $result ?: ValidationResult::create();

        // Check default login (see Security::setDefaultAdmin())
        $asDefaultAdmin = DefaultAdminService::isDefaultAdmin($email);
        if ($asDefaultAdmin) {
            // If logging is as default admin, ensure record is setup correctly
            $member = self::buildDefaultMember($data, $request);
            // Store email for later use
            $session = $request->hasSession();
            if ($session) {
                $request->getSession()->set('Email', $email);
            }
            if ($result->isValid()) {
                // Check if default admin credentials are correct
                if (DefaultAdminService::isDefaultAdminCredentials($email, $data['Password'])) {
                    return $member;
                } else {
                    $result->addError(_t(
                        'SilverStripe\\Security\\Member.ERRORWRONGCRED',
                        "The provided details don't seem to be correct. Please try again."
                    ));
                }
            }
        }

        // A non-existing member occurred. This will make the result "valid" so let's invalidate
        $result->addError(_t(
            'SilverStripe\\Security\\Member.ERRORWRONGCRED',
            "The provided details don't seem to be correct. Please try again."
        ));
        return null;
    }

    /**
     * Check if the passed password matches the stored one (if the member is not locked out).
     *
     * Note, we don't return early, to prevent differences in timings to give away if a member
     * password is invalid.
     *
     * @param Member $member
     * @param string $password
     * @param ValidationResult $result
     * @return ValidationResult
     */
    public function checkPassword(Member $member, $password, ValidationResult &$result = null)
    {
        // Allow default admin to login as self
        if (DefaultAdminService::isDefaultAdminCredentials($member->Email, $password)) {
            return $result;
        }

        // Otherwise, return error
        $result->addError(_t(
            __CLASS__ . '.ERRORWRONGCRED',
            'The provided details don\'t seem to be correct. Please try again.'
        ));

        return $result;
    }
}
