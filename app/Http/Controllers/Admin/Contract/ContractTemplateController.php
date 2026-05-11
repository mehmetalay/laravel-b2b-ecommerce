<?php

namespace App\Http\Controllers\Admin\Contract;

use App\Application\Contract\Services\ContractTemplateResolverService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContractTemplateRequest;
use App\Models\ContractTemplate;

class ContractTemplateController extends Controller
{
    public function __construct(
        private ContractTemplateResolverService $contractTemplateResolverService
    ) {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $templates = ContractTemplate::latest()->paginate(50);

        return view('admin.contract.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('admin.contract.templates.create');
    }

    public function store(ContractTemplateRequest $request)
    {
        if ($request->is_active) {
            ContractTemplate::where('dealer_type', $request->dealer_type)->update(['is_active' => 0]);
        }

        $data = $request->validated();
        $data['version'] = ContractTemplate::where('dealer_type', $request->dealer_type)->max('version') + 1;

        $template = ContractTemplate::create($data);
        $this->contractTemplateResolverService->clearTemplateCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Sozlesme basariyla olusturuldu.',
            'redirect' => route('admin.contracts.templates.edit', [$template->id]),
        ]);
    }

    public function edit(ContractTemplate $template)
    {
        return view('admin.contract.templates.edit', compact('template'));
    }

    public function show(ContractTemplate $template)
    {
        return $this->edit($template);
    }

    public function update(ContractTemplateRequest $request, ContractTemplate $template)
    {
        if ($request->is_active) {
            ContractTemplate::where('id', '!=', $template->id)
                ->where('dealer_type', $request->dealer_type)
                ->update(['is_active' => 0]);
        }

        $template->update($request->validated());
        $this->contractTemplateResolverService->clearTemplateCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Sozlesme guncellendi.',
        ]);
    }

    public function destroy(ContractTemplate $template)
    {
        $template->delete();
        $this->contractTemplateResolverService->clearTemplateCache();

        return response()->json([
            'status' => 'success',
            'message' => 'Sozlesme basariyla silindi.',
        ]);
    }
}
