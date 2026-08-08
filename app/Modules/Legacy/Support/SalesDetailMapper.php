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

    public const INSTALLATION_TYPES = [
        '01' => 'Full (Front + Side + Rear)',
        '52' => 'Full + Accessories',
        '18' => 'Full + SunRoof',
        '20' => 'Full + Alingan',
        '33' => 'Full + Sunroof + Alingan',
        '02' => 'Standard (Side + Rear)',
        '53' => 'Standard (Side + Rear) + Accessories',
        '03' => 'Side + Rear + Alingan',
        '08' => 'Front Full',
        '04' => 'Front + Side',
        '05' => 'Front + Side 1 kc',
        '06' => 'Front + Side 2 kc',
        '42' => 'Front + Side 2 kc + 2 kc mati + Rear + Sunroof',
        '16' => 'Front + Side 3 kc',
        '36' => 'Front + Side 4 kc',
        '45' => 'Front + Side 1 kc + Rear',
        '43' => 'Front + Side 2 kc + Rear',
        '69' => 'Front + Side 2 kc + Sunroof',
        '49' => 'Front + Side 3 kc + Rear',
        '29' => 'Front + Side 4 kc + Rear',
        '74' => 'Front + Side 4 kc + Rear + Sunroof',
        '15' => 'Front + Side + SunRoof',
        '07' => 'Front + Rear',
        '54' => 'Front + Rear + Accessories',
        '46' => 'Front + Rear + SunRoof',
        '38' => 'Front + Sunroof',
        '65' => 'Front + Sunroof 2 kc',
        '39' => 'Front + Alingan',
        '40' => 'Front + Side 3 kc + Rear + kc mati',
        '41' => 'Front + 1 kc mati',
        '09' => 'Alingan',
        '10' => 'Side Full',
        '47' => 'Side Full + SunRoof',
        '27' => 'Side Full + Alingan',
        '11' => 'Side 1 kc',
        '30' => 'Side 1 kc + Alingan',
        '57' => 'Side 2 kc dpn + Alingan',
        '31' => 'Side 1 kc + Rear',
        '12' => 'Side 2 kc',
        '23' => 'Side 2 kc dpn + 2 kc tengah',
        '58' => 'Side 2 kc dpn + Rear + Accessories',
        '59' => 'Side 2 kc dpn + Rear + Alingan + Accessories',
        '13' => 'Side 2 kc tengah + 2 kc blkg',
        '56' => 'Side 2 kc tengah + 2 kc blkg + 2 SunRoof',
        '21' => 'Side 2 kc tengah + 2 kc blkg + Rear',
        '25' => 'Side 2 kc tengah + kc mati + Rear + SunRoof',
        '24' => 'Side 2 kc dpn + 2 kc tengah + 2 kc blkg',
        '17' => 'Side 2 kc tengah + kc mati + Rear',
        '19' => 'Side 2 kc + Rear',
        '28' => 'Side 2 kc + SunRoof',
        '26' => 'Side 3 kc',
        '32' => 'Side 3 kc + Rear',
        '68' => 'Side 4 kc',
        '67' => 'Side 4 kc + Rear',
        '70' => 'Side 4 kc + SunRoof',
        '48' => 'Side 5 kc + Rear',
        '37' => 'Standard (Side + Rear) + SunRoof',
        '14' => 'Rear Full',
        '55' => 'Rear Full + Accessories',
        '44' => 'Rear + Sunroof',
        '22' => 'SunRoof 1 kc',
        '50' => 'SunRoof 2 kc',
        '51' => 'Full + SunRoof 2 kc',
        '34' => '1 kc mati',
        '35' => '2 kc mati',
        '60' => 'Alingan + kks + acc',
        '61' => 'Front + Side 1 kc + SunRoof 2 kc',
        '62' => 'Sunroof 2 kc + Accessories',
        '63' => 'Front + Side 2 kc + SunRoof 2 kc',
        '64' => 'Karpet Baris Ke 3',
        '66' => 'Full + SunRoof 3 kc',
        '71' => 'Alingan + Rear',
        '72' => 'Side 2kc + Rear + Sunroof',
        '73' => 'Full (Front + Side + Rear + SunRoof)',
        '75' => 'Front + Side 2 kc + Allingan',
        '76' => 'Front + Side + Sunroof 2 kc',
        '77' => 'Side 1 kc + SunRoof',
        '78' => 'Front + Side 1 kc + SunRoof',
        '79' => 'Alingan + SunRoof',
        '80' => 'SIDE 2 KACA + REAR + ALINGAN',
        '81' => 'FRONT + SIDE 3 KACA + SUNROOF',
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

    public static function installationTypeLabel(?string $code): ?string
    {
        $code = trim((string) $code);

        return $code === '' ? null : (self::INSTALLATION_TYPES[$code] ?? $code);
    }
}
