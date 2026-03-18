<?php

namespace App\Enums;

enum WorkspaceInviteRole: string
{
    case Owner = 'owner';
    case Member = 'member';
}
