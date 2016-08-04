<?php

namespace Ntb\RestAPI\OAuth;

/**
 * I'm choosing not to use OAuth or any of the other social API abstractions out there because
 * 1) they are all very complicated and 2) the ones that worked didn't have all the services we
 * need and 3) all of these social sites now provide really nice API's themselves so creating our
 * own strategies/drivers is very simple given the very simple task we need to do and that we
 * already have an access token.
 *
 * @author Mark Guinn <mark@adaircreative.com>
 * @date 12.18.2015
 * @author Christian Blank <c.blank@notthatbad.net>
 */
interface ISocialApi {
    /**
     * Checks with the site to confirm that the given token is indeed valid
     * and corresponds with the userID we were given. It can do anything else
     * it needs as well (e.g. facebook provides a debug_token endpoint)
     *
     * @param string $token
     * @param string $userID
     * @return false
     */
    public function validateToken($token, $userID);

    /**
     * @param string $token
     * @return array
     */
    public function getProfileData($token);
}
