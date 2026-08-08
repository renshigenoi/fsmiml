<?php

namespace App\Modules\Legacy\Support;

/**
 * Penerjemah kode window_position & window_position_detail dari DB legacy
 * (query SHOW_SalesDetail) menjadi label yang bisa dibaca manusia.
 */
final class SalesDetailMapper
{
    public const WINDOW_POSITIONS = [
        '1' => 'Front',
        '2' => 'Side',
        '3' => 'Rear',
        '4' => 'Sun Roof',
        '5' => 'Side + Rear',
        '6' => 'Front + Rear',
        '7' => 'Side [FULL]',
    ];

    /**
     * Kunci pertama = window_position; kunci kedua = window_position_detail.
     *
     * @var array<string, array<string, string>>
     */
    public const WINDOW_POSITION_DETAILS = [
        '1' => [
            '1' => 'Full',
            '2' => 'Alingan',
        ],
        '2' => [
            '1' => 'Full',
            '2' => '1 Kaca',
            '11' => '1 Kaca + 1 Kaca Mati',
            '12' => '1 Kaca + 2 Kaca Mati',
            '3' => '2 Kaca depan',
            '4' => '2 Kaca tengah',
            '5' => '2 Kaca belakang',
            '10' => '2 Kaca',
            '13' => '2 Kaca + 1 Kaca Mati',
            '14' => '2 Kaca + 2 Kaca Mati',
            '17' => '2 Kaca + 1 Sun Roof',
            '6' => '3 Kaca',
            '15' => '3 Kaca + 1 Kaca Mati',
            '16' => '3 Kaca + 2 Kaca Mati',
            '7' => '4 Kaca',
            '8' => '2 Kaca Mati',
            '9' => '1 Kaca Mati',
        ],
        '3' => [
            '1' => 'Full',
            '4' => 'Full + 1 Kaca Mati',
            '5' => 'Full + 2 Kaca Mati',
            '6' => 'Full + 1 Kaca',
            '7' => 'Full + 2 Kaca',
            '3' => '1 Kaca',
            '2' => '2 Kaca',
        ],
        '4' => [
            '1' => '1 Kaca depan',
            '2' => '1 Kaca tengah',
            '3' => '1 Kaca belakang',
            '4' => '2 Kaca',
            '5' => '3 Kaca',
            '6' => 'Panoramic',
        ],
        '5' => [
            '1' => 'Full',
        ],
        '6' => [
            '1' => 'Full',
        ],
        '7' => [
            '1' => 'Full',
            '2' => '1 Kaca',
            '11' => '1 Kaca + 1 Kaca Mati',
            '12' => '1 Kaca + 2 Kaca Mati',
            '3' => '2 Kaca depan',
            '4' => '2 Kaca tengah',
            '5' => '2 Kaca belakang',
            '10' => '2 Kaca',
            '13' => '2 Kaca + 1 Kaca Mati',
            '14' => '2 Kaca + 2 Kaca Mati',
            '17' => '2 Kaca + 1 Sun Roof',
            '6' => '3 Kaca',
            '15' => '3 Kaca + 1 Kaca Mati',
            '16' => '3 Kaca + 2 Kaca Mati',
            '7' => '4 Kaca',
            '8' => '2 Kaca Mati',
            '9' => '1 Kaca Mati',
        ],
    ];

    public static function windowPositionLabel(?string $code): ?string
    {
        $code = trim((string) $code);

        return $code === '' ? null : (self::WINDOW_POSITIONS[$code] ?? $code);
    }

    public static function windowPositionDetailLabel(?string $position, ?string $detail): ?string
    {
        $position = trim((string) $position);
        $detail = trim((string) $detail);

        if ($position === '' || $detail === '') {
            return null;
        }

        return self::WINDOW_POSITION_DETAILS[$position][$detail] ?? $detail;
    }
}
