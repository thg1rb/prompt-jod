<?php

namespace App\Enums;

enum AlertType: string
{
    case Warning80 = 'warning_80';
    case Warning100 = 'warning_100';
    case Exceeded = 'exceeded';
}
