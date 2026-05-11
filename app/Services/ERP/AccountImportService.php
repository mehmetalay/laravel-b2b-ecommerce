<?php

namespace App\Services\ERP;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Services\EntityLastUpdateService;

class AccountImportService
{
    public function import(): void
    {
        ini_set('max_execution_time', '1600');
        ini_set('memory_limit', '-1');

        logSession('Account import has been initiated.', null, 'info', 'import_logs');

        try {
            $items = DB::connection('sqlsrv')->select("SELECT * FROM vw_CariListeB2B");

            if (empty($items)) {
                return;
            }

            $existingBalances = DB::table('users')
                ->where('current_account_id', '>=', 100000)
                ->pluck('balance', 'current_account_id')
                ->toArray();

            $values = [];
            $nonAccounts = [];
            $dealersToClearCache = [];
            $now = now();

            foreach ($items as $item) {
                $code = str_replace([' ', '.'], '', $item->Carkod);

                if ((int) $code < 100000) {
                    continue;
                }

                $balance = $this->nullIfEmpty($item->Bakiye) ?? 0.00;
                $oldBalance = $existingBalances[$code] ?? null;

                if ($oldBalance !== null && bccomp((string)$oldBalance, (string)$balance, 2) !== 0) {
                    $dealersToClearCache[] = $code;
                }

                $dealerCode = trim($item->CariKisaKod);
                $name = $this->nullIfEmpty($item->CariAdi);
                $plasiyer1 = $this->nullIfEmpty($item->PlasiyerKodu);
                $province = $this->nullIfEmpty($item->CariIl);
                $district = $this->nullIfEmpty($item->CariIlce);
                $address = $this->nullIfEmpty($item->CariAdres);
                $address1 = $this->nullIfEmpty($item->ADRADRES1);
                $address2 = $this->nullIfEmpty($item->ADRADRES2);
                $address3 = $this->nullIfEmpty($item->ADRADRES3);
                $postalCode = $this->nullIfEmpty($item->CariPostaKodu);
                $taxOffice = $this->nullIfEmpty($item->VergiDairesi);
                $taxNumber = $this->nullIfEmpty($item->VergiNo);
                $identityNumber = $this->nullIfEmpty($item->TCKNumarasi);
                $email = $this->nullIfEmpty($item->CariMail);
                $currency = $this->nullIfEmpty($item->BakiyeParaBirimi) ?? 'TRY';
                $status = (int) $this->nullIfEmpty($item->CariDurumu) ?? 1;

                $username = $dealerCode ?: $code;
                $password = Hash::make("{$code}*");

                $values[] = [
                    $code,
                    $name,
                    $dealerCode,
                    null, // group_code
                    $province,
                    $district,
                    $address,
                    $address1,
                    $address2,
                    $address3,
                    $postalCode,
                    $taxOffice,
                    $taxNumber,
                    $identityNumber, // identity_number
                    $email,
                    null, // phone
                    $dealerCode,
                    $balance,
                    $currency,
                    $plasiyer1,
                    $status,
                    $username,
                    $password,
                    $now,
                    $now,
                ];

                $nonAccounts[] = $code;
            }

            $dealersToClearCache = array_unique($dealersToClearCache);

            DB::transaction(function () use ($values, $nonAccounts, $dealersToClearCache) {
                $chunkSize = 500;

                foreach (array_chunk($values, $chunkSize) as $chunk) {
                    $placeholders = rtrim(str_repeat("(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?),", count($chunk)), ",");

                    DB::insert("
                        INSERT INTO users (
                            current_account_id, name, code, group_code, province, district, address, address_1, address_2, address_3,
                            postal_code, tax_office, tax_number, identity_number, email, phone, dealer_code, balance, currency,
                            plasiyer1, status, username, password, created_at, updated_at
                        ) VALUES {$placeholders}
                        ON DUPLICATE KEY UPDATE
                            name = VALUES(name),
                            province = VALUES(province),
                            district = VALUES(district),
                            address = VALUES(address),
                            address_1 = VALUES(address_1),
                            address_2 = VALUES(address_2),
                            address_3 = VALUES(address_3),
                            postal_code = VALUES(postal_code),
                            tax_office = VALUES(tax_office),
                            tax_number = VALUES(tax_number),
                            identity_number = VALUES(identity_number),
                            email = VALUES(email),
                            balance = VALUES(balance),
                            currency = VALUES(currency),
                            plasiyer1 = VALUES(plasiyer1),
                            status = VALUES(status),
                            username = VALUES(username),
                            updated_at = VALUES(updated_at)
                    ", Arr::flatten($chunk));
                }

                if (count($nonAccounts)) {
                    DB::table('users')
                        ->where('current_account_id', '>=', 100000)
                        ->whereNotIn('current_account_id', $nonAccounts)
                        ->update(['status' => 0]);
                }

                if (!empty($dealersToClearCache)) {
                    $userIds = DB::table('users')
                        ->whereIn('current_account_id', $dealersToClearCache)
                        ->pluck('id');

                    $dealerRepo = app(\App\Repositories\DealerRepository::class);

                    foreach ($userIds as $id) {
                        $dealerRepo->clearCache($id);
                    }
                }
            });

            logSession('Account import is complete.', null, 'info', 'import_logs');

            app(EntityLastUpdateService::class)->touch('customer');
        } catch (\Throwable $e) {
            logException($e, 'Kernel::vw_CariListeB2B');
        }
    }

    protected function nullIfEmpty(mixed $value): ?string
    {
        if (!is_string($value)) {
            return $value === null ? null : (string) $value;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
