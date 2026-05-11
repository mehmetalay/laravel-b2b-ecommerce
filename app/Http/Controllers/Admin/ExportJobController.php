<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show(Request $request, ExportJob $exportJob): JsonResponse
    {
        $this->authorizeAccess($request, $exportJob);

        $downloadUrl = null;
        if ($exportJob->status === 'completed' && is_string($exportJob->file_path) && $exportJob->file_path !== '') {
            if (Storage::disk('local')->exists($exportJob->file_path)) {
                $downloadUrl = url('/admin/api/exports/' . $exportJob->id . '/download');
            }
        }

        return response()->json([
            'id' => $exportJob->id,
            'status' => $exportJob->status,
            'file_name' => $exportJob->file_name,
            'download_url' => $downloadUrl,
            'error' => $exportJob->error,
        ]);
    }

    public function download(Request $request, ExportJob $exportJob)
    {
        $this->authorizeAccess($request, $exportJob);

        if ($exportJob->status !== 'completed') {
            abort(404);
        }

        $path = (string) $exportJob->file_path;
        if ($path === '' || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $filename = $exportJob->file_name ?: basename($path);

        return Storage::disk('local')->download($path, $filename);
    }

    private function authorizeAccess(Request $request, ExportJob $exportJob): void
    {
        $currentAdminId = (int) ($request->user('admin')->id ?? 0);

        if ($currentAdminId <= 0) {
            abort(403);
        }

        if ($exportJob->user_type === null || $exportJob->user_id === null) {
            return;
        }

        if ($exportJob->user_type !== 'admin' || (int) $exportJob->user_id !== $currentAdminId) {
            abort(403);
        }
    }
}
