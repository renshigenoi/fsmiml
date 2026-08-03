<?php

namespace App\Modules\Notification\Providers;

use App\Mail\NotificationMessageMail;
use App\Modules\Notification\Data\NotificationContent;
use App\Modules\Notification\Data\ProviderResult;
use App\Modules\Notification\Exceptions\NotificationDeliveryException;
use App\Modules\Notification\Models\Notification;
use App\Modules\Notification\Providers\Contracts\NotificationProvider;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Sends through Laravel's configured mail driver. With MAIL_MAILER=log the
 * message is written to the log and the audit is marked as sent (dev mode).
 */
final class EmailNotificationProvider implements NotificationProvider
{
    public function send(Notification $notification, NotificationContent $content): ProviderResult
    {
        try {
            Mail::to($notification->recipient)->send(new NotificationMessageMail(
                $content->title,
                $content->body,
                $content->trackingUrl,
                [
                    'address' => config('notifications.from.address'),
                    'name' => config('notifications.from.name'),
                ],
            ));
        } catch (Throwable $exception) {
            throw new NotificationDeliveryException('Email delivery failed: '.Str::limit($exception->getMessage(), 500), 0, $exception);
        }

        return new ProviderResult;
    }
}
