<?php

namespace Ntb\RestAPI\OAuth;

use Form;
use Injector;
use Member;
use MemberAuthenticator;
use Ntb\RestAPI\RestUserException;
use Ntb\SocialIdentity;

/**
 * Overrides the default authenticator to allow either Email and Password OR Token, AuthService, and UserID.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class SocialMemberAuthenticator extends MemberAuthenticator {
    private static $social_services = [
        'facebook' => 'Ntb\RestAPI\OAuth\FacebookApi',
        'google'   => 'Ntb\RestAPI\OAuth\GoogleApi',
    ];

    /**
     * @var bool - if this is false, the account must be registered with a social identity to use it for authentication
     *             if this is true, the social account can authenticate the user as long as the email matches and a new
     *             SocialIdentity will be attached to the member.
     */
    private static $allow_login_to_connect = true;

    /**
     * @param string $token the oauth token for the specified service
     * @param string $service the name of the service (eg. `facebook` or `google`)
     * @param string $userID the id of the user in the service
     * @return bool
     */
    public static function validate_token($token, $service, $userID) {
        $serviceApi = self::get_service_api($service);
        if(!$serviceApi) {
            return false;
        }
        return $serviceApi->validateToken($token, $userID);
    }

    /**
     * @param string $token the oauth token for the specified service
     * @param string $service the name of the service (eg. `facebook` or `google`)
     * @return array
     */
    public static function get_profile($token, $service) {
        $serviceApi = self::get_service_api($service);
        if(!$serviceApi) {
            return null;
        }
        return $serviceApi->getProfileData($token);
    }

    /**
     * @param string $service
     * @return bool|ISocialApi
     */
    public static function get_service_api($service) {
        $serviceMap = self::config()->social_services ?: [];
        if (empty($service) || empty($serviceMap[$service])) {
            return false;
        }
        return Injector::inst()->get($serviceMap[$service]);
    }

    /**
     * Attempt to find and authenticate member if possible from the given data
     *
     * @param array $data
     * @param Form $form
     * @param bool &$success Success flag
     * @return Member Found member, regardless of successful login
     * @throws RestUserException
     */
    protected static function authenticate_member($data, $form, &$success) {
        if (!empty($data['Token'])) {
            /** @var Member $member */
            $member = null;
            // First check that the token is valid
            if (self::validate_token($data['Token'], $data['AuthService'], $data['UserID'])) {
                // Second, check that the Member exists
                /** @var SocialIdentity $identity */
                $identity = SocialIdentity::get()->filter([
                    'AuthService' => $data['AuthService'],
                    'UserID' => $data['UserID']
                ])->first();

                if ($identity) {
                    $member = $identity->Member();
                    $success = true;
                    return $member;
                } elseif (self::config()->allow_login_to_connect) {
                    $profile = self::get_profile($data['Token'], $data['AuthService']);
                    $member = Member::get()->filter('Email', $profile['Email'])->first();
                    if ($member && !empty($profile['Email'])) {
                        $identity = new SocialIdentity();
                        $identity->MemberID = $member->ID;
                        $identity->AuthService = $data['AuthService'];
                        $identity->UserID = $data['UserID'];
                        $identity->write();
                        $success = true;
                        return $member;
                    }
                }

                throw new RestUserException("User not found", 401, 401);
            } else {
                throw new RestUserException("Invalid access token", 401, 401);
            }
        } else {
            return parent::authenticate_member($data, $form, $success);
        }
    }

    /**
     * Get the name of the authentication method
     *
     * @return string Returns the name of the authentication method.
     */
    public static function get_name() {
        return _t('SocialMemberAuthenticator.TITLE', "E-mail &amp; Password or Social Network");
    }
}
