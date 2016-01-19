<?php

/**
 * Test mechanisms in social member authenticator
 */
class SessionValidatorWithSocialTest extends SapphireTest {

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
