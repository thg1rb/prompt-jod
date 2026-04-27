<?php

namespace App\Enums;

enum AlertStatus: string
{
    case Sent = 'sent';
    case Read = 'read';
    case Dismissed = 'dismissed';
}
