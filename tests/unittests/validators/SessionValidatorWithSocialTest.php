<?php

namespace Ntb\RestAPI\OAuth;

use Config;
use Ntb\RestAPI\TestHelper;
use SapphireTest;

/**
 * Test mechanisms in social member authenticator
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class SessionValidatorWithSocialTest extends SapphireTest {

    public function setUp() {
        parent::setUp();
        Config::inst()->update('SessionValidatorWithSocial', 'token_name', 'token');
        Config::inst()->update('SessionValidatorWithSocial', 'auth_service_name', 'authService');
        Config::inst()->update('SessionValidatorWithSocial', 'email_name', 'email');
        Config::inst()->update('SessionValidatorWithSocial', 'user_id_name', 'userID');
        Config::inst()->update('SessionValidatorWithSocial', 'password_name', 'password');
    }

    public function testValidateWithEmailPassword() {
        $data = SessionValidatorWithSocial::validate(['email' => 'mail@example.com', 'password' => 'pass']);
        $this->assertTrue(array_key_exists('Email', $data));
        $this->assertTrue(array_key_exists('Password', $data));
        $this->assertEquals('pass', $data['Password']);
        $this->assertEquals('mail@example.com', $data['Email']);
        $data = SessionValidatorWithSocial::validate(['email' => 'mail@example.com', 'password' => 'pass']);
        $this->assertTrue(array_key_exists('Email', $data));
        $this->assertTrue(array_key_exists('Password', $data));
        $this->assertEquals('pass', $data['Password']);
        $this->assertEquals('mail@example.com', $data['Email']);
    }

    public function testValidateWithToken() {
        $data = SessionValidatorWithSocial::validate(
            ['token' => 'fooBarBaz', 'authService' => 'facebook', 'userID' => '123456']);
        $this->assertTrue(array_key_exists('Token', $data));
        $this->assertTrue(array_key_exists('AuthService', $data));
        $this->assertTrue(array_key_exists('UserID', $data));
        $this->assertEquals('fooBarBaz', $data['Token']);
        $this->assertEquals('facebook', $data['AuthService']);
        $this->assertEquals('123456', $data['UserID']);
    }

    public function testValidateEmpty() {
        TestHelper::assertException(function() {
            SessionValidatorWithSocial::validate([]);
        },
        'ValidationException');
    }

    public function testValidateWithoutAllData() {
        TestHelper::assertException(function() {
            SessionValidatorWithSocial::validate(['token' => 'fooBarBaz']);
        },
        'ValidationException');
    }

}
