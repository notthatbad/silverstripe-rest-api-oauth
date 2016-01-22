<?php
/**
 * Replaces the default REST api validator to allow social tokens or email/password
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class SessionValidatorWithSocial implements IRestValidator {

    /**
     * Validates the given data and returns a mapped version back to the caller.
     *
     * @param array $data
     * @return array
     * @throws ValidationException
     */
    public static function validate($data) {
        $tokenName = Config::inst()->get('SessionValidatorWithSocial', 'token_name');
        $emailName = Config::inst()->get('SessionValidatorWithSocial', 'email_name');
        $authServiceName = Config::inst()->get('SessionValidatorWithSocial', 'auth_service_name');
        $userIDName = Config::inst()->get('SessionValidatorWithSocial', 'user_id_name');
        $passwordName = Config::inst()->get('SessionValidatorWithSocial', 'password_name');
        // allow either email or Email
        if (array_key_exists($tokenName, $data)) {
            return [
                'Token'       => RestValidatorHelper::validate_string($data, $tokenName),
                'AuthService' => RestValidatorHelper::validate_string($data, $authServiceName),
                'UserID'      => RestValidatorHelper::validate_string($data, $userIDName),
            ];
        } else {
            return [
                'Email'    => RestValidatorHelper::validate_email($data, $emailName),
                'Password' => RestValidatorHelper::validate_string($data, $passwordName, ['min' => 3]),
            ];
        }
    }
}
