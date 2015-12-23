<?php

use Mockery as m;

/**
 * Test session endpoint with social login via Google.
 *
 */
class GoogleSocialSessionEndpointTest extends RestTest {


    public function setUp() {
        parent::setUp();
        Config::inst()->update('Director', 'rules', [
            'v/1/test-session/$ID/$OtherID' => 'SessionController',
        ]);
    }

    public function testCreateSession() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'Token' => 'foo_token',
            'AuthService' => 'google',
            'UserID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST']);
        $this->assertTrue(array_key_exists('session', $result));
        $this->assertTrue(array_key_exists('token', $result['session']));
    }

    public function testCreateSessionWithoutToken() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'AuthService' => 'google',
            'UserID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 422]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithoutUserId() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'Token' => 'foo_token',
            'AuthService' => 'google'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 422]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithWrongUser() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'Token' => 'foo_token',
            'AuthService' => 'google',
            'UserID' => 'bar_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 401]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithWrongToken() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'Token' => 'bar_token',
            'AuthService' => 'google',
            'UserID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 401]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    private function mockGoogle() {
        $clientMock = m::mock('overload:Google_AccessToken_Verify');
        $clientMock->shouldReceive('verifyIdToken')
            ->once()
            ->andReturnUsing(function($idToken, $clientId) {
                if($idToken == 'foo_token') {
                    return ['sub' => 'foo_user'];
                }
                return false;
            });
    }

    private function createUser() {
        // create user
        $u = new Member();
        $u->write();
        $s = new \Ntb\SocialIdentity([
            'UserID' => 'foo_user',
            'AuthService' => 'google',
            'MemberID' => $u->ID
        ]);
        $s->write();
    }

}
