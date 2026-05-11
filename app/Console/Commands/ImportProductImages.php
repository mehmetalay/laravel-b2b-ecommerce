<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EntityLastUpdateService;

class ImportProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:product-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'StokKodlarına göre public/ftp/images klasöründen resimleri alır ve kaydeder.';

    protected $imageService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ImageService $imageService)
    {
        parent::__construct();
        $this->imageService = $imageService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Resim import işlemi başladı...');
        Log::info('Resim import işlemi başladı...');

        $basePath = public_path('ftp/images');
        $extensions = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];

        $files = glob($basePath . '/*.*');
        $existingCodes = [];

        foreach ($files as $file) {
            $existingCodes[] = pathinfo($file, PATHINFO_FILENAME);
        }

        if (empty($existingCodes)) {
            $this->info('Klasörde işlenecek resim bulunamadı.');
            return Command::SUCCESS;
        }

        $chunkSize = 2000;
        $items = collect();

        foreach (array_chunk($existingCodes, $chunkSize) as $chunk) {
            $chunkItems = DB::connection('sqlsrv')
                ->table('vw_StokKartB2B')
                ->whereIn('StokKodu', $chunk)
                ->get();

            $items = $items->merge($chunkItems);
        }

        if ($items->isEmpty()) {
            $this->info('Veritabanında eşleşen ürün bulunamadı.');
            return Command::SUCCESS;
        }

        $products = Product::whereIn('code', $items->pluck('StokKodu'))->get()->keyBy('code');

        $processedFiles = [];

        foreach ($items as $item) {
            $stokKodu = trim($item->StokKodu);

            $product = $products->get($stokKodu);

            $imageNames = [
                $product->image_1 ?? null,
                $product->image_2 ?? null,
                $product->image_3 ?? null
            ];

            for ($i = 0; $i < 3; $i++) {
                $suffix = $i === 0 ? '' : '_' . $i;
                $filePath = null;

                foreach ($extensions as $ext) {
                    $tryPath = $basePath . DIRECTORY_SEPARATOR . $stokKodu . $suffix . '.' . $ext;
                    if (file_exists($tryPath)) {
                        $filePath = $tryPath;
                        break;
                    }
                }

                if ($filePath) {
                    try {
                        $imageNames[$i] = $this->imageService->product2($filePath, $stokKodu . $suffix);
                        $this->info(($i + 1) . ". Resim Kaydedildi: {$stokKodu}");
                        $processedFiles[] = $filePath;
                    } catch (\Exception $e) {
                        $this->error(($i + 1) . ". Resim Hata ({$stokKodu}): " . $e->getMessage());
                    }
                } else {
                    $this->warn(($i + 1) . ". Resim bulunamadı: {$stokKodu}");
                }
            }

            DB::table('products')->updateOrInsert(
                ['code' => $stokKodu],
                [
                    'image_1' => $imageNames[0],
                    'image_2' => $imageNames[1],
                    'image_3' => $imageNames[2],
                ]
            );
        }

        foreach ($processedFiles as $file) {
            if (file_exists($file)) {
                try {
                    unlink($file);
                } catch (\Exception $e) {
                    Log::error("Dosya silme hatası: {$file} - " . $e->getMessage());
                }
            }
        }

        $this->info('Resim import işlemi tamamlandı.');
        Log::info('Resim import işlemi tamamlandı.');

        app(EntityLastUpdateService::class)->touch('image');

        return Command::SUCCESS;
    }
}
