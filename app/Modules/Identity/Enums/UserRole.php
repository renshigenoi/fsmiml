<?php

namespace App\Modules\Identity\Enums;

enum UserRole: string
{
    case Administrator = 'administrator';
    case Coordinator = 'coordinator';
    case Technician = 'technician';
}
