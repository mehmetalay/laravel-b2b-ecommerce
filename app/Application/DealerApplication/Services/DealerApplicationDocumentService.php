<?php

namespace App\Application\DealerApplication\Services;

use App\Application\DealerApplication\Enums\DealerApplicationDocumentDirectory;
use App\Application\DealerApplication\Exceptions\DealerApplicationDocumentNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DealerApplicationDocumentService
{
    public const ALLOWED_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'doc',
        'docx',
        'xls',
        'xlsx',
    ];

    /**
     * @param array<int, UploadedFile|string|null>|null $documents
     * @return string[]
     */
    public function storeMany(?array $documents): array
    {
        if (empty($documents)) {
            return [];
        }

        $paths = [];

        foreach ($documents as $document) {
            if (!$document instanceof UploadedFile) {
                continue;
            }

            $paths[] = $document->store(DealerApplicationDocumentDirectory::ROOT->value);
        }

        return $paths;
    }

    /**
     * @param string[] $paths
     */
    public function deleteMany(array $paths): void
    {
        foreach ($paths as $path) {
            if (!is_string($path) || $path === '') {
                continue;
            }

            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }
    }

    public function resolveDownloadPathOrFail(string $path): string
    {
        $normalizedPath = str_replace('\\', '/', ltrim(trim($path), '/\\'));

        if ($normalizedPath === '' || str_contains($normalizedPath, '..')) {
            throw new DealerApplicationDocumentNotFoundException('Belge yolu geçersiz.');
        }

        $root = DealerApplicationDocumentDirectory::ROOT->value . '/';
        $candidates = [];

        $candidates[] = $normalizedPath;
        $candidates[] = ltrim((string) preg_replace('#^app/#', '', $normalizedPath), '/');

        if (!str_starts_with($normalizedPath, $root) && !str_contains($normalizedPath, '/')) {
            $candidates[] = $root . $normalizedPath;
        }

        $rootPos = mb_strpos($normalizedPath, DealerApplicationDocumentDirectory::ROOT->value);
        if ($rootPos !== false) {
            $candidates[] = mb_substr($normalizedPath, $rootPos);
        }

        $candidates = array_values(array_unique(array_filter($candidates)));

        foreach ($candidates as $candidate) {
            if (Storage::exists($candidate)) {
                return $candidate;
            }
        }

        throw new DealerApplicationDocumentNotFoundException('Belge bulunamadı.');
    }
}
