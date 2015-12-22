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
        throw new RestSystemException('Google authentication has not been implemented yet. Coming soon!', 500);
    }
}