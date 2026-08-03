<?php

namespace App\Modules\Notification\Data;

readonly class NotificationContent
{
    public function __construct(
        public string $title,
        public string $body,
        public ?string $trackingUrl = null,
    ) {}

    /**
     * @param  array{title?: string, body?: string, tracking_url?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            body: (string) ($data['body'] ?? ''),
            trackingUrl: isset($data['tracking_url']) ? (string) $data['tracking_url'] : null,
        );
    }

    /**
     * @return array{title: string, body: string, tracking_url: string|null}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'tracking_url' => $this->trackingUrl,
        ];
    }
}
