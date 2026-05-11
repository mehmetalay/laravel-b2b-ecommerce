<?php

namespace App\Application\Contract\Services;

use App\Application\Contract\Events\ContractPdfGenerated;
use App\Application\Contract\Exceptions\ContractPdfGenerationException;
use App\Models\ContractSignature;
use App\Models\ContractTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContractPdfService
{
    public function __construct(
        private ContractPersistenceService $contractPersistenceService,
        private ContractPlaceholderRenderer $contractPlaceholderRenderer
    ) {}

    public function generateForSignature(array $actorContext, ContractSignature $signature, ContractTemplate $template): string
    {
        if (!empty($signature->pdf_path) && file_exists(public_path($signature->pdf_path))) {
            return (string) $signature->pdf_path;
        }

        $contract = $this->contractPersistenceService->findContractByContext($actorContext);
        $filledContent = $this->contractPlaceholderRenderer->render((string) $template->content, $actorContext, $contract);
        $path = $this->buildPdfRelativePath($signature);

        try {
            $this->ensureDirectory(dirname($path));
            $pdf = Pdf::loadView('pdf.contract', ['content' => $filledContent]);
            $pdf->save(public_path($path));
        } catch (Throwable $e) {
            Log::error('Contract PDF generation failed', [
                'signature_id' => $signature->id,
                'actor_type' => $signature->actor_type,
                'exception' => $e->getMessage(),
            ]);

            throw new ContractPdfGenerationException('Sözleşme PDF dosyası oluşturulamadı.');
        }

        $signature->update(['pdf_path' => $path]);

        ContractPdfGenerated::dispatch(
            (int) $signature->id,
            (string) $signature->actor_type,
            $path
        );

        return $path;
    }

    public function buildPdfRelativePath(ContractSignature $signature): string
    {
        return 'contracts/' . $signature->actor_type . '/' . $signature->id . '.pdf';
    }

    private function ensureDirectory(string $relativeDir): void
    {
        $fullDir = public_path($relativeDir);

        if (is_dir($fullDir)) {
            return;
        }

        if (!mkdir($fullDir, 0775, true) && !is_dir($fullDir)) {
            throw new ContractPdfGenerationException('Sözleşme dizini oluşturulamadı.');
        }
    }
}
