<?php

namespace App\Modules\Notification\Enums;

enum NotificationChannel: string
{
    case Push = 'push';
    case WhatsApp = 'whatsapp';
    case Email = 'email';
}
