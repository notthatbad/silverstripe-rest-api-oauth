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
     */
    public function validateToken($token, $userID) {
        $client = new Google_Client();
        $client->setClientId(Config::inst()->get('GoogleApi', 'AppID'));
        $client->setClientSecret(Config::inst()->get('GoogleApi', 'AppSecret'));
        $client->addScope(Google_Service_PlusDomains::PLUS_ME);
        $client->setAccessToken($token);
        $service = new Google_Service_Plus($client);
        // The library expects some things like expires_in along with the token, that we don't have
        // access to here. I don't love silencing errors like this but I don't see any other way.
        $result = @$service->people->get('me');
        if ($result) {
            return $result['id'] === $userID;
        }
        return false;
    }
}