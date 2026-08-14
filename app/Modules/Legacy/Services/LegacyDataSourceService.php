<?php

namespace App\Modules\Legacy\Services;

use Illuminate\Support\Facades\DB;

/**
 * Read-only data source against the legacy sales database (DB_OLD_*).
 *
 * The legacy database is PostgreSQL and is never written from FSM. Both
 * queries below are the operational defaults supplied by the business; a
 * search term narrows the result and a hard limit keeps responses small.
 */
class LegacyDataSourceService
{
    private const TECHNICIANS_SQL = <<<'SQL'
        SELECT u.*, b.bank_name AS bank_name_, u2.full_name AS company_name,
               cb.full_name AS created_by_name, lub.full_name AS last_updated_by_name
        FROM users AS u
        LEFT JOIN users AS u2 ON u.company_serial = u2.serial
        LEFT JOIN users AS cb ON u.created_by = cb.serial
        LEFT JOIN users AS lub ON u.last_updated_by = lub.serial
        LEFT JOIN bank AS b ON u.bank_serial = b.serial
        WHERE u.status ILIKE '%1%'
          AND u.user_type ILIKE '%7%'
          AND u.division ILIKE '%09%'
        SQL;

    private const SALES_SQL = <<<'SQL'
        SELECT s.*, s.serial AS soforsi,
               ish.serial AS serial_shipping, ish.inventory_shipping_no AS inventory_shipping_no_,
               b.serial AS cbserial, so.prefix_depo AS prefix_depo_awal,
               so.sales_order_no_car AS sales_order_no_car_awal,
               so.amount_dealer_1 AS amount_dealer_1_, so.amount_dealer_2 AS amount_dealer_2_,
               so.sales_order_no_car AS sales_order_no_car_, so.sales_order_no_building AS sales_order_no_building_,
               so.sales_order_no_materials AS sales_order_no_materials_, so.pairing_date AS techniciandate,
               so.installation_date AS sellingdate, so.sales_order_no AS soawal,
               so.installation_address AS addressawal,
               sir.sales_invoice_no_car AS si_reff_no_car_, sirb.sales_invoice_no_building AS si_reff_no_building_,
               cus.full_name AS customer_name, cus.email AS customer_email,
               cus.address, cus.city, cus.state, cus.zip, cus.country,
               cus.home_phone, cus.home_phone_2, cus.cell_phone, cus.contact_person, cus.cell_phone_2,
               cus.office_phone, cus.fax_number, cus.office_phone_2,
               dl.user_id AS dealer_code, dl.npwp AS dl_npwp, dl.full_name AS dealer_name,
               asr.user_id AS asuransi_code, asr.npwp AS asr_npwp, asr.full_name AS asuransi_name,
               dllk.full_name AS dealer_luarkota_name, sh.city AS showroom_city, sh.full_name AS showroom_name,
               sh.npwp AS sh_npwp, as_.full_name AS accessories_store_name, sdl.full_name AS sales_dealer_name,
               ssh.full_name AS sales_showroom_name, sas_.full_name AS sales_accessories_store_name,
               mdt.full_name AS mediator_name, mdt.npwp AS md_npwp,
               sdl.cell_phone AS sales_dealer_cell_phone_1, sdl.cell_phone_2 AS sales_dealer_cell_phone_2,
               ssh.cell_phone AS sales_showroom_cell_phone_1, ssh.cell_phone_2 AS sales_showroom_cell_phone_2,
               sas_.cell_phone AS sales_accessories_store_cell_phone_1, sas_.cell_phone_2 AS sales_accessories_store_cell_phone_2,
               bt1.payment_term_prediction AS payment_term_prediction_1, bt1.full_name AS bill_to_name_1,
               bt1.address AS bill_to_address_1, bt1.city AS bill_to_city_1, bt1.zip AS bill_to_zip_1,
               bt1.npwp AS bill_to_npwp_1, bt2.payment_term_prediction AS payment_term_prediction_2,
               bt2.full_name AS bill_to_name_2, bd.full_name AS branch_dealer_name,
               fdod.full_name AS film_dibawa_oleh_dealer_name, bt3.payment_term_prediction AS payment_term_prediction_3,
               bt3.full_name AS bill_to_name_3, so.film_dibawa_oleh AS film_dibawa_oleh_so,
               pbt.full_name AS pay_bonus_to_name, pbtso.full_name AS pay_bonus_to_name_so,
               pbtso.user_type AS pay_bonus_to_type_so, pct.full_name AS pay_commission_to_name,
               pn.price_desc AS nett_price_desc, bcb.cash_bank_no AS bonus_cash_bank_no_,
               ccb.cash_bank_no AS commission_cash_bank_no_, cus.serial AS user_serial, cus.user_type AS user_type,
               cus.user_id AS user_id_, cus.full_name AS user_name, tpc.case_name,
               b.serial AS car_brand_serial, b.car_brand, c.car_model, c.category, c.kategori_sulit,
               cb.full_name AS created_by_name, lub.full_name AS last_updated_by_name,
               luc.full_name AS last_updated_by_counter_name, apby.full_name AS approval_by_name,
               so.approval_date AS approval_date_sopek
        FROM sales AS s
        LEFT JOIN sales AS so ON s.sales_order_no = so.serial
        LEFT JOIN inventory_process AS ish ON s.inventory_shipping_no = ish.serial
        LEFT JOIN sales AS sir ON s.si_reff_no_car = sir.serial
        LEFT JOIN sales AS sirb ON s.si_reff_no_building = sirb.serial
        LEFT JOIN users AS cus ON s.customer_serial = cus.serial
        LEFT JOIN car_type AS c ON s.car_type_serial = c.serial
        LEFT JOIN car_brand AS b ON c.car_brand_serial = b.serial
        LEFT JOIN users AS dl ON s.dealer_serial = dl.serial
        LEFT JOIN users AS asr ON s.asuransi_serial = asr.serial
        LEFT JOIN users AS dllk ON s.dealer_luarkota_serial = dllk.serial
        LEFT JOIN users AS sdl ON s.sales_of_dealer_serial = sdl.serial
        LEFT JOIN users AS sh ON s.showroom_serial = sh.serial
        LEFT JOIN users AS ssh ON s.sales_of_showroom_serial = ssh.serial
        LEFT JOIN users AS as_ ON s.accessories_store_serial = as_.serial
        LEFT JOIN users AS sas_ ON s.sales_of_accessories_store_serial = sas_.serial
        LEFT JOIN users AS bt1 ON s.bill_to_serial_1 = bt1.serial
        LEFT JOIN users AS bt2 ON s.bill_to_serial_2 = bt2.serial
        LEFT JOIN users AS bd ON s.branch_dealer = bd.serial
        LEFT JOIN users AS bt3 ON s.bill_to_serial_3 = bt3.serial
        LEFT JOIN users AS fdod ON s.film_dibawa_oleh_dealer = fdod.serial
        LEFT JOIN users AS pbt ON s.pay_bonus_to_serial = pbt.serial
        LEFT JOIN users AS pbtso ON so.pay_bonus_to_serial = pbtso.serial
        LEFT JOIN users AS pct ON s.pay_commission_to_serial = pct.serial
        LEFT JOIN users AS mdt ON s.mediator_serial = mdt.serial
        LEFT JOIN price_nett AS pn ON s.nett_price_serial = pn.serial
        LEFT JOIN cash_flow_process AS bcb ON s.bonus_cash_bank_no = bcb.serial
        LEFT JOIN cash_flow_process AS ccb ON s.commission_cash_bank_no = ccb.serial
        LEFT JOIN users AS cb ON s.created_by = cb.serial
        LEFT JOIN users AS lub ON s.last_updated_by = lub.serial
        LEFT JOIN users AS luc ON s.last_updated_by_counter = luc.serial
        LEFT JOIN users AS apby ON so.approval_by = apby.serial
        LEFT JOIN technician_process AS tpc ON s.failure_type_case = tpc.serial
        WHERE s.sales_type IN ('1', '3', '5', '7')
          AND s.status IN ('1', '2', '5')
        SQL;

    /**
     * @return list<object>
     */
    public function technicians(?string $search = null, int $limit = 200): array
    {
        $sql = self::TECHNICIANS_SQL;
        $bindings = [];

        if (filled($search)) {
            $sql .= ' AND (u.full_name ILIKE ? OR u.user_id ILIKE ?)';
            $pattern = '%'.trim($search).'%';
            $bindings = [$pattern, $pattern];
        }

        $sql .= ' ORDER BY u.full_name LIMIT '.$this->limit($limit);

        return DB::connection('sales')->select($sql, $bindings);
    }

    /**
     * @return list<object>
     */
    public function sales(?string $search = null, int $limit = 100): array
    {
        $sql = self::SALES_SQL;
        $bindings = [];

        if (filled($search)) {
            $sql .= ' AND (s.spk_no ILIKE ? OR s.sales_invoice_no_car ILIKE ? OR s.sales_order_no_car ILIKE ?)';
            $pattern = '%'.trim($search).'%';
            $bindings = [$pattern, $pattern, $pattern];
        }

        $sql .= ' LIMIT '.$this->limit($limit);

        $rows = DB::connection('sales')->select($sql, $bindings);

        // Urutkan berdasarkan jadwal pasang (installation_date) terbaru dulu;
        // SPK tanpa jadwal diletakkan di akhir. Sortir di PHP agar aman dari
        // perbedaan nama kolom di database legacy.
        usort($rows, static function (object $a, object $b): int {
            $dateA = $a->sellingdate ?? $a->installation_date ?? null;
            $dateB = $b->sellingdate ?? $b->installation_date ?? null;

            if ($dateA === $dateB) {
                return 0;
            }
            if ($dateA === null) {
                return 1;
            }
            if ($dateB === null) {
                return -1;
            }

            return $dateB <=> $dateA; // DESC
        });

        return $rows;
    }

    public function salesBySerial(string $serial): ?object
    {
        $rows = DB::connection('sales')->select(
            self::SALES_SQL.' AND s.serial = ? LIMIT 1',
            [$serial],
        );

        return $rows[0] ?? null;
    }

    public function technicianBySerial(string $serial): ?object
    {
        $rows = DB::connection('sales')->select(
            'SELECT serial, user_id, full_name, email, cell_phone, home_phone, status
             FROM users WHERE serial = ? LIMIT 1',
            [$serial],
        );

        return $rows[0] ?? null;
    }

    /**
     * @return list<object>
     */
    public function bonusesForTechnician(string $technicianSerial, string $date): array
    {
        return DB::connection('sales')->select(
            'SELECT teknisi_name, "date", sales_invoice_no_car, sales_invoice_no_building,
                    sales_invoice_no_materials, total
             FROM "VIEW_Bonus_Teknisi"
             WHERE teknisi_serial = ? AND "date" = ?
             ORDER BY sales_invoice_no_car',
            [$technicianSerial, $date],
        );
    }

    /**
     * Detail item penjualan dari view legacy SHOW_SalesDetail.
     *
     * @return list<object>
     */
    public function salesDetails(string $salesSerial): array
    {
        return DB::connection('sales')->select(
            'SELECT inventory_name, window_position, window_position_detail, width, length_, qty
             FROM "SHOW_SalesDetail"
             WHERE sales_serial = ?
             ORDER BY
                 NULLIF(btrim(window_position), \'\')::int NULLS LAST,
                 NULLIF(btrim(window_position_detail), \'\')::int NULLS LAST,
                 inventory_category ASC NULLS LAST,
                 NULLIF(btrim(item_group), \'\')::int NULLS LAST,
                 inventory_name ASC NULLS LAST',
            [$salesSerial],
        );
    }

    public function countTechnicians(): int
    {
        $row = DB::connection('sales')->selectOne(
            "SELECT COUNT(*) AS total FROM users
             WHERE status ILIKE '%1%' AND user_type ILIKE '%7%' AND division ILIKE '%09%'",
        );

        return (int) ($row->total ?? 0);
    }

    private function limit(int $limit): int
    {
        return max(1, min($limit, 500));
    }
}
