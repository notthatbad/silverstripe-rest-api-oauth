<?php

/**
 * Test mechanisms in social member authenticator
 * @author Christian Blank <c.blank@notthatbad.net>
 */
class SocialMemberAuthenticatorTest extends SapphireTest {

    public function testValidateTokenWithWrongSocialAdapter() {
        $this->assertFalse(SocialMemberAuthenticator::validate_token('randomToken1234', 'notSupported', 'user'));
    }

}
