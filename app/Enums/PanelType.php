<?php

namespace App\Enums;

use ArchTech\Enums\Options;
use ArchTech\Enums\Values;

enum PanelType: string
{
    use Options, Values;

    case ADMIN = 'admin';
    case STUDENT = 'student';
    case SUPERVISOR = 'supervisor';
}
