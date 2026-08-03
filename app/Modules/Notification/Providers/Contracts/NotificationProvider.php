<?php

namespace App\Modules\Notification\Providers\Contracts;

use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;

interface NotificationProvider
{
    /**
     * Deliver a composed message and return the provider message id, if any.
     *
     * @throws NotificationDeliveryException
     */
    public function send(Notification $notification, NotificationContent $content): ProviderResult;
}
