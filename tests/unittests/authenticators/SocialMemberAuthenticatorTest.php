<?php

/**
 * Test mechanisms in social member authenticator
 */
class SocialMemberAuthenticatorTest extends SapphireTest {

    public function testValidateTokenWithWrongSocialAdapter() {
        $this->assertFalse(SocialMemberAuthenticator::validate_token('randomToken1234', 'notSupported', 'user'));
    }

}
