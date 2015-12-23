<?php

/**
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 */
class FacebookApi extends Object implements ISocialApi {

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
            'app_id' => Config::inst()->get('FacebookApi', 'AppID'),
            'app_secret' => Config::inst()->get('FacebookApi', 'AppSecret'),
            'default_graph_version' => 'v2.2',
        ]);
        try {
            // Returns a `Facebook\FacebookResponse` object
            $response = $fb->get('/me?fields=id,name', $token);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            SS_Log::log("Graph returned an error: " . $e->getMessage(), SS_Log::INFO);
            return false;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            SS_Log::log('Facebook SDK returned an error: ' . $e->getMessage(), SS_Log::WARN);
            return false;
        }
        $user = $response->getGraphUser();
        return $user->getId() == $userID;
    }
}
