<?php

namespace App\Console;

use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Arr;
use App\Application\Brand\Services\BrandService;
use App\Services\PaymentService;
use App\Application\Category\Services\CategoryService;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\AltinKaynakService;
use App\Services\EntityLastUpdateService;
use App\Services\BatchLockService;
use App\Services\ERP\AccountImportService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            try {
                Log::info('Döviz servisi çalıştırıldı.');
                $this->updateCurrencies('eta');
                Log::info('Döviz servisi başarılı.');
            } catch (\Throwable $e) {
                logException($e, 'Döviz servisi başarısız');
                sleep(60);
                $this->updateCurrencies('eta');
            }
        })->everyTenMinutes();

        // $schedule->call(function () {
        //     try {
        //         Log::info('Sipariş durumu güncelleme çalıştırıldı.');
        //         $this->updateOrderStatus();
        //     } catch (\Throwable $e) {
        //         logException($e, 'Sipariş durumu güncelleme başarısız');
        //         sleep(60);
        //         $this->updateOrderStatus();
        //     }
        // })->weekdays()->hourly()->between('09:00', '18:00');

        // $schedule->call(function () {
        //     try {
        //         Log::info('Ödeme makbuzu oluşturma işlemi başlatıldı.');
        //         $this->generateReceiptsForPendingPayments();
        //     } catch (\Throwable $e) {
        //         logException($e, 'Ödeme makbuzu oluşturulurken hata oluştu');
        //         sleep(60);
        //         $this->generateReceiptsForPendingPayments();
        //     }
        // })->hourly()->between('09:00', '18:00');//everyMinute

        // Delta sync â€” her 10 dk (sadece değişen ürünler + loglama)
        $schedule->call(function () {
            try {
                $this->vw_StokKartB2B_Delta();
            } catch (\Throwable $e) {
                logException($e, 'vw_StokKartB2B_Delta çalıştırırken hata oluştu');
            }
        })->everyTenMinutes();

        // Full sync â€” gece 03:00 (tüm ürünler + pasifleştirme)
        $schedule->call(function () {
            try {
                $this->vw_StokKartB2B();
            } catch (\Throwable $e) {
                logException($e, 'vw_StokKartB2B çalıştırırken hata oluştu');
            }
        })->dailyAt('03:00');

        $schedule->call(function () {
            try {
                Log::info('vw_CariListeB2B başlatıldı.');
                app(AccountImportService::class)->import();
                Log::info('vw_CariListeB2B bitti.');
            } catch (\Throwable $e) {
                logException($e, 'vw_CariListeB2B çalıştırırken hata oluştu');
                sleep(60);
                app(AccountImportService::class)->import();
            }
        })->everyTenMinutes();//

        $schedule->command('payments:send-mails')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        $schedule->command('orders:sync-pending')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        $schedule->command('payments:sync-pending')
            ->everyFifteenMinutes()
            ->withoutOverlapping();

        $schedule->command('import:product-images')->everyTenMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    private function updateCurrencies($name)
    {
        $currencyService = app(CurrencyService::class);

        if ($name === 'altinkaynak') {
            try {
                $altinkaynakService = new AltinKaynakService();
                $response = $altinkaynakService->getCurrencies();

                if (isset($response['GetCurrencyResult'])) {
                    $xml = simplexml_load_string($response['GetCurrencyResult']);

                    foreach ($xml->Kur as $kur) {
                        $code = (string) $kur->Kod;
                        $buy = (float) $kur->Alis;
                        $sell = (float) $kur->Satis;

                        if (in_array($code, ['USD', 'EUR', 'GBP'])) {
                            $currencyService->updateRaw($code, ['buy' => $buy, 'sell' => $sell]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                logException($e, 'Kernel::updateCurrencies altinkaynak');
            }
        } elseif ($name === 'eta') {
            try {
                $items = DB::connection('sqlsrv')->select("SELECT * FROM vw_Kurlar");

                foreach ($items as $item) {
                    $code = $item->DovizKodu === 'EURO' ? 'EUR' : (string) $item->DovizKodu;
                    $buy = (float) $item->Kur;
                    $sell = (float) $item->Kur;

                    if (in_array($code, ['USD', 'EUR', 'GBP'])) {
                        $currencyService->updateRaw($code, ['buy' => $buy, 'sell' => $sell]);
                    }
                }
            } catch (\Throwable $e) {
                logException($e, 'Kernel::updateCurrencies eta');
            }
        }
    }

    private function vw_StokKartB2B()
    {
        $lock = new BatchLockService('vw_StokKartB2B_Full', 120);

        if (!$lock->acquire()) {
            logSession('[SYNC_SKIP] Full sync zaten çalışıyor, atlandı.', null, 'warning', 'erp/product');
            return;
        }

        try {
            logSession('[SYNC_START] Full sync başladı.', null, 'info', 'erp/product');

            $brandService = app(BrandService::class);
            $categoryService = app(CategoryService::class);

            $items = DB::connection('sqlsrv')->select("SELECT * FROM vw_StokKartB2B");

            $nonProducts = [];
            $productPassiveOnes = [];
            $usedBrandIds = [];
            $usedCategoryIds = [];

            $brandMap = $brandService->getAllBrands()
                ->mapWithKeys(fn ($b) => [mb_strtoupper($b->name) => $b->id])
                ->toArray();

            $categoryMap = $categoryService->getAllCategories()
                ->mapWithKeys(fn ($c) => [($c->parent_id ?? 'NULL') . '::' . mb_strtoupper($c->name) => $c->id])
                ->toArray();

            $values = [];

            foreach ($items as $item) {
                $brandID = null;

                if ($item->Markasi) {
                    $brandKey = mb_strtoupper(trim($item->Markasi));
                    $brandID = $brandMap[$brandKey] ?? null;

                    if (!$brandID) {
                        $brand = $brandService->createRaw([
                            'name' => $brandKey,
                            'slug' => str_slug($item->Markasi),
                            'status' => 1,
                        ]);

                        $brandID = $brand->id;
                        $brandMap[$brandKey] = $brandID;
                    }

                    $usedBrandIds[] = $brandID;
                }

                $categoryID = null;
                if (trim($item->AnaKategori)) {
                    $parentKey = 'NULL::' . mb_strtoupper($item->AnaKategori);
                    $parentID = $categoryMap[$parentKey] ?? null;

                    if (!$parentID) {
                        $parent = $categoryService->createRaw([
                            'name' => mb_strtoupper($item->AnaKategori),
                            'slug' => str_slug($item->AnaKategori),
                            'parent_id' => null,
                        ]);

                        $parentID = $parent->id;
                        $categoryMap[$parentKey] = $parentID;
                    }

                    $usedCategoryIds[] = $parentID;

                    if (trim($item->AltKategori)) {
                        $childKey = $parentID . '::' . mb_strtoupper($item->AltKategori);
                        $childID = $categoryMap[$childKey] ?? null;

                        if (!$childID) {
                            $child = $categoryService->createRaw([
                                'name' => mb_strtoupper($item->AltKategori),
                                'slug' => str_slug($item->AnaKategori . $item->AltKategori),
                                'parent_id' => $parentID,
                            ]);

                            $childID = $child->id;
                            $categoryMap[$childKey] = $childID;
                        }

                        $usedCategoryIds[] = $childID;

                        $categoryID = $childID;
                    } else {
                        $categoryID = $parentID;
                    }
                }

                $urunAd = $this->normalizeErpString($item->StokAdi);
                $urunAdEn = $this->normalizeErpString($item->StokAdi);
                $code = $this->normalizeErpString(trim($item->StokKodu));
                $code_2 = $this->normalizeErpString(trim($item->StokOzelKodu));
                $codeGroup = null;
                $barcode = null;

                // Fiyatlar
                $listeFiyatiNet = $item->ListeFiyati_Net;

                $havaleNet = $item->Havale_Net;
                $havaleIskonto = $item->HavaleIskontoYuzde ?? 0.00;

                $krediNet = $item->KrediKartiTaksit_Net;
                $krediIskonto = $item->KrediKartiIskontoYuzde ?? 0.00;

                $vadeliNet = $item->Vadeli_Net;
                $vadeliIskonto = $item->VadeliIskontoYuzde ?? 0.00;

                $status = $item->Aktif;

                if ((empty($listeFiyatiNet) || $listeFiyatiNet == 0) && (empty($havaleNet) || $havaleNet == 0) && (empty($krediNet) || $krediNet == 0) && (empty($vadeliNet) || $vadeliNet == 0)) {
                    $status = 0;
                    $price1 = $price2 = $price3 = $price4 = 0.00;
                    $price1DiscountRate = $price2DiscountRate = $price3DiscountRate = $price4DiscountRate = 0.00;
                } else {
                    $price1 = $item->ListeFiyati_Net;
                    $price1DiscountRate = 0.00;
                    $price2 = $item->Havale_Net;
                    $price2DiscountRate = $havaleIskonto;
                    $price3 = $item->KrediKartiTaksit_Net;
                    $price3DiscountRate = $krediIskonto;
                    $price4 = $item->Vadeli_Net;
                    $price4DiscountRate = $vadeliIskonto;
                }

                // Döviz kodlarını da atayalım
                $price1Currency = trim($item->Fiyat1DovizKodu);
                $price2Currency = trim($item->Fiyat2DovizKodu);
                $price3Currency = trim($item->Fiyat3DovizKodu);
                $price4Currency = trim($item->Fiyat4DovizKodu);

                $vatRate = $item->KDV;
                $stock = floor($item->StokAdeti);

                $unitName1 = trim($item->Birimi);

                $unitName2 = trim($item->Birim2_Adi);
                $unitQuantity2 = $item->Birim2_AdetPerBirim;

                $unitName3 = trim($item->Birim3_Adi);
                $unitQuantity3 = $item->Birim3_AdetPerBirim;

                $unitName4 = trim($item->Birim4_Adi);
                $unitQuantity4 = $item->Birim4_AdetPerBirim;

                $boxQuantity = $item->PaketBolunemez === '1' ? $item->Birim2_AdetPerBirim : 1;
                $boxQuantityMustBeExact = $item->PaketBolunemez === '1' ? 1 : 0;

                $slug = str_slug($urunAd . '-' . $code);
                $erp_created_at = !empty($item->StokOlusturmaTarihi) ? $item->StokOlusturmaTarihi : null;
                $erp_updated_at = !empty($item->StokGuncellemeTarihi) ? $item->StokGuncellemeTarihi : null;

                $values[] = [
                    $brandID, $categoryID, $urunAd, $urunAdEn,
                    $price1, $price1DiscountRate, $price1Currency,
                    $price2, $price2DiscountRate, $price2Currency,
                    $price3, $price3DiscountRate, $price3Currency,
                    $price4, $price4DiscountRate, $price4Currency,
                    $vatRate, $stock, $boxQuantity, $boxQuantityMustBeExact,
                    $unitName1, $unitName2, $unitQuantity2,
                    $unitName3, $unitQuantity3, $unitName4, $unitQuantity4,
                    $code, $code_2, $codeGroup, $barcode, $slug, $status,
                    $erp_created_at, $erp_updated_at,
                ];

                $nonProducts[] = $this->normalizeErpString($item->StokKodu);
            }

            $chunkSize = 500;

            foreach (array_chunk($values, $chunkSize) as $chunk) {
                $placeholders = rtrim(str_repeat("(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?),", count($chunk)), ",");
                $flat = Arr::flatten($chunk);

                DB::insert("
                    INSERT INTO products (
                        brand_id, category_id, name, name_en, price_1, price_1_discount_rate, price_1_currency, price_2, price_2_discount_rate, price_2_currency, price_3, price_3_discount_rate, price_3_currency, price_4, price_4_discount_rate, price_4_currency, vat_rate, stock, box_quantity, box_quantity_must_be_exact, unit_name_1, unit_name_2, unit_quantity_2, unit_name_3, unit_quantity_3, unit_name_4, unit_quantity_4, code, code_2, code_group, barcode, slug, status, erp_created_at, erp_updated_at
                    ) VALUES {$placeholders}
                    ON DUPLICATE KEY UPDATE
                        brand_id = VALUES(brand_id),
                        category_id = VALUES(category_id),
                        name = VALUES(name),
                        name_en = VALUES(name_en),
                        price_1 = VALUES(price_1),
                        price_1_discount_rate = VALUES(price_1_discount_rate),
                        price_1_currency = VALUES(price_1_currency),
                        price_2 = VALUES(price_2),
                        price_2_discount_rate = VALUES(price_2_discount_rate),
                        price_2_currency = VALUES(price_2_currency),
                        price_3 = VALUES(price_3),
                        price_3_discount_rate = VALUES(price_3_discount_rate),
                        price_3_currency = VALUES(price_3_currency),
                        price_4 = VALUES(price_4),
                        price_4_discount_rate = VALUES(price_4_discount_rate),
                        price_4_currency = VALUES(price_4_currency),
                        vat_rate = VALUES(vat_rate),
                        stock = VALUES(stock),
                        box_quantity = VALUES(box_quantity),
                        box_quantity_must_be_exact = VALUES(box_quantity_must_be_exact),
                        unit_name_1 = VALUES(unit_name_1),
                        unit_name_2 = VALUES(unit_name_2),
                        unit_quantity_2 = VALUES(unit_quantity_2),
                        unit_name_3 = VALUES(unit_name_3),
                        unit_quantity_3 = VALUES(unit_quantity_3),
                        unit_name_4 = VALUES(unit_name_4),
                        unit_quantity_4 = VALUES(unit_quantity_4),
                        code_2 = VALUES(code_2),
                        code_group = VALUES(code_group),
                        barcode = VALUES(barcode),
                        slug = VALUES(slug),
                        status = VALUES(status),
                        erp_created_at = IF(is_flagged_as_new = 1, erp_created_at, VALUES(erp_created_at)),
                        erp_updated_at = VALUES(erp_updated_at)
                ", $flat);
            }

            if (count($productPassiveOnes)) {
                Product::whereIn('code', $productPassiveOnes)->update([
                    'status' => 0
                ]);
            }

            $passivatedCount = 0;
            if (count($nonProducts)) {
                $passivatedCount = Product::whereNotIn('code', $nonProducts)->where('status', 1)->count();
                Product::whereNotIn('code', $nonProducts)->update([
                    'status' => 0
                ]);
            }

            $categoryService->deleteCategoriesNotIn(array_values(array_unique($usedCategoryIds ?? [])));

            $brandService->deactivateBrandsNotIn(array_values(array_unique($usedBrandIds ?? [])));

            app(EntityLastUpdateService::class)->touch('product');

            logSession("[SYNC_END] Full sync tamamlandı. " . count($items) . " ürün işlendi, {$passivatedCount} ürün pasifleştirildi.", null, 'info', 'erp/product');

        } catch (\Throwable $e) {
            logException($e, 'Kernel::vw_StokKartB2B');
            logSession('[SYNC_ERROR] Full sync hatası: ' . $e->getMessage(), null, 'error', 'erp/product');
        } finally {
            $lock->release();
        }
    }

    private function vw_StokKartB2B_Delta()
    {
        $lock = new BatchLockService('vw_StokKartB2B_Delta', 30);

        if (!$lock->acquire()) {
            logSession('[SYNC_SKIP] Delta sync zaten çalışıyor, atlandı.', null, 'warning', 'erp/product');
            return;
        }

        try {
            $lastSync = app(EntityLastUpdateService::class)->get('product');
            $lastSyncFormatted = '2000-01-01 00:00:00'; //$lastSync ? \Carbon\Carbon::parse($lastSync)->subMinutes(5)->format('Y-m-d H:i:s') : 

            logSession("[SYNC_START] Delta sync başladı. Son sync: {$lastSyncFormatted}", null, 'info', 'erp/product');

            $items = DB::connection('sqlsrv')->select(
                "SELECT * FROM vw_StokKartB2B WHERE StokOlusturmaTarihi >= ? OR StokGuncellemeTarihi >= ?",
                [$lastSyncFormatted, $lastSyncFormatted]
            );

            if (empty($items)) {
                logSession('[SYNC_END] Delta sync: değişen ürün yok.', null, 'info', 'erp/product');
                // app(EntityLastUpdateService::class)->touch('product');
                return;
            }

            // Mevcut ürünleri DB'den çek (karşılaştırma için)
            $codes = array_map(fn ($item) => $this->normalizeErpString(trim($item->StokKodu)), $items);
            $existingProducts = Product::whereIn('code', $codes)->get()->keyBy('code');

            $brandService = app(BrandService::class);
            $categoryService = app(CategoryService::class);

            $brandMap = $brandService->getAllBrands()
                ->mapWithKeys(fn ($b) => [mb_strtoupper($b->name) => $b->id])
                ->toArray();

            $categoryMap = $categoryService->getAllCategories()
                ->mapWithKeys(fn ($c) => [($c->parent_id ?? 'NULL') . '::' . mb_strtoupper($c->name) => $c->id])
                ->toArray();

            $values = [];
            $changeCount = 0;

            foreach ($items as $item) {
                $code = $this->normalizeErpString(trim($item->StokKodu));
                $existing = $existingProducts[$code] ?? null;

                // Marka
                $brandID = null;
                if ($item->Markasi) {
                    $brandKey = mb_strtoupper(trim($item->Markasi));
                    $brandID = $brandMap[$brandKey] ?? null;

                    if (!$brandID) {
                        $brand = $brandService->createRaw([
                            'name' => $brandKey,
                            'slug' => str_slug($item->Markasi),
                            'status' => 1,
                        ]);
                        $brandID = $brand->id;
                        $brandMap[$brandKey] = $brandID;
                    }
                }

                // Kategori
                $categoryID = null;
                if (trim($item->AnaKategori)) {
                    $parentKey = 'NULL::' . mb_strtoupper($item->AnaKategori);
                    $parentID = $categoryMap[$parentKey] ?? null;

                    if (!$parentID) {
                        $parent = $categoryService->createRaw([
                            'name' => mb_strtoupper($item->AnaKategori),
                            'slug' => str_slug($item->AnaKategori),
                            'parent_id' => null,
                        ]);
                        $parentID = $parent->id;
                        $categoryMap[$parentKey] = $parentID;
                    }

                    if (trim($item->AltKategori)) {
                        $childKey = $parentID . '::' . mb_strtoupper($item->AltKategori);
                        $childID = $categoryMap[$childKey] ?? null;

                        if (!$childID) {
                            $child = $categoryService->createRaw([
                                'name' => mb_strtoupper($item->AltKategori),
                                'slug' => str_slug($item->AnaKategori . $item->AltKategori),
                                'parent_id' => $parentID,
                            ]);
                            $childID = $child->id;
                            $categoryMap[$childKey] = $childID;
                        }
                        $categoryID = $childID;
                    } else {
                        $categoryID = $parentID;
                    }
                }

                // Fiyatlar
                $listeFiyatiNet = $item->ListeFiyati_Net;
                $havaleNet = $item->Havale_Net;
                $havaleIskonto = $item->HavaleIskontoYuzde ?? 0.00;
                $krediNet = $item->KrediKartiTaksit_Net;
                $krediIskonto = $item->KrediKartiIskontoYuzde ?? 0.00;
                $vadeliNet = $item->Vadeli_Net;
                $vadeliIskonto = $item->VadeliIskontoYuzde ?? 0.00;

                $status = $item->Aktif;

                if ((empty($listeFiyatiNet) || $listeFiyatiNet == 0) && (empty($havaleNet) || $havaleNet == 0) && (empty($krediNet) || $krediNet == 0) && (empty($vadeliNet) || $vadeliNet == 0)) {
                    $status = 0;
                    $price1 = $price2 = $price3 = $price4 = 0.00;
                    $price1DiscountRate = $price2DiscountRate = $price3DiscountRate = $price4DiscountRate = 0.00;
                } else {
                    $price1 = $item->ListeFiyati_Net;
                    $price1DiscountRate = 0.00;
                    $price2 = $item->Havale_Net;
                    $price2DiscountRate = $havaleIskonto;
                    $price3 = $item->KrediKartiTaksit_Net;
                    $price3DiscountRate = $krediIskonto;
                    $price4 = $item->Vadeli_Net;
                    $price4DiscountRate = $vadeliIskonto;
                }

                $price1Currency = trim($item->Fiyat1DovizKodu);
                $price2Currency = trim($item->Fiyat2DovizKodu);
                $price3Currency = trim($item->Fiyat3DovizKodu);
                $price4Currency = trim($item->Fiyat4DovizKodu);

                $vatRate = $item->KDV;
                $stock = floor($item->StokAdeti);
                $boxQuantity = $item->PaketBolunemez === '1' ? $item->Birim2_AdetPerBirim : 1;
                $boxQuantityMustBeExact = $item->PaketBolunemez === '1' ? 1 : 0;

                $slug = str_slug($item->StokAdi . '-' . $code);
                $erp_created_at = !empty($item->StokOlusturmaTarihi) ? $item->StokOlusturmaTarihi : null;
                $erp_updated_at = !empty($item->StokGuncellemeTarihi) ? $item->StokGuncellemeTarihi : null;

                // --- Değişiklik Loglama ---
                if (!$existing) {
                    logSession("[NEW] {$code} â€” {$item->StokAdi}", null, 'info', 'erp/product');
                    $changeCount++;
                } else {
                    $changes = [];

                    // Fiyat değişimleri
                    if ((float) $existing->price_1 != (float) $price1) $changes[] = "price_1: {$existing->price_1} â†’ {$price1}";
                    if ((float) $existing->price_2 != (float) $price2) $changes[] = "price_2: {$existing->price_2} â†’ {$price2}";
                    if ((float) $existing->price_3 != (float) $price3) $changes[] = "price_3: {$existing->price_3} â†’ {$price3}";
                    if ((float) $existing->price_4 != (float) $price4) $changes[] = "price_4: {$existing->price_4} â†’ {$price4}";

                    if (!empty($changes)) {
                        logSession("[PRICE] {$code} â€” " . implode(', ', $changes), null, 'info', 'erp/product');
                        $changeCount++;
                    }

                    // İndirim oranı değişimleri
                    $discountChanges = [];
                    if ((float) $existing->price_2_discount_rate != (float) $price2DiscountRate) $discountChanges[] = "price_2_discount_rate: {$existing->price_2_discount_rate} â†’ {$price2DiscountRate}";
                    if ((float) $existing->price_3_discount_rate != (float) $price3DiscountRate) $discountChanges[] = "price_3_discount_rate: {$existing->price_3_discount_rate} â†’ {$price3DiscountRate}";
                    if ((float) $existing->price_4_discount_rate != (float) $price4DiscountRate) $discountChanges[] = "price_4_discount_rate: {$existing->price_4_discount_rate} â†’ {$price4DiscountRate}";

                    if (!empty($discountChanges)) {
                        logSession("[DISCOUNT] {$code} â€” " . implode(', ', $discountChanges), null, 'info', 'erp/product');
                        $changeCount++;
                    }

                    // Stok değişimi
                    if ((int) $existing->stock != (int) $stock) {
                        logSession("[STOCK] {$code} â€” stock: {$existing->stock} â†’ {$stock}", null, 'info', 'erp/product');
                        $changeCount++;
                    }

                    // Status değişimi
                    if ((int) $existing->status != (int) $status) {
                        logSession("[STATUS] {$code} â€” status: {$existing->status} â†’ {$status}", null, 'warning', 'erp/product');
                        $changeCount++;
                    }

                    // Box quantity değişimi
                    if ((int) $existing->box_quantity != (int) $boxQuantity || (int) $existing->box_quantity_must_be_exact != (int) $boxQuantityMustBeExact) {
                        logSession("[BOX_QTY] {$code} â€” box_quantity: {$existing->box_quantity} â†’ {$boxQuantity}, must_be_exact: {$existing->box_quantity_must_be_exact} â†’ {$boxQuantityMustBeExact}", null, 'info', 'erp/product');
                        $changeCount++;
                    }
                }

                $values[] = [
                    $brandID, $categoryID, $this->normalizeErpString($item->StokAdi), $this->normalizeErpString($item->StokAdi),
                    $price1, $price1DiscountRate, $price1Currency,
                    $price2, $price2DiscountRate, $price2Currency,
                    $price3, $price3DiscountRate, $price3Currency,
                    $price4, $price4DiscountRate, $price4Currency,
                    $vatRate, $stock, $boxQuantity, $boxQuantityMustBeExact,
                    $this->normalizeErpString(trim($item->Birimi)), $this->normalizeErpString(trim($item->Birim2_Adi)), $item->Birim2_AdetPerBirim,
                    $this->normalizeErpString(trim($item->Birim3_Adi)), $item->Birim3_AdetPerBirim,
                    $this->normalizeErpString(trim($item->Birim4_Adi)), $item->Birim4_AdetPerBirim,
                    $code, $this->normalizeErpString(trim($item->StokOzelKodu)), null, null, $slug, $status,
                    $erp_created_at, $erp_updated_at,
                ];
            }

            // Upsert
            $chunkSize = 500;

            foreach (array_chunk($values, $chunkSize) as $chunk) {
                $placeholders = rtrim(str_repeat("(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?),", count($chunk)), ",");
                $flat = Arr::flatten($chunk);

                DB::insert("
                    INSERT INTO products (
                        brand_id, category_id, name, name_en, price_1, price_1_discount_rate, price_1_currency, price_2, price_2_discount_rate, price_2_currency, price_3, price_3_discount_rate, price_3_currency, price_4, price_4_discount_rate, price_4_currency, vat_rate, stock, box_quantity, box_quantity_must_be_exact, unit_name_1, unit_name_2, unit_quantity_2, unit_name_3, unit_quantity_3, unit_name_4, unit_quantity_4, code, code_2, code_group, barcode, slug, status, erp_created_at, erp_updated_at
                    ) VALUES {$placeholders}
                    ON DUPLICATE KEY UPDATE
                        brand_id = VALUES(brand_id),
                        category_id = VALUES(category_id),
                        name = VALUES(name),
                        name_en = VALUES(name_en),
                        price_1 = VALUES(price_1),
                        price_1_discount_rate = VALUES(price_1_discount_rate),
                        price_1_currency = VALUES(price_1_currency),
                        price_2 = VALUES(price_2),
                        price_2_discount_rate = VALUES(price_2_discount_rate),
                        price_2_currency = VALUES(price_2_currency),
                        price_3 = VALUES(price_3),
                        price_3_discount_rate = VALUES(price_3_discount_rate),
                        price_3_currency = VALUES(price_3_currency),
                        price_4 = VALUES(price_4),
                        price_4_discount_rate = VALUES(price_4_discount_rate),
                        price_4_currency = VALUES(price_4_currency),
                        vat_rate = VALUES(vat_rate),
                        stock = VALUES(stock),
                        box_quantity = VALUES(box_quantity),
                        box_quantity_must_be_exact = VALUES(box_quantity_must_be_exact),
                        unit_name_1 = VALUES(unit_name_1),
                        unit_name_2 = VALUES(unit_name_2),
                        unit_quantity_2 = VALUES(unit_quantity_2),
                        unit_name_3 = VALUES(unit_name_3),
                        unit_quantity_3 = VALUES(unit_quantity_3),
                        unit_name_4 = VALUES(unit_name_4),
                        unit_quantity_4 = VALUES(unit_quantity_4),
                        code_2 = VALUES(code_2),
                        code_group = VALUES(code_group),
                        barcode = VALUES(barcode),
                        slug = VALUES(slug),
                        status = VALUES(status),
                        erp_created_at = IF(is_flagged_as_new = 1, erp_created_at, VALUES(erp_created_at)),
                        erp_updated_at = VALUES(erp_updated_at)
                ", $flat);
            }

            app(EntityLastUpdateService::class)->touch('product');

            logSession("[SYNC_END] Delta sync tamamlandı. " . count($items) . " ürün işlendi, {$changeCount} değişiklik loglandı.", null, 'info', 'erp/product');

        } catch (\Throwable $e) {
            logException($e, 'Kernel::vw_StokKartB2B_Delta');
            logSession('[SYNC_ERROR] Delta sync hatası: ' . $e->getMessage(), null, 'error', 'erp/product');
        } finally {
            $lock->release();
        }
    }
    private function generateReceiptsForPendingPayments()
    {
        exit;
        try {
            $payments = Payment::where('status', 'SUCCESS')
                ->where('receipt_issued', 0)
                ->whereHas('user', function ($query) {
                    $query->where('receipt_enabled', 1);
                })
                ->get();

            foreach ($payments as $payment) {
                app(PaymentService::class)->generatePaymentReceiptPdf($payment, 'payment', true);

                $payment->update([
                   'receipt_issued' => 1
                ]);
            }
        } catch (\Throwable $e) {
            logException($e, 'Kernel::generateReceiptsForPendingPayments');
        }
    }

    /**
     * SQL Server'dan gelen string değerleri tutarlı UTF-8'e normalize eder.
     * Türkçe İ/ı/ï¿½?/ş/Ç/ç/ï¿½?/ğ/Ö/ö/Ü/ü gibi karakterlerin byte tutarsızlığını önler.
     */
    private function normalizeErpString(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Eğer geçerli UTF-8 değilse, Windows-1254 (Turkish) veya auto-detect ile dönüştür
        if (!mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'Windows-1254');
        }

        // Unicode NFC normalizasyonu (aynı karakterin farklı unicode gösterimlerini birleştirir)
        if (class_exists('Normalizer')) {
            $normalized = \Normalizer::normalize($value, \Normalizer::FORM_C);
            if ($normalized !== false) {
                $value = $normalized;
            }
        }

        return $value;
    }
}




