<?php

namespace App\Support;

final class StatusMap
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'draft' => 'Draft',
            'waiting_acceptance' => 'Menunggu Konfirmasi',
            'accepted' => 'Diterima',
            'on_the_way' => 'Dalam Perjalanan',
            'arrived' => 'Tiba di Lokasi',
            'installation' => 'Pemasangan',
            'finished' => 'Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'failed' => 'Gagal',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function colors(): array
    {
        return [
            'draft' => 'gray',
            'waiting_acceptance' => 'amber',
            'accepted' => 'blue',
            'on_the_way' => 'indigo',
            'arrived' => 'cyan',
            'installation' => 'violet',
            'finished' => 'green',
            'rejected' => 'rose',
            'cancelled' => 'gray',
            'failed' => 'red',
        ];
    }

    public static function label(?string $value): string
    {
        return self::labels()[$value ?? ''] ?? ucfirst((string) $value);
    }

    public static function color(?string $value): string
    {
        return self::colors()[$value ?? ''] ?? 'gray';
    }
}
