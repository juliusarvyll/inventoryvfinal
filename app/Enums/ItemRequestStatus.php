<?php

namespace App\Enums;

enum ItemRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Denied = 'denied';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
}
