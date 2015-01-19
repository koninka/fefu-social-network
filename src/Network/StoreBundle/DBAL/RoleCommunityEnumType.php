<?php

namespace Network\StoreBundle\DBAL;

class RoleCommunityEnumType extends EnumType
{

    const RC_OWNER = 'role_community_owner';
    const RC_PARTICIPANT = 'role_community_participant';
    const RC_INVITEE = 'role_community_invitee';
    const RC_ASKING = 'role_community_asking';

    protected $name = 'roleCommunityEnumType';
    protected $values = [
        self::RC_OWNER,
        self::RC_PARTICIPANT,
        self::RC_INVITEE,
        self::RC_ASKING
    ];

}
