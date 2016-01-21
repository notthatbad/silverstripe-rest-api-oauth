<?php

use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookRequest;
use Mockery as m;

/**
 * Test session endpoint with social login via Facebook.
 *
 * @preserveGlobalState disabled
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class FacebookSocialSessionEndpointTest extends RestTest {


    public function setUp() {
        parent::setUp();
        Config::inst()->update('Director', 'rules', [
            'v/1/test-session/$ID/$OtherID' => 'SessionController',
        ]);
    }

    public function testCreateSession() {
        $this->createUser();
        $this->mockFacebook();
        $data = [
            'token' => 'foo_token',
            'authService' => 'facebook',
            'userID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST']);
        $this->assertTrue(array_key_exists('session', $result));
        $this->assertTrue(array_key_exists('token', $result['session']));
    }

    public function testCreateSessionWithoutToken() {
        $this->createUser();
        $this->mockFacebook();
        $data = [
            'authService' => 'facebook',
            'userID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 422]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithoutUserId() {
        $this->createUser();
        $this->mockFacebook();
        $data = [
            'token' => 'foo_token',
            'authService' => 'facebook'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 422]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithWrongUser() {
        $this->createUser();
        $this->mockFacebook();
        $data = [
            'token' => 'foo_token',
            'authService' => 'facebook',
            'userID' => 'bar_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 401]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithWrongToken() {
        $this->createUser();
        $this->mockFacebook();
        $data = [
            'token' => 'bar_token',
            'authService' => 'facebook',
            'userID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 401]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    private function mockFacebook() {
        $clientMock = m::mock('overload:Facebook\FacebookClient');
        $clientMock->shouldReceive('sendRequest')
            ->once()
            ->andReturnUsing(function(FacebookRequest $request) {
                if($request->getAccessToken() == 'foo_token') {
                    return new Facebook\FacebookResponse(
                        new \Facebook\FacebookRequest(),
                        json_encode(["id" => "foo_user", "name" => "Foo User"]),
                        200);
                }
                throw new FacebookSDKException();
            });
    }

    private function createUser() {
        // create user
        $u = new Member();
        $u->write();
        $s = new \Ntb\SocialIdentity([
            'UserID' => 'foo_user',
            'AuthService' => 'facebook',
            'MemberID' => $u->ID
        ]);
        $s->write();
    }

}
