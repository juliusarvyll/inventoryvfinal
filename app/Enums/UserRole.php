<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case ItStaff = 'IT Staff';
    case EndUser = 'End User';
}
