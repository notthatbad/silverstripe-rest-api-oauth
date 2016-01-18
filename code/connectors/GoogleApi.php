<?php

/**
 *
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 */
class GoogleApi implements ISocialApi {
    /**
     * Checks with the site to confirm that the given token is indeed valid
     * and corresponds with the userID we were given. It can do anything else
     * it needs as well (e.g. facebook provides a debug_token endpoint)
     *
     * @param string $token
     * @param string $userID
     * @return bool
     * @throws RestSystemException
     * @throws RestUserException
     */
    public function validateToken($token, $userID) {
        set_error_handler(
            create_function('$severity, $message', 'throw new ErrorException($message);')
        );
        $client = new Google_Client();
        $client->setClientId(Config::inst()->get('GoogleApi', 'AppID'));
        $client->setClientSecret(Config::inst()->get('GoogleApi', 'AppSecret'));
        $client->addScope(Google_Service_PlusDomains::PLUS_ME);
        $ticket = $client->verifyIdToken($token);
        $client->setAccessToken($ticket);
        $service = new Google_Service_Plus($client);
        try {
            $result = $service->people->get('me');
            restore_error_handler();
            if ($result) {
                return $result['id'] === $userID;
            }
            return false;
        } catch (Google_Service_Exception $e) {
            restore_error_handler();
            throw new RestUserException($e->getMessage(), $e->getCode(), 401);
        } catch(Exception $e) {
            restore_error_handler();
            throw new RestSystemException($e->getMessage(), $e->getCode());
        }
    }
}