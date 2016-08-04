<?php

namespace Ntb\RestAPI\OAuth;

use Config;
use Injector;
use Member;
use Ntb\SocialIdentity;
use PHPUnit_Framework_MockObject_MockObject;
use SapphireTest;

/**
 * Test mechanisms in social member authenticator
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class SocialMemberAuthenticatorTest extends SapphireTest {
    protected $usesDatabase = true;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $mockFacebook;

    /** @var Member */
    protected $member;

    /** @var SocialIdentity */
    protected $identity;

    /** @var SocialMemberAuthenticator */
    protected $sut;


    public function setUp() {
        parent::setUp();

        $this->mockFacebook = $this->getMock('FacebookApi');
        Injector::inst()->registerService($this->mockFacebook, 'FacebookApi');

        $this->member = $this->getFixtureFactory()->createObject(
            'Member',
            'm1',
            ['Email' => 'test@test.com', 'Password' => 'tEst1234']
        );
        $this->identity = $this->getFixtureFactory()->createObject(
            'Ntb\SocialIdentity',
            'i1',
            [
                'AuthService' => 'facebook',
                'UserID'      => 'fb1234',
                'Member'      => '=>Member.m1',
            ]
        );
        $this->sut = new SocialMemberAuthenticator();
    }

    /**
     * Allows us to stub the facebook API's response about whether the access token matches
     * @param boolean $result
     */
    protected function givenFacebookValidatesTheTokenAs($result) {
        $this->mockFacebook
            ->expects($this->any())
            ->method('validateToken')
            ->will($this->returnValue($result));
    }

    /**
     * Allows us to stub the facebook API's response about the profile
     * @param array $data
     */
    protected function givenFacebookReturnsProfileData($data) {
        $data = array_merge([
            'FirstName' => 'Test',
            'Surname' => 'Person',
            'Email' => 'test@test.com',
            'Alias' => 'Tester',
            'BirthYear' => '1980',
            'Description' => 'this is just for testing',
            'Gender' => 'M',
            'ProfileImage' => 'https://placeholdit.imgix.net/~text?txtsize=33&txt=Obnoxious+Selfie&w=350&h=350'
        ], $data);
        $this->mockFacebook
            ->expects($this->any())
            ->method('getProfileData')
            ->will($this->returnValue($data));
    }

    public function testValidateTokenWithWrongSocialAdapter() {
        $this->assertFalse(SocialMemberAuthenticator::validate_token('randomToken1234', 'notSupported', 'user'));
    }

    public function testCanAuthenticateWithValidToken() {
        $this->givenFacebookValidatesTheTokenAs(true);
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'facebook', 'UserID' => 'fb1234']);
        $this->assertEquals($this->member->ID, $member->ID);
    }

    public function testInvalidToken() {
        $this->givenFacebookValidatesTheTokenAs(false);
        $this->setExpectedException('RestUserException');
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'facebook', 'UserID' => 'fb1234']);
        $this->assertNull($member);
    }

    public function testInvalidService() {
        $this->givenFacebookValidatesTheTokenAs(true);
        $this->setExpectedException('RestUserException');
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'hackertown', 'UserID' => 'fb1234']);
        $this->assertNull($member);
    }

    public function testInvalidUserId() {
        $this->givenFacebookValidatesTheTokenAs(true);
        $this->setExpectedException('RestUserException');
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'facebook', 'UserID' => 'fb999']);
        $this->assertNull($member);
    }

    public function testCanAuthenticateWithEmailAndPassword() {
        $member = $this->sut->authenticate(['Email' => 'test@test.com', 'Password' => 'tEst1234']);
        $this->assertEquals($this->member->ID, $member->ID);
    }

    public function testInvalidPassword() {
        $member = $this->sut->authenticate(['Email' => 'test@test.com', 'Password' => 'wrong']);
        $this->assertNull($member);
    }

    public function testEmpty() {
        $member = $this->sut->authenticate([]);
        $this->assertNull($member);
    }

    public function testCanConnectSocialAccountThroughLogin() {
        Config::inst()->update('SocialMemberAuthenticator', 'allow_login_to_connect', true);
        $this->identity->delete();
        $this->givenFacebookValidatesTheTokenAs(true);
        $this->givenFacebookReturnsProfileData(['Email' => 'test@test.com']);
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'facebook', 'UserID' => 'fb1234']);
        $this->assertEquals($this->member->ID, $member->ID);
    }

    public function testCannotConnectSocialAccountWithMismatchedEmail() {
        Config::inst()->update('SocialMemberAuthenticator', 'allow_login_to_connect', true);
        $this->identity->delete();
        $this->givenFacebookValidatesTheTokenAs(true);
        $this->givenFacebookReturnsProfileData(['Email' => 'wrong@test.com']);
        $this->setExpectedException('RestUserException');
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'facebook', 'UserID' => 'fb1234']);
        $this->assertNull($member);
    }

    public function testCanDisableConnectingSocialAccountThroughLogin() {
        Config::inst()->update('SocialMemberAuthenticator', 'allow_login_to_connect', false);
        $this->identity->delete();
        $this->givenFacebookValidatesTheTokenAs(true);
        $this->setExpectedException('RestUserException');
        $member = $this->sut->authenticate(['Token' => 'abc123', 'AuthService' => 'facebook', 'UserID' => 'fb1234']);
        $this->assertNull($member);
    }
}
