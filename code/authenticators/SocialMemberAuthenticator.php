<?php
use Ntb\SocialIdentity;

/**
 * Overrides the default authenticator to allow either Email and Password OR Token, AuthService, and UserID.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 */
class SocialMemberAuthenticator extends MemberAuthenticator {
    private static $social_services = [
        'facebook' => 'FacebookApi',
        'google'   => 'GoogleApi',
    ];

    /**
     * @param string $token the oauth token for the specified service
     * @param string $service the name of the service (eg. `facebook` or `google`)
     * @param string $userID the id of the user in the service
     * @return bool
     */
    public static function validate_token($token, $service, $userID) {
        $serviceMap = self::config()->social_services ?: [];
        if (empty($service) || empty($serviceMap[$service])) return false;
        /** @var ISocialApi $serviceApi */
        $serviceApi = Injector::inst()->get($serviceMap[$service]);
        return $serviceApi->validateToken($token, $userID);
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