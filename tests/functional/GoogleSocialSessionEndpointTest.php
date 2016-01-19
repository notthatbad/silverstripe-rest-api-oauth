<?php

use Mockery as m;
use Monolog\Logger;
use Psr\Http\Message\RequestInterface;

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

    public function tearDown() {
        Mockery::close();
    }

    public function testCreateSession() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'token' => 'foo_token',
            'authService' => 'google',
            'userID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST']);
        $this->assertTrue(array_key_exists('session', $result));
        $this->assertTrue(array_key_exists('token', $result['session']));
    }

    public function testCreateSessionWithoutToken() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'authService' => 'google',
            'userID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 422]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithoutUserId() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'token' => 'foo_token',
            'authService' => 'google'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 422]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithWrongUser() {
        $this->createUser();
        $this->mockGoogle();
        $data = [
            'token' => 'foo_token',
            'authService' => 'google',
            'userID' => 'bar_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 401]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    public function testCreateSessionWithWrongToken() {
        $this->createUser();
        $this->mockGoogle('bar_token');
        $data = [
            'token' => 'bar_token',
            'authService' => 'google',
            'userID' => 'foo_user'
        ];
        $result = $this->makeApiRequest('test-session', ['body' => json_encode($data), 'method' => 'POST', 'code' => 401]);
        $this->assertTrue(array_key_exists('code', $result));
        $this->assertTrue(array_key_exists('message', $result));
    }

    private function mockGoogle($token='foo_token') {
        $clientMock = m::mock('overload:Google_Client');
        $clientMock->shouldReceive('setClientId')->once();
        $clientMock->shouldReceive('setClientSecret')->once();
        $clientMock->shouldReceive('addScope')->once();
        $clientMock->shouldReceive('setAccessToken')->once();
        $clientMock->shouldReceive('shouldDefer')->once()->andReturn(false);
        $clientMock->shouldReceive('getLogger')->once()->andReturn(new Logger('google-api-php-client'));
        $clientMock->shouldReceive('execute')
            ->once()
            ->andReturnUsing(function(RequestInterface $request) use ($token) {
                if($token != 'foo_token') {
                    throw new Google_Service_Exception("Invalid Credentials");
                }
                return ["id" => "foo_user", "name" => "Foo User"];
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
