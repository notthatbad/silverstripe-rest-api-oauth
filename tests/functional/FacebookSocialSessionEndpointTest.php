<?php

use Mockery as m;

/**
 * Test session endpoint with social login via Facebook.
 *
 */
class FacebookSocialSessionEndpointTest extends RestTest {


    public function setUp() {
        parent::setUp();
        Config::inst()->update('Director', 'rules', [
            'v/1/session/$ID/$OtherID' => 'SessionController',
        ]);
    }

    public function testCreateSessionWithToken() {
        // create user
        $u = new Member();
        $u->write();
        $s = new \Ntb\SocialIdentity([
            'UserID' => 'foo_user',
            'AuthService' => 'facebook',
            'MemberID' => $u->ID
        ]);
        $s->write();
        $data = [
            'Token' => 'foo_token',
            'AuthService' => 'facebook',
            'UserID' => 'foo_user'
        ];
        $clientMock = m::mock('overload:Facebook\FacebookClient');
        $clientMock->shouldReceive('sendRequest')
            ->once()
            ->andReturn(new Facebook\FacebookResponse(
                new \Facebook\FacebookRequest(),
                json_encode(["id" => "foo_user", "name" => "Foo User"]),
                200));
        $dataString = json_encode($data);
        $result = $this->makeApiRequest('sessions', ['body' => $dataString, 'method' => 'POST']);
        $this->assertTrue(array_key_exists('session', $result));
        $this->assertTrue(array_key_exists('token', $result['session']));
    }

}
