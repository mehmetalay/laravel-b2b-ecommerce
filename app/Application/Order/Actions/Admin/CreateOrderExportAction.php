<?php

namespace App\Application\Order\Actions\Admin;

use App\Jobs\RunExportJob;
use App\Models\ExportJob;
use Illuminate\Http\Request;

class CreateOrderExportAction
{
    public function handle(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:xlsx,csv'],
            'scope' => ['required', 'in:filtered,selected'],
            'filters' => ['nullable', 'array'],
            'ids' => ['required_if:scope,selected', 'array'],
            'ids.*' => ['required_if:scope,selected', 'integer', 'exists:orders,id'],
        ]);

        $scope = (string) $validated['scope'];
        $selectedIds = $scope === 'selected'
            ? collect($validated['ids'] ?? [])->map(fn ($id) => (int) $id)->values()->all()
            : null;
        $filters = $scope === 'selected'
            ? []
            : (is_array($validated['filters'] ?? null) ? $validated['filters'] : []);
        $adminUser = $request->user('admin');

        $exportJob = ExportJob::query()->create([
            'user_type' => $adminUser ? 'admin' : null,
            'user_id' => $adminUser?->id,
            'type' => 'orders',
            'format' => (string) $validated['format'],
            'scope' => $scope,
            'filters' => $filters,
            'selected_ids' => $selectedIds,
            'status' => 'pending',
        ]);

        RunExportJob::dispatch((int) $exportJob->id);

        return response()->json([
            'success' => true,
            'message' => 'Dışa aktarma kuyruğa alındı',
            'export_job_id' => $exportJob->id,
        ]);
    }
}

