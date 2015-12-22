<?php
/**
 * Overrides the default authenticator to allow either Email and Password OR Token, AuthService, and UserID.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 * @package simple-giving
 * @subpackage auth
 */
class SocialMemberAuthenticator extends MemberAuthenticator {
    private static $social_services = [
        'facebook' => 'FacebookApi',
        'google'   => 'GoogleApi',
    ];
    /**
     * @param string $token
     * @param string $service
     * @param string $userID
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
     */
    protected static function authenticate_member($data, $form, &$success) {
        if (!empty($data['Token'])) {
            $success = false;
            $result = new ValidationResult();
            /** @var Member $member */
            $member = null;
            // First check that the token is valid
            if (self::validate_token($data['Token'], $data['AuthService'], $data['UserID'])) {
                // Second, check that the Member exists
                $identity = SocialIdentity::get()->filter([
                    'AuthService' => $data['AuthService'],
                    'UserID'      => $data['UserID'],
                ])->first();
                if ($identity) {
                    $member = $identity->Member();
                }
                if ($member) {
                    $success = true;
                } else {
                    $result->error("User not found");
                }
            } else {
                $result->error("Invalid access token");
            }
            // Emit failure to member and form (if available)
            if(!$success) {
                if ($member) $member->registerFailedLogin();
                if ($form) $form->sessionMessage($result->message(), 'bad');
            } else {
                if ($member) $member->registerSuccessfulLogin();
            }
            return $member;
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