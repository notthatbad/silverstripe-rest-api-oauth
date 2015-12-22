<?php
/**
 * Replaces the default REST api validator to allow social tokens or email/password
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
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
        // allow either email or Email
        if (isset($data['email'])) $data['Email'] = $data['email'];
        if (isset($data['password'])) $data['Password'] = $data['password'];
        if (!empty($data['Token'])) {
            return [
                'Token'       => RestValidatorHelper::validate_string($data, 'Token'),
                'AuthService' => RestValidatorHelper::validate_string($data, 'AuthService'),
                'UserID'      => RestValidatorHelper::validate_string($data, 'UserID'),
            ];
        } else {
            return [
                'Email'    => RestValidatorHelper::validate_email($data, 'Email'),
                'Password' => RestValidatorHelper::validate_string($data, 'Password', ['min' => 3]),
            ];
        }
    }
}