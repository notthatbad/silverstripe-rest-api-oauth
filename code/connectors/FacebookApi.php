<?php

/**
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 */
class FacebookApi extends Object implements ISocialApi {
    /** @var string */
    private static $app_id = '';

    /** @var string */
    private static $app_secret = '';

    /**
     * Checks with the site to confirm that the given token is indeed valid
     * and corresponds with the userID we were given. It can do anything else
     * it needs as well (e.g. facebook provides a debug_token endpoint)
     *
     * @param string $token
     * @param string $userID
     * @return boolean
     */
    public function validateToken($token, $userID) {
        $fb = new Facebook\Facebook([
            'app_id' => self::config()->app_id,
            'app_secret' => self::config()->app_secret,
            'default_graph_version' => 'v2.2',
        ]);

        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name', $token);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            SS_Log::log("Graph returned an error: " . $e->getMessage(), SS_Log::ERR);
            return false;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            SS_Log::log('Facebook SDK returned an error: ' . $e->getMessage(), SS_Log::ERR);
            return false;
        }

        $user = $response->getGraphUser();
        return $user->getId() == $userID;
    }
}
