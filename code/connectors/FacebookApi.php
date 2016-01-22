<?php

/**
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 * @author Christian Blank <c.blank@notthatbad.net>
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

    /**
     * @param string $token
     * @return array
     */
    public function getProfileData($token) {
        $fb = new Facebook\Facebook([
            'app_id' => Config::inst()->get('FacebookApi', 'AppID'),
            'app_secret' => Config::inst()->get('FacebookApi', 'AppSecret'),
            'default_graph_version' => 'v2.2',
        ]);
        try {
            // Returns a `Facebook\FacebookResponse` object
            $fieldList = [
                'id', 'first_name', 'last_name', 'location', 'hometown', 'gender', 'email', 'birthday', 'about',
                'website'
            ];
            $profileResponse = $fb->get('/me?fields='.implode(',', $fieldList), $token);
            $imageResponse = $fb->get('/me/picture?width=1000&redirect=false', $token);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            SS_Log::log("Graph returned an error: " . $e->getMessage(), SS_Log::INFO);
            return false;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            SS_Log::log('Facebook SDK returned an error: ' . $e->getMessage(), SS_Log::WARN);
            return false;
        }
        $user = $profileResponse->getGraphUser();
        $imageData = $imageResponse->getGraphNode();
        $userData = [
            'FirstName' => $user->getFirstName(),
            'Surname' => $user->getLastName(),
            'Email' => $user->getEmail(),
            'Alias' => $user->getFirstName(),
            'BirthYear' => $user->getBirthday()->format("Y"),
            'Description' => $user->getField('about'),
            'Gender' => $user->getGender(),
            'ProfileImage' => $imageData->getField('url', null)
        ];
        return $userData;
    }
}
