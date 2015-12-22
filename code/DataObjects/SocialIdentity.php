<?php

namespace Ntb;

/**
 * Class SocialIdentity
 * @package Ntb
 */
class SocialIdentity extends \DataObject {
    private static $db = [
        /**
         * The name of the connected auth service (eg. `twitter`, `google`, `facebook`).
         */
        'AuthService' => 'Varchar(50)',
        /**
         * The id of the member in the connected auth service.
         */
        'UserID' => 'Varchar(100)'
    ];

    private static $has_one = [
        /**
         * The member who owns this social id.
         */
        'Member' => 'Member'
    ];
}